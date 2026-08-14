@extends('layouts.reporte_base')

@section('title', 'Reporte - Catálogo de Proyectos')

@section('report_title', 'LISTA COMPLETA DE PROYECTOS')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No</th>
            <th>Proyecto</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($proyectos as $index => $row)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $row->proyecto }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron proyectos registrados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
