@extends('layouts.reporte_base')

@php
    // Calcular título según número de categorías filtradas
    // (nunca usar {{ }} anidado dentro de los argumentos de @section)
    if ($categoriasSeleccionadas->count() === 1) {
        $tituloPage   = 'Reporte - Archivos de ' . $categoriasSeleccionadas->first()->categoria;
        $tituloReport = 'LISTA COMPLETA DE ARCHIVOS DE ' . strtoupper($categoriasSeleccionadas->first()->categoria);
    } else {
        $tituloPage   = 'Reporte - Lista de Archivos';
        $tituloReport = 'LISTADO COMPLETO DE ARCHIVOS';
    }
    $mostrarColumnaCategoria = $categoriasSeleccionadas->count() !== 1;
@endphp

@section('title', $tituloPage)

@section('report_title', $tituloReport)

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No.</th>
            <th>Nombre</th>
            @if($mostrarColumnaCategoria)
                <th>Categoría</th>
            @endif
            <th>Descripción</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($archivos as $index => $archivo)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $archivo->nombre }}</td>
                @if($mostrarColumnaCategoria)
                    <td>{{ $archivo->categoria->categoria ?? 'Sin Categoría' }}</td>
                @endif
                <td>{{ $archivo->descripcion_archivo ?: 'Sin descripción registrada.' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $mostrarColumnaCategoria ? 4 : 3 }}" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron archivos activos.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
