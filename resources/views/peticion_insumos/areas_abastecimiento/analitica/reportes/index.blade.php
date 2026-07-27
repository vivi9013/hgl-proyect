@extends('layouts.app')

@section('title', 'Reportes - Áreas de Abastecimiento')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold text-dark">
                <i class="bi bi-file-earmark-pdf me-2 text-primary"></i> Reportes de Áreas de Abastecimiento
            </h2>
            <small class="text-muted">Generación e impresión del catálogo oficial de áreas principales de abastecimiento.</small>
        </div>
        <a href="{{ route('areas_abastecimiento.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
        </a>
    </div>

    <div class="row g-4">
        <!-- Tarjeta 1: Áreas de Abastecimiento -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100 reporte-card">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold text-dark mb-0">Lista completa de áreas de abastecimiento</h5>
                            <i class="bi bi-file-earmark-text fs-3 text-secondary"></i>
                        </div>
                        <p class="text-muted small mb-4">
                            Imprime una lista completa de las áreas de abastecimiento ingresadas en el sistema, ordenadas por área ascendente.
                        </p>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('areas_abastecimiento.imprimir') }}" target="_blank" class="btn btn-outline-dark btn-sm fw-bold px-3">
                            <i class="bi bi-printer me-1"></i> Imprimir Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Subáreas de Abastecimiento -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100 reporte-card">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold text-dark mb-0">Lista completa de subáreas</h5>
                            <i class="bi bi-file-earmark-text fs-3 text-secondary"></i>
                        </div>
                        <p class="text-muted small mb-4">
                            Imprime una lista completa de las subáreas ingresadas en el sistema, ordenadas por subárea ascendente.
                        </p>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('subareas_abastecimiento.imprimir') }}" target="_blank" class="btn btn-outline-dark btn-sm fw-bold px-3">
                            <i class="bi bi-printer me-1"></i> Imprimir Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Lista de Relaciones -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100 reporte-card">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold text-dark mb-0">Lista completa de relaciones</h5>
                            <i class="bi bi-file-earmark-text fs-3 text-secondary"></i>
                        </div>
                        <p class="text-muted small mb-4">
                            Imprime una lista completa de las relaciones entre áreas y subáreas ingresadas en el sistema.
                        </p>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('almacen_subareas.imprimir') }}" target="_blank" class="btn btn-outline-dark btn-sm fw-bold px-3">
                            <i class="bi bi-printer me-1"></i> Imprimir Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
