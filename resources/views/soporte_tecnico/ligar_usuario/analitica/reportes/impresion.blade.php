@extends('layouts.reporte_base')
@section('title', 'Técnicos y Áreas de Soporte - Reporte')
@section('report_title', 'Lista de Técnicos y Áreas de Soporte Asignadas')
@section('content')

<table>
    <thead>
        <tr>
            <th class="num">#</th>
            <th>Nombre del Trabajador</th>
            <th class="center">Áreas Asignadas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($trabajadores as $i => $row)
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td>{{ $row->nombre_completo }}</td>
                <td class="center">{{ $row->cantidad_areas }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="center">No hay trabajadores registrados.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer-info" style="margin-top: 10px;">
    <p><strong>Total de Registros:</strong> {{ count($trabajadores) }}</p>
</div>

@endsection
