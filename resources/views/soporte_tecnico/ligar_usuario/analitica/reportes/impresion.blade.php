@extends('layouts.reporte_base')
@section('title', 'Técnicos y Áreas de Soporte - Reporte')
@section('report_title', 'Lista Oficial de Técnicos y Áreas de Atención Asignadas')
@section('content')

<table>
    <thead>
        <tr>
            <th class="num" style="width: 40px;">#</th>
            <th style="width: 250px;">Nombre del Trabajador</th>
            <th class="center" style="width: 80px;">Total Áreas</th>
            <th>Áreas de Atención Asignadas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($trabajadores as $i => $row)
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td><strong>{{ $row->nombre_completo }}</strong></td>
                <td class="center">
                    @if($row->cantidad_areas > 0)
                        <strong>{{ $row->cantidad_areas }}</strong>
                    @else
                        <span style="color: #999;">0</span>
                    @endif
                </td>
                <td>
                    @if(!empty($row->nombres_areas))
                        {{ $row->nombres_areas }}
                    @else
                        <em style="color: #999;">Sin áreas asignadas</em>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="center">No hay trabajadores registrados.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer-info" style="margin-top: 15px;">
    <p><strong>Total de Trabajadores Evaluados:</strong> {{ count($trabajadores) }}</p>
</div>

@endsection
