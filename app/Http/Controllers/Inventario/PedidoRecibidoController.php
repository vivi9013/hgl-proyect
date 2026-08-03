<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Pedido;
use App\Models\Inventario\DetallePedido;
use App\Models\Inventario\InsumoArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PedidoRecibidoController extends Controller
{
    // ── Eager loads comunes ──
    private function withRelaciones()
    {
        return ['areaAbastecimiento', 'subareaAbastecimiento', 'areaAlmacen', 'usuario.persona'];
    }

    // ── Lógica de query compartida por los 3 listados ──
    private function queryFiltrada(Request $request, string $status)
    {
        $buscar     = trim($request->get('buscar', ''));
        $fechaInit  = $request->get('fecha_inicio', '');
        $fechaFin   = $request->get('fecha_fin', '');

        $query = Pedido::where('status', $status)
            ->with($this->withRelaciones())
            ->orderByDesc('fecha_registro')
            ->orderByDesc('id_pedido');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('areaAbastecimiento', fn($s) => $s->where('nombre', 'like', "%{$buscar}%"))
                  ->orWhereHas('subareaAbastecimiento', fn($s) => $s->where('nombre', 'like', "%{$buscar}%"))
                  ->orWhereHas('areaAlmacen', fn($s) => $s->where('nombre', 'like', "%{$buscar}%"))
                  ->orWhereHas('usuario.persona', fn($s) => $s->where('nombre', 'like', "%{$buscar}%")
                                                               ->orWhere('ap_paterno', 'like', "%{$buscar}%"));
            });
        }

        if ($fechaInit) {
            $query->whereDate('fecha_registro', '>=', $fechaInit);
        }
        if ($fechaFin) {
            $query->whereDate('fecha_registro', '<=', $fechaFin);
        }

        return [$query, $buscar, $fechaInit, $fechaFin];
    }

    /**
     * Listado de pedidos PENDIENTES por surtir.
     */
    public function index(Request $request)
    {
        [$query, $buscar, $fechaInit, $fechaFin] = $this->queryFiltrada($request, Pedido::STATUS_PENDIENTE);
        $pedidos = $query->paginate(15)->withQueryString();

        return view('inventario.pedidos_recibidos.index', compact('pedidos', 'buscar', 'fechaInit', 'fechaFin'));
    }

    /**
     * Listado de pedidos ACEPTADOS (ya surtidos).
     */
    public function aceptados(Request $request)
    {
        [$query, $buscar, $fechaInit, $fechaFin] = $this->queryFiltrada($request, Pedido::STATUS_ACEPTADO);
        $pedidos = $query->paginate(15)->withQueryString();

        return view('inventario.pedidos_recibidos.aceptados', compact('pedidos', 'buscar', 'fechaInit', 'fechaFin'));
    }

    /**
     * Listado de pedidos CANCELADOS.
     */
    public function cancelados(Request $request)
    {
        [$query, $buscar, $fechaInit, $fechaFin] = $this->queryFiltrada($request, Pedido::STATUS_CANCELADO);
        $pedidos = $query->paginate(15)->withQueryString();

        return view('inventario.pedidos_recibidos.cancelados', compact('pedidos', 'buscar', 'fechaInit', 'fechaFin'));
    }

    /**
     * Vista de detalle/surtimiento de un pedido.
     */
    /**
     * Vista de detalle/surtimiento de un pedido.
     */
    public function detalle(int $id)
    {
        $pedido = Pedido::with([
            'areaAbastecimiento',
            'subareaAbastecimiento',
            'areaAlmacen',
            'usuario.persona',
            'detalles.insumo',
        ])->findOrFail($id);

        return view('inventario.pedidos_recibidos.detalle', compact('pedido'));
    }

    /**
     * Guarda la cantidad surtida de un detalle individual (AJAX).
     */
    public function guardarSurtido(Request $request, int $idDetalle)
    {
        $detalle = DetallePedido::findOrFail($idDetalle);
        $pedido  = Pedido::findOrFail($detalle->id_pedido);

        if ($pedido->status !== Pedido::STATUS_PENDIENTE) {
            return response()->json(['error' => 'El pedido no está en estado pendiente.'], 422);
        }

        $request->validate([
            'surtido' => 'required|integer|min:0',
        ]);

        $surtido = (int) $request->get('surtido');

        // Validar contra stock disponible
        $insumoArea = $detalle->insumoArea();
        $stockDisponible = $insumoArea ? $insumoArea->stock : 0;
        if ($surtido > $stockDisponible) {
            return response()->json(['error' => "Stock insuficiente. Disponible: {$stockDisponible}"], 422);
        }
        if ($surtido > $detalle->cantidad) {
            $surtido = $detalle->cantidad;
        }

        $faltante = $detalle->cantidad - $surtido;
        $detalle->update(['surtido' => $surtido, 'faltante' => $faltante]);

        return response()->json([
            'success'  => true,
            'surtido'  => $surtido,
            'faltante' => $faltante,
        ]);
    }

    /**
     * Libera el pedido: descuenta stock y calcula porcentaje de entrega.
     */
    public function liberar(Request $request, int $id)
    {
        $pedido = Pedido::with(['detalles'])->findOrFail($id);

        if ($pedido->status !== Pedido::STATUS_PENDIENTE) {
            return back()->with('error', 'Este pedido no se puede liberar porque ya fue procesado.');
        }

        // Verificar que al menos un insumo tenga cantidad surtida
        $tieneSurtido = $pedido->detalles->some(fn($d) => ($d->surtido ?? 0) > 0);
        if (!$tieneSurtido) {
            return back()->with('error', 'Debe surtir al menos un insumo antes de liberar el pedido.');
        }

        DB::transaction(function () use ($pedido) {
            $totalCantidad = 0;
            $totalSurtido  = 0;

            foreach ($pedido->detalles as $detalle) {
                $surtido  = $detalle->surtido ?? 0;
                $faltante = $detalle->cantidad - $surtido;

                $detalle->update(['faltante' => $faltante]);

                $totalCantidad += $detalle->cantidad;
                $totalSurtido  += $surtido;

                // Descontar stock
                if ($surtido > 0) {
                    $insumoArea = $detalle->insumoArea();
                    if ($insumoArea) {
                        $insumoArea->decrement('stock', $surtido);
                    }
                }
            }

            $porcentaje = $totalCantidad > 0
                ? round(($totalSurtido / $totalCantidad) * 100, 2)
                : 0;

            $pedido->update([
                'status'             => Pedido::STATUS_ACEPTADO,
                'fecha_entrega'      => Carbon::now()->toDateString(),
                'hora_entrega'       => Carbon::now()->format('H:i:s'),
                'porcentaje_entrega' => $porcentaje,
            ]);
        });

        return redirect()
            ->route('pedidos_recibidos.comprobante', $pedido->id_pedido)
            ->with('exitog', "Pedido {$pedido->folio} liberado correctamente.");
    }

    /**
     * Cancela un pedido y restaura el stock si ya fue surtido.
     */
    public function cancelar(Request $request, int $id)
    {
        $pedido = Pedido::with(['detalles'])->findOrFail($id);

        if ($pedido->status === Pedido::STATUS_CANCELADO) {
            return back()->with('error', 'El pedido ya está cancelado.');
        }

        DB::transaction(function () use ($pedido) {
            // Si fue aceptado, restaurar stock
            if ($pedido->status === Pedido::STATUS_ACEPTADO) {
                foreach ($pedido->detalles as $detalle) {
                    $surtido = $detalle->surtido ?? 0;
                    if ($surtido > 0) {
                        $insumoArea = $detalle->insumoArea();
                        if ($insumoArea) {
                            $insumoArea->increment('stock', $surtido);
                        }
                    }
                }
            }

            $pedido->update([
                'status'             => Pedido::STATUS_CANCELADO,
                'porcentaje_entrega' => 0,
            ]);
        });

        return redirect()
            ->route('pedidos_recibidos.cancelados')
            ->with('exitog', "Pedido {$pedido->folio} cancelado correctamente.");
    }

    /**
     * Comprobante imprimible de un pedido aceptado.
     */
    public function comprobante(int $id)
    {
        $pedido = Pedido::with([
            'areaAbastecimiento',
            'subareaAbastecimiento',
            'areaAlmacen',
            'usuario.persona',
            'detalles.insumo',
        ])->findOrFail($id);

        return view('inventario.pedidos_recibidos.comprobante', compact('pedido'));
    }
}
