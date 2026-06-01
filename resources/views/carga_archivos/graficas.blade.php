@extends('layouts.app')

@section('title', 'Gráficas - Carga de Archivos')

@push('styles')
<style>
    @media print {
        /* Ocultar elementos innecesarios al imprimir */
        .no-print, 
        .app-sidebar, 
        .sidebar, 
        .sidebar-brand, 
        .main-header, 
        nav, 
        .breadcrumb, 
        .btn, 
        .card-header span,
        footer {
            display: none !important;
        }

        /* Ajustar contenedor principal al ancho completo de la página */
        body, 
        .container-fluid, 
        .content-wrapper, 
        main {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            background-color: #ffffff !important;
        }

        /* Colocar gráficos en página completa apilados de forma limpia */
        .row {
            display: block !important;
        }

        .col-12, .col-lg-6 {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
            margin-bottom: 40px !important;
            page-break-inside: avoid !important;
        }

        .card {
            border: 1px solid #e2e8f0 !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-line-chart text-primary me-2"></i>Gráficas del Repositorio
            </h1>
            <p class="text-muted mb-0">Visualización de distribución de archivos por categoría</p>
        </div>
        <nav aria-label="breadcrumb" class="no-print">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('carga_archivos.index') }}">Archivos</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Gráficas</li>
            </ol>
        </nav>
    </div>

    {{-- ── Botón Volver y Botón Imprimir ── --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('carga_archivos.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa fa-arrow-left me-1"></i> Volver a la Lista de Archivos
                    </a>
                    <button onclick="window.print()" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa fa-print me-1"></i> Imprimir Gráficas
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Título Impreso (Visible únicamente al imprimir) --}}
    <div class="d-none d-print-block text-center mb-4 border-bottom pb-3">
        <h2 class="fw-bold text-primary mb-1">Hospital General de Linares</h2>
        <h5 class="text-secondary fw-medium">Reporte Gráfico de Archivos por Categoría</h5>
        <div class="text-muted small mt-2">
            <span><b>Fecha de emisión:</b> {{ date('d/m/Y') }}</span> | 
            <span><b>Hora de emisión:</b> {{ date('H:i:s') }}</span>
        </div>
    </div>

    {{-- ── Panel de Gráficas ── --}}
    <div class="row g-4">
        
        {{-- Gráfica de Pastel (Donut) --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-pie-chart text-warning me-2"></i>Distribución de Archivos
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1.5 rounded-pill fw-bold">Pastel / Donut</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 380px;">
                    <div style="width: 100%; max-width: 320px; position: relative;">
                        <canvas id="pastelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-success me-2"></i>Cantidad por Categoría
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
        const labels = {!! json_encode($categorias->pluck('categoria')) !!};
        const data = {!! json_encode($categorias->pluck('archivos_count')) !!};

        const colors = [
            '#3182ce', '#38a169', '#dd6b20', '#e53e3e', '#805ad5',
            '#319795', '#d69e2e', '#4a5568', '#b7791f', '#2b6cb0',
            '#2c5282', '#276749', '#9c4221', '#9b2c2c', '#553c9a',
            '#234e52', '#744210', '#1a202c', '#2b6cb0', '#2f855a'
        ];

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
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: {
                                family: "'Inter', sans-serif",
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} archivos`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // 2. BAR CHART
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de Archivos',
                    data: data,
                    backgroundColor: '#3182ce',
                    borderRadius: 8,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
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
