@extends('layouts.reporte_base')

@section('title', 'Reporte - Áreas de Surtimiento')

@section('report_title', 'LISTA COMPLETA DE ÁREAS DE SURTIMIENTO')

@section('extra_actions')
    <a href="{{ route('areas_surtimiento.index') }}" class="btn-close-win" style="text-decoration:none; margin-right:8px;">
        ← Regresar al módulo
    </a>
    <button class="btn-close-win" onclick="window.close()">✕ Cerrar</button>
@endsection

@section('report_subheader')
<div style="margin-bottom: 12px; font-size: 11px; color: #444;">
    <strong>Total de registros:</strong> {{ $areas->count() }}
</div>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">#</th>
            <th>Nombre del Área de Surtimiento</th>
            <th>Tipo</th>
            <th>Fecha de Registro</th>
            <th>Hora</th>
            <th class="center" style="width:100px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($areas as $index => $area)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $area->nombre }}</td>
                <td>{{ $area->tipo }}</td>
                <td>{{ $area->fecha_registro ? \Carbon\Carbon::parse($area->fecha_registro)->format('d/m/Y') : '—' }}</td>
                <td>{{ $area->hora_registro ?? '—' }}</td>
                <td class="center">
                    @if ($area->activo == 1)
                        Activo
                    @else
                        Inactivo
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:12px; color:#666;">
                    No hay áreas de surtimiento registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
