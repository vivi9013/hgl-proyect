<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Insumos de Impresoras - Excel</title>
</head>
<body style="font-family: Calibri, Arial, sans-serif; font-size: 11pt;">
    <table style="border-collapse: collapse; width: 100%; margin-bottom: 25px;">
        <thead>
            <tr>
                <th colspan="4" style="background-color: #70ad47; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 14pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #385723; height: 36px; padding: 10px;">
                    CATÁLOGO DE INSUMOS DE IMPRESORAS
                </th>
            </tr>
            <tr>
                <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px; width: 300px;">INSUMO</th>
                <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px; width: 120px;">TIPO</th>
                <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px; width: 120px;">COLOR</th>
                <th style="background-color: #008000; color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 10pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #004d00; height: 26px; width: 80px;">STOCK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($insumos as $insumo)
                <tr>
                    {{-- INSUMO (modelo) --}}
                    <td style="border: 1px solid #bfbfbf; text-align: left; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">
                        {{ mb_strtoupper($insumo->modelo ?? '—') }}
                    </td>
                    {{-- TIPO (familia) --}}
                    <td style="border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">
                        {{ mb_strtoupper($insumo->familia ?? '—') }}
                    </td>
                    {{-- COLOR --}}
                    <td style="border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">
                        {{ mb_strtoupper($insumo->color ?? '—') }}
                    </td>
                    {{-- STOCK --}}
                    <td style="border: 1px solid #bfbfbf; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">
                        {{ $insumo->stock ?? 0 }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="border: 1px solid #bfbfbf; text-align: center; padding: 20px; font-family: Calibri, Arial, sans-serif; font-size: 10pt;">
                        No se encontraron insumos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($insumos->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3" style="background-color: #e2efda; font-weight: bold; border: 1px solid #385723; text-align: right; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">STOCK TOTAL:</td>
                    <td style="background-color: #e2efda; font-weight: bold; border: 1px solid #385723; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">{{ $insumos->sum('stock') }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="background-color: #e2efda; font-weight: bold; border: 1px solid #385723; text-align: right; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt; padding: 5px 6px;">TOTAL REGISTROS:</td>
                    <td style="background-color: #e2efda; font-weight: bold; border: 1px solid #385723; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 9.5pt;">{{ $insumos->count() }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
