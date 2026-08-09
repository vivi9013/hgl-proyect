@extends('layouts.reporte_base')

@section('title', 'Reporte de Entradas al CENDIS – HGL')

@push('styles')
<style>
    .titulo-reporte { padding: 10px 0; border-bottom: 1px solid #e2e8f0; margin-bottom: 12px; }
    .titulo-reporte h1 { font-size: 14px; font-weight: bold; color: #0f172a; }
    .titulo-reporte .filtros { font-size: 10px; color: #64748b; margin-top: 3px; }

    /* ── Línea de resumen ── */
    .resumen-linea {
        font-size: 10px;
        color: #555;
        margin-bottom: 8px;
    }
    .resumen-linea strong { color: #111; }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    thead tr { background-color: #1d4ed8; color: #fff; }
    thead th { padding: 7px 8px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
    tbody tr { border-bottom: 1px solid #e2e8f0; }
    tbody tr:nth-child(even) { background-color: #f8fafc; }
    tbody td { padding: 6px 8px; vertical-align: top; font-size: 10.5px; }

    .folio-badge { font-weight: bold; color: #1d4ed8; font-family: monospace; }
    .faltante-rojo { color: #dc2626; font-weight: bold; }
    .faltante-ok { color: #16a34a; font-weight: bold; }
</style>
@endpush

@section('content')

    {{-- Título y filtros activos --}}
    <div class="titulo-reporte">
        <h1>Reporte Histórico de Entradas al CENDIS</h1>
        <div class="filtros">
            @if($fechaInit || $fechaFin)
                Período: {{ $fechaInit ? \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') : '—' }}
                al {{ $fechaFin ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') : '—' }}
                &nbsp;&bull;&nbsp;
            @endif
            @if($buscar) Búsqueda: "{{ $buscar }}" &nbsp;&bull;&nbsp; @endif
            Total de registros: {{ $entradas->count() }}
            @if($entradas->count() >= 500)
                (limitado a 500 registros)
            @endif
        </div>
    </div>

    {{-- Resumen en una línea --}}
    @php
        $totalEntradas = $entradas->count();
        $totalInsumos  = $entradas->sum('total_productos');
        $totalCantidad = $entradas->sum('total_cantidad');
        $totalFaltante = $entradas->sum('faltante');
    @endphp
    <p class="resumen-linea">
        <strong>{{ $totalEntradas }}</strong> entradas
        &nbsp;·&nbsp;
        <strong>{{ $totalInsumos }}</strong> insumos distintos
        &nbsp;·&nbsp;
        <strong>{{ $totalCantidad }}</strong> unidades entregadas
        &nbsp;·&nbsp;
        <strong>{{ $totalFaltante }}</strong> faltante total
    </p>

    {{-- Tabla principal --}}
    @if($entradas->isEmpty())
        <p style="text-align:center; color:#64748b; padding: 20px 0; font-style: italic;">
            No se encontraron entradas con los filtros aplicados.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:35px;">#</th>
                    <th style="width:90px;">Folio</th>
                    <th>Área de Almacén</th>
                    <th>Área de Surtimiento</th>
                    <th style="width:65px; text-align:center;">Insumos</th>
                    <th style="width:65px; text-align:center;">Solicitado</th>
                    <th style="width:65px; text-align:center;">Entregado</th>
                    <th style="width:65px; text-align:center;">Faltante</th>
                    <th style="width:80px;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entradas as $i => $entrada)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="folio-badge">ENT-{{ str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT) }}</span></td>
                        <td>{{ $entrada->areaAlmacen->nombre ?? '—' }}</td>
                        <td>{{ $entrada->areaSurtimiento->nombre ?? '—' }}</td>
                        <td style="text-align:center;">{{ $entrada->total_productos }}</td>
                        <td style="text-align:center;">{{ $entrada->solicitado }}</td>
                        <td style="text-align:center; font-weight:bold;">{{ $entrada->total_cantidad }}</td>
                        <td style="text-align:center;" class="{{ $entrada->faltante > 0 ? 'faltante-rojo' : 'faltante-ok' }}">{{ $entrada->faltante }}</td>
                        <td>{{ $entrada->fecha_entrada ? \Carbon\Carbon::parse($entrada->fecha_entrada)->format('d/m/Y') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right; font-weight:bold;">Totales:</td>
                    <td style="text-align:center; font-weight:bold;">{{ $totalInsumos }}</td>
                    <td style="text-align:center;"></td>
                    <td style="text-align:center; font-weight:bold;">{{ $totalCantidad }}</td>
                    <td style="text-align:center;" class="{{ $totalFaltante > 0 ? 'faltante-rojo' : 'faltante-ok' }}">{{ $totalFaltante }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

@endsection
