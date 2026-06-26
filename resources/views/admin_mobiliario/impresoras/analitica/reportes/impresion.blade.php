@extends('layouts.reporte_base')

@section('title', 'Reporte - Catálogo de Impresoras')

@section('report_title', 'LISTA COMPLETA DE IMPRESORAS')

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:120px;">Inventario</th>
            <th style="width:110px;">No Serie</th>
            <th>Modelo</th>
            <th>Descripcion</th>
            <th style="width:70px;">Marca</th>
            <th style="width:90px;">Tecnología</th>
            <th class="center" style="width:80px;">Consumible</th>
            <th class="center" style="width:40px;">Red</th>
            <th style="width:90px;">Ip</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($impresoras as $imp)
            <tr>
                <td><strong>{{ $imp->inventario }}</strong></td>
                <td>{{ $imp->serie }}</td>
                <td>{{ $imp->modelo }}</td>
                <td>{{ $imp->descripcion ?? '' }}</td>
                <td>{{ $imp->marca }}</td>
                <td>{{ $imp->tecnologia ?? '' }}</td>
                <td class="center">{{ $imp->consumible }}</td>
                <td class="center">{{ $imp->red }}</td>
                <td>{{ $imp->ip ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron impresoras registradas en el catálogo.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
