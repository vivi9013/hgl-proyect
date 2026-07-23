@extends('layouts.app')

@section('title', 'Estadísticas y Reportes RX - Hospital General')

@push('styles')
    @vite(['resources/css/pacientes/estadisticas/estadisticas.css'])
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado Principal --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="h2 text-dark fw-bold mb-1">
                <i class="fa fa-bar-chart text-primary me-2"></i>Estadísticas y Reportes RX
            </h1>
            <p class="text-muted mb-0">Consolidado analítico de placas, regiones anatómicas y reportes diarios oficiales.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            {{-- Enlace de Regreso a Estudios --}}
            <a href="{{ route('rx.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                <i class="fa fa-arrow-left me-1"></i>Regresar a Estudios
            </a>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- Panel de Filtros y Control --}}
    <div class="card filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-end g-3">
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="form-group mb-0">
                        <label for="fechai" class="form-label small fw-bold text-muted mb-1.5">Fecha Inicial</label>
                        <input class="form-control py-2 fw-semibold" type="date" id="fechai" name="fi" value="{{ $hoy }}">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="form-group mb-0">
                        <label for="fechaf" class="form-label small fw-bold text-muted mb-1.5">Fecha Final</label>
                        <input class="form-control py-2 fw-semibold" type="date" id="fechaf" name="ff" value="{{ $hoy }}">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="form-group mb-0">
                        <label for="totale" class="form-label small fw-bold text-muted mb-1.5">Total de Estudios</label>
                        <input class="form-control py-2 fw-bold text-center bg-light font-monospace" type="text" id="totale" readonly value="0">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-3">
                    <button type="button" id="reporte" class="btn btn-print-report w-100 py-2 shadow-sm" disabled>
                        <i class="fa fa-print me-1.5"></i>Imprimir Reporte Diario
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs de Gráficos --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="border: 1px solid #e5e7eb !important;">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-2.5 d-flex align-items-center" id="regiones-tab" data-bs-toggle="tab" data-bs-target="#tab-regiones" type="button" role="tab" aria-controls="tab-regiones" aria-selected="true">
                        <i class="fa fa-bar-chart me-2"></i>Estudios
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2.5 d-flex align-items-center ms-2" id="tecnicos-tab" data-bs-toggle="tab" data-bs-target="#tab-tecnicos" type="button" role="tab" aria-controls="tab-tecnicos" aria-selected="false">
                        <i class="fa fa-user-circle me-2"></i>Técnicos RX
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2.5 d-flex align-items-center ms-2" id="genero-tab" data-bs-toggle="tab" data-bs-target="#tab-genero" type="button" role="tab" aria-controls="tab-genero" aria-selected="false">
                        <i class="fa fa-venus-mars me-2"></i>Por Genero
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="myTabContent">
                
                {{-- Tab 1: Total de Estudios RX (barras horizontales) --}}
                <div class="tab-pane fade show active" id="tab-regiones" role="tabpanel" aria-labelledby="regiones-tab">
                    <h5 class="fw-bold mb-1 text-dark">Total de estudios de RX</h5>
                    <p class="text-muted small mb-3">Hospital General de Linares</p>
                    <div style="position: relative; height: 380px; width: 100%;">
                        <canvas id="chartRegiones"></canvas>
                    </div>
                </div>

                {{-- Tab 2: Técnicos RX --}}
                <div class="tab-pane fade" id="tab-tecnicos" role="tabpanel" aria-labelledby="tecnicos-tab">
                    <h5 class="fw-bold mb-1 text-dark">Pacientes atendidos por Técnico RX</h5>
                    <p class="text-muted small mb-3">Hospital General de Linares</p>
                    <div style="position: relative; height: 480px; width: 100%;">
                        <canvas id="chartTecnicos"></canvas>
                    </div>
                </div>

                {{-- Tab 3: Género del Paciente --}}
                <div class="tab-pane fade" id="tab-genero" role="tabpanel" aria-labelledby="genero-tab">
                    <h5 class="fw-bold mb-1 text-dark">Grafica clasificada por genero del Paciente</h5>
                    <p class="text-muted small mb-3">Hospital General de Linares</p>
                    <div style="position: relative; height: 480px; width: 100%;">
                        <canvas id="chartGeneros"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/js/pacientes/estadisticas/estadisticas.js'])
@endpush
