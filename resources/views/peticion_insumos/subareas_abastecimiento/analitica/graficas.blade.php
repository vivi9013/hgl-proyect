@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa fa-chart-pie me-2 text-success"></i> Analíticas: Subáreas de Abastecimiento
            </h4>
            <p class="text-muted small mb-0">Distribución de estatus y top de áreas por número de subáreas registradas.</p>
        </div>
        <a href="{{ route('subareas_abastecimiento.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fa fa-arrow-left me-1"></i> Regresar al Catálogo
        </a>
    </div>

    <div class="row g-3">
        <!-- Donut Estatus -->
        <div class="col-12 col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title fw-bold text-dark mb-0 fs-6">
                        <i class="fa fa-pie-chart me-1 text-primary"></i> Estatus de Subáreas de Abastecimiento
                    </h6>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center">
                    <div style="max-width: 320px; width: 100%;">
                        <canvas id="chartEstatusSubarea"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barras por Área -->
        <div class="col-12 col-md-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title fw-bold text-dark mb-0 fs-6">
                        <i class="fa fa-bar-chart me-1 text-success"></i> Top 10 Áreas por Subáreas Registradas
                    </h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="chartSubareasPorArea"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.dataGrafica = @json($dataGrafica);
</script>

@push('scripts')
    @vite(['resources/css/peticion_insumos/subareas_abastecimiento/subareas_abastecimiento.css', 'resources/js/peticion_insumos/subareas_abastecimiento/subareas_abastecimiento.js'])
@endpush
@endsection
