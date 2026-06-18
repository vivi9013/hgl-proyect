@extends('layouts.app')

@section('title', 'Gráficas de Proyectos - Hospital General')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado de Navegación y Título ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Módulo Estadístico de Proyectos
            </h1>
            <p class="text-muted mb-0">Visualización analítica de la cantidad de módulos activos asignados a cada proyecto</p>
        </div>
    </div>

    {{-- ── Botón Volver ── --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('proyectos.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa fa-arrow-left me-1"></i> Volver al Catálogo de Proyectos
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Panel de Gráficas ── --}}
    <div class="row g-4">
        
        {{-- Gráfica de Pastel (Donut) --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-pie-chart text-warning me-2"></i>Distribución de Módulos por Proyecto
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1.5 rounded-pill fw-bold">Pastel / Donut</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 380px;">
                    <div class="position-relative" style="width: 100%; max-width: 320px; aspect-ratio: 1 / 1;">
                        <canvas id="pastelChart"></canvas>
                        <div id="chartCenterText" class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; transform: translate(-50%, -50%);">
                            <div id="chartCenterLabel" style="font-size: 16px; font-weight: bold; color: #333333; line-height: 1.2; max-width: 180px; word-wrap: break-word;"></div>
                            <div id="chartCenterValue" style="font-size: 24px; font-weight: bold; color: #333333; margin-top: 4px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-success me-2"></i>Cantidad de Módulos por Proyecto
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 380px;">
                    <div style="width: 100%; height: 320px; position: relative;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Cargamos Chart.js CDN para gráficos interactivos animados de alta calidad --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = {!! json_encode($dataGrafica->pluck('proyecto')) !!};
        const data = {!! json_encode($dataGrafica->pluck('contador')) !!};

        const colors = [
            "#3498db", "#2ecc71", "#1abc9c", "#9b59b6", "#34495e", 
            "#e67e22", "#e74c3c", "#f1c40f", "#16a085", "#27ae60", 
            "#2980b9", "#8e44ad", "#2c3e50", "#d35400", "#c0392b", 
            "#95a5a6", "#7f8c8d", "#bdc3c7", "#ecf0f1", "#000000"
        ];

        // Inicializar texto central
        const centerLabel = document.getElementById('chartCenterLabel');
        const centerValue = document.getElementById('chartCenterValue');
        if (labels.length > 0) {
            centerLabel.textContent = labels[0];
            centerValue.textContent = data[0];
        }

        // 1. PASTEL CHART (DONUT)
        const ctxPastel = document.getElementById('pastelChart').getContext('2d');
        new Chart(ctxPastel, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                cutout: '70%',
                onHover: function(event, activeElements) {
                    if (activeElements && activeElements.length > 0) {
                        const index = activeElements[0].index;
                        centerLabel.textContent = labels[index];
                        centerValue.textContent = data[index];
                    }
                }
            }
        });

        // 2. BAR CHART
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de Módulos',
                    data: data,
                    backgroundColor: '#3498db',
                    borderRadius: 0,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return `Cantidad de módulos: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        },
                        grid: {
                            color: '#edf2f7'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif",
                                size: 10
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
