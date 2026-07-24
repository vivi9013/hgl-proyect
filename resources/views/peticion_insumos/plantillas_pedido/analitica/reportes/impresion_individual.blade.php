@extends('layouts.reporte_base')

@section('titulo_reporte', 'FORMATO OFICIAL DE PLANTILLA DE PEDIDO POR ÁREA')

@section('contenido')
<div style="font-family: Arial, sans-serif; font-size: 11px; color: #333;">

    <!-- Datos de la Plantilla y Área -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 1px solid #ccc;">
        <tr style="background-color: #f8f9fa;">
            <td style="padding: 6px; font-weight: bold; border-right: 1px solid #ccc; width: 20%;">NOMBRE DE PLANTILLA:</td>
            <td style="padding: 6px; font-weight: bold; color: #0d6efd; border-right: 1px solid #ccc; width: 30%; font-size: 12px;">
                {{ $plantilla->nombre }}
            </td>
            <td style="padding: 6px; font-weight: bold; border-right: 1px solid #ccc; width: 20%;">FECHA REGISTRO:</td>
            <td style="padding: 6px; width: 30%;">
                {{ $plantilla->fecha_registro ? \Carbon\Carbon::parse($plantilla->fecha_registro)->format('d/m/Y') : '' }} {{ $plantilla->hora_registro ?: '' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">ÁREA SOLICITANTE:</td>
            <td style="padding: 6px; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">
                {{ $plantilla->areaAbastecimiento ? $plantilla->areaAbastecimiento->nombre : 'N/A' }}
            </td>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">SUBÁREA:</td>
            <td style="padding: 6px; border-top: 1px solid #ccc;">
                {{ $plantilla->subareaAbastecimiento ? $plantilla->subareaAbastecimiento->nombre : 'General / No especificada' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">DESCRIPCIÓN:</td>
            <td style="padding: 6px; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">
                {{ $plantilla->descripcion ?: 'Sin observaciones adicionales' }}
            </td>
            <td style="padding: 6px; font-weight: bold; border-top: 1px solid #ccc; border-right: 1px solid #ccc;">ESTADO:</td>
            <td style="padding: 6px; border-top: 1px solid #ccc; font-weight: bold;">
                {{ $plantilla->activo == 1 ? 'ACTIVA' : 'INACTIVA' }}
            </td>
        </tr>
    </table>

    <h3 style="font-size: 12px; margin-bottom: 5px; border-bottom: 2px solid #333; padding-bottom: 3px;">CATÁLOGO DE INSUMOS Y CANTIDADES PRESTABLECIDAS</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;" border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr style="background-color: #e9ecef; font-weight: bold; text-align: center;">
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">CLAVE</th>
                <th style="width: 60%; text-align: left;">DESCRIPCIÓN DEL INSUMO</th>
                <th style="width: 20%;">CANTIDAD BASE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plantilla->detalles as $index => $det)
                @php $insumo = $det->insumo; @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-family: monospace; font-weight: bold;">{{ $det->cve_insumo ?: ($insumo->clave ?? 'N/A') }}</td>
                    <td>{{ $insumo->descripcion ?? 'N/A' }}</td>
                    <td style="text-align: center; font-weight: bold; color: #0d6efd;">{{ $det->cantidad }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 15px; color: #777;">Esta plantilla no contiene insumos asignados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bloque de Autorización -->
    <table style="width: 100%; margin-top: 50px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #000; width: 75%; margin: 0 auto; padding-top: 5px;">
                    <strong>RESPONSABLE DE ÁREA</strong><br>
                    <span style="font-size: 10px;">Nombre y Firma de Autorización</span>
                </div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #000; width: 75%; margin: 0 auto; padding-top: 5px;">
                    <strong>CENDIS / ALMACÉN CENTRAL</strong><br>
                    <span style="font-size: 10px;">Vo.Bo. Registro de Plantilla</span>
                </div>
            </td>
        </tr>
    </table>

</div>
@endsection
