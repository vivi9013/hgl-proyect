@extends('layouts.app')

@section('title', 'Métrica de Pastel - Hospital General')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado de Navegación y Título --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inicio') }}" class="text-decoration-none">Panel de Control</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('categoria_modulos.graficas') }}" class="text-decoration-none">Gráficas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pastel</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pie-chart text-primary me-2"></i>Distribución Porcentual
            </h1>
            <p class="text-muted mb-0">Proporción del volumen de módulos contenidos en cada categoría del sistema</p>
        </div> 
    </div>

    {{-- Contenedor del Gráfico Circular Plano --}}
    <div class="row g-4 justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-adjust text-secondary me-2"></i>Impacto por Categorías
                    </h5>
                    <a href="{{ route('categoria_modulos.graficas') }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <div class="card-body p-4 d-flex justify-content-center">
                    {{-- Limitamos el alto máximo para que la gráfica de pastel no se desborde en pantallas grandes --}}
                    <div class="position-relative w-100 style-chart-container" style="height: 380px; max-width: 500px;">
                        <canvas id="canvasGraficaPie"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- Inyección segura de datos del backend a las variables globales de JavaScript --}}
<script>
    window.chartLabels = @json($dataGrafica->pluck('categoria'));
    window.chartValues = @json($dataGrafica->pluck('contador'));
</script>

{{-- Inyección de activos Vite específicos para esta visualización --}}
@vite(['resources/css/categoria_modulos/categoria_graficas.css', 'resources/js/categoria_modulos/grafica_pie.js'])
@endsection