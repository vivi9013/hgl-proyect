@extends('layouts.app')

@section('title', 'Reportes - Estudios RX')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('rx.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa fa-arrow-left me-1"></i>Regresar
            </a>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-file-pdf-o text-primary me-2"></i>Reportes de Estudios RX
            </h1>
            <p class="text-muted mb-0">Impresión de formatos y listados de estudios realizados en el sistema de radiología</p>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    <div class="row">
        {{-- Reporte: Filtrado por Rango de Fechas --}}
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="border: 1px solid #e5e7eb !important;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-calendar text-secondary me-2"></i>Reporte por Rango de Fechas
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    <p class="text-muted small mb-4">
                        Selecciona un rango de fechas para generar un reporte impreso oficial de todos los estudios de radiología realizados. Dejar vacío para ver todos los estudios.
                    </p>
                    <form action="{{ route('rx.imprimir') }}" method="GET" target="_blank">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="fecha_inicio" class="form-label small fw-bold mb-1 text-dark">Fecha de Inicio</label>
                                <input type="date" id="fecha_inicio" name="fi" class="form-control bg-light py-2">
                            </div>
                            <div class="col-sm-6">
                                <label for="fecha_fin" class="form-label small fw-bold mb-1 text-dark">Fecha Fin</label>
                                <input type="date" id="fecha_fin" name="ff" class="form-control bg-light py-2">
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" style="font-weight: 600;">
                                <i class="fa fa-print me-1"></i>Imprimir Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
