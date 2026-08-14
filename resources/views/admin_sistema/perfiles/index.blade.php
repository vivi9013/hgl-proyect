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

<div class="container-fluid py-4" id="modulo-perfiles">
    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-users text-primary me-2"></i>Catálogo de Perfiles
            </h1>
            <p class="text-muted mb-0">Gestione los roles de acceso del personal a los distintos módulos del sistema</p>
        </div> 
    </div>

    {{-- Área Principal: Tabla de Registros --}}
    <div class="row g-4"
         data-tabla-interactiva
         data-endpoint="{{ route('perfiles.index') }}"
         data-tbody-target="cuerpoTablaPerfiles"
         data-info-target="infoPaginacionPerfiles"
         data-paginacion-target="paginacionPerfiles"
         data-btn-imprimir="#btnImprimirPerfiles">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Lista de Perfiles
                        </h5>
                    </div>

                    {{-- ── Panel de filtros ────────────────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">
                        <x-filtro-buscar id="filtro-buscar" label="Buscar perfil" placeholder="Nombre o descripción..." clase="col-12 col-md-4" />
                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registrar, Reportes, Gráficas) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalAltaPerfil">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Nuevo Perfil
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('perfiles.graficas') }}"
                               class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-bar-chart me-1"></i>Gráficas
                            </a>
                            <a id="btnImprimirPerfiles"
                               href="{{ route('perfiles.imprimir') }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Reportes
                            </a>
                        </div>
                    </div>
                </div>
                
                {{-- Contenedor de la Tabla Asíncrona --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
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
                        <tbody id="cuerpoTablaPerfiles">
                            @include('admin_sistema.perfiles.partials.tabla')
                        </tbody>
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionPerfiles">
                        Mostrando {{ $perfiles->firstItem() ?? 0 }} a {{ $perfiles->lastItem() ?? 0 }} de {{ $perfiles->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de perfiles">
                        <div id="paginacionPerfiles">
                            {{ $perfiles->links('pagination::bootstrap-4') }}
                        </div>
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