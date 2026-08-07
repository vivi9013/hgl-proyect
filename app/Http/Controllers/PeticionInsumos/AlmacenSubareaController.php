<?php

namespace App\Http\Controllers\PeticionInsumos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondeTablaAjax;
use App\Models\PeticionInsumos\AlmacenSubarea;
use App\Models\PeticionInsumos\DetalleAlmacenSubarea;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlmacenSubareaController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Muestra el listado de almacenes de subáreas con búsqueda, filtros y paginación.
     */
    public function index(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $idArea    = $request->get('id_area_abastecimiento', '');
        $idSubarea = $request->get('id_subarea_abastecimiento', '');
        $status    = $request->get('status', '');

        $query = AlmacenSubarea::with(['areaAbastecimiento', 'subareaAbastecimiento', 'detalles.insumo'])
            ->orderBy('id_almacen_subarea', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('areaAbastecimiento', function ($aq) use ($buscar) {
                    $aq->where('nombre', 'LIKE', "%{$buscar}%");
                })
                ->orWhereHas('subareaAbastecimiento', function ($sq) use ($buscar) {
                    $sq->where('nombre', 'LIKE', "%{$buscar}%")
                       ->orWhere('siglas', 'LIKE', "%{$buscar}%");
                })
                ->orWhereHas('detalles', function ($dq) use ($buscar) {
                    $dq->where('cve_insumo', 'LIKE', "%{$buscar}%")
                       ->orWhereHas('insumo', function ($iq) use ($buscar) {
                           $iq->where('clave', 'LIKE', "%{$buscar}%")
                              ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
                       });
                });
            });
        }

        if (!empty($idArea)) {
            $query->where('id_area_abastecimiento', $idArea);
        }

        if (!empty($idSubarea)) {
            $query->where('id_subarea_abastecimiento', $idSubarea);
        }

        $this->aplicarFiltroEstatus($query, $status);

        $almacenes = $query->paginate(10)->withQueryString();

        $areas    = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $subareas = empty($idArea)
            ? collect()
            : SubareaAbastecimiento::whereHas('relacionArea', fn($rq) => $rq->where('id_area_abastecimiento', $idArea))->where('activo', 1)->orderBy('nombre')->get();
        $insumos  = Insumo::where('activo', 1)->orderBy('descripcion')->limit(100)->get();

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $almacenes,
            'peticion_insumos.almacen_subareas.partials.tabla',
            compact('almacenes', 'buscar'),
            'almacenes de subáreas'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('peticion_insumos.almacen_subareas.index', compact(
            'almacenes',
            'areas',
            'subareas',
            'insumos',
            'buscar',
            'idArea',
            'idSubarea'
        ));
    }

    /**
     * Devuelve las subáreas activas de un área dada (para el combo en cascada).
     */
    public function subareasPorArea(Request $request)
    {
        $idArea = $request->get('id_area_abastecimiento', '');

        $query = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre');

        if (!empty($idArea)) {
            $query->whereHas('relacionArea', function ($rq) use ($idArea) {
                $rq->where('id_area_abastecimiento', $idArea);
            });
        }

        $subareas = $query->get(['id_subarea_abastecimiento', 'nombre', 'siglas']);

        return response()->json($subareas);
    }

    /**
     * Registra una nueva asignación de almacén a subárea.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_area_abastecimiento'    => 'required|integer|exists:areasabastecimiento,id_area_abastecimiento',
            'id_subarea_abastecimiento' => 'required|integer|exists:subareas_abastecimiento,id_subarea_abastecimiento',
        ], [
            'id_area_abastecimiento.required'    => 'Debe seleccionar un área de abastecimiento.',
            'id_subarea_abastecimiento.required' => 'Debe seleccionar una subárea.',
        ]);

        $existe = AlmacenSubarea::where('id_area_abastecimiento', $request->id_area_abastecimiento)
            ->where('id_subarea_abastecimiento', $request->id_subarea_abastecimiento)
            ->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['id_subarea_abastecimiento' => 'Este almacén de subárea ya está registrado.'])
                ->with('hasFormErrors', true);
        }

        $idUsuario = Auth::id();
        if (!$idUsuario) {
            abort(401, 'Usuario no autenticado.');
        }

        $almacen = AlmacenSubarea::create([
            'id_area_abastecimiento'    => $request->id_area_abastecimiento,
            'id_subarea_abastecimiento' => $request->id_subarea_abastecimiento,
            'fecha_registro'            => now()->toDateString(),
            'hora_registro'             => now()->toTimeString(),
            'activo'                    => 1,
            'id_usuario'                => $idUsuario,
        ]);

        return redirect()
            ->route('almacen_subareas.index', [
                'id_area_abastecimiento'    => $almacen->id_area_abastecimiento,
                'id_subarea_abastecimiento' => $almacen->id_subarea_abastecimiento,
            ])
            ->with('exitog', 'El almacén de la subárea se ha registrado correctamente.');
    }

    /**
     * Agrega un insumo con stock y fondo fijo al almacén de la subárea.
     */
    public function agregarInsumo(Request $request, $id)
    {
        $almacen = AlmacenSubarea::findOrFail($id);

        $request->validate([
            'id_insumo'  => 'required|integer|exists:insumos,id_insumo',
            'cantidad'   => 'required|integer|min:0',
            'fondo_fijo' => 'required|integer|min:0',
        ], [
            'id_insumo.required'  => 'Debe seleccionar un insumo.',
            'cantidad.required'   => 'Debe indicar la cantidad inicial.',
            'fondo_fijo.required' => 'Debe establecer el fondo fijo.',
        ]);

        $insumo = Insumo::findOrFail($request->id_insumo);

        $detalleExistente = DetalleAlmacenSubarea::where('id_almacen_subarea', $id)
            ->where('id_insumo', $insumo->id_insumo)
            ->first();

        if ($detalleExistente) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'id_insumo' => "El insumo [{$insumo->clave}] {$insumo->descripcion} ya está asignado a esta subárea (Stock actual: {$detalleExistente->cantidad}, Fondo fijo: {$detalleExistente->fondo_fijo}). Si deseas modificarlo, usa los botones de edición en la tabla.",
                ]);
        }

        DetalleAlmacenSubarea::create([
            'id_almacen_subarea' => $id,
            'id_insumo'          => $insumo->id_insumo,
            'cve_insumo'         => $insumo->clave ?? '',
            'cantidad'           => $request->cantidad,
            'fondo_fijo'         => $request->fondo_fijo,
        ]);
        $mensaje = "El insumo '{$insumo->descripcion}' se asignó correctamente a la subárea.";

        return redirect()
            ->route('almacen_subareas.index', [
                'id_area_abastecimiento'    => $almacen->id_area_abastecimiento,
                'id_subarea_abastecimiento' => $almacen->id_subarea_abastecimiento,
                'buscar'                    => $insumo->clave ?: $insumo->descripcion,
            ])
            ->with('exitog', $mensaje);
    }

    /**
     * Actualiza el stock o fondo fijo de un detalle.
     */
    public function actualizarDetalle(Request $request, $idDetalle)
    {
        $detalle = DetalleAlmacenSubarea::findOrFail($idDetalle);

        $request->validate([
            'cantidad'   => 'required|integer|min:0',
            'fondo_fijo' => 'required|integer|min:0',
        ]);

        $detalle->update([
            'cantidad'   => $request->cantidad,
            'fondo_fijo' => $request->fondo_fijo,
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Stock y Fondo Fijo actualizados correctamente.',
        ]);
    }

    /**
     * Elimina un insumo del almacén de la subárea.
     */
    public function eliminarDetalle($idDetalle)
    {
        $detalle = DetalleAlmacenSubarea::findOrFail($idDetalle);
        $detalle->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Insumo eliminado del almacén de la subárea.',
        ]);
    }

    /**
     * Alterna el estado activo/inactivo del almacén de subárea.
     */
    public function cambiarStatus($id)
    {
        $almacen = AlmacenSubarea::findOrFail($id);
        $almacen->activo = $almacen->activo == 1 ? 0 : 1;
        $almacen->save();

        return response()->json([
            'success' => true,
            'activo'  => $almacen->activo,
            'mensaje' => 'Estado del almacén de la subárea actualizado con éxito.',
        ]);
    }

    /**
     * Vista de configuración de reportes.
     */
    public function reportes(Request $request)
    {
        $areas = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $subareas = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();

        return view('peticion_insumos.almacen_subareas.analitica.reportes.index', compact('areas', 'subareas'));
    }

    /**
     * Genera el reporte de impresión oficial.
     */
    public function imprimir(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $idArea    = $request->get('id_area_abastecimiento', '');
        $idSubarea = $request->get('id_subarea_abastecimiento', '');
        $status    = $request->get('status', '');

        $query = AlmacenSubarea::with(['areaAbastecimiento', 'subareaAbastecimiento', 'detalles.insumo'])
            ->orderBy('id_almacen_subarea', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('areaAbastecimiento', function ($aq) use ($buscar) {
                    $aq->where('nombre', 'LIKE', "%{$buscar}%");
                })
                ->orWhereHas('subareaAbastecimiento', function ($sq) use ($buscar) {
                    $sq->where('nombre', 'LIKE', "%{$buscar}%");
                });
            });
        }

        if (!empty($idArea)) {
            $query->where('id_area_abastecimiento', $idArea);
        }

        if (!empty($idSubarea)) {
            $query->where('id_subarea_abastecimiento', $idSubarea);
        }

        if ($status !== '' && $status !== null) {
            $query->where('activo', $status);
        }

        $almacenes = $query->get();

        return view('peticion_insumos.almacen_subareas.analitica.reportes.impresion', compact('almacenes'));
    }

}
