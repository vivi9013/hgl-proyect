<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos de Insumos - Excel</title>
</head>
<body style="font-family: Calibri, Arial, sans-serif; font-size: 11pt;">
    <table style="border-collapse: collapse; width: 100%; margin-bottom: 25px;">
        <thead>
            {{-- Título principal del reporte --}}
            <tr>
                <th colspan="3" style="background-color: #70ad47; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 14pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #385723; height: 36px;">
                    REPORTE DE MOVIMIENTOS DE INSUMOS
                </th>
            </tr>

            {{-- Sub-título con rango de fechas aplicado --}}
            @if($fechaInicio || $fechaFin)
                <tr>
                    <th colspan="3" style="background-color: #a9d18e; color: #1e3a0f; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #385723; padding: 5px;">
                        Período:
                        {{ $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') : '—' }}
                        al
                        {{ $fechaFin    ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y')    : '—' }}
                    </th>
                </tr>
            @endif

            {{-- Encabezados de columnas --}}
            <tr>
                <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px; width: 450px;">INSUMO</th>
                <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px; width: 120px;">TIPO</th>
                <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px; width: 100px;">CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                @php
                    $nombreInsumo = $mov->insumo
                        ? mb_strtoupper($mov->insumo->familia . ' — ' . $mov->insumo->modelo . ' (' . $mov->insumo->color . ')')
                        : '—';
                    $bgFila = $mov->tipo === 'Entrada' ? '#e2efda' : '#fce4e4';
                    $colorTipo = $mov->tipo === 'Entrada' ? '#155724' : '#721c24';
                @endphp
                <tr>
                    {{-- INSUMO --}}
                    <td style="background-color: {{ $bgFila }}; border: 1px solid #bfbfbf; text-align: left; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">
                        {{ $nombreInsumo }}
                    </td>
                    {{-- TIPO --}}
                    <td style="background-color: {{ $bgFila }}; color: {{ $colorTipo }}; font-weight: bold; border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">
                        {{ mb_strtoupper($mov->tipo ?? '—') }}
                    </td>
                    {{-- CANTIDAD --}}
                    <td style="background-color: {{ $bgFila }}; border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">
                        {{ $mov->cantidad }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="border: 1px solid #bfbfbf; text-align: center; padding: 20px; font-family: Calibri, Arial, sans-serif; font-size: 10pt;">
                        No se encontraron movimientos de insumos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($movimientos->isNotEmpty())
            <tfoot>
                {{-- Totales --}}
                <tr>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; text-align: right; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">TOTAL ENTRADAS:</td>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;"></td>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">{{ $movimientos->where('tipo', 'Entrada')->sum('cantidad') }}</td>
                </tr>
                <tr>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; text-align: right; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">TOTAL SALIDAS:</td>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;"></td>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">{{ $movimientos->where('tipo', 'Salida')->sum('cantidad') }}</td>
                </tr>
                <tr>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; text-align: right; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">TOTAL REGISTROS:</td>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;"></td>
                    <td style="background-color: #d9e1f2; font-weight: bold; border: 1px solid #2f5496; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">{{ $movimientos->count() }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
