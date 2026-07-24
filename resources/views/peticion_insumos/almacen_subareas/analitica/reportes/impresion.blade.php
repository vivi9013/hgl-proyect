@extends('layouts.reporte_base')

@section('title', 'Reporte Almacén de Subáreas - Hospital General')

@section('report_title')
    Inventario de Almacén por Subárea
@endsection

@section('report_subheader')
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de almacenes registrados:</strong> {{ $almacenes->count() }}
    </p>
@endsection

@section('content')
@forelse($almacenes as $index => $almacen)
    <div style="margin-bottom: 20px; page-break-inside: avoid;">
        <h4 style="margin: 0 0 5px 0; font-size: 13px; color: #1a252f; border-bottom: 1px solid #1a252f; padding-bottom: 3px;">
            {{ $index + 1 }}. Área: {{ $almacen->areaAbastecimiento->nombre ?? 'N/A' }} | Subárea: {{ $almacen->subareaAbastecimiento->nombre ?? 'N/A' }}
            <span style="float: right; font-size: 10px; font-weight: normal; color: #666;">
                Estatus: {{ $almacen->activo == 1 ? 'Activo' : 'Inactivo' }}
            </span>
        </h4>

        @if($almacen->detalles && $almacen->detalles->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th class="center" style="width: 30px;">#</th>
                        <th style="width: 100px;">Clave Insumo</th>
                        <th>Descripción del Insumo</th>
                        <th class="center" style="width: 80px;">Stock Actual</th>
                        <th class="center" style="width: 80px;">Fondo Fijo</th>
                        <th class="center" style="width: 90px;">Estado Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($almacen->detalles as $dIndex => $detalle)
                        @php
                            $insumo = $detalle->insumo;
                            $esBajo = $detalle->cantidad < $detalle->fondo_fijo;
                        @endphp
                        <tr>
                            <td class="num">{{ $dIndex + 1 }}</td>
                            <td>{{ $detalle->cve_insumo ?: ($insumo->clave ?? 'N/A') }}</td>
                            <td>{{ $insumo->descripcion ?? 'N/A' }}</td>
                            <td class="center">{{ $detalle->cantidad }}</td>
                            <td class="center">{{ $detalle->fondo_fijo }}</td>
                            <td class="center" style="{{ $esBajo ? 'color: #c5221f; font-weight: bold;' : 'color: #137333;' }}">
                                {{ $esBajo ? 'BAJO FONDO' : 'NORMAL' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="font-size: 10px; color: #888; font-style: italic; margin-top: 5px;">
                Sin insumos registrados en esta subárea.
            </p>
        @endif
    </div>
@empty
    <table>
        <tbody>
            <tr>
                <td colspan="6" style="text-align: center; color: #555;">
                    No hay registros de almacenes de subáreas para mostrar.
                </td>
            </tr>
        </tbody>
    </table>
@endforelse
@endsection
