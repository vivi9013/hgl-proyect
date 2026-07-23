@extends('layouts.app')

@section('title', 'Analítica de Departamentos - Hospital General')

@section('content')
<div class="container-fluid py-4" id="modulo-departamentos">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Módulo Estadístico de Departamentos
            </h1>
            <p class="text-muted mb-0">Análisis y distribución del catálogo de departamentos institucionales</p>
        </div>
    </div>

    {{-- Botón Volver --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('departamentos.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa fa-arrow-left me-1"></i> Volver al Catálogo
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
                    <i class="fa fa-cube fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Total Departamentos</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="fa fa-check-circle fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Activos</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $stats['activos'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                    <i class="fa fa-times-circle fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Inactivos</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $stats['inactivos'] }}</h4>
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
                        <i class="fa fa-toggle-on text-primary me-2"></i>Estado del Catálogo
                    </h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold">Donut</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
                        <canvas id="donutEstadoChart" data-json="{{ json_encode($porEstado) }}" style="max-height: 280px; max-width: 280px;"></canvas>
                        <div id="donutCenterWrapper" class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none;">
                            <div id="donutCenterLabel" style="font-size: 11px; color: #6b7280; font-weight: 600;">Total</div>
                            <div id="donutCenterValue" style="font-size: 20px; font-weight: bold; color: #1f2937;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras - Top departamentos por mobiliario --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-success me-2"></i>Mobiliario por Departamento (Top 10)
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="w-100 h-100" style="min-height: 260px; position: relative;">
                        <canvas id="barMobiliarioChart" data-json="{{ json_encode($porMobiliario) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Gráfica de Barras - Top departamentos por trabajadores --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-users text-info me-2"></i>Trabajadores por Departamento (Top 10)
                    </h5>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="w-100 h-100" style="min-height: 260px; position: relative;">
                        <canvas id="barTrabajadoresChart" data-json="{{ json_encode($porTrabajadores) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Chart.js desde CDN --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@vite(['resources/css/departamentos/departamentos.css', 'resources/js/departamentos/departamentos.js'])
@endsection
