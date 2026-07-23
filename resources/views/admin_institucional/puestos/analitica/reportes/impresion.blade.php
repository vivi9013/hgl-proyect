@extends('layouts.reporte_base')

@section('title', 'Reporte Puestos - Hospital General de Linares')

@section('report_title')
    Catálogo de Puestos
@endsection

@section('report_subheader')
    @if(!empty($buscar))
        <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
            <strong>Filtro aplicado:</strong> "{{ $buscar }}"
        </p>
    @endif
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de registros:</strong> {{ $puestos->count() }}
    </p>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 40px;">#</th>
            <th>Puesto</th>
            <th class="center" style="width: 120px;">Fecha de Actualización</th>
            <th class="center" style="width: 90px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($puestos as $index => $pue)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $pue->puesto }}</td>
                <td class="center">
                    {{ $pue->fecha ? \Carbon\Carbon::parse($pue->fecha)->format('d/m/Y') : 'N/A' }}
                </td>
                <td class="center">
                    {{ $pue->activo == 1 ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #555;">
                    No hay registros para mostrar.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
