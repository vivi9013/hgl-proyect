@extends('layouts.app')

@section('title', 'Gráficas de Módulos - Hospital General')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Estadísticas de Módulos
            </h1>
            <p class="text-muted mb-0">Visualización analítica de la distribución de módulos por Categoría, Proyecto y Perfil</p>
        </div>
        <a href="{{ route('modulos.index') }}"
           class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
            <i class="fa fa-arrow-left me-2"></i>Volver al Listado
        </a>
    </div>

    {{-- KPIs rápidos --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-3 px-2">
                <div class="h2 fw-bold text-primary mb-0">{{ $stats['total'] }}</div>
                <small class="text-muted">Total Módulos</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-3 px-2">
                <div class="h2 fw-bold text-success mb-0">{{ $stats['activos'] }}</div>
                <small class="text-muted">Activos</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-3 px-2">
                <div class="h2 fw-bold text-danger mb-0">{{ $stats['inactivos'] }}</div>
                <small class="text-muted">Inactivos</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-3 px-2">
                <div class="h2 fw-bold text-warning mb-0">{{ $stats['categorias'] }}</div>
                <small class="text-muted">Categorías</small>
            </div>
        </div>
    </div>

    {{-- Panel de Gráficas --}}
    <div class="row g-4">

        {{-- ================= ROW 1: CATEGORÍAS ================= --}}
        {{-- Donut: Módulos por Categoría --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-pie-chart text-warning me-2"></i>Módulos por Categoría (Pastel)
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 rounded-pill fw-bold">Pastel</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 340px;">
                    <div class="position-relative" style="width: 100%; max-width: 260px; aspect-ratio: 1 / 1;">
                        <canvas id="donutCategoria"></canvas>
                        <div class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; width: 140px;">
                            <div id="donutCatLabel" style="font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Cargando...</div>
                            <div id="donutCatValue" style="font-size: 24px; font-weight: bold; color: #111; margin-top: 2px;">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barras: Módulos por Categoría --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-warning me-2"></i>Módulos por Categoría (Barras)
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 340px;">
                    <div style="width: 100%; height: 260px; position: relative;">
                        <canvas id="barCategoria"></canvas>
                    </div>
                </div>
            </div>
        </div>


        {{-- ================= ROW 2: PROYECTOS ================= --}}
        {{-- Donut: Módulos por Proyecto --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-pie-chart text-info me-2"></i>Módulos por Proyecto (Pastel)
                    </h5>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 rounded-pill fw-bold">Pastel</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 340px;">
                    <div class="position-relative" style="width: 100%; max-width: 260px; aspect-ratio: 1 / 1;">
                        <canvas id="donutProyecto"></canvas>
                        <div class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; width: 140px;">
                            <div id="donutProyLabel" style="font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Cargando...</div>
                            <div id="donutProyValue" style="font-size: 24px; font-weight: bold; color: #111; margin-top: 2px;">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barras: Módulos por Proyecto --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-info me-2"></i>Módulos por Proyecto (Barras)
                    </h5>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 340px;">
                    <div style="width: 100%; height: 260px; position: relative;">
                        <canvas id="barProyecto"></canvas>
                    </div>
                </div>
            </div>
        </div>


        {{-- ================= ROW 3: PERFILES ================= --}}
        {{-- Donut: Módulos por Perfil --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-pie-chart text-success me-2"></i>Módulos por Perfil (Pastel)
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 rounded-pill fw-bold">Pastel</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 340px;">
                    <div class="position-relative" style="width: 100%; max-width: 260px; aspect-ratio: 1 / 1;">
                        <canvas id="donutPerfil"></canvas>
                        <div class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; width: 140px;">
                            <div id="donutPerfLabel" style="font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Cargando...</div>
                            <div id="donutPerfValue" style="font-size: 24px; font-weight: bold; color: #111; margin-top: 2px;">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barras: Módulos por Perfil --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-bar-chart text-success me-2"></i>Módulos por Perfil (Barras)
                    </h5>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 rounded-pill fw-bold">Barras</span>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 340px;">
                    <div style="width: 100%; height: 260px; position: relative;">
                        <canvas id="barPerfil"></canvas>
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
        const colors = [
            "#3498db", "#2ecc71", "#e74c3c", "#f1c40f", "#9b59b6",
            "#1abc9c", "#e67e22", "#34495e", "#16a085", "#27ae60",
            "#2980b9", "#8e44ad", "#d35400", "#c0392b", "#7f8c8d"
        ];

        // ── A. DATOS DE CATEGORÍAS ───────────────────────────────────────────
        const catLabels = {!! json_encode($dataCategoria->pluck('categoria')) !!};
        const catData   = {!! json_encode($dataCategoria->pluck('contador')) !!};

        const donutCatLabel = document.getElementById('donutCatLabel');
        const donutCatValue = document.getElementById('donutCatValue');
        if (catLabels.length > 0) {
            donutCatLabel.textContent = catLabels[0];
            donutCatValue.textContent = catData[0];
        }

        // 1. Donut Categorías
        new Chart(document.getElementById('donutCategoria').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{ data: catData, backgroundColor: colors.slice(0, catLabels.length), borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                onHover: function(event, els) {
                    if (els && els.length > 0) {
                        donutCatLabel.textContent = catLabels[els[0].index];
                        donutCatValue.textContent = catData[els[0].index];
                    }
                }
            }
        });

        // 2. Barras Categorías
        new Chart(document.getElementById('barCategoria').getContext('2d'), {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Módulos',
                    data: catData,
                    backgroundColor: '#e67e22',
                    borderRadius: 4,
                    maxBarThickness: 35
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                }
            }
        });


        // ── B. DATOS DE PROYECTOS ────────────────────────────────────────────
        const proyLabels = {!! json_encode($dataProyectos->pluck('proyecto')) !!};
        const proyData   = {!! json_encode($dataProyectos->pluck('contador')) !!};

        const donutProyLabel = document.getElementById('donutProyLabel');
        const donutProyValue = document.getElementById('donutProyValue');
        if (proyLabels.length > 0) {
            donutProyLabel.textContent = proyLabels[0];
            donutProyValue.textContent = proyData[0];
        }

        // 3. Donut Proyectos
        new Chart(document.getElementById('donutProyecto').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: proyLabels,
                datasets: [{ data: proyData, backgroundColor: colors.slice(2, 2 + proyLabels.length), borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                onHover: function(event, els) {
                    if (els && els.length > 0) {
                        donutProyLabel.textContent = proyLabels[els[0].index];
                        donutProyValue.textContent = proyData[els[0].index];
                    }
                }
            }
        });

        // 4. Barras Proyectos
        new Chart(document.getElementById('barProyecto').getContext('2d'), {
            type: 'bar',
            data: {
                labels: proyLabels,
                datasets: [{
                    label: 'Módulos',
                    data: proyData,
                    backgroundColor: '#3498db',
                    borderRadius: 4,
                    maxBarThickness: 35
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                }
            }
        });


        // ── C. DATOS DE PERFILES ─────────────────────────────────────────────
        const perfLabels = {!! json_encode($dataPerfiles->pluck('perfil')) !!};
        const perfData   = {!! json_encode($dataPerfiles->pluck('contador')) !!};

        const donutPerfLabel = document.getElementById('donutPerfLabel');
        const donutPerfValue = document.getElementById('donutPerfValue');
        if (perfLabels.length > 0) {
            donutPerfLabel.textContent = perfLabels[0];
            donutPerfValue.textContent = perfData[0];
        }

        // 5. Donut Perfiles
        new Chart(document.getElementById('donutPerfil').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: perfLabels,
                datasets: [{ data: perfData, backgroundColor: colors.slice(4, 4 + perfLabels.length), borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                onHover: function(event, els) {
                    if (els && els.length > 0) {
                        donutPerfLabel.textContent = perfLabels[els[0].index];
                        donutPerfValue.textContent = perfData[els[0].index];
                    }
                }
            }
        });

        // 6. Barras Perfiles
        new Chart(document.getElementById('barPerfil').getContext('2d'), {
            type: 'bar',
            data: {
                labels: perfLabels,
                datasets: [{
                    label: 'Módulos',
                    data: perfData,
                    backgroundColor: '#2ecc71',
                    borderRadius: 4,
                    maxBarThickness: 35
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                }
            }
        });

    });
</script>
@endpush
@endsection
