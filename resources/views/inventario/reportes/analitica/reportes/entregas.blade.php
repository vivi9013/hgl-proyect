@extends('layouts.reporte_base')

@section('title', 'Reporte Mensual de Entregas')

@section('report_title')
    REPORTE MENSUAL DE ENTREGAS
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
        font-size: 8px !important;
    }
    table td {
        border: 1px solid #000000 !important;
        padding: 3px 2px !important;
        font-size: 8px !important;
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
    .footer-info {
        font-size: 10px !important;
        margin-top: 15px !important;
    }
@endpush

@section('report_subheader')
<div class="info-header">
    <div class="row" style="display: flex; justify-content: space-between;">
        <div>
            <strong>Área:</strong> {{ $area->nombre }}
        </div>
        <div>
            <strong>Subárea:</strong> {{ $subarea->nombre }}
        </div>
        <div>
            <strong>Periodo:</strong> {{ $nombreMes }} / {{ $ano }}
        </div>
    </div>
</div>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th style="width: 8%;">Clave</th>
            <th style="width: 25%;">Descripción</th>
            <th style="width: 4%;">FF</th>
            @for ($dia = 1; $dia <= 31; $dia++)
                <th style="width: 1.8%;">{{ $dia }}</th>
            @endfor
            <th style="width: 5%;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($insumos as $insumo)
            <tr>
                <td class="text-center">{{ $insumo->clave }}</td>
                <td class="text-left">{{ $insumo->descripcion }}</td>
                <td class="text-center bg-total">{{ $insumo->fondo_fijo ?? 0 }}</td>
                
                @for ($dia = 1; $dia <= 31; $dia++)
                    @php
                        $surtidoDia = '';
                        if ($entregasDiarias->has($insumo->id_insumo)) {
                            $diaData = $entregasDiarias->get($insumo->id_insumo)->firstWhere('dia', $dia);
                            if ($diaData && $diaData->total_surtido > 0) {
                                $surtidoDia = $diaData->total_surtido;
                            }
                        }
                    @endphp
                    <td class="text-center">{{ $surtidoDia }}</td>
                @endfor

                <td class="text-center bg-total">
                    {{ $totalesInsumo->get($insumo->id_insumo, 0) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="35" class="text-center py-4" style="font-size: 11px !important;">
                    No se encontraron entregas registradas para el periodo seleccionado.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
