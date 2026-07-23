@extends('layouts.app')

@section('title', 'Gráficas de Monitores - Hospital General')

@section('content')
<div class="container-fluid py-4" id="modulo-monitores">

    {{-- Encabezado de Navegación y Título --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Módulo Estadístico de Monitores
            </h1>
            <p class="text-muted mb-0">Visualización analítica de la cantidad de monitores agrupados por marca y tipo</p>
        </div>
    </div>

    {{-- Botón Volver --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('monitores.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa fa-arrow-left me-1"></i> Volver al Catálogo de Monitores
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel de Gráficas --}}
    <div class="row g-4">
        
        {{-- Gráfica de Pastel (Donut) - Por Marca --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-pie-chart text-warning me-2"></i>Distribución por Marca
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1.5 rounded-pill fw-bold">Doughnut</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center chart-container-donut">
                    <div class="position-relative chart-donut-wrapper">
                        <canvas id="pastelChart" data-json="{{ json_encode($porMarca) }}"></canvas>
                        <div id="chartCenterText" class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; transform: translate(-50%, -50%);">
                            <div id="chartCenterLabel" class="chart-center-label"></div>
                            <div id="chartCenterValue" class="chart-center-value"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras - Por Tipo --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-success me-2"></i>Monitores por Tipo de Pantalla
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center chart-container-bar">
                    <div class="chart-bar-wrapper">
                        <canvas id="barChart" data-json="{{ json_encode($porTipo) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Cargamos Chart.js CDN para gráficos interactivos animados de alta calidad --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@vite(['resources/css/monitores/monitores.css', 'resources/js/monitores/monitores.js'])
@endsection
