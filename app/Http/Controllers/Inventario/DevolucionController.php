<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Devolucion;
use App\Models\Inventario\DetalleDevolucion;
use App\Models\Inventario\AreaAlmacen;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\Motivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    // ── Número de registros por página ────────────────────────────────────────
    private const PER_PAGE = 10;

    // =========================================================================
    // PENDIENTES
    // =========================================================================

    /**
     * Lista las devoluciones con status "En proceso".
     */
    public function index(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');

        [$fechaInitDb, $fechaInit] = $this->normalizarFecha($fechaInit);
        [$fechaFinDb,  $fechaFin]  = $this->normalizarFecha($fechaFin);

        if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {
            return redirect()->back()->withInput()
                ->with('error', 'La fecha de inicio no puede ser posterior a la fecha de fin.');
        }

        $query = Devolucion::with(['areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->whereIn('status', ['En proceso', 'Cancelado'])
            ->orderBy('id_devolucion', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_devolucion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('areaAbastecimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if ($fechaInitDb) $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);
        if ($fechaFinDb)  $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);

        $devoluciones  = $query->paginate(self::PER_PAGE)->withQueryString();
        $areasAlmacen  = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        $motivos       = Motivo::where('activo', 1)->orderBy('descripcion')->get();

        // Intentar cargar áreas de abastecimiento (pueden no existir en la BD)
        try {
            $areasAbastecimiento = AreaAbastecimiento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $areasAbastecimiento = collect();
        }

        return view('inventario.devoluciones.index', compact(
            'devoluciones', 'areasAlmacen', 'areasAbastecimiento', 'motivos',
            'buscar', 'fechaInit', 'fechaFin'
        ));
    }

    /**
     * Crea una nueva devolución "En proceso".
     * La tabla no tiene folio ni observaciones, solo los campos del esquema real.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_area_almacen' => 'required|integer|exists:areas_almacen,id_area_almacen',
            'id_motivo'       => 'required|integer|exists:motivos,id_motivo',
        ], [
            'id_area_almacen.required' => 'Debe seleccionar un área de almacén.',
            'id_area_almacen.exists'   => 'El área de almacén seleccionada no existe.',
            'id_motivo.required'       => 'Debe seleccionar un motivo de devolución.',
            'id_motivo.exists'         => 'El motivo de devolución seleccionado no existe.',
        ]);

        $devolucion = Devolucion::create([
            'id_usuario_registro'      => Auth::id() ?? 1,
            'id_area_almacen'          => $request->id_area_almacen,
            'id_area_abastecimiento'   => $request->id_area_abastecimiento ?? null,
            'id_subarea_abastecimiento'=> $request->id_subarea_abastecimiento ?? null,
            'fecha_devolucion'         => now()->toDateString(),
            'hora_devolucion'          => now()->toTimeString(),
            'status'                   => 'En proceso',
            'total_productos'          => 0,
            'total_cantidad'           => 0,
            'id_motivo'                => $request->id_motivo,
        ]);

        return redirect()
            ->route('devoluciones.detalle', $devolucion->id_devolucion)
            ->with('exitog', 'Devolución creada correctamente. Ahora agregue los insumos.');
    }

    // =========================================================================
    // DETALLE (agregar / ver insumos)
    // =========================================================================

    /**
     * Muestra el detalle de una devolución y sus insumos.
     */
    public function detalle($id)
    {
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento', 'motivo'])->findOrFail($id);

        return view('inventario.devoluciones.detalle', compact('devolucion'));
    }

    // =========================================================================
    // FINALIZAR
    // =========================================================================

    /**
     * Marca la devolución como "Terminado" y redirige a la vista de comprobante.
     */
    public function finalizar($id)
    {
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento'])->findOrFail($id);

        if ($devolucion->status !== 'En proceso') {
            return redirect()->route('devoluciones.index')
                ->with('error', 'Esta devolución ya fue finalizada.');
        }

        if ($devolucion->detalles->isEmpty()) {
            return redirect()->route('devoluciones.detalle', $id)
                ->with('error', 'No puede finalizar una devolución sin insumos registrados.');
        }

        DB::transaction(function () use ($devolucion) {
            // Incrementar stock en el almacén de destino para cada insumo devuelto
            foreach ($devolucion->detalles as $detalle) {
                $insumoArea = InsumoArea::where('id_insumo', $detalle->id_insumo)
                    ->where('id_area_almacen', $devolucion->id_area_almacen)
                    ->first();

                if ($insumoArea) {
                    $nuevoStock = (int) $insumoArea->stock + (int) $detalle->cantidad;
                    $insumoArea->update(['stock' => (string) $nuevoStock]);
                } else {
                    InsumoArea::create([
                        'id_insumo'       => $detalle->id_insumo,
                        'id_area_almacen' => $devolucion->id_area_almacen,
                        'stock'           => (string) $detalle->cantidad,
                        'fondo_fijo'      => 0,
                    ]);
                }
            }

            // Actualizar totales y marcar como Terminado
            $devolucion->update([
                'status'          => 'Terminado',
                'total_productos' => $devolucion->detalles->count(),
                'total_cantidad'  => $devolucion->detalles->sum('cantidad'),
            ]);
        });

        return redirect()
            ->route('devoluciones.comprobante', $devolucion->id_devolucion)
            ->with('exitog', "La devolución DEV-{$devolucion->id_devolucion} ha sido finalizada correctamente.");
    }

    /**
     * Vista de comprobante/impresión de una devolución finalizada.
     */
    public function comprobante($id)
    {
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->findOrFail($id);

        return view('inventario.devoluciones.comprobante', compact('devolucion'));
    }

    // =========================================================================
    // ALTERNAR ESTADO (CANCELAR / REACTIVAR)
    // =========================================================================

    /**
     * Alterna el estado de una devolución entre "En proceso" (Pendiente) y "Cancelado".
     */
    public function toggleStatus($id)
    {
        $devolucion = Devolucion::findOrFail($id);

        if ($devolucion->status === 'Terminado') {
            return redirect()->route('devoluciones.index')
                ->with('error', 'No se puede cambiar el estado de una devolución terminada.');
        }

        if ($devolucion->status === 'En proceso') {
            $devolucion->update(['status' => 'Cancelado']);
            return redirect()->route('devoluciones.index')
                ->with('exito', "La devolución DEV-{$devolucion->id_devolucion} ha sido cancelada.");
        } else {
            $devolucion->update(['status' => 'En proceso']);
            return redirect()->route('devoluciones.index')
                ->with('exito', "La devolución DEV-{$devolucion->id_devolucion} ha sido reactivada.");
        }
    }

    // =========================================================================
    // TERMINADAS
    // =========================================================================

    /**
     * Lista las devoluciones con status "Terminado".
     */
    public function terminadas(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');

        [$fechaInitDb, $fechaInit] = $this->normalizarFecha($fechaInit);
        [$fechaFinDb,  $fechaFin]  = $this->normalizarFecha($fechaFin);

        $query = Devolucion::with(['areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->where('status', 'Terminado')
            ->orderBy('id_devolucion', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_devolucion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if ($fechaInitDb) $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);
        if ($fechaFinDb)  $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);

        $devoluciones = $query->paginate(self::PER_PAGE)->withQueryString();

        return view('inventario.devoluciones.terminadas', compact(
            'devoluciones', 'buscar', 'fechaInit', 'fechaFin'
        ));
    }

    // =========================================================================
    // REPORTES
    // =========================================================================

    /**
     * Vista de reportes con filtros.
     */
    public function reportes(Request $request)
    {
        $areasAlmacen = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();

        try {
            $areasAbastecimiento = AreaAbastecimiento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $areasAbastecimiento = collect();
        }

        return view('inventario.devoluciones.reportes', compact('areasAlmacen', 'areasAbastecimiento'));
    }

    /**
     * Genera el reporte de devoluciones para impresión.
     */
    public function imprimir(Request $request)
    {
        $buscar        = $request->get('buscar', '');
        $fechaInit     = $request->get('fecha_inicio', '');
        $fechaFin      = $request->get('fecha_fin', '');
        $idAreaAlmacen = $request->get('id_area_almacen', '');
        $status        = $request->get('status', '');

        [$fechaInitDb, $fechaInit] = $this->normalizarFecha($fechaInit);
        [$fechaFinDb,  $fechaFin]  = $this->normalizarFecha($fechaFin);

        // Intercambiar si hay incoherencia para no romper la impresión
        if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {
            [$fechaInitDb, $fechaFinDb] = [$fechaFinDb, $fechaInitDb];
            [$fechaInit,   $fechaFin]   = [$fechaFin,   $fechaInit];
        }

        $query = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->orderBy('fecha_devolucion', 'desc')
            ->orderBy('hora_devolucion', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_devolucion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if (!empty($idAreaAlmacen)) $query->where('id_area_almacen', $idAreaAlmacen);
        if (!empty($status))        $query->where('status', $status);
        if ($fechaInitDb)           $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);
        if ($fechaFinDb)            $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);

        // Limitar a 500 registros para prevenir agotamiento de memoria
        $devoluciones = $query->limit(500)->get();

        return view('inventario.devoluciones.reporte_impresion', compact(
            'devoluciones', 'buscar', 'fechaInit', 'fechaFin', 'status'
        ));
    }

    /**
     * Busca insumos activos por clave o descripción para el autocompletado (AJAX).
     */
    public function buscarInsumos(Request $request)
    {
        $termino = $request->get('q', '');
        $all     = $request->boolean('all', false);

        if (!$all && strlen($termino) < 2) {
            return response()->json([]);
        }

        $query = Insumo::where('activo', 1);

        if (strlen($termino) >= 1) {
            $query->where(function ($q) use ($termino) {
                $q->where('descripcion', 'LIKE', "%{$termino}%")
                  ->orWhere('clave', 'LIKE', "%{$termino}%");
            });
        }

        $insumos = $query->select('id_insumo', 'clave', 'descripcion', 'tipo')
            ->orderBy('clave')
            ->when(!$all, fn($q) => $q->limit(20))
            ->get();

        return response()->json($insumos);
    }

    // =========================================================================
    // HELPER: Normalizar fecha
    // =========================================================================

    /**
     * Normaliza una fecha de entrada a formato Y-m-d.
     * Retorna [fecha_db, fecha_display].
     */
    private function normalizarFecha(?string $fecha): array
    {
        if (empty($fecha)) return [null, ''];

        try {
            $db = str_contains($fecha, '/')
                ? \Carbon\Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d')
                : \Carbon\Carbon::parse($fecha)->format('Y-m-d');
            return [$db, $db];
        } catch (\Exception $e) {
            return [null, ''];
        }
    }
}
