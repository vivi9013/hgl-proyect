<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\AreaAlmacen;
use App\Models\Inventario\Pedido;
use App\Helpers\FechaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteInventarioController extends Controller
{
    /**
     * Vista principal con los formularios de reportes.
     */
    public function index()
    {
        return view('inventario.reportes.index');
    }

    /**
     * Retorna áreas de abastecimiento activas para AJAX.
     */
    public function areasAbastecimiento()
    {
        $areas = AreaAbastecimiento::where('activo', 1)
            ->orderBy('nombre', 'asc')
            ->get(['id_area_abastecimiento', 'nombre']);

        return response()->json($areas);
    }

    /**
     * Retorna subáreas activas de un área para AJAX.
     */
    public function subareasAbastecimiento($idArea)
    {
        $subareas = SubareaAbastecimiento::where('id_area_abastecimiento', $idArea)
            ->where('activo', 1)
            ->orderBy('nombre', 'asc')
            ->get(['id_subarea_abastecimiento', 'nombre']);

        return response()->json($subareas);
    }

    /**
     * Retorna áreas de almacén activas para AJAX.
     */
    public function areasAlmacen()
    {
        $areas = AreaAlmacen::where('activo', 1)
            ->orderBy('nombre', 'asc')
            ->get(['id_area_almacen', 'nombre']);

        return response()->json($areas);
    }

    /**
     * Vista de impresión del Reporte Mensual de Entregas.
     */
    public function imprimirEntregas(Request $request)
    {
        $request->validate([
            'A' => 'required|integer',  // id_area_abastecimiento
            'S' => 'required|integer',  // id_subarea_abastecimiento
            'M' => 'required|integer|between:1,12', // mes
            'AN1' => 'required|integer', // año
        ]);

        $areaId = $request->get('A');
        $subareaId = $request->get('S');
        $mes = $request->get('M');
        $ano = $request->get('AN1');

        $area = AreaAbastecimiento::findOrFail($areaId);
        $subarea = SubareaAbastecimiento::findOrFail($subareaId);

        // Mes en español
        $nombreMes = FechaHelper::obtenerNombreMes($mes);

        // Obtener id_almacen_subarea
        $almacenSubarea = DB::table('almacen_subareas')
            ->where('id_area_abastecimiento', $areaId)
            ->where('id_subarea_abastecimiento', $subareaId)
            ->first();

        $almacenSubareaId = $almacenSubarea ? $almacenSubarea->id_almacen_subarea : 0;

        // Query distinct insumos delivered in that month/year
        $insumos = DB::table('detalle_pedidos as dp')
            ->join('insumos as i', 'dp.id_insumo', '=', 'i.id_insumo')
            ->join('pedidos as p', 'dp.id_pedido', '=', 'p.id_pedido')
            ->leftJoin('detalle_almacen_subareas as das', function ($join) use ($almacenSubareaId) {
                $join->on('das.id_insumo', '=', 'dp.id_insumo')
                     ->where('das.id_almacen_subarea', '=', $almacenSubareaId);
            })
            ->whereMonth('p.fecha_entrega', $mes)
            ->whereYear('p.fecha_entrega', $ano)
            ->where('p.id_area_abastecimiento', $areaId)
            ->where('p.id_subarea_abastecimiento', $subareaId)
            ->select('dp.id_insumo', 'das.fondo_fijo', 'i.clave', 'i.descripcion')
            ->distinct()
            ->orderBy('i.clave', 'asc')
            ->get();

        $insumoIds = $insumos->pluck('id_insumo')->toArray();

        // Obtener entregas diarias agrupadas por insumo y día
        $entregasDiarias = DB::table('detalle_pedidos as dp')
            ->join('pedidos as p', 'dp.id_pedido', '=', 'p.id_pedido')
            ->whereMonth('p.fecha_entrega', $mes)
            ->whereYear('p.fecha_entrega', $ano)
            ->where('p.id_area_abastecimiento', $areaId)
            ->where('p.id_subarea_abastecimiento', $subareaId)
            ->whereIn('dp.id_insumo', $insumoIds)
            ->select('dp.id_insumo', DB::raw('DAY(p.fecha_entrega) as dia'), DB::raw('SUM(dp.surtido) as total_surtido'))
            ->groupBy('dp.id_insumo', DB::raw('DAY(p.fecha_entrega)'))
            ->get()
            ->groupBy('id_insumo');

        // Obtener total surtido de cada insumo en el mes
        $totalesInsumo = DB::table('detalle_pedidos as dp')
            ->join('pedidos as p', 'dp.id_pedido', '=', 'p.id_pedido')
            ->whereMonth('p.fecha_entrega', $mes)
            ->whereYear('p.fecha_entrega', $ano)
            ->where('p.id_area_abastecimiento', $areaId)
            ->where('p.id_subarea_abastecimiento', $subareaId)
            ->whereIn('dp.id_insumo', $insumoIds)
            ->select('dp.id_insumo', DB::raw('SUM(dp.surtido) as total'))
            ->groupBy('dp.id_insumo')
            ->pluck('total', 'id_insumo');

        return view('inventario.reportes.analitica.reportes.entregas', compact(
            'area', 'subarea', 'mes', 'nombreMes', 'ano', 'insumos', 'entregasDiarias', 'totalesInsumo'
        ));
    }

    /**
     * Vista de impresión del Concentrado CENDIS.
     */
    public function imprimirConcentrado(Request $request)
    {
        $request->validate([
            'AA' => 'required|integer',          // id_area_almacen
            'A' => 'required|string',            // id_area_abastecimiento separadas por coma
            'M' => 'required|integer|between:1,12', // mes
            'AP' => 'required|integer',          // año pedido
        ]);

        $areaAlmacenId = $request->get('AA');
        $areasString = $request->get('A');
        $mes = $request->get('M');
        $anoPedido = $request->get('AP');

        $areaAlmacen = AreaAlmacen::findOrFail($areaAlmacenId);
        $arrayAreas = array_unique(array_filter(explode(',', $areasString)));

        $areasAbastecimiento = AreaAbastecimiento::whereIn('id_area_abastecimiento', $arrayAreas)
            ->where('activo', 1)
            ->orderBy('siglas', 'asc')
            ->get();

        // Mes en español
        $nombreMes = FechaHelper::obtenerNombreMes($mes);

        // Obtener insumos entregados en el mes y áreas elegidas
        $insumos = DB::table('detalle_pedidos as dp')
            ->join('pedidos as p', 'p.id_pedido', '=', 'dp.id_pedido')
            ->join('insumos as i', 'dp.id_insumo', '=', 'i.id_insumo')
            ->whereMonth('p.fecha_entrega', $mes)
            ->whereYear('p.fecha_entrega', $anoPedido)
            ->whereIn('p.id_area_abastecimiento', $arrayAreas)
            ->select('dp.id_insumo', 'i.clave', 'i.descripcion')
            ->distinct()
            ->orderBy('i.clave', 'asc')
            ->get();

        $insumoIds = $insumos->pluck('id_insumo')->toArray();

        // --- CÁLCULOS POR INSUMO (BATCH) ---

        // 1. Surtido CENDIS del mes
        $surtidosCendisMes = DB::table('detalle_entradas_cendis as det')
            ->join('entradas_cendis as ec', 'det.id_entrada', '=', 'ec.id_entrada')
            ->whereMonth('ec.fecha_entrada', $mes)
            ->whereYear('ec.fecha_entrada', $anoPedido)
            ->where('ec.status', 'terminado')
            ->where('ec.id_area_almacen', $areaAlmacenId)
            ->whereIn('det.id_insumo', $insumoIds)
            ->select('det.id_insumo', DB::raw('SUM(det.cantidad) as total'))
            ->groupBy('det.id_insumo')
            ->pluck('total', 'id_insumo');

        // 2. Bajas del mes
        $bajasMes = DB::table('bajasinsumos')
            ->whereMonth('fecha_baja', $mes)
            ->whereYear('fecha_baja', $anoPedido)
            ->where('cancelado', 'No')
            ->where('id_area_almacen', $areaAlmacenId)
            ->whereIn('id_insumo', $insumoIds)
            ->select('id_insumo', DB::raw('SUM(cantidad) as total'))
            ->groupBy('id_insumo')
            ->pluck('total', 'id_insumo');

        // 3. Devoluciones del mes
        $devolucionesMes = DB::table('detalle_devoluciones as dd')
            ->join('devoluciones as d', 'dd.id_devolucion', '=', 'd.id_devolucion')
            ->join('motivos as m', 'd.id_motivo', '=', 'm.id_motivo')
            ->whereMonth('d.fecha_devolucion', $mes)
            ->whereYear('d.fecha_devolucion', $anoPedido)
            ->where('d.status', 'terminado')
            ->where('m.modificar', 'Si')
            ->where('d.id_area_almacen', $areaAlmacenId)
            ->whereIn('dd.id_insumo', $insumoIds)
            ->select('dd.id_insumo', DB::raw('SUM(dd.cantidad) as total'))
            ->groupBy('dd.id_insumo')
            ->pluck('total', 'id_insumo');

        // 4. Stock Actual en almacén
        $stocksActuales = DB::table('insumosarea')
            ->where('id_area_almacen', $areaAlmacenId)
            ->whereIn('id_insumo', $insumoIds)
            ->pluck('stock', 'id_insumo');

        // --- CÁLCULOS HISTÓRICOS PARA STOCK INICIAL ---
        $startDate = sprintf('%04d-%02d-01', $anoPedido, $mes);

        // 5. Entregas a CENDIS históricas (desde start date hasta hoy)
        $entregasCendisHistoricas = DB::table('detalle_entradas_cendis as det')
            ->join('entradas_cendis as ec', 'det.id_entrada', '=', 'ec.id_entrada')
            ->where('ec.fecha_entrada', '>=', $startDate)
            ->where('ec.status', 'terminado')
            ->where('ec.id_area_almacen', $areaAlmacenId)
            ->whereIn('det.id_insumo', $insumoIds)
            ->select('det.id_insumo', DB::raw('SUM(det.cantidad) as total'))
            ->groupBy('det.id_insumo')
            ->pluck('total', 'id_insumo');

        // 6. Pedidos históricos (desde start date hasta hoy)
        $pedidosHistoricos = DB::table('detalle_pedidos as dp')
            ->join('pedidos as p', 'dp.id_pedido', '=', 'p.id_pedido')
            ->where('p.fecha_entrega', '>=', $startDate)
            ->where('p.status', 'aceptado')
            ->where('p.id_area_almacen', $areaAlmacenId)
            ->whereIn('dp.id_insumo', $insumoIds)
            ->select('dp.id_insumo', DB::raw('SUM(dp.surtido) as total'))
            ->groupBy('dp.id_insumo')
            ->pluck('total', 'id_insumo');

        // 7. Bajas históricas (desde start date hasta hoy)
        $bajasHistoricas = DB::table('bajasinsumos')
            ->where('fecha_baja', '>=', $startDate)
            ->where('cancelado', 'No')
            ->where('id_area_almacen', $areaAlmacenId)
            ->whereIn('id_insumo', $insumoIds)
            ->select('id_insumo', DB::raw('SUM(cantidad) as total'))
            ->groupBy('id_insumo')
            ->pluck('total', 'id_insumo');

        // 8. Devoluciones históricas (desde start date hasta hoy)
        $devolucionesHistoricas = DB::table('detalle_devoluciones as dd')
            ->join('devoluciones as d', 'dd.id_devolucion', '=', 'd.id_devolucion')
            ->join('motivos as m', 'd.id_motivo', '=', 'm.id_motivo')
            ->where('d.fecha_devolucion', '>=', $startDate)
            ->where('d.status', 'terminado')
            ->where('m.modificar', 'Si')
            ->where('d.id_area_almacen', $areaAlmacenId)
            ->whereIn('dd.id_insumo', $insumoIds)
            ->select('dd.id_insumo', DB::raw('SUM(dd.cantidad) as total'))
            ->groupBy('dd.id_insumo')
            ->pluck('total', 'id_insumo');

        // 9. Entregas por área y por insumo en el mes de reporte
        $entregasPorArea = DB::table('detalle_pedidos as dp')
            ->join('pedidos as p', 'dp.id_pedido', '=', 'p.id_pedido')
            ->whereMonth('p.fecha_entrega', $mes)
            ->whereYear('p.fecha_entrega', $anoPedido)
            ->whereIn('p.id_area_abastecimiento', $arrayAreas)
            ->whereIn('dp.id_insumo', $insumoIds)
            ->select('dp.id_insumo', 'p.id_area_abastecimiento', DB::raw('SUM(dp.surtido) as total'))
            ->groupBy('dp.id_insumo', 'p.id_area_abastecimiento')
            ->get()
            ->groupBy('id_insumo');

        return view('inventario.reportes.analitica.reportes.concentrado', compact(
            'areaAlmacen', 'areasAbastecimiento', 'mes', 'nombreMes', 'anoPedido', 'insumos',
            'surtidosCendisMes', 'bajasMes', 'devolucionesMes', 'stocksActuales',
            'entregasCendisHistoricas', 'pedidosHistoricos', 'bajasHistoricas', 'devolucionesHistoricas',
            'entregasPorArea', 'arrayAreas'
        ));
    }
}
