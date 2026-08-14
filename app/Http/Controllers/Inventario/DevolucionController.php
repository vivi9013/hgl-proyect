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
use App\Traits\ParseaRangoFechas;
use App\Traits\AjustaStockInsumoArea;
use App\Traits\BuscaInsumosAjax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DevolucionFormatoExport;

class DevolucionController extends Controller
{
    use ParseaRangoFechas, AjustaStockInsumoArea, BuscaInsumosAjax;

    private const PER_PAGE = 10;

    /**
     * Muestra las devoluciones pendientes (en proceso o canceladas).
     */
    public function index(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        if ($errorMsg) {
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        if ($fechaInitDb) {
            $fechaInit = $fechaInitDb;
        }
        if ($fechaFinDb) {
            $fechaFin = $fechaFinDb;
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

        $devoluciones = $query->paginate(self::PER_PAGE)->withQueryString();
        $areasAlmacen = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        $motivos      = Motivo::where('activo', 1)->orderBy('descripcion')->get();

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
     * Guarda el encabezado de una nueva devolución.
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
            'id_area_abastecimiento'   => $request->id_area_abastecimiento,
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

    /**
     * Muestra la vista para agregar o consultar insumos de una devolución.
     */
    public function detalle($id)
    {
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento', 'motivo'])->findOrFail($id);
        return view('inventario.devoluciones.detalle', compact('devolucion'));
    }

    /**
     * Finaliza una devolución incrementando el stock del área de almacén correspondiente.
     */
    public function finalizar($id)
    {
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento'])->findOrFail($id);

        if ($devolucion->status !== 'En proceso') {
            return redirect()->route('devoluciones.index')
                ->with('error', 'Esta devolución ya fue finalizada.');
        }

        if ($devolucion->detalles->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No se puede finalizar una devolución sin insumos.');
        }

        DB::transaction(function () use ($devolucion) {
            foreach ($devolucion->detalles as $detalle) {
                $insumoArea = InsumoArea::where('id_insumo', $detalle->id_insumo)
                    ->where('id_area_almacen', $devolucion->id_area_almacen)
                    ->first();

                if ($insumoArea) {
                    $this->ajustarStockInsumoArea($insumoArea, (int) $detalle->cantidad, 'sumar');
                } else {
                    InsumoArea::create([
                        'id_insumo'       => $detalle->id_insumo,
                        'id_area_almacen' => $devolucion->id_area_almacen,
                        'stock'           => (string) $detalle->cantidad,
                        'fondo_fijo'      => 0,
                    ]);
                }
            }

            $devolucion->update([
                'status'          => 'Terminado',
                'total_productos' => $devolucion->detalles->count(),
                'total_cantidad'  => $devolucion->detalles->sum('cantidad'),
            ]);
        });

        return redirect()
            ->route('devoluciones.detalle', $devolucion->id_devolucion)
            ->with('exitog', "La devolución DEV-{$devolucion->id_devolucion} ha sido finalizada correctamente.");
    }

    /**
     * Genera la vista del comprobante de devolución terminada.
     */
    public function comprobante($id)
    {
        $devolucion = Devolucion::with(['detalles.insumo', 'areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->findOrFail($id);

        if ($devolucion->status !== 'Terminado') {
            return redirect()->route('devoluciones.detalle', $id)
                ->with('error', 'No se puede generar el comprobante de una devolución que no esté terminada.');
        }

        return view('inventario.devoluciones.comprobante', compact('devolucion'));
    }

    /**
     * Alterna el estado de una devolución entre "En proceso" y "Cancelado".
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

    /**
     * Muestra el historial de devoluciones en estado "Terminado".
     */
    public function terminadas(Request $request)
    {
        $buscar       = $request->get('buscar', '');
        $fechaInit    = $request->get('fecha_inicio', '');
        $fechaFin     = $request->get('fecha_fin', '');
        $filtroMotivo = $request->get('id_motivo', '');
        $filtroArea   = $request->get('id_area_abastecimiento', '');

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        if ($errorMsg) {
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        if ($fechaInitDb) {
            $fechaInit = $fechaInitDb;
        }
        if ($fechaFinDb) {
            $fechaFin = $fechaFinDb;
        }

        $motivos = Motivo::where('activo', 1)->orderBy('descripcion')->get();

        try {
            $areasAbastecimiento = AreaAbastecimiento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $areasAbastecimiento = collect();
        }

        $query = Devolucion::with(['areaAlmacen', 'areaAbastecimiento', 'usuario.persona', 'motivo'])
            ->where('status', 'Terminado')
            ->orderBy('id_devolucion', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_devolucion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('areaAbastecimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if (!empty($filtroMotivo)) {
            $query->where('id_motivo', $filtroMotivo);
        }

        if (!empty($filtroArea)) {
            $query->where('id_area_abastecimiento', $filtroArea);
        }

        if ($fechaInitDb) $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);
        if ($fechaFinDb)  $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);

        $devoluciones = $query->paginate(self::PER_PAGE)->withQueryString();

        return view('inventario.devoluciones.terminadas', compact(
            'devoluciones', 'motivos', 'areasAbastecimiento', 'buscar', 'fechaInit', 'fechaFin', 'filtroMotivo', 'filtroArea'
        ));
    }

    /**
     * Muestra la vista de configuración de reportes de devoluciones.
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
     * Genera el reporte de impresión con los filtros aplicados.
     */
    public function imprimir(Request $request)
    {
        $buscar        = $request->get('buscar', '');
        $fechaInit     = $request->get('fecha_inicio', '');
        $fechaFin      = $request->get('fecha_fin', '');
        $idAreaAlmacen = $request->get('id_area_almacen', '');

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        if ($errorMsg) {
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        if ($fechaInitDb) {
            $fechaInit = $fechaInitDb;
        }
        if ($fechaFinDb) {
            $fechaFin = $fechaFinDb;
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

        if (!empty($idAreaAlmacen)) {
            $query->where('id_area_almacen', $idAreaAlmacen);
        }

        $status = 'Terminado';
        $query->where('status', 'Terminado');

        if ($fechaInitDb) $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);
        if ($fechaFinDb)  $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);

        $devoluciones = $query->limit(500)->get();

        return view('inventario.devoluciones.reporte_impresion', compact(
            'devoluciones', 'buscar', 'fechaInit', 'fechaFin', 'status'
        ));
    }

    /**
     * Exporta el Formato de Devolución y Medicamento Caducado a Excel (.xlsx).
     * Solo exporta devoluciones con status = 'Terminado' (mismos filtros que la vista terminadas).
     */
    public function exportarExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date',
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'    => 'La fecha de fin es obligatoria.',
        ]);

        $buscar        = $request->get('buscar', '');
        $fechaInit     = $request->get('fecha_inicio', '');
        $fechaFin      = $request->get('fecha_fin', '');
        $filtroMotivos = $request->get('motivos', $request->get('id_motivo', []));
        $filtroArea    = $request->get('id_area_abastecimiento', '');

        [$fechaInitDb, $fechaFinDb] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        $query = Devolucion::with(['detalles.insumo', 'motivo', 'areaAlmacen', 'areaAbastecimiento', 'usuario.persona'])
            ->where('status', 'Terminado')
            ->orderBy('fecha_devolucion', 'asc')
            ->orderBy('hora_devolucion', 'asc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_devolucion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('areaAbastecimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if (!empty($filtroMotivos)) {
            if (is_array($filtroMotivos)) {
                $query->whereIn('id_motivo', $filtroMotivos);
            } else {
                $query->where('id_motivo', $filtroMotivos);
            }
        }

        if (!empty($filtroArea)) {
            $query->where('id_area_abastecimiento', $filtroArea);
        }

        if ($fechaInitDb) {
            $query->whereDate('fecha_devolucion', '>=', $fechaInitDb);
        }
        if ($fechaFinDb) {
            $query->whereDate('fecha_devolucion', '<=', $fechaFinDb);
        }

        $devoluciones = $query->get();

        $filename = 'Formato_Devolucion_Medicamento_Caducado_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            new DevolucionFormatoExport($devoluciones, $fechaInit, $fechaFin),
            $filename
        );
    }
}
