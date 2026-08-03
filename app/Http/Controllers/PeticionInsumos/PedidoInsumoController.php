<?php

namespace App\Http\Controllers\PeticionInsumos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondeTablaAjax;
use App\Models\Inventario\Pedido;
use App\Models\Inventario\DetallePedido;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\AreaAlmacen;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use App\Models\PeticionInsumos\PlantillaPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PedidoInsumoController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Muestra el listado de pedidos de insumos con búsqueda, filtros por estado, fecha y paginación AJAX.
     */
    public function index(Request $request)
    {
        $buscar     = trim($request->get('buscar', ''));
        $status     = $request->get('status', '');
        $fechaInit  = $request->get('fecha_inicio', '');
        $fechaFin   = $request->get('fecha_fin', '');

        $query = Pedido::with(['areaAbastecimiento', 'subareaAbastecimiento', 'areaAlmacen', 'usuario.persona', 'detalles.insumo'])
            ->orderBy('id_pedido', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_pedido', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAbastecimiento', fn($aq) => $aq->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('subareaAbastecimiento', fn($sq) => $sq->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('areaAlmacen', fn($alq) => $alq->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('usuario.persona', fn($uq) =>
                      $uq->where('nombre', 'LIKE', "%{$buscar}%")
                         ->orWhere('ap_paterno', 'LIKE', "%{$buscar}%")
                  )
                  ->orWhereHas('detalles.insumo', fn($iq) =>
                      $iq->where('clave', 'LIKE', "%{$buscar}%")
                         ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                  );
            });
        }

        if (!empty($status)) {
            $statusArray = is_array($status) ? $status : explode(',', $status);
            $query->whereIn('status', $statusArray);
        }

        if (!empty($fechaInit)) {
            $query->whereDate('fecha_registro', '>=', $fechaInit);
        }

        if (!empty($fechaFin)) {
            $query->whereDate('fecha_registro', '<=', $fechaFin);
        }

        $pedidos = $query->paginate(10)->withQueryString();

        $areas      = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $subareas   = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $almacenes  = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        $plantillas = PlantillaPedido::where('activo', 1)->orderBy('nombre')->get();

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $pedidos,
            'peticion_insumos.pedido_insumos.partials.tabla',
            compact('pedidos'),
            'pedidos de insumos'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('peticion_insumos.pedido_insumos.index', compact(
            'pedidos', 'areas', 'subareas', 'almacenes', 'plantillas', 'buscar', 'status', 'fechaInit', 'fechaFin'
        ));
    }

    /**
     * Obtiene las subáreas activas pertenecientes a un área de abastecimiento.
     */
    public function subareasPorArea(Request $request)
    {
        $idArea = $request->get('id_area_abastecimiento');
        
        $subareas = SubareaAbastecimiento::where('activo', 1)
            ->when($idArea, fn($q) => $q->whereHas('relacionArea', fn($rq) => $rq->where('id_area_abastecimiento', $idArea)))
            ->orderBy('nombre')
            ->get(['id_subarea_abastecimiento', 'nombre', 'siglas']);

        return response()->json($subareas);
    }

    /**
     * Autocompletado AJAX para buscar insumos por clave o descripción.
     */
    public function autocompletarInsumo(Request $request)
    {
        $term = trim($request->get('term', ''));
        $idAreaAlmacen = $request->get('id_area_almacen', null);

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $insumos = Insumo::where('activo', 1)
            ->where(function ($q) use ($term) {
                $q->where('clave', 'LIKE', "%{$term}%")
                  ->orWhere('descripcion', 'LIKE', "%{$term}%");
            })
            ->orderBy('descripcion')
            ->limit(20)
            ->get();

        $resultado = $insumos->map(function ($insumo) use ($idAreaAlmacen) {
            $existencia = 0;
            $fondoFijo  = 0;

            if ($idAreaAlmacen) {
                $insumoArea = InsumoArea::where('id_insumo', $insumo->id_insumo)
                    ->where('id_area_almacen', $idAreaAlmacen)
                    ->first();
                if ($insumoArea) {
                    $existencia = $insumoArea->stock;
                    $fondoFijo  = $insumoArea->fondo_fijo;
                }
            }

            return [
                'id_insumo'   => $insumo->id_insumo,
                'clave'       => $insumo->clave,
                'descripcion' => $insumo->descripcion,
                'tipo'        => $insumo->tipo,
                'existencia'  => $existencia,
                'fondo_fijo'  => $fondoFijo,
                'text'        => "[{$insumo->clave}] {$insumo->descripcion}",
            ];
        });

        return response()->json($resultado);
    }

    /**
     * Carga los insumos y detalles pertenecientes a una plantilla de pedido seleccionada.
     */
    public function insumosPlantilla(int $idPlantilla)
    {
        $plantilla = PlantillaPedido::with('detalles.insumo')->find($idPlantilla);

        if (!$plantilla) {
            return response()->json(['error' => 'Plantilla no encontrada'], 404);
        }

        $insumos = $plantilla->detalles->map(function ($det) use ($plantilla) {
            $existencia = 0;
            $fondoFijo  = 0;

            if ($plantilla->id_area_almacen && $det->insumo) {
                $insumoArea = InsumoArea::where('id_insumo', $det->insumo->id_insumo)
                    ->where('id_area_almacen', $plantilla->id_area_almacen)
                    ->first();
                if ($insumoArea) {
                    $existencia = $insumoArea->stock;
                    $fondoFijo  = $insumoArea->fondo_fijo;
                }
            }

            return [
                'id_insumo'   => $det->id_insumo,
                'clave'       => $det->cve_insumo ?: ($det->insumo ? $det->insumo->clave : ''),
                'descripcion' => $det->insumo ? $det->insumo->descripcion : 'N/A',
                'cantidad'    => $det->cantidad,
                'existencia'  => $existencia,
                'fondo_fijo'  => $fondoFijo,
            ];
        });

        return response()->json([
            'id_area_abastecimiento'   => $plantilla->id_area_abastecimiento,
            'id_subarea_abastecimiento'=> $plantilla->id_subarea_abastecimiento,
            'id_area_almacen'          => $plantilla->id_area_almacen,
            'insumos'                  => $insumos,
        ]);
    }

    /**
     * Guarda un nuevo pedido de insumos (como 'borrador' o 'terminado' [enviado a CENDIS]).
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_area_abastecimiento'    => 'required|integer|exists:areas_abastecimiento,id_area_abastecimiento,activo,1',
            'id_subarea_abastecimiento' => 'nullable|integer|exists:subareas_abastecimiento,id_subarea_abastecimiento,activo,1',
            'id_area_almacen'           => 'required|integer|exists:areas_almacen,id_area_almacen,activo,1',
            'status'                    => 'required|in:borrador,terminado',
            'insumos'                   => 'required|array|min:1',
            'insumos.*.id_insumo'       => 'required|integer|exists:insumos,id_insumo,activo,1',
            'insumos.*.cantidad'        => 'required|integer|min:1',
        ], [
            'id_area_abastecimiento.required' => 'Debe seleccionar un área de abastecimiento.',
            'id_area_abastecimiento.exists'   => 'El área de abastecimiento seleccionada no existe o está inactiva.',
            'id_subarea_abastecimiento.exists' => 'La subárea de abastecimiento seleccionada no existe o está inactiva.',
            'id_area_almacen.required'        => 'Debe seleccionar un área de almacén.',
            'id_area_almacen.exists'          => 'El área de almacén seleccionada no existe o está inactiva.',
            'insumos.required'                => 'Debe agregar al menos un insumo al pedido.',
            'insumos.min'                     => 'Debe agregar al menos un insumo al pedido.',
            'insumos.*.id_insumo.exists'      => 'Uno o más insumos del pedido no existen o están inactivos.',
        ]);

        try {
            DB::beginTransaction();

            $now = Carbon::now();

            $pedido = Pedido::create([
                'id_area_abastecimiento'   => $request->id_area_abastecimiento,
                'id_subarea_abastecimiento'=> $request->id_subarea_abastecimiento ?: null,
                'id_area_almacen'          => $request->id_area_almacen,
                'fecha_registro'           => $now->toDateString(),
                'hora_registro'            => $now->toTimeString(),
                'status'                   => $request->status, // 'borrador' o 'terminado' (enviado)
                'activo'                   => 1,
                'id_usuario'               => Auth::id() ?? abort(500, 'Usuario no autenticado.'),
                'porcentaje_entrega'       => 0.00,
            ]);

            // Deduplicar insumos: si el frontend envía el mismo id_insumo más de una vez,
            // se agrupan sumando sus cantidades para evitar duplicados en detalle_pedidos.
            $insumosAgrupados = [];
            foreach ($request->insumos as $item) {
                $idInsumo = (int) $item['id_insumo'];
                if (isset($insumosAgrupados[$idInsumo])) {
                    $insumosAgrupados[$idInsumo]['cantidad'] += (int) $item['cantidad'];
                } else {
                    $insumosAgrupados[$idInsumo] = [
                        'id_insumo' => $idInsumo,
                        'cantidad'  => (int) $item['cantidad'],
                        'cve_insumo' => $item['cve_insumo'] ?? null,
                    ];
                }
            }

            foreach ($insumosAgrupados as $item) {
                $insumo = Insumo::find($item['id_insumo']);

                $existencia = 0;
                $fondoFijo  = 0;

                $insumoArea = InsumoArea::where('id_insumo', $item['id_insumo'])
                    ->where('id_area_almacen', $request->id_area_almacen)
                    ->first();

                if ($insumoArea) {
                    $existencia = $insumoArea->stock;
                    $fondoFijo  = $insumoArea->fondo_fijo;
                }

                DetallePedido::create([
                    'id_pedido'  => $pedido->id_pedido,
                    'id_insumo'  => $item['id_insumo'],
                    'cve_insumo' => $insumo ? $insumo->clave : $item['cve_insumo'],
                    'cantidad'   => $item['cantidad'],
                    'existencia' => $existencia,
                    'fondo_fijo' => $fondoFijo,
                    'surtido'    => 0,
                    'faltante'   => $item['cantidad'],
                ]);
            }

            DB::commit();

            $msg = $request->status === 'terminado' 
                ? "El pedido #{$pedido->id_pedido} ha sido enviado exitosamente a CENDIS."
                : "El pedido #{$pedido->id_pedido} ha sido guardado como borrador.";

            return response()->json([
                'success' => true,
                'message' => $msg,
                'id_pedido' => $pedido->id_pedido
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna el detalle completo de un pedido en JSON.
     */
    public function detalle(int $id)
    {
        $pedido = Pedido::with([
            'areaAbastecimiento',
            'subareaAbastecimiento',
            'areaAlmacen',
            'usuario.persona',
            'detalles.insumo'
        ])->find($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }

    /**
     * Cancela un pedido pendiente o en borrador.
     */
    public function cancelar(Request $request, int $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        if ($pedido->status === 'Aceptado') {
            return response()->json([
                'success' => false,
                'message' => 'No es posible cancelar un pedido que ya ha sido surtido y aceptado por CENDIS.'
            ], 422);
        }

        try {
            $pedido->update(['status' => 'cancelado']);
            return response()->json([
                'success' => true,
                'message' => "El pedido #{$id} ha sido cancelado correctamente."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista de reporte / comprobantes del módulo.
     */
    public function reportes(Request $request)
    {
        $pedidos = Pedido::with(['areaAbastecimiento', 'subareaAbastecimiento', 'areaAlmacen', 'usuario.persona'])
            ->orderBy('id_pedido', 'desc')
            ->limit(50)
            ->get();

        return view('peticion_insumos.pedido_insumos.analitica.reportes.index', compact('pedidos'));
    }

    /**
     * Impresión oficial del comprobante de solicitud de pedido.
     */
    public function imprimir(Request $request, int $id)
    {
        $pedido = Pedido::with([
            'areaAbastecimiento',
            'subareaAbastecimiento',
            'areaAlmacen',
            'usuario.persona',
            'detalles.insumo'
        ])->findOrFail($id);

        return view('peticion_insumos.pedido_insumos.analitica.reportes.impresion', compact('pedido'));
    }
}
