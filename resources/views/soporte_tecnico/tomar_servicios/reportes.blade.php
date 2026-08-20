@extends('layouts.app')
@section('title', 'Reportes y Analítica – Soporte Técnico')

@push('styles')
    @vite(['resources/css/soporte_tecnico/tomar_servicios/tomar_servicios.css'])
@endpush

@section('content')
<div class="container-fluid py-4" id="modulo-tomar-reportes">

    {{-- Cabecera del módulo --}}
    <div class="modulo-header">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-bar-chart me-2"></i>Métricas y Reportes de Soporte Técnico
            </h1>
            <p class="text-muted mb-0 small">
                Estadísticas de atención y reporte consolidado por período y área
            </p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                <i class="fa fa-print me-1"></i>Imprimir Reporte
            </button>
        </div>
    </div>

    {{-- Pestañas de Navegación del Flujo de Soporte --}}
    <div class="modulo-nav-tabs no-print">
        <a href="{{ route('tomar_servicios.index') }}" class="nav-link-custom">
            <i class="fa fa-inbox"></i>
            <span>Por Tomar</span>
        </a>
        <a href="{{ route('tomar_servicios.mis_servicios') }}" class="nav-link-custom">
            <i class="fa fa-wrench"></i>
            <span>Mis Servicios en Proceso</span>
        </a>
        <a href="{{ route('tomar_servicios.por_liberar') }}" class="nav-link-custom">
            <i class="fa fa-check-circle"></i>
            <span>Por Liberar</span>
        </a>
        <a href="{{ route('tomar_servicios.historial') }}" class="nav-link-custom">
            <i class="fa fa-history"></i>
            <span>Historial General</span>
        </a>
        <a href="{{ route('tomar_servicios.reportes') }}" class="nav-link-custom active">
            <i class="fa fa-bar-chart"></i>
            <span>Reportes / Métricas</span>
        </a>
    </div>

    {{-- Tarjetas de Indicadores --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm bg-dark text-white rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-white-50 d-block">TOTAL SERVICIOS</span>
                            <span class="fs-3 fw-bold">{{ $totalServicios }}</span>
                        </div>
                        <i class="fa fa-ticket fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-white-50 d-block">LIBERADOS</span>
                            <span class="fs-3 fw-bold">{{ $liberados }}</span>
                        </div>
                        <i class="fa fa-check-circle fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-white-50 d-block">EN PROCESO</span>
                            <span class="fs-3 fw-bold">{{ $enProceso }}</span>
                        </div>
                        <i class="fa fa-wrench fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-black-50 d-block">POR ATENDER</span>
                            <span class="fs-3 fw-bold">{{ $pendientes }}</span>
                        </div>
                        <i class="fa fa-clock-o fa-2x text-black-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros del Reporte --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('tomar_servicios.reportes') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold">Fecha Desde</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ $fechaDesde }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ $fechaHasta }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Área de Soporte</label>
                    <select name="id_area" class="form-select form-select-sm">
                        <option value="">-- Todas las áreas asignadas --</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->id }}" {{ $areaId == $a->id ? 'selected' : '' }}>{{ $a->area }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-sm btn-dark w-100">
                        <i class="fa fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Servicios Filtrados --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0">Detalle de Servicios en el Período</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="tabla-servicios">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Área</th>
                            <th>Solicitante</th>
                            <th>Técnico</th>
                            <th>Problema</th>
                            <th>Solución</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servicios as $s)
                        <tr>
                            <td><strong>#{{ $s->id }}</strong></td>
                            <td>{{ $s->area ? $s->area->area : '—' }}</td>
                            <td class="text-start">{{ $s->nombre_solicitante }}</td>
                            <td class="text-start">{{ $s->nombre_servidor ?: 'Sin asignar' }}</td>
                            <td class="text-start" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ Str::limit($s->descripcion_servicio, 40) }}
                            </td>
                            <td class="text-start" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ Str::limit($s->accion_realizada, 40) ?: '—' }}
                            </td>
                            <td>{{ $s->fecha_peticion }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $s->estatus_final ?: ($s->liberado ? 'Liberado' : ($s->terminado ? 'Terminado' : 'En Proceso')) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No se encontraron servicios en el rango seleccionado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-center py-3 no-print">
            {{ $servicios->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>
@endsection
