@extends('layouts.app')

@section('title', 'Analítica de Computadoras - Hospital General')

@section('content')
<div class="container-fluid py-4" id="modulo-computadoras">

    {{-- Encabezado de Navegación y Título --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Módulo Estadístico de Computadoras
            </h1>
            <p class="text-muted mb-0">Análisis y distribución de equipos de cómputo, sistemas operativos y áreas</p>
        </div>
    </div>

    {{-- Botón Volver --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('computadoras.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
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
                    <i class="fa fa-desktop fa-lg"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Total Equipos</h6>
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
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Equipos Activos</h6>
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
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Equipos Inactivos</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $stats['inactivos'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel de Gráficas --}}
    <div class="row g-4 mb-4">
        
        {{-- Gráfica de Pastel (Donut) - Por Sistema Operativo --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-windows text-primary me-2"></i>Sistemas Operativos
                    </h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-bold">Donut</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="position-relative w-100 h-100 d-flex align-items-center justify-content-center">
                        <canvas id="pastelChart" data-json="{{ json_encode($porSO) }}" style="max-height: 280px; max-width: 280px;"></canvas>
                        <div id="chartCenterText" class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; transform: translate(-50%, -50%);">
                            <div id="chartCenterLabel" style="font-size: 11px; color: #6b7280; font-weight: 600;">Total</div>
                            <div id="chartCenterValue" style="font-size: 20px; font-weight: bold; color: #1f2937;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras - Por Marca --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-industry text-success me-2"></i>Distribución por Marca
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="w-100 h-100" style="min-height: 260px; position: relative;">
                        <canvas id="barChart" data-json="{{ json_encode($porMarca) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras - Por Área --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-hospital-o text-info me-2"></i>Computadoras por Área
                    </h5>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-1.5 rounded-pill fw-bold">Distribución</span>
                </div>
                <div class="card-body p-4" style="min-height: 300px;">
                    <div class="w-100 h-100" style="min-height: 240px; position: relative;">
                        <canvas id="areaChart" data-json="{{ json_encode($porArea) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Cargamos Chart.js desde CDN --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@vite(['resources/css/computadoras/computadoras.css', 'resources/js/computadoras/computadoras.js'])
@endsection
