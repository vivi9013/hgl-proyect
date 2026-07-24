@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 px-4">

    <!-- Title and Navigation -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold mb-0 text-dark">
                <i class="bi bi-pie-chart-fill me-2"></i>Analítica de Pedidos por Diferencia
            </h1>
            <p class="text-secondary small mb-0">Indicadores de cobertura de fondo fijo y déficit de insumos por área</p>
        </div>
        <a href="{{ route('pedido_insumos_dif.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Volver al Módulo
        </a>
    </div>

    <!-- Tarjetas KPI -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3">
                    <div class="small text-uppercase opacity-75 fw-bold">TOTAL PEDIDOS DIFERENCIA</div>
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
                    <div class="small text-uppercase opacity-75 fw-bold">DÉFICIT TOTAL UNIDADES</div>
                    <div class="fs-3 fw-bold">{{ number_format($deficitTotal) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas Chart.js -->
    <div class="row g-4 mb-4">
        
        <!-- Top 10 Insumos con Mayor Déficit -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line me-2"></i>Top 10 Insumos con Mayor Déficit (Fondo Fijo - Stock)</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="chartTopDeficit" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Pedidos por Área Solicitante -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-2"></i>Pedidos por Área Solicitante</h6>
                </div>
                <div class="card-body p-3 d-flex align-items-center justify-content-center">
                    <canvas id="chartPedidosPorArea" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Chart Top Déficit
        const topRaw = @json($topDeficit);
        const labelsTop = topRaw.map(i => i.insumo ? (i.insumo.descripcion.length > 25 ? i.insumo.descripcion.substring(0,25)+'...' : i.insumo.descripcion) : 'Insumo #'+i.id_insumo);
        const dataTop = topRaw.map(i => i.deficit);

        new Chart(document.getElementById('chartTopDeficit'), {
            type: 'bar',
            data: {
                labels: labelsTop,
                datasets: [{
                    label: 'Unidades Faltantes (Diferencia)',
                    data: dataTop,
                    backgroundColor: '#dc3545'
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

        // 2. Chart Pedidos por Área
        const areasRaw = @json($pedidosPorArea);
        const labelsAreas = areasRaw.map(a => a.area_abastecimiento ? a.area_abastecimiento.nombre : 'Sin Área');
        const dataAreas = areasRaw.map(a => a.total);

        new Chart(document.getElementById('chartPedidosPorArea'), {
            type: 'pie',
            data: {
                labels: labelsAreas,
                datasets: [{
                    data: dataAreas,
                    backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#fd7e14', '#6610f2', '#6f42c1']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

    });
</script>
@endsection
