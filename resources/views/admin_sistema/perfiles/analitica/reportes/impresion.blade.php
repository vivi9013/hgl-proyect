@extends('layouts.reporte_base')

@section('title', 'Reporte - Catálogo de Perfiles')

@section('report_title', 'LISTA COMPLETA DE PERFILES')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No</th>
            <th>Perfil</th>
            <th>Descripción</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($perfiles as $index => $row)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $row->nombre }}</td>
                <td>{{ $row->descripcion }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron perfiles registrados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
