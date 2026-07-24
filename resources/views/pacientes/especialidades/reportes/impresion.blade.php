@extends('layouts.reporte_base')

@section('title', 'Lista completa de Especialidades')

@section('report_title', 'Listado de Especialidades RX')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 60px;">#</th>
            <th style="width: 150px;">Abreviatura</th>
            <th>Especialidad</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($especialidades as $index => $esp)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td class="center font-monospace" style="font-weight: bold;">{{ $esp->abreviatura }}</td>
                <td>{{ $esp->nombre }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align: center; font-style: italic; padding: 15px 0;">
                    No hay especialidades registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
