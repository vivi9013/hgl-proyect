@extends('layouts.reporte_base')

@section('title', 'Reporte Sedes - Hospital General')

@section('report_title')
    Catálogo de Sedes
@endsection

@section('report_subheader')
    @if(!empty($buscar))
        <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
            <strong>Filtro aplicado:</strong> "{{ $buscar }}"
        </p>
    @endif
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de registros:</strong> {{ $sedes->count() }}
    </p>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 40px;">#</th>
            <th>Sede</th>
            <th class="center" style="width: 90px;">Abreviatura</th>
            <th class="center" style="width: 120px;">Fecha de Registro</th>
            <th class="center" style="width: 90px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sedes as $index => $sed)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $sed->nombre }}</td>
                <td class="center">{{ $sed->abreviatura }}</td>
                <td class="center">
                    {{ $sed->fecha ? \Carbon\Carbon::parse($sed->fecha)->format('d/m/Y') : 'N/A' }}
                </td>
                <td class="center">
                    {{ $sed->activo == 1 ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #555;">
                    No hay registros para mostrar.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
