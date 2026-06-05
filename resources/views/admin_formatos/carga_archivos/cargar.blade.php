@extends('layouts.app')

@section('title', 'Cargar PDF - Hospital General')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado del Módulo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-upload text-primary me-2"></i>Subir Documento Físico
            </h1>
            <p class="text-muted mb-0">Carga del archivo PDF correspondiente al registro: <strong>{{ $archivo->nombre }}</strong></p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
                <li class="breadcrumb-item"><a href="{{ route('carga_archivos.index') }}"><i class="fa fa-upload"></i> Administración de Archivos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Subir PDF</li>
            </ol>
        </nav>
    </div>

    <!-- Contenido Principal -->
    <div class="row">
        <div class="col-12 col-lg-6 mx-auto">
            <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-file-pdf-o text-danger me-2"></i>Selecciona el archivo PDF a subir
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Resumen del Registro -->
                    <div class="bg-light rounded-3 p-3 mb-4 border">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Categoría:</span>
                                <strong class="text-dark">{{ $archivo->categoria->categoria ?? 'Sin categoría' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Versión actual:</span>
                                <strong class="text-dark">v{{ $archivo->version_archivo }}</strong>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('carga_archivos.subir_archivo', $archivo->id_archivo) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Input de archivo -->
                        <div class="mb-4">
                            <label for="archivo-a-subir" class="form-label fw-bold text-secondary">
                                <i class="fa fa-folder-open text-primary me-1"></i> Selecciona archivo (.pdf):
                            </label>
                            <input class="form-control border-gray-300 shadow-sm" 
                                   type="file" 
                                   id="archivo-a-subir" 
                                   name="archivo-a-subir" 
                                   accept=".pdf" 
                                   required>
                            <div class="form-text text-muted mt-2">
                                <i class="fa fa-info-circle text-info me-1"></i> Solo se permiten archivos PDF. Tamaño máximo de 50MB.
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('carga_archivos.index') }}" class="btn btn-light px-4 py-2 rounded-pill border shadow-sm">
                                <i class="fa fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                                <i class="fa fa-cloud-upload me-2"></i>Subir Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/carga_archivos/carga.css'])
@endsection
