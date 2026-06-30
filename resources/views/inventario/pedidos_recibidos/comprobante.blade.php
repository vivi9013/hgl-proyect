<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante – PED-{{ str_pad($pedido->id_pedido, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; max-width: 800px; margin: 0 auto; padding: 20px; }

        .encabezado { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 14px; border-bottom: 2.5px solid #1d4ed8; margin-bottom: 14px; }
        .hospital-nombre { font-size: 15px; font-weight: bold; color: #1d4ed8; }
        .hospital-sub { font-size: 10px; color: #64748b; }
        .fecha-impresion { text-align: right; font-size: 10px; color: #64748b; }

        .titulo { text-align: center; margin-bottom: 16px; }
        .titulo h1 { font-size: 14px; font-weight: bold; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; }
        .folio { display: inline-block; background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #16803d; font-size: 13px; font-weight: bold; padding: 3px 14px; border-radius: 6px; margin-top: 4px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .info-item { display: flex; flex-direction: column; }
        .info-item .etiqueta { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 0.05em; }
        .info-item .valor { font-size: 11.5px; color: #0f172a; font-weight: 600; margin-top: 1px; }

        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        thead tr { background-color: #1d4ed8; color: #fff; }
        thead th { padding: 7px 8px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        tbody td { padding: 6px 8px; font-size: 10.5px; vertical-align: top; }
        .clave-badge { font-weight: bold; color: #1d4ed8; font-family: monospace; }

        .firma-area { display: flex; justify-content: space-around; margin-top: 40px; }
        .firma-item { text-align: center; width: 200px; }
        .firma-linea { border-top: 1.5px solid #1a1a1a; margin-bottom: 4px; }
        .firma-etiqueta { font-size: 9.5px; color: #374151; }

        @media print {
            body { margin: 0; padding: 14px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }

        .no-print {
            position: sticky; top: 0; z-index: 100;
            background: #16803d; color: #fff;
            padding: 10px 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 12px rgba(22,128,61,0.18);
            margin-bottom: 18px;
        }
        .no-print .print-title { font-size: 13px; font-weight: bold; opacity: 0.92; letter-spacing: 0.03em; }
        .no-print .btn-accion { display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; border-radius: 7px; font-size: 12.5px; font-weight: bold; cursor: pointer; border: none; transition: background 0.18s; }
        .no-print .btn-imprimir { background: #fff; color: #16803d; }
        .no-print .btn-imprimir:hover { background: #e8f5e9; }
        .no-print .btn-cerrar { background: rgba(255,255,255,0.15); color: #fff; border: 1.5px solid rgba(255,255,255,0.4); }
        .no-print .btn-cerrar:hover { background: rgba(255,255,255,0.28); }
    </style>
</head>
<body>

    {{-- Barra de acción (no se imprime) --}}
    <div class="no-print">
        <span class="print-title">
            ⎙ Comprobante PED-{{ str_pad($pedido->id_pedido, 5, '0', STR_PAD_LEFT) }}
        </span>
        <div style="display:flex; gap:10px;">
            <button class="btn-accion btn-imprimir" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir
            </button>
            <button class="btn-accion btn-cerrar" onclick="window.close()">✕ Cerrar</button>
        </div>
    </div>

    {{-- Encabezado institucional --}}
    <div class="encabezado">
        <div>
            <div class="hospital-nombre">&#9829; Hospital General de Linares</div>
            <div class="hospital-sub">Sistema de Gestión de Inventario</div>
        </div>
        <div class="fecha-impresion">
            Impreso el {{ now()->format('d/m/Y \a \l\a\s H:i') }}
        </div>
    </div>

    {{-- Título y folio --}}
    <div class="titulo">
        <h1>Comprobante de Surtimiento de Pedido</h1>
        <div class="folio">PED-{{ str_pad($pedido->id_pedido, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    {{-- Info general --}}
    <div class="info-grid">
        <div class="info-item">
            <div class="etiqueta">Área de Abastecimiento</div>
            <div class="valor">{{ $pedido->areaAbastecimiento->nombre ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Subárea</div>
            <div class="valor">{{ $pedido->subareaAbastecimiento->nombre ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Almacén Origen</div>
            <div class="valor">{{ $pedido->areaAlmacen->nombre ?? 'CENDIS' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Fecha Solicitud</div>
            <div class="valor">{{ $pedido->fecha_registro ? \Carbon\Carbon::parse($pedido->fecha_registro)->format('d/m/Y') : '—' }} {{ $pedido->hora_registro ?? '' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Fecha Entrega</div>
            <div class="valor">{{ $pedido->fecha_entrega ? \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') : '—' }} {{ $pedido->hora_entrega ?? '' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Solicitado por</div>
            <div class="valor">
                {{ $pedido->usuario && $pedido->usuario->persona
                   ? ($pedido->usuario->persona->nombre . ' ' . $pedido->usuario->persona->ap_paterno)
                   : ($pedido->usuario->nombre_usuario ?? '—') }}
            </div>
        </div>
        <div class="info-item" style="grid-column: span 2;">
            <div class="etiqueta">Porcentaje General de Surtimiento</div>
            <div class="valor" style="color: #16803d; font-size: 13px;">{{ $pedido->porcentaje_entrega }}% de los insumos entregados</div>
        </div>
    </div>

    {{-- Tabla de insumos --}}
    <h2 style="font-size:11.5px; font-weight:bold; margin-bottom:6px; color:#0f172a;">
        Detalle de Insumos Surtidos ({{ $pedido->detalles->count() }})
    </h2>
    @if($pedido->detalles->isEmpty())
        <p style="color:#64748b; font-style:italic; padding:10px 0;">No hay insumos en este pedido.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:35px;">#</th>
                    <th style="width:95px;">Clave</th>
                    <th>Descripción del Insumo</th>
                    <th style="width:75px; text-align:center;">Solicitado</th>
                    <th style="width:75px; text-align:center;">Surtido</th>
                    <th style="width:75px; text-align:center;">Faltante</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->detalles as $i => $detalle)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="clave-badge">{{ $detalle->insumoArea->insumo->clave ?? $detalle->cve_insumo }}</span></td>
                        <td>{{ $detalle->insumoArea->insumo->descripcion ?? '—' }}</td>
                        <td style="text-align:center;">{{ $detalle->cantidad }}</td>
                        <td style="text-align:center; font-weight:bold; color: #16803d;">{{ $detalle->surtido ?? 0 }}</td>
                        <td style="text-align:center; color: #b91c1c;">{{ $detalle->faltante ?? $detalle->cantidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Área de firmas --}}
    <div class="firma-area">
        <div class="firma-item">
            <div class="firma-linea"></div>
            <div class="firma-etiqueta">Entregó (CENDIS Almacén)</div>
        </div>
        <div class="firma-item">
            <div class="firma-linea"></div>
            <div class="firma-etiqueta">Recibió (Área Solicitante)</div>
        </div>
    </div>

</body>
</html>
