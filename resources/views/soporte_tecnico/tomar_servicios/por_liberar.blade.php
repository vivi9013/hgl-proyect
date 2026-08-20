@extends('layouts.app')
@section('title', 'Servicios Por Liberar – Soporte Técnico')

@push('styles')
    @vite(['resources/css/soporte_tecnico/tomar_servicios/tomar_servicios.css'])
@endpush

@section('content')
<div class="container-fluid py-4" id="modulo-tomar-por-liberar">

    {{-- Cabecera del módulo --}}
    <div class="modulo-header">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-check-circle me-2"></i>Servicios Por Liberar
            </h1>
            <p class="text-muted mb-0 small">
                Servicios concluidos técnicamente en espera de cierre o liberación
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tomar_servicios.index') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                <i class="fa fa-inbox me-1"></i>Bandeja de Pendientes
            </a>
        </div>
    </div>

    {{-- Pestañas de Navegación del Flujo de Soporte --}}
    <div class="modulo-nav-tabs">
        <a href="{{ route('tomar_servicios.index') }}" class="nav-link-custom">
            <i class="fa fa-inbox"></i>
            <span>Por Tomar</span>
            <span class="badge badge-counter bg-warning text-dark">{{ $totalPendientes }}</span>
        </a>
        <a href="{{ route('tomar_servicios.mis_servicios') }}" class="nav-link-custom">
            <i class="fa fa-wrench"></i>
            <span>Mis Servicios en Proceso</span>
            <span class="badge badge-counter bg-primary text-white">{{ $totalEnProceso }}</span>
        </a>
        <a href="{{ route('tomar_servicios.por_liberar') }}" class="nav-link-custom active">
            <i class="fa fa-check-circle"></i>
            <span>Por Liberar</span>
            <span class="badge badge-counter bg-success text-white">{{ $totalPorLiberar }}</span>
        </a>
        <a href="{{ route('tomar_servicios.historial') }}" class="nav-link-custom">
            <i class="fa fa-history"></i>
            <span>Historial General</span>
        </a>
        <a href="{{ route('tomar_servicios.reportes') }}" class="nav-link-custom">
            <i class="fa fa-bar-chart"></i>
            <span>Reportes / Métricas</span>
        </a>
    </div>

    {{-- Barra de Búsqueda --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fa fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0 bg-light"
                               data-rol="buscar-por-liberar"
                               placeholder="Buscar por folio, solicitante, técnico, solución...">
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end text-muted small">
                    <span id="info-registros-por-liberar">Cargando servicios por liberar...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenedor de la Tabla AJAX --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0" id="contenedor-tabla-por-liberar">
            @include('soporte_tecnico.tomar_servicios.partials.tabla_por_liberar')
        </div>
        <div class="card-footer bg-white d-flex justify-content-center py-3" id="paginador-por-liberar">
            {{ $servicios->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>

@push('scripts')
    @vite(['resources/js/soporte_tecnico/tomar_servicios/tomar_servicios.js'])
@endpush
@endsection
