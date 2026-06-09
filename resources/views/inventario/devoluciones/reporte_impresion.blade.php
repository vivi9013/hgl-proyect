<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Devoluciones – HGL</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }

        .encabezado { display: flex; justify-content: space-between; align-items: flex-start; padding: 18px 24px 12px; border-bottom: 2.5px solid #1d4ed8; }
        .encabezado .hospital { font-size: 16px; font-weight: bold; color: #1d4ed8; }
        .encabezado .subtitulo { font-size: 11px; color: #64748b; margin-top: 2px; }
        .encabezado .fecha-impresion { text-align: right; font-size: 10px; color: #64748b; }
        .encabezado .fecha-impresion strong { display: block; font-size: 12px; color: #1a1a1a; }

        .titulo-reporte { padding: 10px 24px; background: #f1f5f9; border-bottom: 1px solid #e2e8f0; }
        .titulo-reporte h1 { font-size: 13px; font-weight: bold; color: #0f172a; }
        .titulo-reporte .filtros { font-size: 10px; color: #64748b; margin-top: 3px; }

        .contenido { padding: 12px 24px 24px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead tr { background-color: #1d4ed8; color: #fff; }
        thead th { padding: 7px 8px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        tbody td { padding: 6px 8px; vertical-align: top; font-size: 10.5px; }

        .badge-folio { font-weight: bold; color: #1d4ed8; }
        .badge-status { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 9.5px; font-weight: bold; }
        .status-proceso { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .status-terminado { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

        .sub-tabla { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .sub-tabla td { padding: 3px 6px; font-size: 9.5px; color: #374151; border-bottom: 1px solid #f1f5f9; }
        .sub-tabla .clave { font-weight: bold; color: #1d4ed8; font-family: monospace; }

        .pie-pagina { position: fixed; bottom: 0; left: 0; right: 0; padding: 6px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 9px; color: #94a3b8; background: #fff; }

        .resumen { display: flex; gap: 20px; margin-bottom: 12px; padding: 10px 14px; background: #f1f5f9; border-radius: 8px; border: 1px solid #e2e8f0; }
        .resumen-item { text-align: center; }
        .resumen-item .valor { font-size: 16px; font-weight: bold; color: #1d4ed8; }
        .resumen-item .etiqueta { font-size: 9px; color: #64748b; text-transform: uppercase; }

        @media print {
            .pie-pagina { position: fixed; bottom: 0; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }

        /* Barra de acciones */
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
            margin-bottom: 16px;
        }
        .no-print .print-title { font-size: 13px; font-weight: bold; opacity: 0.92; letter-spacing: 0.03em; }
        .no-print .btn-accion {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background 0.18s;
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
        <span class="print-title">⎙ Reporte de Devoluciones – HGL</span>
        <div style="display:flex; gap:10px;">
            <button class="btn-accion btn-imprimir" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir
            </button>
            <button class="btn-accion btn-cerrar" onclick="window.close()">✕ Cerrar</button>
        </div>
    </div>

    {{-- Encabezado del reporte --}}
    <div class="encabezado">
        <div>
            <div class="hospital">&#9829; Hospital General de Linares</div>
            <div class="subtitulo">Sistema de Gestión de Inventario – Módulo de Devoluciones</div>
        </div>
        <div class="fecha-impresion">
            <strong>{{ now()->format('d/m/Y H:i') }}</strong>
            Fecha de impresión
        </div>
    </div>

    {{-- Título y filtros activos --}}
    <div class="titulo-reporte">
        <h1>Reporte de Devoluciones</h1>
        <div class="filtros">
            @if($fechaInit || $fechaFin)
                Período: {{ $fechaInit ? \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') : '—' }}
                al {{ $fechaFin ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') : '—' }}
                &nbsp;&bull;&nbsp;
            @endif
            @if($status)
                Status: {{ $status }} &nbsp;&bull;&nbsp;
            @endif
            Total de registros: {{ $devoluciones->count() }}
            @if($devoluciones->count() >= 500)
                (limitado a 500 registros)
            @endif
        </div>
    </div>

    <div class="contenido">

        {{-- Resumen --}}
        <div class="resumen">
            <div class="resumen-item">
                <div class="valor">{{ $devoluciones->count() }}</div>
                <div class="etiqueta">Devoluciones</div>
            </div>
            <div class="resumen-item">
                <div class="valor">{{ $devoluciones->where('status', 'Terminado')->count() }}</div>
                <div class="etiqueta">Terminadas</div>
            </div>
            <div class="resumen-item">
                <div class="valor">{{ $devoluciones->where('status', 'En proceso')->count() }}</div>
                <div class="etiqueta">En Proceso</div>
            </div>
            <div class="resumen-item">
                <div class="valor">{{ $devoluciones->sum(fn($d) => $d->detalles->count()) }}</div>
                <div class="etiqueta">Total Insumos</div>
            </div>
        </div>

        {{-- Tabla principal --}}
        @if($devoluciones->isEmpty())
            <p style="text-align:center; color:#64748b; padding: 20px 0;">
                No hay devoluciones que coincidan con los filtros seleccionados.
            </p>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th style="width:130px;">Folio</th>
                        <th>Área de Almacén</th>
                        <th>Área Abastecimiento</th>
                        <th>Motivo</th>
                        <th style="width:75px;">Fecha</th>
                        <th style="width:70px;">Status</th>
                        <th>Insumos devueltos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devoluciones as $i => $devolucion)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><span class="badge-folio">DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}</span></td>
                            <td>{{ $devolucion->areaAlmacen->nombre ?? '—' }}</td>
                            <td>{{ $devolucion->areaAbastecimiento->nombre ?? '—' }}</td>
                            <td>{{ $devolucion->motivo->descripcion ?? '—' }}</td>
                            <td>{{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : '—' }}</td>
                            <td>
                                <span class="badge-status {{ $devolucion->status === 'Terminado' ? 'status-terminado' : 'status-proceso' }}">
                                    {{ $devolucion->status }}
                                </span>
                            </td>
                            <td>
                                @if($devolucion->detalles->isNotEmpty())
                                    <table class="sub-tabla">
                                        @foreach($devolucion->detalles as $detalle)
                                            <tr>
                                                <td class="clave" style="width:80px;">{{ $detalle->insumo->clave ?? '—' }}</td>
                                                <td>{{ $detalle->insumo->descripcion ?? '—' }}</td>
                                                <td style="width:40px; text-align:center; font-weight:bold;">{{ $detalle->cantidad }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    <span style="color:#94a3b8; font-style:italic;">Sin insumos</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="pie-pagina">
        <span>Hospital General de Linares – Sistema HGL</span>
        <span>Impreso el {{ now()->format('d/m/Y \a \l\a\s H:i:s') }}</span>
    </div>

    <script>
        // El usuario controla la impresión con el botón
    </script>
</body>
</html>
