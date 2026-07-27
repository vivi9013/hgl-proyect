@extends('layouts.reporte_base')

@section('report_title', 'SOLICITUD Y COMPROBANTE DE PEDIDO DE INSUMOS')

@section('content')
<div style="font-family: Arial, sans-serif; font-size: 11px; color: #333;">

    <!-- Encabezado del Pedido -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 1px solid #ccc;">
        <tr style="background-color: #f5f5f5;">
            <td style="padding: 6px; font-weight: bold; border-right: 1px solid #ccc; width: 15%;">FOLIO DE PEDIDO:</td>
            <td style="padding: 6px; font-weight: bold; color: #d9534f; border-right: 1px solid #ccc; width: 35%; font-size: 13px;">
                #{{ $pedido->id_pedido }}
            </td>
            <td style="padding: 6px; font-weight: bold; border-right: 1px solid #ccc; width: 15%;">FECHA / HORA:</td>
            <td style="padding: 6px; width: 35%;">
                {{ $pedido->fecha_registro ? $pedido->fecha_registro->format('d/m/Y') : '' }} {{ $pedido->hora_registro ?: '' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">ÁREA SOLICITANTE:</td>
            <td style="padding: 6px; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">
                {{ $pedido->areaAbastecimiento ? $pedido->areaAbastecimiento->nombre : 'N/A' }}
            </td>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">SUBÁREA:</td>
            <td style="padding: 6px; border-top: 1px solid #ccc;">
                {{ $pedido->subareaAbastecimiento ? $pedido->subareaAbastecimiento->nombre : 'General' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">ALMACÉN DESTINO:</td>
            <td style="padding: 6px; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">
                {{ $pedido->areaAlmacen ? $pedido->areaAlmacen->nombre : 'General' }}
            </td>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">SOLICITADO POR:</td>
            <td style="padding: 6px; border-top: 1px solid #ccc;">
                {{ $pedido->usuario && $pedido->usuario->persona ? $pedido->usuario->persona->nombre . ' ' . $pedido->usuario->persona->ap_paterno : 'Sistema' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">ESTADO:</td>
            <td colspan="3" style="padding: 6px; border-top: 1px solid #ccc; font-weight: bold;">
                {{ strtoupper($pedido->status) }}
            </td>
        </tr>
    </table>

    <!-- Tabla de Insumos -->
    <h3 style="font-size: 12px; margin-bottom: 5px; border-bottom: 2px solid #333; padding-bottom: 3px;">DETALLE DE MEDICAMENTOS Y MATERIAL DE CURACIÓN SOLICITADO</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;" border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr style="background-color: #e9ecef; font-weight: bold; text-align: center;">
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">CLAVE</th>
                <th style="width: 45%; text-align: left;">DESCRIPCIÓN DEL INSUMO</th>
                <th style="width: 11%;">CANT. PEDIDA</th>
                <th style="width: 12%;">SURTIDO CENDIS</th>
                <th style="width: 12%;">FALTANTE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedido->detalles as $index => $det)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-family: monospace; font-weight: bold;">{{ $det->cve_insumo ?: ($det->insumo ? $det->insumo->clave : 'N/A') }}</td>
                    <td>{{ $det->insumo ? $det->insumo->descripcion : 'N/A' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $det->cantidad }}</td>
                    <td style="text-align: center; color: #28a745;">{{ $det->surtido }}</td>
                    <td style="text-align: center; color: #dc3545;">{{ $det->faltante }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px; color: #777;">No se registraron insumos en este pedido.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bloque de Firmas -->
    <table style="width: 100%; margin-top: 40px; border-collapse: collapse;">
        <tr>
            <td style="width: 33%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #000; width: 85%; margin: 0 auto; padding-top: 5px;">
                    <strong>SOLICITÓ</strong><br>
                    <span style="font-size: 10px;">Firma del Encargado de Área</span>
                </div>
            </td>
            <td style="width: 33%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #000; width: 85%; margin: 0 auto; padding-top: 5px;">
                    <strong>ENTREGÓ (CENDIS)</strong><br>
                    <span style="font-size: 10px;">Firma de Despacho de Almacén</span>
                </div>
            </td>
            <td style="width: 33%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #000; width: 85%; margin: 0 auto; padding-top: 5px;">
                    <strong>RECIBIÓ CONFORME</strong><br>
                    <span style="font-size: 10px;">Nombre y Firma del Personal</span>
                </div>
            </td>
        </tr>
    </table>

</div>
@endsection
