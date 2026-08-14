@extends('layouts.reporte_base')

@php
    // Calcular títulos: si $buscar no está vacío, agregar sufijo
    $sufijo       = ($buscar !== '') ? ' — Filtrado: "' . $buscar . '"' : '';
    $tituloPage   = 'Reporte - Categoría de Archivos' . $sufijo;
    $tituloReport = 'LISTA COMPLETA DE LA CATEGORIZACIÓN DE ARCHIVOS' . strtoupper($sufijo);
@endphp

@section('title', $tituloPage)

@section('report_title', $tituloReport)

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No</th>
            <th class="center" style="width:100px;">Categoría</th>
            <th class="center" style="width:100px;">Cantidad de archivos</th>
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
