@extends('layouts.app')

@section('title', 'Categoría de Módulos - Hospital General')

@section('content')
{{-- Alertas de Sesión renderizadas por SweetAlert2 desde categoria.js --}}
@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display: none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif
<div class="container-fluid py-4" id="modulo-categoria-modulos">
    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-tags text-primary me-2"></i>Categoría de Módulos
            </h1>
            <p class="text-muted mb-0">Catálogo general de clasificación y orden de módulos del sistema</p>
        </div> 
    </div>

    {{-- Área Principal: Tabla de Registros --}}
    <div class="row g-4"
         data-tabla-interactiva
         data-endpoint="{{ route('categoria_modulos.index') }}"
         data-tbody-target="cuerpoTablaCategorias"
         data-info-target="infoPaginacionCategorias"
         data-paginacion-target="paginacionCategorias"
         data-btn-imprimir="#btnImprimirCategorias">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Lista de Categorías
                        </h5>
                    </div>

                    {{-- ── Panel de filtros ────────────────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">
                        <x-filtro-buscar id="filtro-buscar" label="Buscar categoría" placeholder="Categoría o proyecto..." clase="col-12 col-md-4" />
                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registrar, Reportes, Gráficas) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalAltaCategoria">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Nueva Categoría
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('categoria_modulos.graficas') }}"
                               class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-bar-chart me-1"></i>Gráficas
                            </a>
                            <a id="btnImprimirCategorias"
                               href="{{ route('categoria_modulos.imprimir') }}"
                               target="_blank" rel="noopener"
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
                                <th class="text-center" style="width: 100px;">Acciones</th>
                                <th>Categoría</th>
                                <th>Proyecto</th>
                                <th class="text-center" style="width: 120px;">Panel Abierto</th>
                                <th class="text-center" style="width: 100px;">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaCategorias">
                            @include('admin_sistema.categoria_modulos.partials.tabla')
                        </tbody>
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionCategorias">
                        Mostrando {{ $categorias->firstItem() ?? 0 }} a {{ $categorias->lastItem() ?? 0 }} de {{ $categorias->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de categorias">
                        <div id="paginacionCategorias">
                            {{ $categorias->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal: Registrar Nueva Categoría --}}
    <div class="modal fade" id="modalAltaCategoria" tabindex="-1" aria-labelledby="modalAltaCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalAltaCategoriaLabel">
                        <i class="fa fa-edit text-dark me-2"></i>Registrar Nueva Categoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0);"></button>
                </div>
                
                {{-- Formulario procesado vía API/AJAX en JS para evitar romper flujos --}}
                <form id="formAltaCategoria" action="{{ route('categoria_modulos.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        
                        <div class="mb-3">
                            <label for="categoria" class="form-label fw-bold text-secondary">
                                <i class="fa fa-tag me-1 text-dark"></i> Nombre de la Categoría:
                            </label>
                            <input type="text" name="categoria" id="categoria" 
                                   class="form-control border-gray-300 shadow-sm @error('categoria') is-invalid @enderror" 
                                   value="{{ old('categoria') }}"
                                   placeholder="Coloque el nombre de la categoría del módulo" 
                                   required>
                            <div id="feedbackDisponibilidad" class="mt-1 small fw-semibold"></div>
                            <div id="loadingSpinner" class="spinner-border spinner-border-sm text-primary mt-1" role="status" style="display: none;">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            @error('categoria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="proyecto" class="form-label fw-bold text-secondary">
                                <i class="fa fa-laptop me-1 text-dark"></i> Proyecto Relacionado:
                            </label>
                            <input type="text" name="proyecto" id="proyecto" 
                                   class="form-control border-gray-300 shadow-sm @error('proyecto') is-invalid @enderror" 
                                   value="{{ old('proyecto') }}"
                                   placeholder="Coloque el nombre del proyecto" 
                                   required>
                            @error('proyecto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="colapsado" class="form-label fw-bold text-secondary">
                                <i class="fa fa-folder-open-o me-1 text-dark"></i> ¿Panel Abierto Inicialmente?
                            </label>
                            <select name="colapsado" id="colapsado" class="form-select border-gray-300 shadow-sm" required>
                                <option value="no" selected>Sí (Se muestra expandido)</option>
                                <option value="si">No (Se muestra cerrado)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="orden" class="form-label fw-bold text-secondary">
                                <i class="fa fa-sort me-1 text-dark"></i> Orden / Posición:
                            </label>
                            <input type="number" name="orden" id="orden" 
                                   class="form-control border-gray-300 shadow-sm @error('orden') is-invalid @enderror" 
                                   value="{{ old('orden', $siguienteOrden) }}" 
                                   min="1" required>
                            @error('orden')
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

    {{-- Modal: Editar Categoría --}}
    <div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditarCategoriaLabel">
                        <i class="fa fa-pencil-square-o text-primary me-2"></i>Actualización de Categoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0);"></button>
                </div>
                
                <form id="formEditarCategoria" action="" method="POST" autocomplete="off">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body px-4 py-4">
                        
                        <div class="mb-3">
                            <label for="edit_categoria" class="form-label fw-bold text-secondary">
                                <i class="fa fa-tag me-1 text-dark"></i> Nombre de la Categoría:
                            </label>
                            <input type="text" name="categoria" id="edit_categoria" 
                                   class="form-control border-gray-300 shadow-sm" 
                                   placeholder="Coloque el nombre de la categoría del módulo" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_proyecto" class="form-label fw-bold text-secondary">
                                <i class="fa fa-laptop me-1 text-dark"></i> Proyecto Relacionado:
                            </label>
                            <input type="text" name="proyecto" id="edit_proyecto" 
                                   class="form-control border-gray-300 shadow-sm" 
                                   placeholder="Coloque el nombre del proyecto" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_colapsado" class="form-label fw-bold text-secondary">
                                <i class="fa fa-folder-open-o me-1 text-dark"></i> ¿Panel Abierto Inicialmente?
                            </label>
                            <select name="colapsado" id="edit_colapsado" class="form-select border-gray-300 shadow-sm" required>
                                <option value="no">Sí (Se muestra expandido)</option>
                                <option value="si">No (Se muestra cerrado)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_orden" class="form-label fw-bold text-secondary">
                                <i class="fa fa-sort me-1 text-dark"></i> Orden / Posición:
                            </label>
                            <input type="number" name="orden" id="edit_orden" 
                                   class="form-control border-gray-300 shadow-sm" 
                                   min="1" required>
                        </div>

                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnActualizarCategoria" class="btn btn-primary py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Actualizar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Inyección de activos encapsulada por Vite --}}
@vite(['resources/css/categoria_modulos/categoria.css', 'resources/js/categoria_modulos/categoria.js'])
@endsection