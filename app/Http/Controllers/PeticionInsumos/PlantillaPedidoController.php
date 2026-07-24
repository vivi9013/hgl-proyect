<?php

namespace App\Http\Controllers\PeticionInsumos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondeTablaAjax;
use App\Models\PeticionInsumos\PlantillaPedido;
use App\Models\PeticionInsumos\DetallePlantillaPedido;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlantillaPedidoController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Muestra el listado de plantillas de pedido con búsqueda, filtros y paginación AJAX.
     */
    public function index(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $idArea    = $request->get('id_area_abastecimiento', '');
        $idSubarea = $request->get('id_subarea_abastecimiento', '');
        $status    = $request->get('status', '');

        $query = PlantillaPedido::with(['areaAbastecimiento', 'subareaAbastecimiento', 'detalles.insumo'])
            ->orderBy('id_plantilla_pedido', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAbastecimiento', fn($aq) => $aq->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('subareaAbastecimiento', fn($sq) => $sq->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('detalles.insumo', fn($iq) =>
                      $iq->where('clave', 'LIKE', "%{$buscar}%")
                         ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                  );
            });
        }

        if (!empty($idArea)) {
            $query->where('id_area_abastecimiento', $idArea);
        }

        if (!empty($idSubarea)) {
            $query->where('id_subarea_abastecimiento', $idSubarea);
        }

        if (!empty($status)) {
            $statusArray = is_array($status) ? $status : explode(',', $status);
            $statusInts  = array_map(fn($v) => $v === 'Activo' ? 1 : 0, $statusArray);
            $query->whereIn('activo', $statusInts);
        }

        $plantillas = $query->paginate(10)->withQueryString();
        $areas      = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $subareas   = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $insumos    = Insumo::where('activo', 1)->orderBy('descripcion')->limit(200)->get();

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $plantillas,
            'peticion_insumos.plantillas_pedido.partials.tabla',
            compact('plantillas'),
            'plantillas de pedido'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('peticion_insumos.plantillas_pedido.index', compact(
            'plantillas', 'areas', 'subareas', 'insumos', 'buscar', 'idArea', 'idSubarea'
        ));
    }

    /**
     * Devuelve subáreas activas de un área (combo en cascada, JSON).
     */
    public function subareasPorArea(Request $request)
    {
        $subareas = SubareaAbastecimiento::where('activo', 1)
            ->orderBy('nombre')
            ->get(['id_subarea_abastecimiento', 'nombre', 'siglas']);

        return response()->json($subareas);
    }

    /**
     * Registra una nueva plantilla de pedido.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre'                   => 'required|string|max:150',
            'id_area_abastecimiento'   => 'required|integer',
            'id_subarea_abastecimiento' => 'nullable|integer',
        ], [
            'nombre.required'                 => 'El nombre de la plantilla es obligatorio.',
            'id_area_abastecimiento.required' => 'Debe seleccionar un área de abastecimiento.',
        ]);

        PlantillaPedido::create([
            'nombre'                    => trim($request->nombre),
            'descripcion'               => $request->filled('descripcion') ? trim($request->descripcion) : null,
            'id_area_abastecimiento'    => $request->id_area_abastecimiento,
            'id_subarea_abastecimiento' => $request->filled('id_subarea_abastecimiento') ? $request->id_subarea_abastecimiento : null,
            'fecha_registro'            => now()->toDateString(),
            'hora_registro'             => now()->toTimeString(),
            'activo'                    => 1,
            'id_usuario'                => Auth::id(),
        ]);

        return redirect()
            ->route('plantillas_pedido.index')
            ->with('exitog', 'Plantilla de pedido registrada correctamente.');
    }

    /**
     * Agrega un insumo con cantidad prestablecida a la plantilla.
     */
    public function agregarInsumo(Request $request, $id)
    {
        $plantilla = PlantillaPedido::findOrFail($id);

        $request->validate([
            'id_insumo' => 'required|integer',
            'cantidad'  => 'required|integer|min:1',
        ], [
            'id_insumo.required' => 'Debe seleccionar un insumo.',
            'cantidad.required'  => 'Debe indicar la cantidad prestablecida.',
            'cantidad.min'       => 'La cantidad debe ser al menos 1.',
        ]);

        $insumo = Insumo::findOrFail($request->id_insumo);

        $detalleExistente = DetallePlantillaPedido::where('id_plantilla_pedido', $id)
            ->where('id_insumo', $insumo->id_insumo)
            ->first();

        if ($detalleExistente) {
            $detalleExistente->update(['cantidad' => $request->cantidad]);
        } else {
            DetallePlantillaPedido::create([
                'id_plantilla_pedido' => $id,
                'id_insumo'           => $insumo->id_insumo,
                'cve_insumo'          => $insumo->clave ?? '',
                'cantidad'            => $request->cantidad,
            ]);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Insumo asignado a la plantilla correctamente.',
        ]);
    }

    /**
     * Actualiza la cantidad de un detalle de plantilla.
     */
    public function actualizarDetalle(Request $request, $idDetalle)
    {
        $detalle = DetallePlantillaPedido::findOrFail($idDetalle);

        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $detalle->update(['cantidad' => $request->cantidad]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Cantidad actualizada correctamente.',
        ]);
    }

    /**
     * Elimina un insumo de la plantilla.
     */
    public function eliminarDetalle($idDetalle)
    {
        $detalle = DetallePlantillaPedido::findOrFail($idDetalle);
        $detalle->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Insumo eliminado de la plantilla.',
        ]);
    }

    /**
     * Alterna el estado activo/inactivo de una plantilla.
     */
    public function cambiarStatus($id)
    {
        $plantilla = PlantillaPedido::findOrFail($id);
        $plantilla->activo = $plantilla->activo == 1 ? 0 : 1;
        $plantilla->save();

        return response()->json([
            'success' => true,
            'activo'  => $plantilla->activo,
            'mensaje' => 'Estado de la plantilla actualizado correctamente.',
        ]);
    }

    /**
     * Vista de configuración de reportes.
     */
    public function reportes()
    {
        $areas    = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $subareas = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();

        return view('peticion_insumos.plantillas_pedido.analitica.reportes.index', compact('areas', 'subareas'));
    }

    /**
     * Genera la vista de impresión oficial.
     */
    public function imprimir(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $idArea    = $request->get('id_area_abastecimiento', '');
        $idSubarea = $request->get('id_subarea_abastecimiento', '');
        $status    = $request->get('status', '');

        $query = PlantillaPedido::with(['areaAbastecimiento', 'subareaAbastecimiento', 'detalles.insumo'])
            ->orderBy('nombre');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAbastecimiento', fn($aq) => $aq->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('subareaAbastecimiento', fn($sq) => $sq->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if (!empty($idArea)) {
            $query->where('id_area_abastecimiento', $idArea);
        }

        if (!empty($idSubarea)) {
            $query->where('id_subarea_abastecimiento', $idSubarea);
        }

        if (!empty($status)) {
            $statusArray = is_array($status) ? $status : explode(',', $status);
            $statusInts  = array_map(fn($v) => $v === 'Activo' ? 1 : 0, $statusArray);
            $query->whereIn('activo', $statusInts);
        }

        $plantillas = $query->get();

        return view('peticion_insumos.plantillas_pedido.analitica.reportes.impresion', compact('plantillas'));
    }
}
