@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 px-4">

    <!-- Title and Navigation -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold mb-0 text-dark">
                <i class="bi bi-pie-chart-fill me-2"></i>Analítica de Pedidos de Insumos
            </h1>
            <p class="text-secondary small mb-0">Estadísticas e indicadores visuales de las solicitudes enviadas a CENDIS</p>
        </div>
        <a href="{{ route('pedido_insumos.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Volver a Pedidos
        </a>
    </div>

    <!-- Tarjetas KPI -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3">
                    <div class="small text-uppercase opacity-75 fw-bold">TOTAL PEDIDOS</div>
                    <div class="fs-3 fw-bold">{{ number_format($totalPedidos) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3">
                    <div class="small text-uppercase opacity-75 fw-bold">ENVIADOS A CENDIS</div>
                    <div class="fs-3 fw-bold">{{ number_format($enviados) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3">
                    <div class="small text-uppercase opacity-75 fw-bold">SURTIDOS Y ACEPTADOS</div>
                    <div class="fs-3 fw-bold">{{ number_format($surtidos) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-3">
                    <div class="small text-uppercase opacity-75 fw-bold">CANCELADOS</div>
                    <div class="fs-3 fw-bold">{{ number_format($cancelados) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas Chart.js -->
    <div class="row g-4 mb-4">
        
        <!-- Donut Chart: Estado de Pedidos -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-donut-chart me-2"></i>Distribución por Estado</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <div style="width: 100%; max-width: 320px;">
                        <canvas id="chartEstados"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bar Chart: Top 10 Insumos Solicitados -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line me-2"></i>Top 10 Insumos Más Solicitados</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="chartTopInsumos" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Bar Chart: Pedidos por Área de Abastecimiento -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-2"></i>Pedidos por Área Solicitante</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="chartPedidosPorArea" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Chart Estados
        const estadosRaw = @json($estadosCount);
        const labelsEstados = estadosRaw.map(e => e.status.toUpperCase());
        const dataEstados = estadosRaw.map(e => e.total);

        new Chart(document.getElementById('chartEstados'), {
            type: 'doughnut',
            data: {
                labels: labelsEstados,
                datasets: [{
                    data: dataEstados,
                    backgroundColor: ['#ffc107', '#198754', '#dc3545', '#6c757d', '#0d6efd']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // 2. Chart Top Insumos
        const topRaw = @json($topInsumos);
        const labelsTop = topRaw.map(i => i.insumo ? (i.insumo.descripcion.length > 25 ? i.insumo.descripcion.substring(0,25)+'...' : i.insumo.descripcion) : i.cve_insumo);
        const dataTop = topRaw.map(i => i.total_solicitado);

        new Chart(document.getElementById('chartTopInsumos'), {
            type: 'bar',
            data: {
                labels: labelsTop,
                datasets: [{
                    label: 'Cantidad Total Solicitada',
                    data: dataTop,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 3. Chart Pedidos por Área
        const areasRaw = @json($pedidosPorArea);
        const labelsAreas = areasRaw.map(a => a.area_abastecimiento ? a.area_abastecimiento.nombre : 'Sin Área');
        const dataAreas = areasRaw.map(a => a.total);

        new Chart(document.getElementById('chartPedidosPorArea'), {
            type: 'bar',
            data: {
                labels: labelsAreas,
                datasets: [{
                    label: 'Total de Pedidos',
                    data: dataAreas,
                    backgroundColor: '#20c997'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

    });
</script>
@endsection
