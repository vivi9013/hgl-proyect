@extends('layouts.app')
 
@section('title', 'Categoría de Archivos')
 
@section('content')
<div class="container-fluid py-4">
 
    {{-- ── Encabezado + acceso a Reportes (patrón buscador_archivos) ──────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-folder-open text-primary me-2"></i>Categorías de Archivos
            </h1>
            <p class="text-muted mb-0">Registro, edición y consulta de las categorías de archivos </p>
        </div>
    </div>
 
       {{-- ── Informacion de modulo y Submódulos ── --}}
    <div class="row g-4 mb-4">
        <!-- Lógica o información del módulo -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light p-3 rounded-circle text-primary">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Panel de Control de Repositorio</h6>
                        <p class="text-muted small mb-0">Modulo para Registrar, editar y consultar las categorías de archivos </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenedor con los Submódulos -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Submódulo 1: Catálogo de Categorías --}}
                     <a href="{{ route('categoria_archivos.reportes') }}"
                       class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-sm">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes
                    </a>
                  
                </div>
            </div>
        </div>
    </div>
    
 
    {{-- ── Alertas SweetAlert2 ─────────────────────────────────────────────── --}}
    @if(session('exitog'))
        <div id="alertaExitog"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito"></div>
    @endif
 
    {{-- ── Formulario de Alta ──────────────────────────────────────────────── --}}
    <div class="row mb-4">
        <div class="col-xs-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-plus-circle me-2"></i>Registra una nueva categoría</h5>
                    <button class="btn btn-sm btn-outline-light" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseAlta">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseAlta">
                    <form method="POST" action="{{ route('categoria_archivos.store') }}" novalidate>
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label id="Info" for="categoria" class="form-label">
                                            Nombre de la categoría:
                                        </label>
                                        <input
                                            type="text"
                                            name="categoria"
                                            id="categoria"
                                            class="form-control @error('categoria') is-invalid @enderror"
                                            value="{{ old('categoria') }}"
                                            placeholder="Coloque el nombre de la categoría"
                                            autocomplete="off"
                                            maxlength="255"
                                            autofocus
                                            required
                                        >
                                        <div id="feedbackDisponibilidad" class="mt-1 small"></div>
                                        <div id="loadingSpinner" class="mt-1 small text-muted" style="display:none;">
                                            <i class="fa fa-spinner fa-spin me-1"></i>Verificando...
                                        </div>
                                        @error('categoria')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" id="btnGuardar" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i>Guardar Información
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
 
 {{-- ── Tabla de Categorías ─────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-list me-2"></i>Lista de categorías
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalCategorias">
                                {{ $categorias->total() }} Registros
                            </span>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            <div class="input-group" style="min-width: 240px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                                <input type="search" id="global-search" class="form-control bg-light border-0" placeholder="Buscar categoría..." style="font-size: 0.85rem; box-shadow: none;">
                                <span class="input-group-text bg-light border-0 py-0">
                                    <i class="fa fa-search text-dark"></i>
                                </span>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary ms-2" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseTabla">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="collapse show" id="collapseTabla">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tablaCategorias" class="table table-condensed table-bordered table-striped align-middle mb-0">
                                <thead>
                                    <tr class="table-info">
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 80px;" class="text-center">Editar</th>
                                        <th>Categoría</th>
                                        <th style="width: 150px;">Fecha</th>
                                        <th style="width: 150px;">Hora</th>
                                        <th style="width: 100px;" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyCategorias">
                                    {{-- Carga Server-Side inicial --}}
                                    @include('categoria_archivos.partials.tabla')
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                        <div class="text-muted small" id="infoPaginacion">
                            Mostrando {{ $categorias->firstItem() ?? 0 }} a {{ $categorias->lastItem() ?? 0 }} de {{ $categorias->total() }} registros
                        </div>
                        <nav aria-label="Paginacion de categorias">
                            <ul class="pagination mb-0" id="contenedorPaginacion">
                                {{-- Sincronizado por JavaScript de manera dinámica --}}
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>
 
</div>
@vite(['resources/css/categoria_archivos/categoria.css', 'resources/js/categoria_archivos/categoria.js'])
@endsection
