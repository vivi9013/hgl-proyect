<?php

namespace App\Http\Controllers\ControlInsumos;

use App\Http\Controllers\Controller;
use App\Models\ControlInsumos\InsumoImpresora;
use App\Models\ControlInsumos\MovimientoInsumo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimientoInsumoController extends Controller
{
    private const CONCEPTOS_ENTRADA = ['Compra', 'Donación'];
    private const CONCEPTOS_SALIDA  = ['Uso', 'Por daño', 'Donación'];

    // ─── INDEX ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        // Filtros disponibles
        $buscar      = trim($request->get('buscar', ''));
        $tipo        = $request->input('tipo', []);        // [] | ['Entrada'] | ['Salida'] | ['Entrada','Salida']
        $concepto    = $request->input('concepto', []);
        $fechaInicio = $request->get('fecha_inicio', '');
        $fechaFin    = $request->get('fecha_fin', '');
        $status      = $request->input('status', []);      // [] | ['1'] | ['0'] | ['1','0']

        // ── Consulta base unificada con filtros en cascada ──────────────────
        $query = MovimientoInsumo::with('insumo', 'impresora')
            ->orderBy('id_movimiento', 'desc');

        if (!empty($buscar)) {
            $query->whereHas('insumo', fn ($q) => $q->where('modelo', 'like', "%{$buscar}%"));
        }
        if (!empty($tipo)) {
            $query->whereIn('tipo', (array) $tipo);
        }
        if (!empty($concepto)) {
            $query->whereIn('concepto', (array) $concepto);
        }
        if (!empty($fechaInicio)) {
            $query->where('fecha_movimiento', '>=', $fechaInicio);
        }
        if (!empty($fechaFin)) {
            $query->where('fecha_movimiento', '<=', $fechaFin);
        }
        if ($status !== [] && !empty($status)) {
            $query->whereIn('activo', array_map('intval', $status));
        }

        // ── Petición AJAX (paginación + filtros) ────────────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $movimientos = $query->paginate(15);

            return response()->json([
                'html'  => view('control_insumos.movimientos.partials.tabla', compact('movimientos'))->render(),
                'links' => $movimientos->links('pagination::bootstrap-4')->render(),
                'total' => $movimientos->total(),
                'info'  => 'Mostrando ' . ($movimientos->firstItem() ?? 0)
                           . ' a ' . ($movimientos->lastItem() ?? 0)
                           . ' de ' . $movimientos->total() . ' registros',
            ]);
        }

        // ── Carga inicial ────────────────────────────────────────────────────
        $movimientos = $query->paginate(15);
        $insumos     = InsumoImpresora::where('activo', 1)->orderBy('modelo')->get();
        return view('control_insumos.movimientos.index', [
            'movimientos'       => $movimientos,
            'insumos'           => $insumos,
            'conceptosEntrada'  => self::CONCEPTOS_ENTRADA,
            'conceptosSalida'   => self::CONCEPTOS_SALIDA,
        ]);
    }

    // ─── GUARDAR ENTRADA O SALIDA ─────────────────────────────────────────────
    public function guardar(Request $request)
    {
        $tipo = $request->get('tipo');

        $rules = [
            'id_insumo_impresora' => 'required|integer|exists:insumos_impresoras,id_insumo_impresora',
            'tipo'                => 'required|in:Entrada,Salida',
            'concepto'            => 'required|string|max:50',
            'cantidad'            => 'required|integer|min:1',
            'fecha_movimiento'    => 'required|date',
            'proveedor'           => 'nullable|string|max:150',
            'id_impresora'        => 'nullable|integer|exists:impresoras,id_impresora',
        ];

        $request->validate($rules, [
            'id_insumo_impresora.exists' => 'El insumo seleccionado no existe.',
        ]);

        try {
            DB::transaction(function () use ($request, $tipo) {
                $insumo   = InsumoImpresora::lockForUpdate()->findOrFail($request->id_insumo_impresora);
                $cantidad = (int) $request->cantidad;

                // Validar stock suficiente para salidas
                if ($tipo === 'Salida' && $insumo->stock < $cantidad) {
                    throw new \Exception("Stock insuficiente. Stock actual: {$insumo->stock} piezas.");
                }

                // Registrar movimiento
                MovimientoInsumo::create([
                    'id_insumo_impresora' => $insumo->id_insumo_impresora,
                    'tipo'                => $tipo,
                    'concepto'            => trim($request->concepto),
                    'cantidad'            => $cantidad,
                    'id_impresora'        => $request->id_impresora ?? null,
                    'proveedor'           => trim($request->proveedor ?? ''),
                    'fecha_movimiento'    => $request->fecha_movimiento,
                    'activo'              => 1,
                    'fecha'               => now()->toDateString(),
                    'hora'                => now()->toTimeString(),
                    'usuario'             => Auth::id(),
                ]);

                // Actualizar stock del insumo
                $insumo->stock = $tipo === 'Entrada'
                    ? $insumo->stock + $cantidad
                    : $insumo->stock - $cantidad;
                $insumo->fecha   = now()->toDateString();
                $insumo->hora    = now()->toTimeString();
                $insumo->usuario = Auth::id();
                $insumo->save();
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        $mensaje = $tipo === 'Entrada'
            ? 'La entrada de insumo se ha registrado correctamente.'
            : 'La salida de insumo se ha registrado correctamente.';

        return redirect()->route('movimientos_insumos.index')->with('exitog', $mensaje);
    }

    // ─── CANCELAR MOVIMIENTO (AJAX) ───────────────────────────────────────────
    public function cancelar(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $mov    = MovimientoInsumo::lockForUpdate()->findOrFail($id);
                $insumo = InsumoImpresora::lockForUpdate()->findOrFail($mov->id_insumo_impresora);

                if ($mov->activo == 0) {
                    throw new \Exception('Este movimiento ya fue cancelado.');
                }

                $cantidad = (int) $mov->cantidad;

                // Revertir el efecto del movimiento en el stock
                if ($mov->tipo === 'Entrada') {
                    // Se restituye: restar lo que se había sumado
                    if ($insumo->stock < $cantidad) {
                        throw new \Exception(
                            "No se puede cancelar: el stock actual ({$insumo->stock}) es menor a la cantidad de la entrada ({$cantidad}). Posiblemente ya se usaron esas piezas."
                        );
                    }
                    $insumo->stock -= $cantidad;
                } else {
                    // Salida: restituir lo que se había restado
                    $insumo->stock += $cantidad;
                }

                // Marcar como cancelado
                $mov->activo  = 0;
                $mov->fecha   = now()->toDateString();
                $mov->hora    = now()->toTimeString();
                $mov->usuario = Auth::id();
                $mov->save();

                $insumo->fecha   = now()->toDateString();
                $insumo->hora    = now()->toTimeString();
                $insumo->usuario = Auth::id();
                $insumo->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'El movimiento ha sido cancelado y el stock fue restaurado.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ─── OBTENER DATOS PARA MODAL EDITAR (AJAX GET) ─────────────────────────────
    public function editar(int $id)
    {
        $mov = MovimientoInsumo::with('insumo')->findOrFail($id);

        $insumo = $mov->insumo;

        return response()->json([
            'id_movimiento'      => $mov->id_movimiento,
            'tipo'               => $mov->tipo,
            'concepto'           => $mov->concepto,
            'cantidad'           => $mov->cantidad,
            'fecha_movimiento'   => $mov->fecha_movimiento,
            'proveedor'          => $mov->proveedor ?? '',
            'insumo_nombre'      => $insumo
                ? ($insumo->familia . ' — ' . $insumo->modelo . ' (' . $insumo->color . ')')
                : '—',
            'insumo_stock'       => $insumo?->stock ?? 0,
            'insumo_compatibles' => $insumo?->modelos_compatibles ?? '',
            'insumo_hojas'       => $insumo?->hojas_uso_total ?? '',
            'insumo_tiempo'      => $insumo?->tiempo_uso ?? '',
            'conceptos_entrada'  => self::CONCEPTOS_ENTRADA,
            'conceptos_salida'   => self::CONCEPTOS_SALIDA,
        ]);
    }

    // ─── ACTUALIZAR MOVIMIENTO (AJAX PUT) ─────────────────────────────────────
    public function actualizar(Request $request, int $id)
    {
        $mov = MovimientoInsumo::findOrFail($id);

        if (!$mov->activo) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar un movimiento cancelado.',
            ], 422);
        }

        $request->validate([
            'tipo'             => 'required|in:Entrada,Salida',
            'cantidad'         => 'required|integer|min:1',
            'concepto'         => 'required|string|max:50',
            'fecha_movimiento' => 'required|date',
            'proveedor'        => 'nullable|string|max:150',
        ]);

        try {
            DB::transaction(function () use ($request, $mov) {
                $insumo = \App\Models\ControlInsumos\InsumoImpresora::lockForUpdate()->findOrFail($mov->id_insumo_impresora);

                // 1. Revertir el efecto del movimiento original sobre el stock
                if ($mov->tipo === 'Entrada') {
                    $insumo->stock -= $mov->cantidad;
                } else {
                    $insumo->stock += $mov->cantidad;
                }

                // 2. Aplicar el efecto del nuevo tipo y cantidad
                $nuevoTipo = $request->tipo;
                $nuevaCantidad = (int) $request->cantidad;

                if ($nuevoTipo === 'Entrada') {
                    $insumo->stock += $nuevaCantidad;
                } else {
                    $insumo->stock -= $nuevaCantidad;
                }

                // 3. Validar que el stock final no sea negativo
                if ($insumo->stock < 0) {
                    throw new \Exception("Stock insuficiente. Los cambios solicitados dejarían el catálogo del insumo en saldo negativo ({$insumo->stock} piezas).");
                }

                // 4. Guardar los datos del movimiento
                $mov->tipo             = $nuevoTipo;
                $mov->cantidad         = $nuevaCantidad;
                $mov->concepto         = trim($request->concepto);
                $mov->fecha_movimiento = $request->fecha_movimiento;

                if ($nuevoTipo === 'Entrada') {
                    $mov->proveedor = trim($request->proveedor ?? '');
                } else {
                    $mov->proveedor = '';
                }

                $mov->fecha            = now()->toDateString();
                $mov->hora             = now()->toTimeString();
                $mov->usuario          = Auth::id();

                $mov->save();

                // 5. Guardar los datos del insumo
                $insumo->fecha         = now()->toDateString();
                $insumo->hora          = now()->toTimeString();
                $insumo->usuario       = Auth::id();
                $insumo->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'El movimiento y el stock han sido actualizados correctamente.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ─── REPORTE (impresión) ─────────────────────────────────────────────────
    public function imprimir(Request $request)
    {
        $buscar      = trim($request->get('buscar', ''));
        $tipo        = $request->input('tipo', []);
        $concepto    = $request->input('concepto', []);
        $fechaInicio = $request->get('fecha_inicio', '');
        $fechaFin    = $request->get('fecha_fin', '');
        $status      = $request->input('status', []);

        $query = MovimientoInsumo::with('insumo', 'impresora')
            ->orderBy('fecha_movimiento', 'desc')
            ->limit(500);

        if (!empty($buscar)) {
            $query->whereHas('insumo', fn ($q) => $q->where('modelo', 'like', "%{$buscar}%"));
        }
        if (!empty($tipo)) {
            $query->whereIn('tipo', (array) $tipo);
        }
        if (!empty($concepto)) {
            $query->whereIn('concepto', (array) $concepto);
        }
        if (!empty($fechaInicio)) {
            $query->where('fecha_movimiento', '>=', $fechaInicio);
        }
        if (!empty($fechaFin)) {
            $query->where('fecha_movimiento', '<=', $fechaFin);
        }
        if (!empty($status)) {
            $query->whereIn('activo', array_map('intval', $status));
        }

        $movimientos = $query->get();

        return view('control_insumos.movimientos.analitica.reportes.impresion',
            compact('movimientos', 'tipo'));
    }
}
