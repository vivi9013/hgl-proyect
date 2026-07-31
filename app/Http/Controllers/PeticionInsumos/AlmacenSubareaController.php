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

        // ── Comportamiento legacy: sin filtro de área/subárea, no mostrar registros ──
        $sinFiltro = empty($idArea) && empty($idSubarea) && empty($buscar);

        if ($sinFiltro && !$request->ajax()) {
            // Vista inicial vacía — el usuario debe seleccionar área/subárea
            $areas    = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
            $subareas = collect();
            $insumos  = Insumo::where('activo', 1)->orderBy('descripcion')->limit(100)->get();
            $almacenes = AlmacenSubarea::whereNull('id_almacen_subarea')->paginate(10); // vacío
            return view('peticion_insumos.almacen_subareas.index', compact(
                'almacenes', 'areas', 'subareas', 'insumos', 'buscar', 'idArea', 'idSubarea'
            ));
        }

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
                ->orWhereHas('detalles.insumo', function ($iq) use ($buscar) {
                    $iq->where('clave', 'LIKE', "%{$buscar}%")
                       ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
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
        $subareas = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $insumos  = Insumo::where('activo', 1)->orderBy('descripcion')->limit(100)->get();

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $almacenes,
            'peticion_insumos.almacen_subareas.partials.tabla',
            compact('almacenes'),
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

        $subareas = SubareaAbastecimiento::where('activo', 1)
            ->orderBy('nombre')
            ->get(['id_subarea_abastecimiento', 'nombre', 'siglas']);

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

        $almacen = AlmacenSubarea::create([
            'id_area_abastecimiento'    => $request->id_area_abastecimiento,
            'id_subarea_abastecimiento' => $request->id_subarea_abastecimiento,
            'fecha_registro'            => now()->toDateString(),
            'hora_registro'             => now()->toTimeString(),
            'activo'                    => 1,
            'id_usuario'                => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('almacen_subareas.index')
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
            $detalleExistente->update([
                'cantidad'   => $detalleExistente->cantidad + $request->cantidad,
                'fondo_fijo' => $request->fondo_fijo,
            ]);
        } else {
            DetalleAlmacenSubarea::create([
                'id_almacen_subarea' => $id,
                'id_insumo'          => $insumo->id_insumo,
                'cve_insumo'         => $insumo->clave ?? '',
                'cantidad'          => $request->cantidad,
                'fondo_fijo'        => $request->fondo_fijo,
            ]);
        }

        return redirect()
            ->route('almacen_subareas.index')
            ->with('exitog', 'El insumo se ha asignado al almacén de la subárea correctamente.');
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

    /**
     * Muestra las gráficas estadísticas del almacén de subáreas con Chart.js.
     */
    public function graficas()
    {
        $totalActivos = AlmacenSubarea::where('activo', 1)->count();
        $totalInactivos = AlmacenSubarea::where('activo', 0)->count();

        // Top 10 subáreas por total de insumos registrados
        $porSubarea = DB::table('almacen_subareas as a')
            ->join('subareas_abastecimiento as s', 'a.id_subarea_abastecimiento', '=', 's.id_subarea_abastecimiento')
            ->leftJoin('detalle_almacen_subareas as d', 'a.id_almacen_subarea', '=', 'd.id_almacen_subarea')
            ->select('s.nombre as label', DB::raw('count(d.id_detalle_almacen_subarea) as total'))
            ->groupBy('a.id_almacen_subarea', 's.nombre')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Top insumos con menor stock relativo a su fondo fijo
        $insumosBajos = DB::table('detalle_almacen_subareas as d')
            ->join('insumos as i', 'd.id_insumo', '=', 'i.id_insumo')
            ->select('i.descripcion as label', 'd.cantidad', 'd.fondo_fijo')
            ->where('d.cantidad', '<', DB::raw('d.fondo_fijo'))
            ->limit(10)
            ->get();

        return view('peticion_insumos.almacen_subareas.analitica.graficas', compact(
            'totalActivos',
            'totalInactivos',
            'porSubarea',
            'insumosBajos'
        ));
    }
}
