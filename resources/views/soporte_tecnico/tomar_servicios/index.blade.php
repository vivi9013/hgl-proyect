@extends('layouts.app')
@section('title', 'Bandeja de Solicitudes Pendientes – Soporte Técnico')

@push('styles')
    @vite(['resources/css/soporte_tecnico/tomar_servicios/tomar_servicios.css'])
@endpush

@section('content')
<div class="container-fluid py-4" id="modulo-tomar-pendientes">

    {{-- Cabecera del módulo --}}
    <div class="modulo-header">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-cogs me-2"></i>Bandeja de Soporte Técnico
            </h1>
            <p class="text-muted mb-0 small">
                Solicitudes de servicio pendientes de atención en tus áreas asignadas
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('solicitar_servicio.index') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                <i class="fa fa-plus me-1"></i>Crear Solicitud
            </a>
        </div>
    </div>

    {{-- Pestañas de Navegación del Flujo de Soporte --}}
    <div class="modulo-nav-tabs">
        <a href="{{ route('tomar_servicios.index') }}" class="nav-link-custom active">
            <i class="fa fa-inbox"></i>
            <span>Por Tomar</span>
            <span class="badge badge-counter bg-warning text-dark">{{ $totalPendientes }}</span>
        </a>
        <a href="{{ route('tomar_servicios.mis_servicios') }}" class="nav-link-custom">
            <i class="fa fa-wrench"></i>
            <span>Mis Servicios en Proceso</span>
            <span class="badge badge-counter bg-primary text-white">{{ $totalEnProceso }}</span>
        </a>
        <a href="{{ route('tomar_servicios.por_liberar') }}" class="nav-link-custom">
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

    {{-- Alerta si no tiene áreas asignadas --}}
    @if(empty($areasAsignadas))
        <div class="alert alert-warning d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm mb-4">
            <i class="fa fa-exclamation-triangle fa-2x text-warning"></i>
            <div>
                <strong class="d-block">Sin áreas de soporte vinculadas</strong>
                <span class="small text-muted">No tienes áreas asignadas en el módulo de personal técnico. Solicita a un administrador que te vincule en el catálogo de áreas.</span>
            </div>
        </div>
    @endif

    {{-- Barra de Herramientas y Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fa fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0 bg-light"
                               data-rol="buscar-pendientes"
                               placeholder="Buscar por folio, solicitante, departamento, problema...">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <select class="form-select bg-light" id="filtro-area-pendientes">
                        <option value="">-- Todas mis áreas asignadas --</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->id }}">{{ $a->area }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 text-md-end text-muted small">
                    <span id="info-registros-pendientes">Cargando solicitudes...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenedor de la Tabla AJAX --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0" id="contenedor-tabla-pendientes">
            @include('soporte_tecnico.tomar_servicios.partials.tabla_pendientes')
        </div>
        <div class="card-footer bg-white d-flex justify-content-center py-3" id="paginador-pendientes">
            {{ $servicios->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>

{{-- Modal de Asignación / Tomar Servicio --}}
@include('soporte_tecnico.tomar_servicios.partials.modal_tomar')

@push('scripts')
    @vite(['resources/js/soporte_tecnico/tomar_servicios/tomar_servicios.js'])
@endpush
@endsection
