@extends('layouts.app')

@section('title', 'Analítica de Mobiliario General - Hospital General')

@section('content')
<div class="container-fluid py-4" id="modulo-mobiliario-graficas">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Módulo Estadístico de Mobiliario General
            </h1>
            <p class="text-muted mb-0">Análisis y distribución del inventario de mobiliario institucional</p>
        </div>
    </div>

    {{-- Botón Volver --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('mobiliario.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
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
                    <i class="fa fa-cubes fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small fw-bold text-uppercase">Total Mobiliario</h6>
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
                    <h6 class="text-muted mb-0 small fw-bold text-uppercase">Activos</h6>
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
                    <h6 class="text-muted mb-0 small fw-bold text-uppercase">Inactivos</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $stats['inactivos'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Fila 1: Donut + Por Tipo --}}
    <div class="row g-4 mb-4">

        {{-- Donut: Activos vs Inactivos --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-toggle-on text-primary me-2"></i>Estado General
                    </h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold">Donut</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
                        <canvas id="donutEstadoChart" data-json="{{ json_encode($porEstado) }}" style="max-height: 260px; max-width: 260px;"></canvas>
                        <div id="donutCenterWrapper" class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none;">
                            <div id="donutCenterLabel" style="font-size: 11px; color: #6b7280; font-weight: 600;">Total</div>
                            <div id="donutCenterValue" style="font-size: 20px; font-weight: bold; color: #1f2937;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barras: Por Tipo de Mobiliario --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-success me-2"></i>Distribución por Tipo (Top 8)
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4" style="min-height: 320px; position: relative;">
                    <canvas id="barTipoChart" data-json="{{ json_encode($porTipo) }}"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- Fila 2: Por Marca + Por Área --}}
    <div class="row g-4 mb-4">

        {{-- Barras: Por Marca --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-industry text-warning me-2"></i>Distribución por Marca (Top 8)
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4" style="min-height: 300px; position: relative;">
                    <canvas id="barMarcaChart" data-json="{{ json_encode($porMarca) }}"></canvas>
                </div>
            </div>
        </div>

        {{-- Barras: Por Área --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-hospital-o text-info me-2"></i>Distribución por Área (Top 8)
                    </h5>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4" style="min-height: 300px; position: relative;">
                    <canvas id="barAreaChart" data-json="{{ json_encode($porArea) }}"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@vite(['resources/css/mobiliario/mobiliario.css', 'resources/js/mobiliario/mobiliario.js'])
@endsection
