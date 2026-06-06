@extends('layouts.app')

@section('title', 'Gráficas de Categorías - Hospital General')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado de Navegación y Título Corporativo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inicio') }}" class="text-decoration-none">Panel de Control</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('categoria_modulos.index') }}" class="text-decoration-none">Categoría de Módulos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gráficas</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Módulo Estadístico
            </h1>
            <p class="text-muted mb-0">Visualización analítica de módulos distribuidos por categorías</p>
        </div> 
    </div>

    {{-- Opciones de Gráficas Disponibles --}}
    <div class="row g-4">
        
        {{-- Gráfica de Pastel --}}
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-circle-o-notch text-secondary me-2"></i>Distribución Porcentual (Pastel)
                        </h5>
                        <span class="text-info">
                            <i class="fa fa-pie-chart fa-2x"></i>
                        </span>
                    </div>
                    
                    <div class="card-body px-4 py-3">
                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                            Permite visualizar de forma interactiva una gráfica de pastel orientada a mostrar la proporción e impacto de cada categoría basándose en la cantidad total de submódulos que tiene asignados.
                        </p>
                    </div>
                </div>

                <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end border-top">
                    <a href="{{ route('categoria_modulos.graficas.pie') }}" class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
                        <i class="fa fa-eye me-2"></i>Ver Gráfica de Pastel
                    </a>
                </div>
            </div>
        </div>

        {{-- Gráfica de Barras --}}
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-bar-chart text-secondary me-2"></i>Comparativa de Volúmenes (Barras)
                        </h5>
                        <span class="text-success">
                            <i class="fa fa-bar-chart-o fa-2x"></i>
                        </span>
                    </div>
                    
                    <div class="card-body px-4 py-3">
                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                            Permite evaluar mediante una gráfica de barras las métricas comparativas directas entre categorías. Ideal para auditar rápidamente qué áreas del sistema concentran la mayor densidad operativa.
                        </p>
                    </div>
                </div>

                <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end border-top">
                    <a href="{{ route('categoria_modulos.graficas.bar') }}" class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
                        <i class="fa fa-eye me-2"></i>Ver Gráfica de Barras
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Inyección limpia utilizando Vite, desvinculada de dependencias legacy muertas --}}
@vite(['resources/css/categoria_modulos/categoria_graficas.css', 'resources/js/categoria_modulos/categoria_graficas.js'])
@endsection