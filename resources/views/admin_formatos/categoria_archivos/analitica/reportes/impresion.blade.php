@extends('layouts.reporte_base')

@section('title', 'Reporte - Categoría de Archivos')

@section('report_title', 'LISTA COMPLETA DE LA CATEGORIZACIÓN DE ARCHIVOS')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No</th>
            <th>Categoría</th>
            <th class="center" style="width:180px;">Cantidad de archivos</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($categorias as $index => $cat)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $cat->categoria }}</td>
                <td class="center">{{ $cat->archivos_count }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron categorías activas con formatos asociados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
