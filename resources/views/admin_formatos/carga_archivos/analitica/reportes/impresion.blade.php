@extends('layouts.reporte_base')

@section('title', 'Reporte - Archivos por Categoría')

@section('report_title')
LISTA COMPLETA DE ARCHIVOS DE {{ strtoupper($categoria->categoria) }}
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No.</th>
            <th>Nombre</th>
            <th>Descripcion</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($archivos as $index => $archivo)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $archivo->nombre }}</td>
                <td>{{ $archivo->descripcion_archivo }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron archivos activos asociados a esta categoría.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
