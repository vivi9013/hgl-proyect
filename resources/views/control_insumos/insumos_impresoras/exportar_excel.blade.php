<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Insumos de Impresoras - Excel</title>
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
        /* Cabecera principal */
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
        /* Celdas de datos — estilos sólo en td para no contaminar columnas extra en Excel */
        td {
            border: 1px solid #bfbfbf;
            padding: 5px 6px;
            font-size: 9.5pt;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-left   { text-align: left;   }
        .text-right  { text-align: right;  }
        /* Fila de totales — en td para no contaminar columnas extra */
        .footer-total td {
            background-color: #e2efda;
            font-weight: bold;
            border: 1px solid #385723;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="4" class="title-header">
                    CATÁLOGO DE INSUMOS DE IMPRESORAS
                </th>
            </tr>
            <tr>
                <th class="table-header-col" style="width: 300px;">INSUMO</th>
                <th class="table-header-col" style="width: 120px;">TIPO</th>
                <th class="table-header-col" style="width: 120px;">COLOR</th>
                <th class="table-header-col" style="width: 80px;">STOCK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($insumos as $insumo)
                <tr>
                    {{-- INSUMO (modelo) --}}
                    <td class="text-left">{{ mb_strtoupper($insumo->modelo ?? '—') }}</td>
                    {{-- TIPO (familia) --}}
                    <td class="text-center">{{ mb_strtoupper($insumo->familia ?? '—') }}</td>
                    {{-- COLOR --}}
                    <td class="text-center">{{ mb_strtoupper($insumo->color ?? '—') }}</td>
                    {{-- STOCK --}}
                    <td class="text-center">{{ $insumo->stock ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px;">
                        No se encontraron insumos para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($insumos->isNotEmpty())
            <tfoot>
                <tr class="footer-total">
                    <td class="text-right" colspan="3">STOCK TOTAL:</td>
                    <td class="text-center">{{ $insumos->sum('stock') }}</td>
                </tr>
                <tr class="footer-total">
                    <td class="text-right" colspan="3">TOTAL REGISTROS:</td>
                    <td class="text-center">{{ $insumos->count() }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
