@extends('layouts.app')

@section('title', 'Reportes - Especialidades RX')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('rx_especialidades.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa fa-arrow-left me-1"></i>Regresar
            </a>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-file-pdf-o text-primary me-2"></i>Reportes de Especialidades RX
            </h1>
            <p class="text-muted mb-0">Impresión de formatos y listados de especialidades del sistema de radiología</p>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    <div class="row">
        {{-- Reporte: Lista Completa --}}
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-list-alt text-secondary me-2"></i>Lista Completa de Especialidades
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    <p class="text-muted small">
                        Imprime una lista completa de las especialidades médicas ingresadas en el sistema, ordenadas alfabéticamente de forma ascendente.
                    </p>
                    <div class="mt-4 text-end">
                        <a href="{{ route('rx_especialidades.imprimir') }}" target="_blank" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" style="font-weight: 600;">
                            <i class="fa fa-print me-1"></i>Imprimir Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
