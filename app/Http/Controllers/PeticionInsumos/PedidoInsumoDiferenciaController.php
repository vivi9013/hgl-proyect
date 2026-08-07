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
use App\Models\PeticionInsumos\AlmacenSubarea;
use App\Models\PeticionInsumos\DetalleAlmacenSubarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PedidoInsumoDiferenciaController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Muestra la vista principal del módulo Pedido por Diferencia.
     */
    public function index(Request $request)
    {
        $areas     = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $subareas  = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $almacenes = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();

        // Historial reciente de pedidos por diferencia
        $pedidos = Pedido::with(['areaAbastecimiento', 'subareaAbastecimiento', 'areaAlmacen', 'usuario.persona'])
            ->orderBy('id_pedido', 'desc')
            ->paginate(10)
            ->withQueryString();

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $pedidos,
            'peticion_insumos.pedido_insumos_dif.partials.tabla_historial',
            compact('pedidos'),
            'pedidos por diferencia'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('peticion_insumos.pedido_insumos_dif.index', compact('areas', 'subareas', 'almacenes', 'pedidos'));
    }

    /**
     * Endpoint AJAX que calcula los faltantes (Fondo Fijo - Stock) de cada insumo para un área o almacén.
     */
    public function calcularDiferencias(Request $request)
    {
        $idAreaAlmacen  = $request->get('id_area_almacen');
        $idSubarea      = $request->get('id_subarea_abastecimiento');
        $soloFaltantes  = filter_var($request->get('solo_faltantes', true), FILTER_VALIDATE_BOOLEAN);

        $insumosCalculados = collect();

        // 1. Si se especificó una Subárea, consultar almacen_subareas / detalle_almacen_subareas
        if ($idSubarea) {
            $almacenSubarea = AlmacenSubarea::where('id_subarea_abastecimiento', $idSubarea)->first();
            if ($almacenSubarea) {
                $detalles = DetalleAlmacenSubarea::with('insumo')
                    ->where('id_almacen_subarea', $almacenSubarea->id_almacen_subarea)
                    ->get();

                foreach ($detalles as $det) {
                    $stock     = (int) $det->cantidad;
                    $fondoFijo = (int) $det->fondo_fijo;
                    $diferencia= max(0, $fondoFijo - $stock);

                    if ($soloFaltantes && $diferencia <= 0) {
                        continue;
                    }

                    if ($det->insumo && $det->insumo->activo == 1) {
                        $insumosCalculados->push([
                            'id_insumo'     => $det->insumo->id_insumo,
                            'clave'         => $det->insumo->clave,
                            'descripcion'   => $det->insumo->descripcion,
                            'tipo'          => $det->insumo->tipo,
                            'stock'         => $stock,
                            'fondo_fijo'    => $fondoFijo,
                            'diferencia'    => $diferencia,
                            'cantidad_pedir'=> $diferencia > 0 ? $diferencia : 1,
                            'seleccionado'  => $diferencia > 0 ? true : false,
                        ]);
                    }
                }
            }
        }

        // 2. Si no hay insumos por subárea o se seleccionó Área de Almacén general
        if ($insumosCalculados->isEmpty() && $idAreaAlmacen) {
            $insumosArea = InsumoArea::with('insumo')
                ->where('id_area_almacen', $idAreaAlmacen)
                ->get();

            foreach ($insumosArea as $ia) {
                $stock     = (int) $ia->stock;
                $fondoFijo = (int) $ia->fondo_fijo;
                $diferencia= max(0, $fondoFijo - $stock);

                if ($soloFaltantes && $diferencia <= 0) {
                    continue;
                }

                if ($ia->insumo && $ia->insumo->activo == 1) {
                    $insumosCalculados->push([
                        'id_insumo'     => $ia->insumo->id_insumo,
                        'clave'         => $ia->insumo->clave,
                        'descripcion'   => $ia->insumo->descripcion,
                        'tipo'          => $ia->insumo->tipo,
                        'stock'         => $stock,
                        'fondo_fijo'    => $fondoFijo,
                        'diferencia'    => $diferencia,
                        'cantidad_pedir'=> $diferencia > 0 ? $diferencia : 1,
                        'seleccionado'  => $diferencia > 0 ? true : false,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'total'   => $insumosCalculados->count(),
            'insumos' => $insumosCalculados->values()
        ]);
    }

    /**
     * Procesa y guarda la solicitud de pedido por diferencia enviándolo a CENDIS o como borrador.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_area_abastecimiento'    => 'required|integer|exists:areasabastecimiento,id_area_abastecimiento,activo,1',
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
            'insumos.required'                => 'Debe seleccionar al menos un insumo con faltante para generar el pedido.',
            'insumos.min'                     => 'Debe seleccionar al menos un insumo con faltante para generar el pedido.',
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
                'status'                   => $request->status, // 'terminado' (enviado a CENDIS) o 'borrador'
                'activo'                   => 1,
                'id_usuario'               => Auth::id() ?: 1,
                'porcentaje_entrega'       => 0.00,
            ]);

            $almacenSubarea = null;
            if ($request->id_subarea_abastecimiento) {
                $almacenSubarea = AlmacenSubarea::where('id_subarea_abastecimiento', $request->id_subarea_abastecimiento)->first();
            }

            foreach ($request->insumos as $item) {
                $insumo = Insumo::find($item['id_insumo']);

                $stock      = 0;
                $fondoFijo  = 0;
                $encontrado = false;

                if ($almacenSubarea) {
                    $det = DetalleAlmacenSubarea::where('id_almacen_subarea', $almacenSubarea->id_almacen_subarea)
                        ->where('id_insumo', $item['id_insumo'])
                        ->first();
                    if ($det) {
                        $stock      = (int) $det->cantidad;
                        $fondoFijo  = (int) $det->fondo_fijo;
                        $encontrado = true;
                    }
                }

                if (!$encontrado) {
                    $insumoArea = InsumoArea::where('id_insumo', $item['id_insumo'])
                        ->where('id_area_almacen', $request->id_area_almacen)
                        ->first();
                    if ($insumoArea) {
                        $stock     = (int) $insumoArea->stock;
                        $fondoFijo = (int) $insumoArea->fondo_fijo;
                    }
                }

                DetallePedido::create([
                    'id_pedido'  => $pedido->id_pedido,
                    'id_insumo'  => $item['id_insumo'],
                    'cve_insumo' => $insumo ? $insumo->clave : ($item['cve_insumo'] ?? null),
                    'cantidad'   => $item['cantidad'],
                    'existencia' => $stock,
                    'fondo_fijo' => $fondoFijo,
                    'surtido'    => 0,
                    'faltante'   => $item['cantidad'],
                ]);
            }

            DB::commit();

            $msg = $request->status === 'terminado'
                ? "El pedido por diferencia #{$pedido->id_pedido} ha sido generado y enviado a CENDIS."
                : "El pedido por diferencia #{$pedido->id_pedido} se guardó como borrador.";

            return response()->json([
                'success'   => true,
                'message'   => $msg,
                'id_pedido' => $pedido->id_pedido
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el pedido por diferencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hub de reportes del módulo.
     */
    public function reportes(Request $request)
    {
        $pedidos = Pedido::with(['areaAbastecimiento', 'subareaAbastecimiento', 'areaAlmacen', 'usuario.persona'])
            ->orderBy('id_pedido', 'desc')
            ->limit(50)
            ->get();

        return view('peticion_insumos.pedido_insumos_dif.analitica.reportes.index', compact('pedidos'));
    }

    /**
     * Impresión oficial del comprobante de pedido por diferencia.
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

        return view('peticion_insumos.pedido_insumos_dif.analitica.reportes.impresion', compact('pedido'));
    }
}
