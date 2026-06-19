@extends('layouts.reporte_base')

@section('title', 'Reporte - Catálogo de Insumos')

@section('report_title', 'LISTA COMPLETA DE INSUMOS')

@section('extra_actions')
    <a href="{{ route('insumos.index') }}" class="btn-close-win" style="text-decoration:none; margin-right:8px;">
        ← Regresar al módulo
    </a>
    <button class="btn-close-win" onclick="window.close()">✕ Cerrar</button>
@endsection

@section('report_subheader')
<div style="margin-bottom: 12px; font-size: 11px; color: #444;">
    <strong>Total de registros activos:</strong> {{ $insumos->count() }}
</div>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">#</th>
            <th style="width:150px;">Clave</th>
            <th>Descripción</th>
            <th style="width:150px;">Tipo</th>
            <th class="center" style="width:80px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($insumos as $index => $insumo)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $insumo->clave }}</td>
                <td>{{ $insumo->descripcion }}</td>
                <td>{{ $insumo->tipo }}</td>
                <td class="center">
                    @if ($insumo->activo == 1)
                        Activo
                    @else
                        Inactivo
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:12px; color:#666;">
                    No hay insumos registrados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
