@extends('layouts.reporte_base')

@section('title', 'Reporte Catálogo de Subáreas de Abastecimiento - Hospital General')

@section('report_title')
    Catálogo Oficial de Subáreas de Abastecimiento
@endsection

@section('report_subheader')
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de subáreas registradas:</strong> {{ $subareas->count() }}
    </p>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 40px;">#</th>
            <th>Nombre de la Subárea</th>
            <th style="width: 90px;">Siglas</th>
            <th>Área Principal de Abastecimiento</th>
            <th class="center" style="width: 100px;">Estatus</th>
        </tr>
    </thead>
    <tbody>
        @forelse($subareas as $index => $subarea)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td style="font-weight: bold; color: #1a252f;">{{ $subarea->nombre }}</td>
                <td>{{ $subarea->siglas ?: 'N/A' }}</td>
                <td>{{ $subarea->areaAbastecimiento->nombre ?? 'N/A' }}</td>
                <td class="center" style="{{ $subarea->activo == 1 ? 'color: #137333; font-weight: bold;' : 'color: #c5221f;' }}">
                    {{ $subarea->activo == 1 ? 'ACTIVO' : 'INACTIVO' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="center" style="color: #555;">
                    No se encontraron subáreas de abastecimiento para mostrar.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
