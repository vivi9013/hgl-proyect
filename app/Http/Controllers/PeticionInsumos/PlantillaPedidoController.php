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
use Illuminate\Validation\Rule;
use App\Models\Inventario\AreaAlmacen;
use App\Models\PeticionInsumos\AlmacenSubarea;

class PlantillaPedidoController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Vista principal: tabla de Plantillas de Pedido (1 fila = 1 plantilla).
     */
    public function index(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $idArea    = $request->get('id_area_abastecimiento', '');
        $idSubarea = $request->get('id_subarea_abastecimiento', '');
        $status    = $request->get('status', []);

        $query = PlantillaPedido::with([
                'areaAbastecimiento',
                'subareaAbastecimiento',
                'areaAlmacen',
                'detalles',
            ])
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

        $plantillas = $query->paginate(15)->withQueryString();

        // Para el modal de nueva plantilla y los filtros
        $todasAreas   = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $subareas     = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $areasAlmacen = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        $insumos      = Insumo::where('activo', 1)->orderBy('descripcion')->limit(200)->get();

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $plantillas,
            'peticion_insumos.plantillas_pedido.partials.tabla',
            compact('plantillas', 'buscar'),
            'plantillas de pedido'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('peticion_insumos.plantillas_pedido.index', compact(
            'plantillas', 'todasAreas', 'subareas', 'areasAlmacen', 'insumos',
            'buscar', 'idArea', 'idSubarea', 'status'
        ));
    }

    /**
     * Devuelve subáreas activas de un área (combo en cascada, JSON).
     */
    public function subareasPorArea(Request $request)
    {
        $idArea = $request->get('id_area_abastecimiento') ?: $request->get('id_area');

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
     * Registra una nueva plantilla de pedido.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('plantilla_pedidos', 'nombre')->where(function ($query) use ($request) {
                    return $query->where('id_area_abastecimiento', $request->id_area_abastecimiento);
                }),
            ],
            'id_area_abastecimiento' => [
                'required',
                'integer',
                'exists:areasabastecimiento,id_area_abastecimiento,activo,1',
            ],
            'id_subarea_abastecimiento' => [
                'nullable',
                'integer',
                'exists:subareas_abastecimiento,id_subarea_abastecimiento,activo,1',
            ],
            'id_area_almacen' => [
                'nullable',
                'integer',
                'exists:areas_almacen,id_area_almacen,activo,1',
            ],
        ], [
            'nombre.required'                 => 'El nombre de la plantilla es obligatorio.',
            'nombre.unique'                   => 'Ya existe una plantilla de pedido con este nombre en la misma área.',
            'id_area_abastecimiento.required' => 'Debe seleccionar un área de abastecimiento.',
            'id_area_abastecimiento.exists'   => 'El área seleccionada no es válida o está inactiva.',
            'id_subarea_abastecimiento.exists' => 'La subárea seleccionada no es válida o está inactiva.',
            'id_area_almacen.exists'          => 'El área de almacén seleccionada no es válida o está inactiva.',
        ]);

        // Nota sobre decisión de negocio (Opción A implementada):
        // Se corrigió el bug donde id_area_almacen guardaba equivocadamente el id_almacen_subarea de AlmacenSubarea.
        // Ahora id_area_almacen almacena la FK hacia AreaAlmacen (areas_almacen), permitiendo consultar
        // el stock y fondo fijo reales en InsumoArea. Si el negocio requiere la Opción B (desacoplar el almacén
        // de la plantilla y seleccionarlo sólo al crear el pedido), se deberá remover id_area_almacen de las plantillas.
        PlantillaPedido::create([
            'nombre'                    => trim($request->nombre),
            'descripcion'               => $request->filled('descripcion') ? trim($request->descripcion) : null,
            'id_area_abastecimiento'    => $request->id_area_abastecimiento,
            'id_subarea_abastecimiento' => $request->filled('id_subarea_abastecimiento') ? $request->id_subarea_abastecimiento : null,
            'id_area_almacen'           => $request->filled('id_area_almacen') ? $request->id_area_almacen : null,
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
     * Actualiza nombre, área y subárea de una plantilla existente.
     */
    public function actualizar(Request $request, $id)
    {
        $plantilla = PlantillaPedido::findOrFail($id);

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('plantilla_pedidos', 'nombre')
                    ->ignore($plantilla->id_plantilla_pedido, 'id_plantilla_pedido')
                    ->where(fn($q) => $q->where('id_area_abastecimiento', $request->id_area_abastecimiento)),
            ],
            'id_area_abastecimiento' => [
                'required',
                'integer',
                'exists:areasabastecimiento,id_area_abastecimiento,activo,1',
            ],
            'id_subarea_abastecimiento' => [
                'nullable',
                'integer',
                'exists:subareas_abastecimiento,id_subarea_abastecimiento,activo,1',
            ],
            'id_area_almacen' => [
                'nullable',
                'integer',
                'exists:areas_almacen,id_area_almacen,activo,1',
            ],
        ], [
            'nombre.required'                 => 'El nombre de la plantilla es obligatorio.',
            'nombre.unique'                   => 'Ya existe una plantilla con ese nombre en la misma área.',
            'id_area_abastecimiento.required' => 'Debe seleccionar un área de abastecimiento.',
            'id_area_almacen.exists'          => 'El área de almacén seleccionada no es válida o está inactiva.',
        ]);

        $plantilla->update([
            'nombre'                    => trim($request->nombre),
            'descripcion'               => $request->filled('descripcion') ? trim($request->descripcion) : null,
            'id_area_abastecimiento'    => $request->id_area_abastecimiento,
            'id_subarea_abastecimiento' => $request->filled('id_subarea_abastecimiento') ? $request->id_subarea_abastecimiento : null,
            'id_area_almacen'           => $request->filled('id_area_almacen') ? $request->id_area_almacen : null,
        ]);

        return redirect()
            ->route('plantillas_pedido.index')
            ->with('exitog', 'Plantilla actualizada correctamente.');
    }

    /**
     * Página dedicada: lista todos los insumos con toggle Si/No y cantidad por plantilla.
     */
    public function editarInsumos(Request $request, $id)
    {
        $plantilla = PlantillaPedido::with([
            'areaAbastecimiento',
            'subareaAbastecimiento',
            'detalles',
        ])->findOrFail($id);

        $buscarInsumo = $request->get('buscar', '');

        $query = Insumo::where('activo', 1)->orderBy('descripcion');

        if (!empty($buscarInsumo)) {
            $query->where(function ($q) use ($buscarInsumo) {
                $q->where('descripcion', 'LIKE', "%{$buscarInsumo}%")
                  ->orWhere('clave', 'LIKE', "%{$buscarInsumo}%");
            });
        }

        $insumos = $query->paginate(15)->withQueryString();

        // Mapa id_insumo → detalle para saber cuáles ya están en la plantilla
        $detallesMap = $plantilla->detalles->keyBy('id_insumo');

        return view('peticion_insumos.plantillas_pedido.insumos', compact(
            'plantilla', 'insumos', 'detallesMap', 'buscarInsumo'
        ));
    }

    /**
     * Guarda en lote los cambios de insumos: inserta nuevos, actualiza cantidades,
     * elimina los marcados como No.
     */
    public function guardarInsumos(Request $request, $id)
    {
        $plantilla = PlantillaPedido::findOrFail($id);

        $incluidos  = $request->input('incluido', []);   // ['id_insumo' => '1' | '0']
        $cantidades = $request->input('cantidad', []);   // ['id_insumo' => cantidad]

        foreach ($incluidos as $idInsumo => $valor) {
            $insumo = Insumo::find($idInsumo);
            if (!$insumo) continue;

            $detalle = DetallePlantillaPedido::where('id_plantilla_pedido', $id)
                ->where('id_insumo', $idInsumo)
                ->first();

            if ($valor == '1') {
                $cantidad = max(1, (int) ($cantidades[$idInsumo] ?? 1));

                if ($detalle) {
                    $detalle->update(['cantidad' => $cantidad]);
                } else {
                    DetallePlantillaPedido::create([
                        'id_plantilla_pedido' => $id,
                        'id_insumo'           => $idInsumo,
                        'cve_insumo'          => $insumo->clave ?? '',
                        'cantidad'            => $cantidad,
                    ]);
                }
            } else {
                // Marcado como "No" → eliminar si existía
                if ($detalle) {
                    $detalle->delete();
                }
            }
        }

        return redirect()
            ->route('plantillas_pedido.insumos', $id)
            ->with('exitog', 'Insumos de la plantilla actualizados correctamente.');
    }

    /**
     * Agrega un insumo con cantidad prestablecida a la plantilla (vía AJAX modal).
     */
    public function agregarInsumo(Request $request, $id)
    {
        $plantilla = PlantillaPedido::findOrFail($id);

        $request->validate([
            'id_insumo' => [
                'required',
                'integer',
                'exists:insumos,id_insumo,activo,1',
            ],
            'cantidad'  => 'required|integer|min:1',
        ], [
            'id_insumo.required' => 'Debe seleccionar un insumo.',
            'id_insumo.exists'   => 'El insumo seleccionado no es válido o no está activo.',
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
     * Elimina una plantilla y todos sus detalles.
     */
    public function eliminar($id)
    {
        $plantilla = PlantillaPedido::findOrFail($id);
        $plantilla->detalles()->delete();
        $plantilla->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Plantilla eliminada correctamente.',
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

    /**
     * Genera el reporte PDF/impresión individual para una plantilla de pedido específica.
     */
    public function imprimirIndividual($id)
    {
        $plantilla = PlantillaPedido::with(['areaAbastecimiento', 'subareaAbastecimiento', 'detalles.insumo'])
            ->findOrFail($id);

        return view('peticion_insumos.plantillas_pedido.analitica.reportes.impresion_individual', compact('plantilla'));
    }
}
