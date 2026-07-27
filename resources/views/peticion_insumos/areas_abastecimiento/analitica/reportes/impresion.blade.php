@extends('layouts.reporte_base')

@section('title', 'Reporte Catálogo de Áreas de Abastecimiento - Hospital General')

@section('report_title')
    Catálogo Oficial de Áreas de Abastecimiento
@endsection

@section('report_subheader')
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de áreas registradas:</strong> {{ $areas->count() }}
    </p>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 40px;">#</th>
            <th>Nombre del Área de Abastecimiento</th>
            <th class="center" style="width: 100px;">Siglas</th>
            <th class="center" style="width: 150px;">Subáreas Vinculadas</th>
            <th class="center" style="width: 100px;">Estatus</th>
        </tr>
    </thead>
    <tbody>
        @forelse($areas as $index => $area)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td style="font-weight: bold; color: #1a252f;">{{ $area->nombre }}</td>
                <td class="center">{{ $area->siglas ?: 'N/A' }}</td>
                <td class="center">{{ $area->subareas_count }} Subáreas</td>
                <td class="center" style="{{ $area->activo == 1 ? 'color: #137333; font-weight: bold;' : 'color: #c5221f;' }}">
                    {{ $area->activo == 1 ? 'ACTIVO' : 'INACTIVO' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="center" style="color: #555;">
                    No se encontraron áreas de abastecimiento para mostrar.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
