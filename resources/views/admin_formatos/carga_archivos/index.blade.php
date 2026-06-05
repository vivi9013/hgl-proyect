@extends('layouts.app')

@section('title', 'Administración de Archivos - Hospital General')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-upload text-primary me-2"></i>Administración de Archivos
            </h1>
            <p class="text-muted mb-0">Carga y gestión de formatos y documentos institucionales</p>
        </div> 
    </div>

    {{--Informacion de modulo y Submódulos ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light p-3 rounded-circle text-primary">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Panel de Control de Repositorio</h6>
                        <p class="text-muted small mb-0">Modulo para dar de alta nuevos documentos y subir los archivos en PDF. Se pueden subir/actualizar versiones de un archivo/formato</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Submódulo 1: Catálogo de Categorías --}}
                    
                    {{-- Submódulo 2: Reportes del Módulo --}}
                    <a href="{{ route('carga_archivos.reportes') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes
                    </a>

                    {{-- Submódulo 3: Gráficas del Módulo --}}
                    <a href="{{ route('carga_archivos.graficas') }}" class="btn btn-outline-success px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-bar-chart me-2 text-success"></i> Gráficas
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle me-2 fs-4"></i>
                <div>
                    <strong>¡Operación Satisfactoria!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-triangle me-2 fs-4"></i>
                <div>
                    <strong>¡Atención!</strong> Por favor corrige los siguientes errores:
                    <ul class="mb-0 mt-1 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-edit text-secondary me-2"></i>Registrar nuevo archivo
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <form id="formCargaArchivo" action="{{ route('carga_archivos.store') }}" method="POST" autocomplete="off">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="tipo" class="form-label fw-bold text-secondary">
                                <i class="fa fa-folder-open-o me-1 text-primary"></i> Categoría:
                            </label>
                            <select name="tipo" id="tipo" class="form-select border-gray-300 shadow-sm" required>
                                <option value="" disabled selected>Seleccione una categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id_catego_archivos }}" {{ old('tipo') == $categoria->id_catego_archivos ? 'selected' : '' }}>
                                        {{ $categoria->categoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold text-secondary d-flex justify-content-between align-items-center">
                                <span><i class="fa fa-file-text-o me-1 text-primary"></i> Nombre del archivo:</span>
                                <span id="feedbackDisponibilidad" class="small fw-normal"></span>
                            </label>
                            <div class="input-group">
                                <input type="text" name="nombre" id="nombre" 
                                       class="form-control border-gray-300 shadow-sm" 
                                       placeholder="Coloca el nombre del archivo" 
                                       value="{{ old('nombre') }}" 
                                       required>
                                <span class="input-group-text bg-white border-gray-300 text-muted" id="loadingSpinner" style="display: none;">
                                    <i class="fa fa-spinner fa-spin"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="version" class="form-label fw-bold text-secondary">
                                <i class="fa fa-code-fork me-1 text-primary"></i> Versión:
                            </label>
                            <input type="number" name="version" id="version" 
                                   class="form-control border-gray-300 shadow-sm" 
                                   placeholder="Ej. 1" 
                                   min="1" 
                                   value="{{ old('version', 1) }}" 
                                   required>
                        </div>

                        <div class="mb-4">
                            <label for="desc" class="form-label fw-bold text-secondary">
                                <i class="fa fa-info-circle me-1 text-primary"></i> Descripción:
                            </label>
                            <textarea name="desc" id="desc" rows="3" 
                                      class="form-control border-gray-300 shadow-sm" 
                                      placeholder="Ingrese una descripción del archivo" 
                                      required>{{ old('desc') }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" id="btnGuardar" class="btn btn-primary py-2 rounded-pill shadow-sm">
                                <i class="fa fa-save me-2"></i>Guardar Información
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Archivos
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalArchivos">
                                {{ $archivos->total() }} Registros
                            </span>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            
                            <select id="filtroCategoria" class="form-select border-gray-300 shadow-sm text-muted" style="min-width: 180px; font-size: 0.85rem;">
                                <option value="Todos">Todas las categorías</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->categoria }}">{{ $categoria->categoria }}</option>
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
                
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1 sticky-top bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">#</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th class="text-center" style="width: 80px;">Ver.</th>
                                    <th class="text-center" style="width: 90px;">Físico</th>
                                    <th class="text-center" style="width: 100px;">Estado</th>
                                    <th class="text-center pe-4" style="width: 100px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyArchivos">
                                {{-- Carga inicial del servidor --}}
                                @include('admin_formatos.carga_archivos.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $archivos->firstItem() ?? 0 }} a {{ $archivos->lastItem() ?? 0 }} de {{ $archivos->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de archivos cargados">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Los botones se mantendrán sincronizados asíncronamente por JS --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>
</div>

@vite(['resources/css/carga_archivos/carga.css', 'resources/js/carga_archivos/carga.js'])
@endsection