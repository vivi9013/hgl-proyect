@extends('layouts.reporte_base')

@section('title', 'Reporte - Categoría de Módulos')

@section('report_title', 'LISTA COMPLETA DE CATEGORÍAS DE MÓDULOS')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No</th>
            <th>Categoría</th>
            <th>Proyecto</th>
            <th class="center" style="width:120px;">Panel Abierto</th>
            <th class="center" style="width:100px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($categorias as $index => $cat)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $cat->categoria }}</td>
                <td>{{ $cat->proyecto }}</td>
                <td class="center text-uppercase" style="font-weight: bold; font-size: 10px;">{{ $cat->colapsado }}</td>
                <td class="center">
                    @if ($cat->activo == 1)
                        Activo
                    @else
                        Inactivo
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron categorías de módulos registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
