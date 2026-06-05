<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte – Bajas de Insumos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #fff;
            padding: 20px 30px;
        }

        /* ── Encabezado ── */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .report-header h1 {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .report-header .sub {
            font-size: 11px;
            color: #555;
            margin-top: 4px;
        }

        .report-meta {
            text-align: right;
            font-size: 11px;
            color: #444;
        }

        /* ── Filtros activos ── */
        .filtros-activos {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 11px;
            color: #4b5563;
        }

        /* ── Tabla ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead tr th {
            background-color: #1a1a1a;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tbody tr td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11.5px;
        }

        tbody tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .badge-activa {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .badge-cancelada {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* ── Pie ── */
        .report-footer {
            margin-top: 24px;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #9ca3af;
        }

        /* ── Botón de impresión ── */
        .btn-print {
            display: inline-block;
            margin-bottom: 18px;
            padding: 8px 20px;
            background: #1a1a1a;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .btn-print:hover { background: #374151; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 14px;">
        <button class="btn-print" onclick="window.print()">
            &#x1F5A8;&nbsp; Imprimir / Guardar PDF
        </button>
        &nbsp;&nbsp;
        <a href="{{ route('bajas_insumos.index') }}" style="font-size:12px; color:#374151;">&#8592; Regresar al módulo</a>
    </div>

    {{-- ── Encabezado del reporte ── --}}
    <div class="report-header">
        <div>
            <h1>Bajas de Insumos</h1>
            <div class="sub">Inventario de Medicamentos y Material de Curación</div>
            <div class="sub">Sistema de Gestión Hospitalaria – HGL</div>
        </div>
        <div class="report-meta">
            <div><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</div>
            <div><strong>Hora:</strong> {{ now()->format('H:i:s') }}</div>
            <div><strong>Total de registros:</strong> {{ $bajas->count() }}</div>
        </div>
    </div>

    {{-- ── Filtros aplicados ── --}}
    @if($buscar || $fechaInit || $fechaFin)
        <div class="filtros-activos">
            <strong>Filtros aplicados:</strong>
            @if($buscar) &nbsp;Búsqueda: "{{ $buscar }}" @endif
            @if($fechaInit) &nbsp;| Desde: {{ \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') }} @endif
            @if($fechaFin) &nbsp;| Hasta: {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }} @endif
        </div>
    @endif

    @if($bajas->count() >= 500)
        <div style="background-color: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 8px 12px; margin-bottom: 14px; border-radius: 6px; font-size: 11px;" class="no-print">
            <strong>Aviso:</strong> El reporte se ha limitado a los 500 registros más recientes para optimizar el rendimiento y la impresión. Por favor, utilice los filtros de fecha o búsqueda en la pantalla del listado para delimitar los resultados si requiere registros anteriores.
        </div>
    @endif

    {{-- ── Tabla de bajas ── --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Insumo</th>
                <th>Clave</th>
                <th>Área de Almacén</th>
                <th>Motivo</th>
                <th>Cantidad</th>
                <th>Fecha Baja</th>
                <th>Hora</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bajas as $index => $baja)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $baja->insumo->descripcion ?? '—' }}</td>
                    <td>{{ $baja->insumo->clave ?? '—' }}</td>
                    <td>{{ $baja->areaAlmacen->nombre ?? '—' }}</td>
                    <td>{{ $baja->motivo }}</td>
                    <td>{{ $baja->cantidad }}</td>
                    <td>{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $baja->hora_baja ?? '—' }}</td>
                    <td>
                        @if($baja->cancelado === 'Si')
                            <span class="badge-cancelada">Cancelada</span>
                        @else
                            <span class="badge-activa">Activa</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding: 20px; color:#9ca3af;">
                        No hay bajas de insumos registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Pie del reporte ── --}}
    <div class="report-footer">
        <span>HGL – Sistema de Gestión Hospitalaria</span>
        <span>Generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</span>
    </div>

</body>
</html>
