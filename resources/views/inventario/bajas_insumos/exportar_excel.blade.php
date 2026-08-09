<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Bajas de Insumos - Excel</title>
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 25px;
        }
        /* Cabecera superior verde claro */
        .title-header {
            background-color: #70ad47;
            color: #000000;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border: 1px solid #385723;
        }
        /* Cabecera de columnas verde oscuro */
        .table-header-col {
            background-color: #008000;
            color: #ffffff;
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #004d00;
            padding: 8px 6px;
        }
        /* Celdas de datos */
        td {
            border: 1px solid #bfbfbf;
            padding: 5px 6px;
            font-size: 9.5pt;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .num-format {
            mso-number-format: "\@";
        }
        .footer-total {
            background-color: #e2efda;
            font-weight: bold;
            border: 1px solid #70ad47;
        }
    </style>
</head>
<body>
    @forelse($bajasPorArea as $nombreGrupo => $bajasGrupo)
        <table>
            <thead>
                {{-- Fila 3 y 4 de la foto: Encabezado superior verde --}}
                <tr>
                    <th colspan="9" class="title-header">
                        {{ mb_strtoupper($nombreGrupo) }}
                    </th>
                </tr>
                {{-- Encabezados de columnas exactos --}}
                <tr>
                    <th class="table-header-col" style="width: 320px;">MEDICAMENTO</th>
                    <th class="table-header-col" style="width: 70px;">PIEZAS</th>
                    <th class="table-header-col" style="width: 120px;">FECHA DE ENTREGA</th>
                    <th class="table-header-col" style="width: 100px;">HORA</th>
                    <th class="table-header-col" style="width: 130px;">INICIALES DEL PACIENTE</th>
                    <th class="table-header-col" style="width: 110px;">NO. EXPEDIENTE</th>
                    <th class="table-header-col" style="width: 220px;">DOCTOR QUE LO RECETA</th>
                    <th class="table-header-col" style="width: 180px;">PERSONA QUIEN ENTREGA</th>
                    <th class="table-header-col" style="width: 100px;">AREA</th>
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
                        <td class="text-left">{{ mb_strtoupper($baja->insumo->descripcion ?? '—') }}</td>
                        {{-- PIEZAS --}}
                        <td class="text-center">{{ $baja->cantidad }}</td>
                        {{-- FECHA DE ENTREGA --}}
                        <td class="text-center">{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '—' }}</td>
                        {{-- HORA --}}
                        <td class="text-center">{{ $baja->hora_baja ?? '—' }}</td>
                        {{-- INICIALES DEL PACIENTE --}}
                        <td class="text-center">
                            {{ $baja->iniciales_paciente ? mb_strtoupper($baja->iniciales_paciente) : '—' }}
                        </td>
                        {{-- NO. EXPEDIENTE --}}
                        <td class="num-format text-center">{{ $baja->no_expediente ? mb_strtoupper($baja->no_expediente) : $baja->id_baja_insumo }}</td>
                        {{-- DOCTOR QUE LO RECETA --}}
                        <td class="text-left">{{ $baja->doctor_nombre ? mb_strtoupper($baja->doctor_nombre) : '—' }}</td>
                        {{-- PERSONA QUIEN ENTREGA --}}
                        <td class="text-left">{{ $baja->persona_entrega ? mb_strtoupper($baja->persona_entrega) : ($baja->doctor_especialidad ? mb_strtoupper($baja->doctor_especialidad) : '—') }}</td>
                        {{-- AREA --}}
                        <td class="text-center fw-bold">{{ mb_strtoupper($areaDisplay) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="footer-total">
                    <td class="text-right">TOTAL EN {{ mb_strtoupper($nombreGrupo) }}:</td>
                    <td class="text-center">{{ $bajasGrupo->sum('cantidad') }}</td>
                    <td colspan="7"></td>
                </tr>
            </tfoot>
        </table>
    @empty
        <table>
            <thead>
                <tr>
                    <th colspan="9" class="title-header">REPORTE DE BAJAS DE INSUMOS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">
                        No se encontraron registros de bajas de insumos para el rango seleccionado.
                    </td>
                </tr>
            </tbody>
        </table>
    @endforelse
</body>
</html>
