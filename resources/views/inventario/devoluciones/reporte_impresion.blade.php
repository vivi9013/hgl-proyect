@extends('layouts.reporte_base')

@section('title', 'Reporte de Devoluciones – HGL')

@section('report_title', 'Reporte de Devoluciones')

@section('report_subheader')
<div style="font-size: 10px; color: #444; margin-bottom: 10px; line-height: 1.6;">
    @if($fechaInit || $fechaFin)
        <strong>Período:</strong>
        {{ $fechaInit ? \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') : '—' }}
        al {{ $fechaFin ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') : '—' }}
        &nbsp;·&nbsp;
    @endif
    <strong>Total de registros:</strong> {{ $devoluciones->count() }}
    @if($devoluciones->count() >= 500)
        <em>(limitado a 500 registros)</em>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* ── Línea de resumen ── */
    .resumen-linea {
        font-size: 10px;
        color: #555;
        margin-bottom: 8px;
    }
    .resumen-linea strong { color: #111; }

    /* ── Columna folio ── */
    .folio-cell {
        font-weight: bold;
        color: #1d4ed8;
        font-family: monospace;
        white-space: nowrap;
    }

    /* ── Badge de status ── */
    .badge-terminado {
        display: inline-block;
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 2px 7px;
        font-size: 9px;
        font-weight: bold;
        white-space: nowrap;
    }

    /* ── Sub-tabla de insumos devueltos ── */
    .sub-tabla {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .sub-tabla td {
        padding: 2px 5px;
        font-size: 9px;
        color: #374151;
        border: none;
        border-bottom: 1px dotted #e2e8f0;
        vertical-align: top;
    }
    .sub-tabla tr:last-child td { border-bottom: none; }
    /* Sin fondo propio — hereda el de la celda padre */
    .sub-tabla tr { background: none !important; }
    .sub-tabla .td-clave {
        font-weight: bold;
        color: #1d4ed8;
        font-family: monospace;
        width: 88px;
        white-space: nowrap;
    }
    .sub-tabla .td-cant {
        width: 30px;
        text-align: center;
        font-weight: bold;
        color: #111;
    }

    /* ── Ajustes finos a la tabla principal ── */
    tbody td { font-size: 10px; }
    thead th { font-size: 9.5px; }
</style>
@endpush

@section('content')

    {{-- ── Resumen en una línea ── --}}
    @php
        $totalInsumos  = $devoluciones->sum(fn($d) => $d->detalles->count());
        $totalCantidad = $devoluciones->sum(fn($d) => $d->detalles->sum('cantidad'));
    @endphp
    <p class="resumen-linea">
        <strong>{{ $devoluciones->count() }}</strong> devoluciones
        &nbsp;·&nbsp;
        <strong>{{ $totalInsumos }}</strong> tipos de insumo
        &nbsp;·&nbsp;
        <strong>{{ $totalCantidad }}</strong> unidades devueltas
    </p>

    {{-- ── Tabla principal ── --}}
    @if($devoluciones->isEmpty())
        <p style="text-align:center; color:#64748b; padding: 20px 0; font-style: italic;">
            No hay devoluciones que coincidan con los filtros seleccionados.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:28px;" class="center">#</th>
                    <th style="width:80px;">Folio</th>
                    <th>Área de Almacén</th>
                    <th>Área Abastecimiento</th>
                    <th style="width:80px;">Motivo</th>
                    <th style="width:62px;" class="center">Fecha</th>
                    <th>Insumos Devueltos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devoluciones as $i => $devolucion)
                    <tr>
                        <td class="num">{{ $i + 1 }}</td>
                        <td>
                            <span class="folio-cell">DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>{{ $devolucion->areaAlmacen->nombre ?? '—' }}</td>
                        <td>{{ $devolucion->areaAbastecimiento->nombre ?? '—' }}</td>
                        <td>{{ $devolucion->motivo->descripcion ?? '—' }}</td>
                        <td class="center">
                            {{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            @if($devolucion->detalles->isNotEmpty())
                                <table class="sub-tabla">
                                    @foreach($devolucion->detalles as $detalle)
                                        <tr>
                                            <td class="td-clave">{{ $detalle->insumo->clave ?? '—' }}</td>
                                            <td>{{ $detalle->insumo->descripcion ?? '—' }}</td>
                                            <td class="td-cant">{{ $detalle->cantidad }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <span style="color:#94a3b8; font-style:italic; font-size:9px;">Sin insumos</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endsection
