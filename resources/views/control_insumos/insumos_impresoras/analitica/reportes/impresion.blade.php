@extends('layouts.reporte_base')

@section('title', 'Reporte - Catálogo de Insumos de Impresoras')

@section('report_title', 'CATÁLOGO DE INSUMOS DE IMPRESORAS')

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:60px;">Tipo</th>
            <th style="width:100px;">Modelo</th>
            <th style="width:70px;">Color</th>
            <th>Compatibilidad</th>
            <th class="center" style="width:80px;">Rendimiento</th>
            <th class="center" style="width:70px;">Tiempo Uso</th>
            <th class="center" style="width:50px;">Stock</th>
            <th class="center" style="width:60px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($insumos as $insumo)
            <tr>
                <td>{{ $insumo->familia }}</td>
                <td><strong>{{ $insumo->modelo }}</strong></td>
                <td>{{ $insumo->color }}</td>
                <td>{{ $insumo->modelos_compatibles ?? '—' }}</td>
                <td class="center">{{ $insumo->hojas_uso_total ? number_format($insumo->hojas_uso_total).' h.' : '—' }}</td>
                <td class="center">{{ $insumo->tiempo_uso ?: '—' }}</td>
                <td class="center">{{ $insumo->stock }}</td>
                <td class="center">{{ $insumo->activo ? 'Activo' : 'Inactivo' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron insumos registrados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
