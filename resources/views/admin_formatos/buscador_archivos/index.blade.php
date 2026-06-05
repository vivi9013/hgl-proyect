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
        <!-- Contenedor de Submódulos (Alineado a la derecha del encabezado general) -->
        <div>
            <a href="{{ route('busca_archivos.reportes') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-sm">
                <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes
            </a>
        </div>
    </div>
    
    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    <!-- Contenedor del Listado Único Reestructurado -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        
   {{-- Cabecera de la Tarjeta: Título, Filtro por Categoría y Buscador Unificados --}}
        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                
                <div class="d-flex align-items-center gap-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Archivos
                    </h5>
                    <span class="badge bg-primary-gradient rounded-pill px-4 py-2 shadow-sm fw-bold align-middle" id="totalArchivos">
                        {{ $archivos->total() }} {{ $archivos->total() === 1 ? 'Registro' : 'Registros' }}
                    </span>
                </div>
                
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                    
                    <select id="filtroCategoria" class="form-select border-gray-300 shadow-sm text-muted" style="min-width: 180px; font-size: 0.85rem;">
                        <option value="Todos">Todas las categorías</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->categoria }}">{{ $cat->categoria }}</option>
                        @endforeach
                    </select>

                    <div class="input-group" style="min-width: 240px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                        <input type="search" id="global-search" class="form-control bg-light border-0" placeholder="Buscar archivo..." style="font-size: 0.85rem; box-shadow: none;">
                        <span class="input-group-text bg-light border-0 py-0">
                            <i class="fa fa-search text-dark"></i>
                        </span>
                    </div>

                </div>

            </div>
        </div>
        
        <!-- Cuerpo de la Tabla -->
        <div class="card-body p-0 mt-2">
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
                        @include('admin_formatos.buscador_archivos.partials.tabla')
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
            <div class="text-muted small" id="infoPaginacion">
                Mostrando {{ $archivos->firstItem() ?? 0 }} a {{ $archivos->lastItem() ?? 0 }} de {{ $archivos->total() }} registros
            </div>
            <nav aria-label="Paginacion de archivos">
                <ul class="pagination mb-0" id="contenedorPaginacion">
                    @if ($archivos->count() > 0)
                        {!! $archivos->appends(request()->except('page'))->links('pagination::bootstrap-4') !!}
                    @endif
                </ul>
            </nav>
        </div>
    </div> 
</div> 

@vite(['resources/css/buscador_archivos/buscador.css', 'resources/js/buscador_archivos/buscador.js'])
@endsection