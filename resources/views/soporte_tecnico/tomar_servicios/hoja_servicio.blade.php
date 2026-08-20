<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de Servicio #{{ $servicio->id }} – Soporte Técnico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            color: #212529;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .hoja-servicio-container {
            max-width: 850px;
            margin: 20px auto;
            background: #fff;
            padding: 35px 45px;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header-institucional {
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .seccion-header {
            background-color: #f1f3f5;
            padding: 6px 12px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-left: 4px solid #000;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .dato-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
        }

        .dato-valor {
            font-size: 13px;
            font-weight: 600;
            color: #111;
        }

        .caja-texto {
            background-color: #fafbfc;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 12.5px;
            line-height: 1.4;
            min-height: 55px;
            white-space: pre-wrap;
        }

        .firma-box {
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 8px;
            margin-top: 55px;
        }

        .firma-titulo {
            font-weight: bold;
            font-size: 12px;
        }

        .firma-sub {
            font-size: 11px;
            color: #6c757d;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .hoja-servicio-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark d-flex justify-content-center gap-2">
    <button onclick="window.print()" class="btn btn-sm btn-light fw-bold px-4">
        <i class="fa fa-print me-1"></i> Imprimir Hoja de Servicio
    </button>
    <button onclick="window.close()" class="btn btn-sm btn-outline-light px-3">
        <i class="fa fa-times me-1"></i> Cerrar Ventana
    </button>
</div>

<div class="hoja-servicio-container">

    {{-- Encabezado Institucional --}}
    <div class="header-institucional d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1 text-dark">HOSPITAL GENERAL DE LINARES</h4>
            <h6 class="text-secondary fw-semibold mb-0">DEPARTAMENTO DE SOPORTE TÉCNICO Y MANTENIMIENTO</h6>
            <small class="text-muted">ORDEN Y HOJA DE CONTROL DE SERVICIO</small>
        </div>
        <div class="text-end">
            <div class="border border-2 border-dark px-3 py-1 rounded bg-light">
                <span class="d-block text-muted small fw-bold">FOLIO DE SERVICIO</span>
                <span class="fs-4 fw-bold text-dark">#{{ str_pad($servicio->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <span class="badge bg-dark mt-1 px-2 py-1">
                Estatus: {{ $servicio->estatus_final ?: ($servicio->liberado ? 'Liberado' : ($servicio->terminado ? 'Terminado' : 'En Proceso')) }}
            </span>
        </div>
    </div>

    {{-- 1. Datos del Solicitante y Ubicación --}}
    <div class="seccion-header">1. Datos del Solicitante y Ubicación</div>
    <div class="row g-3">
        <div class="col-6">
            <span class="dato-label">Nombre del Solicitante</span>
            <span class="dato-valor">{{ $servicio->nombre_solicitante }}</span>
        </div>
        <div class="col-4">
            <span class="dato-label">Departamento</span>
            <span class="dato-valor">{{ $servicio->departamento }}</span>
        </div>
        <div class="col-2">
            <span class="dato-label">Extensión</span>
            <span class="dato-valor">{{ $servicio->ext_telefonica ?: 'S/Ext' }}</span>
        </div>
        <div class="col-6">
            <span class="dato-label">Área de Soporte Solicitada</span>
            <span class="dato-valor">{{ $servicio->area ? $servicio->area->area : '—' }}</span>
        </div>
        <div class="col-6">
            <span class="dato-label">Sede / Ubicación</span>
            <span class="dato-valor">{{ $servicio->sede }}</span>
        </div>
    </div>

    {{-- 2. Tiempos y Trazabilidad del Servicio --}}
    <div class="seccion-header">2. Tiempos y Trazabilidad del Servicio</div>
    <div class="row g-3">
        <div class="col-3">
            <span class="dato-label">Fecha Petición</span>
            <span class="dato-valor">
                {{ $servicio->fecha_peticion ? \Carbon\Carbon::parse($servicio->fecha_peticion)->format('d/m/Y') : '—' }}
                <small class="text-muted d-block">{{ $servicio->hora_peticion ? \Carbon\Carbon::parse($servicio->hora_peticion)->format('h:i A') : '' }}</small>
            </span>
        </div>
        <div class="col-3">
            <span class="dato-label">Fecha Tomado</span>
            <span class="dato-valor">
                {{ $servicio->fecha_tomado ? \Carbon\Carbon::parse($servicio->fecha_tomado)->format('d/m/Y') : '—' }}
                <small class="text-muted d-block">{{ $servicio->hora_tomado ? \Carbon\Carbon::parse($servicio->hora_tomado)->format('h:i A') : '' }}</small>
            </span>
        </div>
        <div class="col-3">
            <span class="dato-label">Fecha Conclusión</span>
            <span class="dato-valor">
                {{ $servicio->fecha_termino ? \Carbon\Carbon::parse($servicio->fecha_termino)->format('d/m/Y') : '—' }}
                <small class="text-muted d-block">{{ $servicio->hora_termino ? \Carbon\Carbon::parse($servicio->hora_termino)->format('h:i A') : '' }}</small>
            </span>
        </div>
        <div class="col-3">
            <span class="dato-label">Fecha Liberación</span>
            <span class="dato-valor">
                {{ $servicio->fecha_finaliza ? \Carbon\Carbon::parse($servicio->fecha_finaliza)->format('d/m/Y') : '—' }}
                <small class="text-muted d-block">{{ $servicio->hora_finaliza ? \Carbon\Carbon::parse($servicio->hora_finaliza)->format('h:i A') : '' }}</small>
            </span>
        </div>
    </div>

    {{-- 3. Equipo o Mobiliario Involucrado --}}
    <div class="seccion-header">3. Equipo / Mobiliario Atendido</div>
    <div class="row g-3">
        <div class="col-3">
            <span class="dato-label">No. Inventario</span>
            <span class="dato-valor">{{ $servicio->inventario ?: 'Sin equipo específico' }}</span>
        </div>
        <div class="col-6">
            <span class="dato-label">Descripción del Equipo</span>
            <span class="dato-valor">{{ $servicio->descripcion_mobiliario ?: 'Servicio general / Soporte en sitio' }}</span>
        </div>
        <div class="col-3">
            <span class="dato-label">Tipo de Servicio</span>
            <span class="dato-valor">{{ $servicio->tipo_servicio ?: 'Soporte General' }}</span>
        </div>
    </div>

    {{-- 4. Detalle del Problema y Solución Aplicada --}}
    <div class="seccion-header">4. Descripción del Problema y Solución Técnica</div>
    
    <div class="mb-3">
        <span class="dato-label">Problema Reportado por el Solicitante:</span>
        <div class="caja-texto">{{ $servicio->descripcion_servicio }}</div>
    </div>

    <div class="mb-3">
        <span class="dato-label">Acciones Realizadas / Solución y Diagnóstico Técnico:</span>
        <div class="caja-texto">{{ $servicio->accion_realizada ?: 'Atención en proceso...' }}</div>
    </div>

    {{-- 5. Técnico Asignado y Liberación --}}
    <div class="row g-3 mt-1">
        <div class="col-6">
            <span class="dato-label">Técnico que Atendió</span>
            <span class="dato-valor text-primary">{{ $servicio->nombre_servidor ?: 'Sin asignar' }}</span>
        </div>
        <div class="col-6">
            <span class="dato-label">Liberado Por / Modo de Cierre</span>
            <span class="dato-valor">{{ $servicio->liberadox ? ($servicio->liberadox . ' - ' . ($servicio->estatus_final ?: 'Concluido')) : 'Pendiente de liberación' }}</span>
        </div>
    </div>

    {{-- 6. Firmas de Conformidad --}}
    <div class="row mt-4 pt-3">
        <div class="col-4">
            <div class="firma-box">
                <div class="firma-titulo">{{ $servicio->nombre_solicitante }}</div>
                <div class="firma-sub">Firma del Solicitante<br>(Conformidad del Servicio)</div>
            </div>
        </div>
        <div class="col-4">
            <div class="firma-box">
                <div class="firma-titulo">{{ $servicio->nombre_servidor ?: 'Personal de Soporte' }}</div>
                <div class="firma-sub">Firma del Técnico<br>(Atención y Solución)</div>
            </div>
        </div>
        <div class="col-4">
            <div class="firma-box">
                <div class="firma-titulo">Jefatura de Soporte</div>
                <div class="firma-sub">Vo.Bo. Área Técnica<br>(Revisión y Control)</div>
            </div>
        </div>
    </div>

    <div class="text-center text-muted small mt-4 pt-2 border-top">
        Documento oficial generado el {{ now()->format('d/m/Y H:i:s') }} — Sistema HGLinares Soporte Técnico
    </div>

</div>

</body>
</html>
