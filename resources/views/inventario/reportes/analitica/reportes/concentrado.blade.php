@extends('layouts.reporte_base')

@section('title', 'Concentrado CENDIS')

@section('report_title')
    CONCENTRADO MENSUAL CENDIS
@endsection

@section('extra_actions')
    <a href="{{ route('reportes_inventario.index') }}" class="btn-close-win" style="text-decoration:none; margin-right:8px;">
        ← Regresar al módulo
    </a>
    <button class="btn-close-win" onclick="window.close()">✕ Cerrar</button>
@endsection

@push('styles')
    @page {
        size: landscape;
        margin: 5mm;
    }
    .page {
        max-width: 100% !important;
        padding: 5px !important;
        margin: 0 auto !important;
    }
    .header-img {
        max-height: 80px;
        object-fit: contain;
    }
    .report-title {
        font-size: 13px !important;
        margin-bottom: 5px !important;
    }
    .info-header {
        font-size: 11px;
        margin-bottom: 12px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        padding: 8px 12px;
        border-radius: 4px;
    }
    table {
        font-size: 8px !important;
        width: 100% !important;
        border-collapse: collapse !important;
        margin-top: 10px !important;
    }
    table th {
        background-color: #1f4e78 !important;
        color: #ffffff !important;
        border: 1px solid #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        padding: 4px 2px !important;
    }
    table td {
        border: 1px solid #000000 !important;
        padding: 3px 2px !important;
    }
    .text-center {
        text-align: center !important;
    }
    .text-left {
        text-align: left !important;
    }
    .bg-total {
        background-color: #d9d9dc !important;
        color: #1f4e78 !important;
        font-weight: bold !important;
    }
    .bg-neutral {
        background-color: #f3f4f6 !important;
    }
    .footer-info {
        font-size: 10px !important;
        margin-top: 15px !important;
    }
    .legend-table {
        width: auto !important;
        margin-top: 20px !important;
        font-size: 9px !important;
    }
    .legend-table th, .legend-table td {
        border: 1px solid #ccc !important;
        padding: 4px 10px !important;
    }
    .legend-table th {
        background-color: #f3f4f6 !important;
        color: #333 !important;
        font-weight: bold !important;
    }
@endpush

@section('report_subheader')
<div class="info-header">
    <div class="row" style="display: flex; justify-content: space-between;">
        <div>
            <strong>Almacén:</strong> {{ $areaAlmacen->nombre }}
        </div>
        <div>
            <strong>Periodo:</strong> {{ $nombreMes }} / {{ $anoPedido }}
        </div>
    </div>
</div>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th>Clave</th>
            <th>Descripción</th>
            <th>Stock Inicial</th>
            <th>Surtido CENDIS</th>
            <th>Bajas</th>
            <th>Devoluciones</th>
            @foreach ($areasAbastecimiento as $area)
                <th title="{{ $area->nombre }}">{{ $area->siglas }}</th>
            @endforeach
            <th>Total Entregado</th>
            <th>Stock Final</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($insumos as $insumo)
            @php
                // 1. Stock Inicial
                $stockActual = $stocksActuales->get($insumo->id_insumo, 0);
                $surtidoCendisHist = $entregasCendisHistoricas->get($insumo->id_insumo, 0);
                $pedidosHist = $pedidosHistoricos->get($insumo->id_insumo, 0);
                $bajasHist = $bajasHistoricas->get($insumo->id_insumo, 0);
                $devolucionesHist = $devolucionesHistoricas->get($insumo->id_insumo, 0);

                $stockInicial = ($stockActual - $surtidoCendisHist + $pedidosHist + $bajasHist - $devolucionesHist);

                // 2. Transacciones del mes
                $surtidoCendis = $surtidosCendisMes->get($insumo->id_insumo, 0);
                $bajas = $bajasMes->get($insumo->id_insumo, 0);
                $devoluciones = $devolucionesMes->get($insumo->id_insumo, 0);

                // 3. Entregas por área y cálculo del total
                $totalEntregado = 0;
            @endphp
            <tr>
                <td class="text-center">{{ $insumo->clave }}</td>
                <td class="text-left">{{ $insumo->descripcion }}</td>
                <td class="text-center bg-neutral">{{ $stockInicial }}</td>
                <td class="text-center bg-neutral">{{ $surtidoCendis }}</td>
                <td class="text-center bg-neutral">{{ $bajas }}</td>
                <td class="text-center bg-neutral">{{ $devoluciones }}</td>

                @foreach ($areasAbastecimiento as $area)
                    @php
                        $surtidoArea = 0;
                        if ($entregasPorArea->has($insumo->id_insumo)) {
                            $areaData = $entregasPorArea->get($insumo->id_insumo)->firstWhere('id_area_abastecimiento', $area->id_area_abastecimiento);
                            $surtidoArea = $areaData ? $areaData->total : 0;
                        }
                        $totalEntregado += $surtidoArea;
                    @endphp
                    <td class="text-center">{{ $surtidoArea > 0 ? $surtidoArea : '0' }}</td>
                @endforeach

                @php
                    // 4. Stock Final
                    $stockFinal = ($stockInicial + $surtidoCendis + $devoluciones) - ($bajas + $totalEntregado);
                @endphp

                <td class="text-center bg-total">{{ $totalEntregado }}</td>
                <td class="text-center bg-total">{{ $stockFinal }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 8 + $areasAbastecimiento->count() }}" class="text-center py-4" style="font-size: 11px !important;">
                    No se encontraron registros para el periodo y áreas seleccionadas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Tabla de Leyenda / Siglas de Áreas --}}
@if ($areasAbastecimiento->isNotEmpty())
    <table class="legend-table">
        <thead>
            <tr>
                <th>Siglas</th>
                <th>Área de Abastecimiento</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($areasAbastecimiento as $area)
                <tr>
                    <td class="text-center"><strong>{{ $area->siglas }}</strong></td>
                    <td>{{ $area->nombre }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
