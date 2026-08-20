@extends('layouts.reporte_base')

@section('title', 'Reporte - Movimientos de Insumos')

@php
    $tipoArr = (array) $tipo;
    $sufijoTitulo = count($tipoArr) === 1
        ? ' — ' . strtoupper($tipoArr[0]) . 'S'
        : '';
@endphp

@section('report_title', 'MOVIMIENTOS DE INSUMOS DE IMPRESORAS' . $sufijoTitulo)

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:60px;">Tipo</th>
            <th>Insumo</th>
            <th style="width:70px;">Concepto</th>
            <th class="center" style="width:50px;">Cant.</th>
            <th>Proveedor</th>
            <th class="center" style="width:70px;">Fecha</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movimientos as $mov)
            <tr>
                <td class="center">
                    <strong>{{ $mov->tipo }}</strong>
                </td>
                <td>
                    @if($mov->insumo)
                        {{ $mov->insumo->modelo }} ({{ $mov->insumo->color }})
                    @else
                        —
                    @endif
                </td>
                <td>{{ $mov->concepto }}</td>
                <td class="center">{{ $mov->cantidad }}</td>
                <td>
                    @if($mov->tipo === 'Entrada' && $mov->proveedor)
                        {{ $mov->proveedor }}
                    @else
                        —
                    @endif
                </td>
                <td class="center">{{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron movimientos registrados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection