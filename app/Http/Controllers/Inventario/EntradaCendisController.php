<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\EntradaCendis;
use App\Models\Inventario\DetalleEntradaCendis;
use App\Models\Inventario\AreaAlmacen;
use App\Models\Inventario\AreaSurtimiento;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\ParseaRangoFechas;
use App\Traits\AjustaStockInsumoArea;
use App\Traits\BuscaInsumosAjax;
use App\Traits\ConsultaStockInsumoArea;

class EntradaCendisController extends Controller
{
    use ParseaRangoFechas, AjustaStockInsumoArea, BuscaInsumosAjax, ConsultaStockInsumoArea;

    private const PER_PAGE = 10;

    /**
     * Listado de entradas con estado "En proceso" o "Cancelado".
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

        $query = EntradaCendis::with(['areaAlmacen', 'areaSurtimiento', 'usuario.persona'])
            ->whereIn('status', ['En proceso', 'Cancelado'])
            ->orderBy('id_entrada', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_entrada', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('areaSurtimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if ($fechaInitDb) $query->whereDate('fecha_entrada', '>=', $fechaInitDb);
        if ($fechaFinDb)  $query->whereDate('fecha_entrada', '<=', $fechaFinDb);

        $entradas   = $query->paginate(self::PER_PAGE)->withQueryString();
        $areasAlmacen  = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        $areasSurtimiento = AreaSurtimiento::where('activo', 1)->orderBy('nombre')->get();

        return view('inventario.entradas_cendis.index', compact(
            'entradas', 'areasAlmacen', 'areasSurtimiento', 'buscar', 'fechaInit', 'fechaFin'
        ));
    }

    /**
     * Almacena el folio inicial de la entrada.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_area_almacen'     => 'required|integer|exists:areas_almacen,id_area_almacen',
            'id_area_surtimiento' => 'required|integer|exists:areas_surtimiento,id_area_surtimiento',
        ], [
            'id_area_almacen.required'     => 'Debe seleccionar un área de almacén.',
            'id_area_almacen.exists'       => 'El área de almacén seleccionada no existe.',
            'id_area_surtimiento.required' => 'Debe seleccionar un área de surtimiento.',
            'id_area_surtimiento.exists'   => 'El área de surtimiento seleccionada no existe.',
        ]);

        $entrada = EntradaCendis::create([
            'id_area_surtimiento' => $request->id_area_surtimiento,
            'id_area_almacen'     => $request->id_area_almacen,
            'fecha_entrada'       => now()->toDateString(),
            'hora_entrada'        => now()->toTimeString(),
            'id_usuario_registro' => Auth::id() ?? 1,
            'total_productos'     => 0,
            'solicitado'          => 0,
            'total_cantidad'      => 0,
            'faltante'            => 0,
            'status'              => 'En proceso',
        ]);

        return redirect()
            ->route('entradas_cendis.detalle', $entrada->id_entrada)
            ->with('exitog', 'Entrada creada correctamente. Ahora agregue los insumos.');
    }

    /**
     * Vista interactiva del formulario de entrada donde se agregan y editan los insumos.
     */
    public function detalle($id)
    {
        $entrada = EntradaCendis::with(['detalles.insumo', 'areaAlmacen', 'areaSurtimiento'])->findOrFail($id);
        return view('inventario.entradas_cendis.detalle', compact('entrada'));
    }

    /**
     * Finaliza la entrada (guarda transaccionalmente y actualiza stock).
     */
    public function finalizar($id)
    {
        $entrada = EntradaCendis::with(['detalles.insumo', 'areaAlmacen'])->findOrFail($id);

        if ($entrada->status !== 'En proceso') {
            return redirect()->route('entradas_cendis.index')
                ->with('error', 'Esta entrada ya fue finalizada.');
        }

        if ($entrada->detalles->isEmpty()) {
            return redirect()->route('entradas_cendis.detalle', $id)
                ->with('error', 'No puede finalizar una entrada sin insumos registrados.');
        }

        DB::transaction(function () use ($entrada) {
            foreach ($entrada->detalles as $detalle) {
                $insumoArea = InsumoArea::where('id_insumo', $detalle->id_insumo)
                    ->where('id_area_almacen', $entrada->id_area_almacen)
                    ->first();

                if ($insumoArea) {
                    $this->ajustarStockInsumoArea($insumoArea, (int) $detalle->cantidad, 'sumar');
                } else {
                    InsumoArea::create([
                        'id_insumo'       => $detalle->id_insumo,
                        'id_area_almacen' => $entrada->id_area_almacen,
                        'stock'           => (string)$detalle->cantidad,
                        'fondo_fijo'      => 0,
                    ]);
                }
            }

            $entrada->update([
                'status'          => 'Terminado',
                'total_productos' => $entrada->detalles->count(),
                'total_cantidad'  => $entrada->detalles->sum('cantidad'),
                'solicitado'      => $entrada->detalles->sum('solicitado'),
                'faltante'        => $entrada->detalles->sum('faltante'),
                'fecha_entrada'   => now()->toDateString(),
                'hora_entrada'    => now()->toTimeString(),
            ]);
        });

        return redirect()
            ->route('entradas_cendis.comprobante', $entrada->id_entrada)
            ->with('exitog', "La entrada ENT-{$entrada->id_entrada} ha sido finalizada correctamente.");
    }

    /**
     * Comprobante oficial de la entrada.
     */
    public function comprobante($id)
    {
        $entrada = EntradaCendis::with(['detalles.insumo', 'areaAlmacen', 'areaSurtimiento', 'usuario.persona'])
            ->findOrFail($id);

        if ($entrada->status !== 'Terminado') {
            return redirect()->route('entradas_cendis.detalle', $id)
                ->with('error', 'No se puede generar el comprobante de una entrada que no esté terminada.');
        }

        return view('inventario.entradas_cendis.comprobante', compact('entrada'));
    }

    /**
     * Alternar el estado entre "En proceso" y "Cancelado".
     */
    public function toggleStatus($id)
    {
        $entrada = EntradaCendis::findOrFail($id);

        if ($entrada->status === 'Terminado') {
            return redirect()->route('entradas_cendis.index')
                ->with('error', 'No se puede cambiar el estado de una entrada terminada.');
        }

        if ($entrada->status === 'En proceso') {
            $entrada->update(['status' => 'Cancelado']);
            return redirect()->route('entradas_cendis.index')
                ->with('exito', "La entrada ENT-{$entrada->id_entrada} ha sido cancelada.");
        } else {
            $entrada->update(['status' => 'En proceso']);
            return redirect()->route('entradas_cendis.index')
                ->with('exito', "La entrada ENT-{$entrada->id_entrada} ha sido reactivada.");
        }
    }

    /**
     * Listado de entradas finalizadas.
     */
    public function terminadas(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        if ($errorMsg) {
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        $query = EntradaCendis::with(['areaAlmacen', 'areaSurtimiento', 'usuario.persona'])
            ->where('status', 'Terminado')
            ->orderBy('id_entrada', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_entrada', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('areaSurtimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if ($fechaInitDb) $query->whereDate('fecha_entrada', '>=', $fechaInitDb);
        if ($fechaFinDb)  $query->whereDate('fecha_entrada', '<=', $fechaFinDb);

        $entradas = $query->paginate(self::PER_PAGE)->withQueryString();

        return view('inventario.entradas_cendis.terminadas', compact(
            'entradas', 'buscar', 'fechaInit', 'fechaFin'
        ));
    }

    /**
     * Formulario de filtros para generar reportes.
     */
    public function reportes(Request $request)
    {
        $areasAlmacen  = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        $areasSurtimiento = AreaSurtimiento::where('activo', 1)->orderBy('nombre')->get();

        return view('inventario.entradas_cendis.reportes', compact('areasAlmacen', 'areasSurtimiento'));
    }

    /**
     * Generar reporte histórico de entradas.
     */
    public function imprimir(Request $request)
    {
        $buscar              = $request->get('buscar', '');
        $fechaInit           = $request->get('fecha_inicio', '');
        $fechaFin            = $request->get('fecha_fin', '');
        $idAreaAlmacen       = $request->get('id_area_almacen', '');
        $idAreaSurtimiento   = $request->get('id_area_surtimiento', '');

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        if ($errorMsg) {
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        $query = EntradaCendis::with(['detalles.insumo', 'areaAlmacen', 'areaSurtimiento', 'usuario.persona'])
            ->orderBy('fecha_entrada', 'desc')
            ->orderBy('hora_entrada', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('id_entrada', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('areaAlmacen', fn($q2) => $q2->where('nombre', 'LIKE', "%{$buscar}%"))
                  ->orWhereHas('areaSurtimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if (!empty($idAreaAlmacen))     $query->where('id_area_almacen', $idAreaAlmacen);
        if (!empty($idAreaSurtimiento)) $query->where('id_area_surtimiento', $idAreaSurtimiento);

        $query->where('status', 'Terminado');

        if ($fechaInitDb) $query->whereDate('fecha_entrada', '>=', $fechaInitDb);
        if ($fechaFinDb)  $query->whereDate('fecha_entrada', '<=', $fechaFinDb);

        $entradas = $query->limit(500)->get();

        return view('inventario.entradas_cendis.reporte_impresion', compact(
            'entradas', 'buscar', 'fechaInit', 'fechaFin'
        ));
    }




}

