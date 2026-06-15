@extends('layouts.app')

@section('title', 'Gráficas de Módulos - Hospital General')

@section('content')
<div class="container-fluid py-4" id="modulo-gestion-modulos">

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
                        <canvas id="donaCategoria"></canvas>
                        <div class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; width: 140px;">
                            <div id="etiquetaDonaCategoria" style="font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Cargando...</div>
                            <div id="valorDonaCategoria" style="font-size: 24px; font-weight: bold; color: #111; margin-top: 2px;">0</div>
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
                        <canvas id="barraCategoria"></canvas>
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
                        <canvas id="donaProyecto"></canvas>
                        <div class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; width: 140px;">
                            <div id="etiquetaDonaProyecto" style="font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Cargando...</div>
                            <div id="valorDonaProyecto" style="font-size: 24px; font-weight: bold; color: #111; margin-top: 2px;">0</div>
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
                        <canvas id="barraProyecto"></canvas>
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
                        <canvas id="donaPerfil"></canvas>
                        <div class="position-absolute start-50 top-50 translate-middle text-center" style="pointer-events: none; width: 140px;">
                            <div id="etiquetaDonaPerfil" style="font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Cargando...</div>
                            <div id="valorDonaPerfil" style="font-size: 24px; font-weight: bold; color: #111; margin-top: 2px;">0</div>
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
                        <canvas id="barraPerfil"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Datos para las gráficas encapsulados en atributos data- para desacoplar el JS --}}
        <div id="datos-graficas" class="d-none"
             data-categorias="{{ json_encode($dataCategoria->pluck('contador', 'categoria')) }}"
             data-proyectos="{{ json_encode($dataProyectos->pluck('contador', 'proyecto')) }}"
             data-perfiles="{{ json_encode($dataPerfiles->pluck('contador', 'perfil')) }}">
        </div>

    </div>
</div>

@push('scripts')
@vite(['resources/css/modulos/modulos.css', 'resources/js/modulos/modulos.js'])
@endpush
@endsection
