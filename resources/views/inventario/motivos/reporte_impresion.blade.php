{{-- Vista de reporte de impresión para Motivos de Devoluciones --}}
{{-- Se abre en una nueva ventana del navegador con diseño optimizado para impresión --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte – Motivos de Devoluciones</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #fff;
            padding: 24px;
        }

        /* ── Encabezado ── */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
        }

        .report-header h1 {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .report-header p {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        .report-meta {
            text-align: right;
            font-size: 11px;
            color: #555;
        }

        /* ── Filtro activo ── */
        .filtro-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 11px;
            color: #475569;
            margin-bottom: 12px;
        }

        /* ── Tabla ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        thead th {
            background: #000;
            color: #fff;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 8px 10px;
            text-align: left;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: middle;
        }

        /* ── Badge de tipo ── */
        .badge-si {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-no {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        /* ── Status ── */
        .badge-activo {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-inactivo {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        /* ── Sin resultados ── */
        .no-data {
            text-align: center;
            padding: 24px;
            color: #9ca3af;
            font-style: italic;
        }

        /* ── Pie de reporte ── */
        .report-footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #94a3b8;
        }

        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    {{-- ── Encabezado del reporte ── --}}
    <div class="report-header">
        <div>
            <h1>📋 Reporte – Motivos de Devoluciones</h1>
            <p>Inventario de Medicamentos y Material de Curación</p>
        </div>
        <div class="report-meta">
            <strong>Fecha:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ \Carbon\Carbon::now()->format('H:i:s') }}<br>
            <strong>Total:</strong> {{ $motivos->count() }} {{ $motivos->count() === 1 ? 'motivo' : 'motivos' }}
        </div>
    </div>

    {{-- Filtro activo --}}
    @if($buscar)
        <div class="filtro-badge">
            🔍 Filtro activo: "{{ $buscar }}"
        </div>
    @endif

    {{-- ── Tabla de datos ── --}}
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Descripción del Motivo</th>
                <th style="width: 130px;">Modifica Stock</th>
                <th style="width: 120px;">Fecha Registro</th>
                <th style="width: 90px;">Hora</th>
                <th style="width: 80px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($motivos as $index => $motivo)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $motivo->descripcion }}</strong></td>
                    <td>
                        @if($motivo->modificar === 'Si')
                            <span class="badge-si">Sí</span>
                        @else
                            <span class="badge-no">No</span>
                        @endif
                    </td>
                    <td>{{ $motivo->fecha_registro ? \Carbon\Carbon::parse($motivo->fecha_registro)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $motivo->hora_registro ?? '—' }}</td>
                    <td style="text-align: center;">
                        @if($motivo->activo == 1)
                            <span class="badge-activo">Activo</span>
                        @else
                            <span class="badge-inactivo">Inactivo</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="no-data">No hay motivos de devolución registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Pie de reporte ── --}}
    <div class="report-footer">
        <span>HGL – Sistema de Inventario</span>
        <span>Generado el {{ \Carbon\Carbon::now()->format('d/m/Y \a \l\a\s H:i') }}</span>
    </div>

    {{-- Auto-imprimir al cargar ── --}}
    <script>
        window.onload = function () {
            window.print();
        };
    </script>

</body>
</html>
