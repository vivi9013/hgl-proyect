@extends('layouts.reporte_base')

@section('title', 'Reporte de Solicitudes de Servicio')
@section('report_title', 'Reporte de Solicitudes de Servicio')

@section('content')
<style>
    .meta-filtros {
        font-size: 11px;
        color: #555;
        margin-bottom: 15px;
    }
    .badge-estado-print {
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
    }
</style>

<div class="meta-filtros">
    <strong>Filtro Estado:</strong> {{ ucfirst($estado) }} | 
    <strong>Rango:</strong> {{ $fechaDesde ? $fechaDesde : 'Inicio' }} al {{ $fechaHasta ? $fechaHasta : 'Hoy' }} |
    <strong>Total Registros:</strong> {{ count($servicios) }}
</div>

<table>
    <thead>
        <tr>
            <th class="num" style="width: 5%;">#</th>
            <th style="width: 8%;">Folio</th>
            <th style="width: 15%;">Área</th>
            <th style="width: 32%;">Descripción del Servicio</th>
            <th style="width: 12%;">Fecha Petición</th>
            <th style="width: 15%;">Atiende (Técnico)</th>
            <th style="width: 13%;">Estatus Final</th>
        </tr>
    </thead>
    <tbody>
        @forelse($servicios as $i => $s)
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td class="center"><strong>#{{ $s->id }}</strong></td>
                <td>{{ $s->area ? $s->area->area : '—' }}</td>
                <td>{{ $s->descripcion_servicio }}</td>
                <td class="center">
                    {{ $s->fecha_peticion ? \Carbon\Carbon::parse($s->fecha_peticion)->format('d/m/Y') : '—' }}
                    @if($s->hora_peticion)
                        <br><small>{{ \Carbon\Carbon::parse($s->hora_peticion)->format('h:i a') }}</small>
                    @endif
                </td>
                <td>{{ $s->nombre_servidor ?? 'Sin Asignar' }}</td>
                <td class="center">
                    <span class="badge-estado-print">
                        {{ $s->estatus_final ?? ($s->liberado ? 'Liberado' : 'Pendiente') }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="center">No se encontraron solicitudes de servicio registradas con los criterios seleccionados.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer-info" style="margin-top: 15px;">
    <p><strong>Total de solicitudes mostradas:</strong> {{ count($servicios) }}</p>
</div>
@endsection
