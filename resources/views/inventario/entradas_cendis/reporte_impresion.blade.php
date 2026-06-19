<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Entradas al CENDIS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10.5px; color: #1a1a1a; background: #fff; max-width: 960px; margin: 0 auto; padding: 20px; }

        .encabezado { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 2.5px solid #1d4ed8; margin-bottom: 12px; }
        .hospital-nombre { font-size: 14px; font-weight: bold; color: #1d4ed8; }
        .hospital-sub { font-size: 9.5px; color: #64748b; }
        .fecha-impresion { text-align: right; font-size: 9.5px; color: #64748b; }

        .titulo { text-align: center; margin-bottom: 14px; }
        .titulo h1 { font-size: 13px; font-weight: bold; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; }
        .titulo .filtros { font-size: 9.5px; color: #64748b; margin-top: 4px; }

        .resumen-grid { display: flex; gap: 12px; margin-bottom: 12px; }
        .resumen-item { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; text-align: center; }
        .resumen-item .num { font-size: 18px; font-weight: bold; color: #1d4ed8; }
        .resumen-item .label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }

        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        thead tr { background-color: #1d4ed8; color: #fff; }
        thead th { padding: 6px 8px; text-align: left; font-size: 9.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        tbody td { padding: 5px 8px; font-size: 10px; vertical-align: top; }
        tfoot td { padding: 6px 8px; font-size: 10px; font-weight: bold; border-top: 2px solid #1d4ed8; background: #eff6ff; }
        .folio-badge { font-family: monospace; font-weight: bold; font-size: 9.5px; color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 1px 6px; display: inline-block; }
        .faltante-rojo { color: #dc2626; font-weight: bold; }
        .faltante-ok { color: #16a34a; font-weight: bold; }

        .sub-tabla { margin-left: 20px; margin-top: 4px; font-size: 9.5px; color: #374151; }
        .sub-tabla td { padding: 2px 6px; }

        @media print {
            body { margin: 0; padding: 10px; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }

        .no-print {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #1d4ed8;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 12px rgba(29,78,216,0.18);
            margin-bottom: 18px;
        }
        .no-print .print-title { font-size: 13px; font-weight: bold; opacity: 0.92; }
        .no-print .btn-accion {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 18px; border-radius: 7px; font-size: 12px;
            font-weight: bold; cursor: pointer; border: none;
        }
        .no-print .btn-imprimir { background: #fff; color: #1d4ed8; }
        .no-print .btn-imprimir:hover { background: #e0eaff; }
        .no-print .btn-cerrar { background: rgba(255,255,255,0.15); color: #fff; border: 1.5px solid rgba(255,255,255,0.4); }
        .no-print .btn-cerrar:hover { background: rgba(255,255,255,0.28); }
    </style>
</head>
<body>

    {{-- Barra de acciones (no se imprime) --}}
    <div class="no-print">
        <span class="print-title">⎙ Reporte de Entradas al CENDIS</span>
        <div style="display:flex; gap:10px;">
            <button class="btn-accion btn-imprimir" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir
            </button>
            <button class="btn-accion btn-cerrar" onclick="window.close()">✕ Cerrar</button>
        </div>
    </div>

    {{-- Encabezado --}}
    <div class="encabezado">
        <div>
            <div class="hospital-nombre">&#9829; Hospital General de Linares</div>
            <div class="hospital-sub">Sistema de Gestión de Inventario · CENDIS</div>
        </div>
        <div class="fecha-impresion">
            Impreso el {{ now()->format('d/m/Y \a \l\a\s H:i') }}
        </div>
    </div>

    {{-- Título con filtros activos --}}
    <div class="titulo">
        <h1>Reporte Histórico de Entradas al CENDIS</h1>
        <div class="filtros">
            @if($fechaInit && $fechaFin)
                Período: {{ \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            @elseif($fechaInit)
                Desde: {{ \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') }}
            @elseif($fechaFin)
                Hasta: {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            @else
                Todos los registros disponibles
            @endif
            @if($buscar) · Búsqueda: "{{ $buscar }}" @endif
        </div>
    </div>

    {{-- Resumen estadístico --}}
    @php
        $totalEntradas     = $entradas->count();
        $totalInsumos      = $entradas->sum('total_productos');
        $totalCantidad     = $entradas->sum('total_cantidad');
        $totalFaltante     = $entradas->sum('faltante');
    @endphp
    <div class="resumen-grid">
        <div class="resumen-item">
            <div class="num">{{ $totalEntradas }}</div>
            <div class="label">Entradas</div>
        </div>
        <div class="resumen-item">
            <div class="num">{{ $totalInsumos }}</div>
            <div class="label">Insumos Distintos</div>
        </div>
        <div class="resumen-item">
            <div class="num">{{ $totalCantidad }}</div>
            <div class="label">Cantidad Entregada</div>
        </div>
        <div class="resumen-item">
            <div class="num {{ $totalFaltante > 0 ? 'faltante-rojo' : 'faltante-ok' }}">{{ $totalFaltante }}</div>
            <div class="label">Faltante Total</div>
        </div>
    </div>

    {{-- Tabla principal --}}
    @if($entradas->isEmpty())
        <p style="color:#64748b; font-style:italic; padding:20px 0; text-align:center;">
            No se encontraron entradas con los filtros aplicados.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:35px;">#</th>
                    <th style="width:90px;">Folio</th>
                    <th>Área de Almacén</th>
                    <th>Área de Surtimiento</th>
                    <th style="width:65px; text-align:center;">Insumos</th>
                    <th style="width:65px; text-align:center;">Solicitado</th>
                    <th style="width:65px; text-align:center;">Entregado</th>
                    <th style="width:65px; text-align:center;">Faltante</th>
                    <th style="width:80px;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entradas as $i => $entrada)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="folio-badge">ENT-{{ str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT) }}</span></td>
                        <td>{{ $entrada->areaAlmacen->nombre ?? '—' }}</td>
                        <td>{{ $entrada->areaSurtimiento->nombre ?? '—' }}</td>
                        <td style="text-align:center;">{{ $entrada->total_productos }}</td>
                        <td style="text-align:center;">{{ $entrada->solicitado }}</td>
                        <td style="text-align:center; font-weight:bold;">{{ $entrada->total_cantidad }}</td>
                        <td style="text-align:center;" class="{{ $entrada->faltante > 0 ? 'faltante-rojo' : 'faltante-ok' }}">{{ $entrada->faltante }}</td>
                        <td>{{ $entrada->fecha_entrada ? \Carbon\Carbon::parse($entrada->fecha_entrada)->format('d/m/Y') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">Totales:</td>
                    <td style="text-align:center;">{{ $totalInsumos }}</td>
                    <td style="text-align:center;"></td>
                    <td style="text-align:center;">{{ $totalCantidad }}</td>
                    <td style="text-align:center;" class="{{ $totalFaltante > 0 ? 'faltante-rojo' : 'faltante-ok' }}">{{ $totalFaltante }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <p style="font-size:9px; color:#94a3b8; margin-top:10px; text-align:right;">
            Mostrando {{ $entradas->count() }} registros. Máximo 500 por reporte.
        </p>
    @endif

</body>
</html>
