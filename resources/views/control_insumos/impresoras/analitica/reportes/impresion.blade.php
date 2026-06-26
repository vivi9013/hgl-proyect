@extends('layouts.reporte_base')

@section('title', 'Reporte - Catálogo de Impresoras')

@section('report_title', 'LISTADO COMPLETO DEL CATÁLOGO DE IMPRESORAS')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:40px;">No</th>
            <th class="center" style="width:100px;">Inventario</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Tipo</th>
            <th>Serie</th>
            <th>Tecnología</th>
            <th class="center" style="width:90px;">Consumible</th>
            <th class="center" style="width:40px;">Red</th>
            <th>IP</th>
            <th class="center" style="width:60px;">Comodato</th>
            <th class="center" style="width:60px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($impresoras as $index => $imp)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td class="center font-monospace">{{ $imp->inventario }}</td>
                <td>{{ $imp->marca }}</td>
                <td>{{ $imp->modelo }}</td>
                <td>{{ $imp->tipo }}</td>
                <td>{{ $imp->serie }}</td>
                <td>{{ $imp->tecnologia ?? 'N/A' }}</td>
                <td class="center">{{ $imp->consumible }}</td>
                <td class="center">{{ $imp->red }}</td>
                <td class="font-monospace">{{ $imp->ip ?? 'N/A' }}</td>
                <td class="center">{{ $imp->comodato }}</td>
                <td class="center">
                    {{ $imp->activo ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron impresoras registradas en el catálogo.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
