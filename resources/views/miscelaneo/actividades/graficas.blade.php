@extends('layouts.app')

@section('title', 'Gráficas de Actividades - Hospital General')

@push('styles')
    @vite(['resources/css/miscelaneo/actividades/actividades.css'])
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado Principal --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="h2 text-dark fw-bold mb-1">
                <i class="fa fa-line-chart text-primary me-2"></i>Gráficas de Registro de Actividades
            </h1>
            <p class="text-muted mb-0">Visualizaciones analíticas de los inicios de sesión por persona.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('actividades.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                <i class="fa fa-arrow-left me-1"></i>Regresar a Listado
            </a>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- Panel de Selección de Rango --}}
    <div class="card filter-card border-0 mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-end g-3 justify-content-center">
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="form-group mb-0">
                        <label for="graficas-fecha-inicio" class="form-label small fw-bold text-muted mb-1.5">Fecha Inicial</label>
                        <input class="form-control py-2 fw-semibold" type="date" id="graficas-fecha-inicio" value="{{ $hoy }}">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="form-group mb-0">
                        <label for="graficas-fecha-fin" class="form-label small fw-bold text-muted mb-1.5">Fecha Final</label>
                        <input class="form-control py-2 fw-semibold" type="date" id="graficas-fecha-fin" value="{{ $hoy }}">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-3">
                    <button type="button" id="btn-generar-graficas" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">
                        <i class="fa fa-refresh me-1"></i> Generar Gráficas
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs de Gráficos --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="border: 1px solid #e5e7eb !important;">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <ul class="nav nav-tabs card-header-tabs" id="graficasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-2.5 d-flex align-items-center" id="barras-tab" data-bs-toggle="tab" data-bs-target="#tab-barras" type="button" role="tab" aria-controls="tab-barras" aria-selected="true">
                        <i class="fa fa-bar-chart me-2"></i>Inicios de Sesión (Barras)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2.5 d-flex align-items-center ms-2" id="pastel-tab" data-bs-toggle="tab" data-bs-target="#tab-pastel" type="button" role="tab" aria-controls="tab-pastel" aria-selected="false">
                        <i class="fa fa-pie-chart me-2"></i>Inicios de Sesión (Pastel)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="graficasTabContent">
                
                {{-- Tab 1: Inicios de Sesión por persona (Barras) --}}
                <div class="tab-pane fade show active" id="tab-barras" role="tabpanel" aria-labelledby="barras-tab">
                    <h5 class="fw-bold mb-1 text-dark">Inicio de sesión por persona</h5>
                    <p class="text-muted small mb-3">Hospital General de Linares</p>
                    <div style="position: relative; height: 380px; width: 100%;">
                        <canvas id="chartBarras"></canvas>
                    </div>
                </div>

                {{-- Tab 2: Inicios de Sesión por persona (Pastel) --}}
                <div class="tab-pane fade" id="tab-pastel" role="tabpanel" aria-labelledby="pastel-tab">
                    <h5 class="fw-bold mb-1 text-dark">Inicio de sesión por persona</h5>
                    <p class="text-muted small mb-3">Hospital General de Linares</p>
                    <div style="position: relative; height: 480px; width: 100%;">
                        <canvas id="chartPie"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/js/miscelaneo/actividades/actividades.js'])
@endpush
