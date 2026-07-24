@extends('layouts.app')

@section('title', 'Analítica de Trabajadores - Hospital General')

@section('content')
<div class="container-fluid py-4" id="modulo-trabajadores">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Módulo Estadístico de Trabajadores
            </h1>
            <p class="text-muted mb-0">Análisis y distribución del personal de la institución</p>
        </div>
    </div>

    {{-- Botón Volver --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('trabajadores.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa fa-arrow-left me-1"></i> Volver al Catálogo de Trabajadores
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas de Estadísticas Rápidas --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="fa fa-male fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Total Trabajadores</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $totalActivos + $totalInactivos }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="fa fa-check-circle fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Trabajadores Activos</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $totalActivos }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                    <i class="fa fa-times-circle fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Trabajadores Inactivos</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $totalInactivos }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel de Gráficas --}}
    <div class="row g-4 mb-4">

        {{-- Gráfica Donut - Activos vs Inactivos --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-toggle-on text-primary me-2"></i>Estado del Personal
                    </h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold">Donut</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
                        <canvas id="donutEstadoChart" data-json="{{ json_encode([['label' => 'Activos', 'total' => $totalActivos], ['label' => 'Inactivos', 'total' => $totalInactivos]]) }}" style="max-height: 280px; max-width: 280px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras - Trabajadores por Departamento --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-sitemap text-success me-2"></i>Trabajadores por Departamento (Top 10)
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="w-100 h-100" style="min-height: 260px; position: relative;">
                        <canvas id="barDeptoChart" data-json="{{ json_encode($porDepartamento) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras - Trabajadores por Puesto --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-briefcase text-info me-2"></i>Trabajadores por Puesto (Top 10)
                    </h5>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="w-100 h-100" style="min-height: 260px; position: relative;">
                        <canvas id="barPuestoChart" data-json="{{ json_encode($porPuesto) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras - Trabajadores por Tipo --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-quote-left text-warning me-2"></i>Trabajadores por Tipo de Trabajador
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="w-100 h-100" style="min-height: 260px; position: relative;">
                        <canvas id="barTipoChart" data-json="{{ json_encode($porTipo) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@vite(['resources/css/trabajadores/trabajadores.css', 'resources/js/trabajadores/trabajadores.js'])
@endsection
