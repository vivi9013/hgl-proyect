<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Bajas</title>
</head>
<body style="font-family: Calibri, Arial, sans-serif; font-size: 11pt;">
    @forelse($bajasPorArea as $nombreGrupo => $bajasGrupo)
        <table style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">
            <thead>
                {{-- Fila 1: Encabezado superior verde claro --}}
                <tr>
                    <th colspan="9" style="background-color: #70ad47; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 13pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #385723; height: 32px;">
                        {{ mb_strtoupper($nombreGrupo) }}
                    </th>
                </tr>
                {{-- Fila 2: Encabezados de columnas verde oscuro con texto blanco --}}
                <tr>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">MEDICAMENTO</th>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">PIEZAS</th>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">FECHA DE ENTREGA</th>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">HORA</th>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">INICIALES DEL PACIENTE</th>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">NO. EXPEDIENTE</th>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">DOCTOR QUE LO RECETA</th>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">PERSONA QUIEN ENTREGA</th>
                    <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px;">AREA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bajasGrupo as $baja)
                    @php
                        $areaDisplay = $baja->areaAbastecimiento->nombre 
                            ?? $baja->areaAbastecimiento->siglas 
                            ?? $baja->insumo->areaAbastecimiento->nombre 
                            ?? $baja->insumo->areaAbastecimiento->siglas 
                            ?? $baja->motivo
                            ?? $baja->areaAlmacen->nombre 
                            ?? '—';
                    @endphp
                    <tr>
                        {{-- MEDICAMENTO --}}
                        <td style="border: 1px solid #bfbfbf; text-align: left; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ mb_strtoupper($baja->insumo->descripcion ?? '—') }}
                        </td>
                        {{-- PIEZAS --}}
                        <td style="border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ $baja->cantidad }}
                        </td>
                        {{-- FECHA DE ENTREGA --}}
                        <td style="border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '—' }}
                        </td>
                        {{-- HORA --}}
                        <td style="border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ $baja->hora_baja ?? '—' }}
                        </td>
                        {{-- INICIALES DEL PACIENTE --}}
                        <td style="border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ $baja->iniciales_paciente ? mb_strtoupper($baja->iniciales_paciente) : '—' }}
                        </td>
                        {{-- NO. EXPEDIENTE --}}
                        <td style="border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ $baja->no_expediente ? mb_strtoupper($baja->no_expediente) : ($baja->id_baja_insumo ?? '—') }}
                        </td>
                        {{-- DOCTOR QUE LO RECETA --}}
                        <td style="border: 1px solid #bfbfbf; text-align: left; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ $baja->doctor_nombre ? mb_strtoupper($baja->doctor_nombre) : '—' }}
                        </td>
                        {{-- PERSONA QUIEN ENTREGA --}}
                        <td style="border: 1px solid #bfbfbf; text-align: left; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ $baja->persona_entrega ? mb_strtoupper($baja->persona_entrega) : ($baja->doctor_especialidad ? mb_strtoupper($baja->doctor_especialidad) : '—') }}
                        </td>
                        {{-- AREA --}}
                        <td style="border: 1px solid #bfbfbf; text-align: left; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                            {{ mb_strtoupper($areaDisplay) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td style="background-color: #e2efda; font-weight: bold; border: 1px solid #70ad47; text-align: right; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                        TOTAL EN {{ mb_strtoupper($nombreGrupo) }}:
                    </td>
                    <td style="background-color: #e2efda; font-weight: bold; border: 1px solid #70ad47; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">
                        {{ $bajasGrupo->sum('cantidad') }}
                    </td>
                    <td colspan="7" style="background-color: #e2efda; border: 1px solid #70ad47;"></td>
                </tr>
            </tfoot>
        </table>
    @empty
        <table style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th colspan="9" style="background-color: #70ad47; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 13pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #385723; height: 32px;">
                        REPORTE DE BAJAS DE INSUMOS
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="9" style="border: 1px solid #bfbfbf; text-align: center; padding: 20px; font-family: Calibri, Arial, sans-serif; font-size: 10pt;">
                        No se encontraron registros de bajas de insumos para el rango seleccionado.
                    </td>
                </tr>
            </tbody>
        </table>
    @endforelse
</body>
</html>
