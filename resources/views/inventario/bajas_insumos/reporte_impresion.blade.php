@extends('layouts.reporte_base')

@section('title', 'Reporte - Bajas de Insumos')

@section('report_title', 'BAJAS DE INSUMOS')

@section('extra_actions')
    <a href="{{ route('bajas_insumos.index') }}" class="btn-close-win" style="text-decoration:none; margin-right:8px;">
        ← Regresar al módulo
    </a>
    <button class="btn-close-win" onclick="window.close()">✕ Cerrar</button>
@endsection

@push('styles')
    .filtros-activos {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 14px;
        font-size: 11px;
        color: #4b5563;
    }
    .badge-activa {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }
    .badge-cancelada {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .alerta-limite {
        background-color: #fffbeb; 
        border: 1px solid #fef3c7; 
        color: #92400e; 
        padding: 8px 12px; 
        margin-bottom: 14px; 
        border-radius: 6px; 
        font-size: 11px;
    }
@endpush

@section('report_subheader')
<div style="margin-bottom: 12px; font-size: 11px; color: #444;">
    <strong>Total de registros:</strong> {{ $bajas->count() }}
</div>

@if($buscar || $fechaInit || $fechaFin)
    <div class="filtros-activos">
        <strong>Filtros aplicados:</strong>
        @if($buscar) &nbsp;Búsqueda: "{{ $buscar }}" @endif
        @if($fechaInit) &nbsp;| Desde: {{ \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') }} @endif
        @if($fechaFin) &nbsp;| Hasta: {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }} @endif
    </div>
@endif

@if($bajas->count() >= 500)
    <div class="alerta-limite no-print">
        <strong>Aviso:</strong> El reporte se ha limitado a los 500 registros más recientes para optimizar el rendimiento y la impresión. Por favor, utilice los filtros de fecha o búsqueda en la pantalla del listado para delimitar los resultados si requiere registros anteriores.
    </div>
@endif
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">#</th>
            <th>Insumo</th>
            <th>Clave</th>
            <th>Área de Almacén</th>
            <th>Motivo</th>
            <th>Cantidad</th>
            <th>Fecha Baja</th>
            <th>Hora</th>
            <th class="center" style="width:90px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($bajas as $index => $baja)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $baja->insumo->descripcion ?? '—' }}</td>
                <td>{{ $baja->insumo->clave ?? '—' }}</td>
                <td>{{ $baja->areaAlmacen->nombre ?? '—' }}</td>
                <td>{{ $baja->motivo }}</td>
                <td class="center">{{ $baja->cantidad }}</td>
                <td class="center">{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '—' }}</td>
                <td class="center">{{ $baja->hora_baja ?? '—' }}</td>
                <td class="center">
                    @if($baja->cancelado === 'Si')
                        <span class="badge-cancelada">Cancelada</span>
                    @else
                        <span class="badge-activa">Activa</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center; padding: 20px; color:#9ca3af;">
                    No hay bajas de insumos registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
