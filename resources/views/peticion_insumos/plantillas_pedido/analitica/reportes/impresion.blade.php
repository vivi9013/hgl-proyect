@extends('layouts.reporte_base')

@section('title', 'Reporte Plantillas de Pedido - Hospital General')

@section('report_title')
    Plantillas de Pedido — Petición de Insumos
@endsection

@section('report_subheader')
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de plantillas:</strong> {{ $plantillas->count() }}
    </p>
@endsection

@section('content')
@forelse($plantillas as $index => $plantilla)
    <div style="margin-bottom: 20px; page-break-inside: avoid;">
        <h4 style="margin: 0 0 5px 0; font-size: 13px; color: #1a252f; border-bottom: 1px solid #1a252f; padding-bottom: 3px;">
            {{ $index + 1 }}. {{ $plantilla->nombre }}
            <span style="float: right; font-size: 10px; font-weight: normal; color: #666;">
                Estatus: {{ $plantilla->activo == 1 ? 'Activa' : 'Inactiva' }}
            </span>
        </h4>
        <p style="font-size: 10px; color: #555; margin: 2px 0 6px 0;">
            <strong>Área:</strong> {{ $plantilla->areaAbastecimiento->nombre ?? 'N/A' }}
            @if($plantilla->subareaAbastecimiento)
                | <strong>Subárea:</strong> {{ $plantilla->subareaAbastecimiento->nombre }}
            @endif
            @if($plantilla->descripcion)
                | {{ $plantilla->descripcion }}
            @endif
        </p>

        @if($plantilla->detalles && $plantilla->detalles->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th class="center" style="width: 30px;">#</th>
                        <th style="width: 100px;">Clave Insumo</th>
                        <th>Descripción del Insumo</th>
                        <th class="center" style="width: 100px;">Cantidad Prestablecida</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plantilla->detalles as $dIndex => $detalle)
                        @php $insumo = $detalle->insumo; @endphp
                        <tr>
                            <td class="num">{{ $dIndex + 1 }}</td>
                            <td>{{ $detalle->cve_insumo ?: ($insumo->clave ?? 'N/A') }}</td>
                            <td>{{ $insumo->descripcion ?? 'N/A' }}</td>
                            <td class="center">{{ $detalle->cantidad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="font-size: 10px; color: #888; font-style: italic; margin-top: 5px;">
                Sin insumos asignados a esta plantilla.
            </p>
        @endif
    </div>
@empty
    <table>
        <tbody>
            <tr>
                <td colspan="4" style="text-align: center; color: #555;">
                    No hay plantillas de pedido para mostrar.
                </td>
            </tr>
        </tbody>
    </table>
@endforelse
@endsection
