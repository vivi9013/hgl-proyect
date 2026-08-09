@extends('layouts.app')
@section('title', 'Gráficas Analíticas – Solicitar Servicio')

@section('content')
<div class="container-fluid py-4" id="modulo-graficas-servicio">

    {{-- Cabecera --}}
    <div class="modulo-header">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-pie-chart me-2"></i>Gráficas y Analítica de Servicios
            </h1>
            <p class="text-muted mb-0 small">Estadísticas de tus solicitudes registradas</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('solicitar_servicio.reportes') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i>Volver a Reportes
            </a>
            <a href="{{ route('solicitar_servicio.index') }}" class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="fa fa-home me-1"></i>Módulo Principal
            </a>
        </div>
    </div>

    <hr class="mb-4">

    {{-- Métricas Clave --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value text-dark">{{ $total }}</div>
                <div class="stat-label">Total Solicitudes</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card" style="border-color: #ffc107;">
                <div class="stat-value text-warning">{{ $activos }}</div>
                <div class="stat-label">Pendientes / Activas</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card" style="border-color: #0d6efd;">
                <div class="stat-value text-primary">{{ $liberados }}</div>
                <div class="stat-label">Liberadas</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card" style="border-color: #dc3545;">
                <div class="stat-value text-danger">{{ $cancelados }}</div>
                <div class="stat-label">Canceladas</div>
            </div>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="row g-4">
        {{-- Gráfica de Donut: Estados --}}
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-3 px-3">
                    <h6 class="fw-bold mb-0"><i class="fa fa-adjust me-2"></i>Distribución por Estatus</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="chart-estados" 
                            data-activos="{{ $activos }}" 
                            data-liberados="{{ $liberados }}" 
                            data-cancelados="{{ $cancelados }}"
                            style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras: Por Área --}}
        <div class="col-12 col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-3 px-3">
                    <h6 class="fw-bold mb-0"><i class="fa fa-bar-chart me-2"></i>Top Áreas con Solicitudes</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="chart-por-area" 
                            data-labels="{{ json_encode($porArea->pluck('area')) }}" 
                            data-values="{{ json_encode($porArea->pluck('total')) }}"
                            style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Tendencia Mensual --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-3 px-3">
                    <h6 class="fw-bold mb-0"><i class="fa fa-line-chart me-2"></i>Tendencia Mensual de Solicitudes (Último Año)</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="chart-por-mes" 
                            data-labels="{{ json_encode($porMes->pluck('mes')) }}" 
                            data-values="{{ json_encode($porMes->pluck('total')) }}"
                            style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite([
        'resources/css/soporte_tecnico/solicitar_servicio/solicitar_servicio.css',
        'resources/js/soporte_tecnico/solicitar_servicio/solicitar_servicio.js'
    ])
@endpush
@endsection
