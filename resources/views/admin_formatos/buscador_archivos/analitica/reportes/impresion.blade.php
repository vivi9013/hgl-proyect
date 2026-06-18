@extends('layouts.reporte_base')

@section('title', 'Reporte - Lista de Archivos')

@section('report_title', 'LISTA COMPLETA DE ARCHIVOS Y FORMATOS DISPONIBLES')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">#</th>
            <th>Nombre del Archivo / Formato</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th class="center" style="width:80px;">Versión</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($archivos as $index => $archivo)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $archivo->nombre }}</td>
                <td>{{ $archivo->categoria->categoria }}</td>
                <td>{{ $archivo->descripcion_archivo ?: 'Sin descripción registrada.' }}</td>
                <td class="center">v{{ $archivo->version_archivo ?: '1' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:12px; color:#666;">
                    No hay formatos asignados a su perfil de usuario registrados actualmente.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
