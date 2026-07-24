@extends('layouts.reporte_base')

@section('title', 'Lista completa de Médicos')

@section('report_title', 'Listado de Médicos RX')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 60px;">#</th>
            <th style="width: 150px;">Abreviatura</th>
            <th>Nombre del Médico</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($medicos as $index => $med)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td class="center font-monospace" style="font-weight: bold;">{{ $med->abreviatura }}</td>
                <td>{{ $med->nombre }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align: center; font-style: italic; padding: 15px 0;">
                    No hay médicos registrados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
