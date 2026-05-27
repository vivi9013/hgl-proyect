@extends('layouts.app')

@section('title', 'Buscador de Archivos - Hospital General')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado del Módulo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-search text-primary me-2"></i>Buscador de Archivos
            </h1>
            <p class="text-muted mb-0">Administración de formatos institucionales</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buscador</li>
            </ol>
        </nav>
    </div>

    <!-- Opciones y Menú de Filtro -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <label for="filtroCategoria" class="fw-bold mb-0 text-secondary">
                        <i class="fa fa-filter me-1 text-primary"></i> Selecciona una categoría:
                    </label>
                    <select id="filtroCategoria" class="form-select w-auto min-w-200 shadow-sm border-gray-300">
                        <option value="Todos">Todos</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->categoria }}">{{ $cat->categoria }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-md-end">
                    <a href="{{ route('busca_archivos.reportes') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-sm">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes del Módulo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor del Listado -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Archivos
            </h5>
            <span class="badge bg-primary-gradient rounded-pill px-4 py-2 shadow-sm fw-bold" id="totalArchivos">Cargando...</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaArchivos">
                    <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                        <tr>
                            <th class="ps-4" style="width: 80px;">#</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th class="text-center" style="width: 180px;">Versión</th>
                            <th class="text-center pe-4" style="width: 150px;">Descargar</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyArchivos">
                        <!-- El contenido se cargará dinámicamente vía AJAX -->
              </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
            <div class="text-muted small" id="infoPaginacion">
                Mostrando 0 a 0 de 0 registros
            </div>
            <nav aria-label="Paginacion de archivos">
                <ul class="pagination mb-0" id="contenedorPaginacion">
                    </ul>
            </nav>
        </div>
    </div> </div> @vite(['resources/css/buscador_archivos/buscador.css', 'resources/js/buscador_archivos/buscador.js'])
@endsection
