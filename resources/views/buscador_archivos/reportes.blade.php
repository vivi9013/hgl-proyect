@extends('layouts.app')

@section('title', 'Reportes - Buscador de Archivos')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Reportes del Módulo
            </h1>
            <p class="text-muted mb-0">Impresión y exportación de formatos y listas del sistema</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
                <li class="breadcrumb-item"><a href="{{ route('busca_archivos.index') }}">Buscador</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reportes</li>
            </ol>
        </nav>
    </div>

    <!-- Opciones de Reportes -->
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm h-100 overflow-hidden rounded-3 bg-white">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-danger me-3">
                                <i class="fa fa-print fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">Lista Completa de Archivos</h5>
                                <span class="badge bg-light text-secondary border">Formato PDF / Impresión</span>
                            </div>
                        </div>
                        <p class="text-secondary mb-0">Genera una lista completa con todos los archivos y formatos ingresados en el sistema a los que tu perfil de usuario tiene autorización para acceder.</p>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('busca_archivos.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary fw-medium">
                            <i class="fa fa-arrow-left me-1"></i> Volver al buscador
                        </a>
                        <a href="{{ route('busca_archivos.imprimir') }}" target="_blank" class="btn btn-danger-gradient px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-1.5 fw-medium">
                            <i class="fa fa-print"></i> Imprimir Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@vite(['resources/css/buscador_archivos/buscador.css'])
@endsection
