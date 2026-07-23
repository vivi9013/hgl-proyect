@extends('layouts.app')

@section('title', 'Gráficas - Almacén de Subáreas')

@push('styles')
@vite(['resources/css/peticion_insumos/almacen_subareas/almacen_subareas.css'])
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold text-dark">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Analítica de Almacén de Subáreas
            </h2>
            <small class="text-muted">Estadísticas visuales e indicadores de stock local y fondo fijo</small>
        </div>
        <a href="{{ route('almacen_subareas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
        </a>
    </div>

    <!-- Tarjetas de Resumen KPI -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-6">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <h6 class="text-white-50 text-uppercase mb-1 small fw-bold">Almacenes Activos</h6>
                        <h2 class="mb-0 fw-bold">{{ $totalActivos }}</h2>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <h6 class="text-white-50 text-uppercase mb-1 small fw-bold">Almacenes Inactivos</h6>
                        <h2 class="mb-0 fw-bold">{{ $totalInactivos }}</h2>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas en 2 Columnas -->
    <div class="row g-4">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-secondary">
                        <i class="bi bi-pie-chart me-1"></i> Distribución por Estatus
                    </h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-4">
                    <canvas id="chartStatusAlmacenes" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-secondary">
                        <i class="bi bi-bar-chart me-1"></i> Top Subáreas por Total de Insumos Registrados
                    </h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="chartSubareasTop" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.graficaDataStatus = {
        activos: {{ $totalActivos }},
        inactivos: {{ $totalInactivos }}
    };
    window.graficaDataSubareas = @json($porSubarea);
</script>
@vite(['resources/js/peticion_insumos/almacen_subareas/almacen_subareas.js'])
@endpush
