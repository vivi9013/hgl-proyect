<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos de Insumos - Excel</title>
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
        /* Cabecera superior del reporte */
        .title-header {
            background-color: #70ad47;
            color: #000000;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border: 1px solid #385723;
        }
        /* Sub-cabecera de rango de fechas */
        .subtitle-header {
            background-color: #a9d18e;
            color: #1e3a0f;
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            border: 1px solid #385723;
        }
        /* Cabecera de columnas */
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
        .text-center { text-align: center; }
        .text-left   { text-align: left;   }
        .text-right  { text-align: right;  }
        /* Filas de Entrada: verde muy claro — en td para no contaminar columnas extra */
        .row-entrada td {
            background-color: #e2efda;
        }
        /* Filas de Salida: rojo muy claro — en td para no contaminar columnas extra */
        .row-salida td {
            background-color: #fce4e4;
        }
        /* Badge tipo Entrada */
        .badge-entrada {
            color: #155724;
            font-weight: bold;
        }
        /* Badge tipo Salida */
        .badge-salida {
            color: #721c24;
            font-weight: bold;
        }
        /* Fila de totales al pie — estilos en td para no contaminar columnas extra en Excel */
        .footer-total td {
            background-color: #d9e1f2;
            font-weight: bold;
            border: 1px solid #2f5496;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            {{-- Título principal del reporte --}}
            <tr>
                <th colspan="3" class="title-header">
                    REPORTE DE MOVIMIENTOS DE INSUMOS
                </th>
            </tr>

            {{-- Sub-título con rango de fechas aplicado --}}
            @if($fechaInicio || $fechaFin)
                <tr>
                    <th colspan="3" class="subtitle-header">
                        Período:
                        {{ $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') : '—' }}
                        al
                        {{ $fechaFin    ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y')    : '—' }}
                    </th>
                </tr>
            @endif

            {{-- Encabezados de columnas --}}
            <tr>
                <th class="table-header-col" style="width: 450px;">INSUMO</th>
                <th class="table-header-col" style="width: 120px;">TIPO</th>
                <th class="table-header-col" style="width: 100px;">CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                @php
                    // Construye el nombre legible del insumo concatenando familia, modelo y color.
                    $nombreInsumo = $mov->insumo
                        ? mb_strtoupper($mov->insumo->familia . ' — ' . $mov->insumo->modelo . ' (' . $mov->insumo->color . ')')
                        : '—';
                    // Selecciona la clase de fila según el tipo de movimiento.
                    $claseFila  = $mov->tipo === 'Entrada' ? 'row-entrada' : 'row-salida';
                    // Selecciona la clase del badge según el tipo de movimiento.
                    $claseTipo  = $mov->tipo === 'Entrada' ? 'badge-entrada' : 'badge-salida';
                @endphp
                <tr class="{{ $claseFila }}">
                    {{-- INSUMO --}}
                    <td class="text-left">{{ $nombreInsumo }}</td>
                    {{-- TIPO --}}
                    <td class="text-center {{ $claseTipo }}">{{ mb_strtoupper($mov->tipo ?? '—') }}</td>
                    {{-- CANTIDAD --}}
                    <td class="text-center">{{ $mov->cantidad }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 20px;">
                        No se encontraron movimientos de insumos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($movimientos->isNotEmpty())
            <tfoot>
                {{-- Fila de totales: suma de Entradas y Salidas por separado --}}
                <tr class="footer-total">
                    <td class="text-right">TOTAL ENTRADAS:</td>
                    <td></td>
                    <td class="text-center">{{ $movimientos->where('tipo', 'Entrada')->sum('cantidad') }}</td>
                </tr>
                <tr class="footer-total">
                    <td class="text-right">TOTAL SALIDAS:</td>
                    <td></td>
                    <td class="text-center">{{ $movimientos->where('tipo', 'Salida')->sum('cantidad') }}</td>
                </tr>
                <tr class="footer-total">
                    <td class="text-right">TOTAL REGISTROS:</td>
                    <td></td>
                    <td class="text-center">{{ $movimientos->count() }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
