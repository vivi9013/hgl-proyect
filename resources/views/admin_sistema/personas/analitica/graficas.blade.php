@extends('layouts.app')

@section('title', 'Gráficas de Personas - Hospital General de Linares')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Módulo Estadístico — Personas
            </h1>
            <p class="text-muted mb-0">Distribución analítica del padrón de personas por género</p>
        </div>
    </div>

    {{-- Botón Volver --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('personas.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa fa-arrow-left me-1"></i> Volver al Catálogo de Personas
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white text-center p-3">
                <div class="text-primary fw-bold stat-value">{{ $totalActivos }}</div>
                <small class="text-muted fw-semibold">Personas Activas</small>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white text-center p-3">
                <div class="text-danger fw-bold stat-value">{{ $totalInactivos }}</div>
                <small class="text-muted fw-semibold">Personas Inactivas</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white text-center p-3">
                <div class="text-success fw-bold stat-value">{{ $totalEstudiantes }}</div>
                <small class="text-muted fw-semibold">Estudiantes Activos</small>
            </div>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="row g-4">

        {{-- Donut: Distribución por Sexo --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-pie-chart text-warning me-2"></i>Distribución por Género
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1 rounded-pill fw-bold">Donut</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 340px;">
                    <div class="position-relative" style="width: 100%; max-width: 280px; aspect-ratio: 1 / 1;">
                        <canvas id="donutSexoChart"></canvas>
                        <div class="position-absolute start-50 top-50 translate-middle text-center donut-center-label">
                            <div id="centerSexoLabel" class="donut-center-label"></div>
                            <div id="centerSexoValue" class="donut-center-value"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barras: Distribución por Género --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-success me-2"></i>Distribución por Género
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 340px;">
                    <div style="width: 100%; height: 280px; position: relative;">
                        <canvas id="barSexoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ── Datos desde el Controlador ────────────────────────────────────────
        const sexoLabels = {!! json_encode($porSexo->keys()->map(fn($k) => $k === 'M' ? 'Masculino' : 'Femenino')) !!};
        const sexoData   = {!! json_encode($porSexo->values()) !!};

        const coloresSexo = ['#3498db', '#e74c3c'];
        const temaColor   = '{{ session('s_colGr', '#2980b9') }}';

        // ── DONUT: Sexo ───────────────────────────────────────────────────────
        const centerLabel = document.getElementById('centerSexoLabel');
        const centerValue = document.getElementById('centerSexoValue');
        if (sexoLabels.length > 0) {
            centerLabel.textContent = sexoLabels[0];
            centerValue.textContent = sexoData[0];
        }

        new Chart(document.getElementById('donutSexoChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: sexoLabels,
                datasets: [{
                    data: sexoData,
                    backgroundColor: coloresSexo.slice(0, sexoLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                onHover: function(event, activeElements) {
                    if (activeElements && activeElements.length > 0) {
                        const i = activeElements[0].index;
                        centerLabel.textContent = sexoLabels[i];
                        centerValue.textContent = sexoData[i];
                    }
                }
            }
        });

        // ── BAR: Género ───────────────────────────────────────────────────────
        new Chart(document.getElementById('barSexoChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: sexoLabels,
                datasets: [{
                    label: 'Cantidad de personas',
                    data: sexoData,
                    backgroundColor: coloresSexo.slice(0, sexoLabels.length),
                    borderRadius: 0,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => `Total: ${ctx.raw}`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endpush
@endsection
