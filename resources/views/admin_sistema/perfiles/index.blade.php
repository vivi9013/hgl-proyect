@extends('layouts.app')

@section('title', 'Perfiles - Hospital General')

@section('content')
{{-- Alertas de Sesión renderizadas por SweetAlert2 desde perfiles.js --}}
@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display: none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif

<div class="container-fluid py-4">
    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-users text-primary me-2"></i>Catálogo de Perfiles
            </h1>
            <p class="text-muted mb-0">Gestione los roles de acceso del personal a los distintos módulos del sistema</p>
        </div> 
    </div>

    {{-- Panel Informativo y de Acciones Rápidas --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light p-3 rounded-circle text-primary">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Administración de Perfiles</h6>
                        <p class="text-muted small mb-0">Permite dar de alta nuevos roles de usuario en el sistema, modularizar sus permisos y asignar el acceso a los módulos correspondientes.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Botón Gatillo del Modal --}}
                    <button type="button" class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#modalAltaPerfil">
                        <i class="fa fa-plus-circle me-2"></i> Registrar Nuevo Perfil
                    </button>

                    {{-- Submódulo: Reportes del Módulo --}}
                    <a href="{{ route('perfiles.reportes') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes
                    </a>

                    {{-- Submódulo: Gráficas del Módulo --}}
                    <a href="{{ route('perfiles.graficas') }}" class="btn btn-outline-success px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-bar-chart me-2 text-success"></i> Gráficas
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Área Principal: Tabla de Registros --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Perfiles
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalPerfiles">
                                {{ $perfiles->total() }} Registros
                            </span>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            {{-- Buscador Reactivo --}}
                            <div class="input-group search-group">
                                <input type="search" id="global-search" class="form-control bg-light border-0" placeholder="Buscar perfil...">
                                <span class="input-group-text bg-light border-0 py-0">
                                    <i class="fa fa-search text-dark"></i>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
                
                {{-- Contenedor de la Tabla Asíncrona --}}
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1 sticky-top bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 60px;">#</th>
                                    <th class="text-center" style="width: 90px;">Editar</th>
                                    <th class="text-center" style="width: 150px;">Agregar módulos</th>
                                    <th class="text-center">Perfil</th>
                                    <th class="text-center">Descripción</th>
                                    <th class="text-center" style="width: 140px;">Total módulos</th>
                                    <th class="text-center" style="width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPerfiles">
                                @include('admin_sistema.perfiles.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Footer con Paginación Homologada --}}
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $perfiles->firstItem() ?? 0 }} a {{ $perfiles->lastItem() ?? 0 }} de {{ $perfiles->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de perfiles">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Sincronizado dinámicamente por JS --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal: Registrar Nuevo Perfil --}}
    <div class="modal fade" id="modalAltaPerfil" tabindex="-1" aria-labelledby="modalAltaPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3 modal-alta">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalAltaPerfilLabel">
                        <i class="fa fa-edit text-dark me-2"></i>Registrar Nuevo Perfil
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="formAltaPerfil" action="{{ route('perfiles.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold text-secondary">
                                <i class="fa fa-user me-1 text-dark"></i> Nombre del Perfil:
                            </label>
                            <input type="text" name="nombre" id="nombre" 
                                   class="form-control border-gray-300 shadow-sm @error('nombre') is-invalid @enderror" 
                                   value="{{ old('nombre') }}"
                                   placeholder="Coloque el nombre del perfil" 
                                   required>
                            <div id="feedbackDisponibilidad" class="mt-1 small fw-semibold"></div>
                            <div id="loadingSpinner" class="spinner-border spinner-border-sm text-primary mt-1" role="status" style="display: none;">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-bold text-secondary">
                                <i class="fa fa-comment-o me-1 text-dark"></i> Descripción detallada:
                            </label>
                            <textarea name="descripcion" id="descripcion" 
                                      class="form-control border-gray-300 shadow-sm @error('descripcion') is-invalid @enderror" 
                                      placeholder="Coloque la descripción detallada del perfil" 
                                      rows="3" required>{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/perfiles/perfiles.css', 'resources/js/perfiles/perfiles.js'])
@endsection
