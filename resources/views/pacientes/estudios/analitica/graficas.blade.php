@extends('layouts.app')

@section('title', 'Gráficas - Estudios RX')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('rx.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa fa-arrow-left me-1"></i>Regresar
            </a>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-bar-chart text-primary me-2"></i>Análisis y Gráficas de Estudios RX
            </h1>
            <p class="text-muted mb-0">Visualiza estadísticas en tiempo real de estudios de radiología agrupados por regiones anatómicas</p>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    <div class="row g-4">
        {{-- Gráfica de Barras --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3" style="border: 1px solid #e5e7eb !important;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-bar-chart text-secondary me-2"></i>Estudios por Región Anatómica
                    </h5>
                    <p class="text-muted small mb-0">Total de estudios acumulados en el sistema por cada región anatómica</p>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="chartEstudiosBarras"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfica de Rosquilla --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3" style="border: 1px solid #e5e7eb !important;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-pie-chart text-secondary me-2"></i>Distribución Regional
                    </h5>
                    <p class="text-muted small mb-0">Porcentaje y proporción de cada estudio en el universo general</p>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    <div style="position: relative; height: 350px; width: 100%; max-width: 350px;">
                        <canvas id="chartEstudiosRosquilla"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Cargamos Chart.js CDN para gráficos interactivos --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = ['Cráneo', 'Tórax', 'Abdomen', 'Columna', 'Miembro Superior', 'Miembro Inferior', 'Medio de Contraste'];
        const dataPoints = [
            {{ $stats->craneo }},
            {{ $stats->torax }},
            {{ $stats->abdomen }},
            {{ $stats->columna }},
            {{ $stats->m_sup }},
            {{ $stats->m_inf }},
            {{ $stats->contraste }}
        ];

        const totalEstudios = dataPoints.reduce((a, b) => a + b, 0);

        if (totalEstudios === 0) {
            // Mostrar mensaje si no hay datos
            document.querySelectorAll('canvas').forEach(c => {
                const ctx = c.getContext('2d');
                ctx.font = '14px sans-serif';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('No hay suficientes estudios registrados para graficar.', c.width / 2, c.height / 2);
            });
            return;
        }

        // Paleta de colores vibrantes y armoniosos
        const colors = [
            'rgba(52, 152, 219, 0.85)',  // Cráneo: Azul
            'rgba(46, 204, 113, 0.85)',  // Tórax: Verde
            'rgba(155, 89, 182, 0.85)',  // Abdomen: Púrpura
            'rgba(26, 188, 156, 0.85)',  // Columna: Turquesa
            'rgba(230, 126, 34, 0.85)',  // Miembro Superior: Naranja
            'rgba(231, 76, 60, 0.85)',   // Miembro Inferior: Rojo
            'rgba(52, 73, 94, 0.85)'     // Contraste: Marino
        ];

        const borderColors = colors.map(c => c.replace('0.85', '1.0'));

        // 1. GRAFICA DE BARRAS
        const ctxBarras = document.getElementById('chartEstudiosBarras').getContext('2d');
        new Chart(ctxBarras, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Estudios',
                    data: dataPoints,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // 2. GRAFICA DE ROSQUILLA
        const ctxRosquilla = document.getElementById('chartEstudiosRosquilla').getContext('2d');
        new Chart(ctxRosquilla, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: dataPoints,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 1.5
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
                            padding: 15
                        }
                    }
                },
                cutout: '60%'
            }
        });
    });
</script>
@endsection
