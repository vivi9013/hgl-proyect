@extends('layouts.reporte_base')

@section('title', 'Reporte Diario de Entregas')

@section('report_title')
    REPORTE DIARIO DE ENTREGAS DE INSUMOS
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
        font-size: 9px !important;
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
        padding: 5px 4px !important;
        font-size: 9px !important;
    }
    table td {
        border: 1px solid #000000 !important;
        padding: 4px 4px !important;
        font-size: 9px !important;
    }
    .text-center {
        text-align: center !important;
    }
    .text-left {
        text-align: left !important;
    }
    .text-right {
        text-align: right !important;
    }
    .bg-total {
        background-color: #d9d9dc !important;
        color: #1f4e78 !important;
        font-weight: bold !important;
    }
    .footer-signatures {
        margin-top: 30px;
        display: flex;
        justify-content: space-around;
        text-align: center;
        font-size: 10px;
    }
    .signature-line {
        border-top: 1px solid #000;
        width: 200px;
        margin: 40px auto 5px auto;
    }
@endpush

@section('report_subheader')
<div class="info-header">
    <div class="row" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
        <div>
            <strong>Área de Almacén:</strong> {{ $areaAlmacen->nombre }}
        </div>
        <div>
            <strong>Área Asignada:</strong> {{ $area->nombre }}
        </div>
        <div>
            <strong>Fecha:</strong> {{ $fechaFormateada }}
        </div>
    </div>
</div>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th style="width: 4%;">#</th>
            <th style="width: 10%;">Folio</th>
            <th style="width: 16%;">Clave</th>
            <th style="width: 42%;">Descripción del Insumo</th>
            <th style="width: 9%;">Solicitado</th>
            <th style="width: 9%;">Surtido</th>
            <th style="width: 10%;">Faltante</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalSolicitado = 0;
            $totalSurtido = 0;
            $totalFaltante = 0;
        @endphp
        @forelse ($entregas as $index => $item)
            @php
                $totalSolicitado += $item->solicitado;
                $totalSurtido += $item->surtido;
                $totalFaltante += $item->faltante;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">PED-{{ str_pad($item->id_pedido, 5, '0', STR_PAD_LEFT) }}</td>
                <td class="text-center fw-bold">{{ $item->clave }}</td>
                <td class="text-left">{{ mb_strtoupper($item->descripcion) }}</td>
                <td class="text-center">{{ $item->solicitado }}</td>
                <td class="text-center fw-bold text-success">{{ $item->surtido }}</td>
                <td class="text-center text-danger">{{ $item->faltante }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-4" style="font-size: 11px !important;">
                    No se encontraron entregas de insumos para la fecha y áreas seleccionadas.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if(count($entregas) > 0)
        <tfoot>
            <tr class="bg-total">
                <td colspan="4" class="text-right" style="padding-right: 10px;">TOTALES:</td>
                <td class="text-center">{{ $totalSolicitado }}</td>
                <td class="text-center">{{ $totalSurtido }}</td>
                <td class="text-center">{{ $totalFaltante }}</td>
            </tr>
        </tfoot>
    @endif
</table>

<div class="footer-signatures">
    <div>
        <div class="signature-line"></div>
        <strong>Entrega (Almacén)</strong>
    </div>
    <div>
        <div class="signature-line"></div>
        <strong>Recibe (Área Asignada)</strong>
    </div>
</div>
@endsection
