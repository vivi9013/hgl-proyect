@extends('layouts.reporte_base')

@section('title', isset($categoria) ? 'Reporte - Archivos de ' . $categoria->categoria : 'Reporte - Lista de Archivos')

@section('report_title')
{{ isset($categoria) ? 'LISTA COMPLETA DE ARCHIVOS DE ' . strtoupper($categoria->categoria) : 'LISTADO COMPLETO DE ARCHIVOS' }}
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No.</th>
            <th>Nombre</th>
            @if(!isset($categoria))
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
                @if(!isset($categoria))
                    <td>{{ $archivo->categoria->categoria ?? 'Sin Categoría' }}</td>
                @endif
                <td>{{ $archivo->descripcion_archivo ?: 'Sin descripción registrada.' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ isset($categoria) ? 3 : 4 }}" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron archivos activos.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
