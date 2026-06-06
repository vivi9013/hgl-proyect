@extends('layouts.app')

@section('title', 'Categoría de Módulos - Hospital General')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-tags text-primary me-2"></i>Categoría de Módulos
            </h1>
            <p class="text-muted mb-0">Catálogo general de clasificación y orden de módulos del sistema</p>
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
                        <h6 class="fw-bold mb-1 text-dark">Administración del Catálogo</h6>
                        <p class="text-muted small mb-0">Permite dar de alta nuevas agrupaciones de menús, definir a qué proyecto pertenecen y configurar el comportamiento inicial del panel visual.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Botón Gatillo del Modal --}}
                    <button type="button" class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#modalAltaCategoria">
                        <i class="fa fa-plus-circle me-2"></i> Registrar Nueva Categoría
                    </button>
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
                                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Categorías
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalRegistros">
                                {{ $categorias->total() }} Registros
                            </span>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            {{-- Buscador Reactivo --}}
                            <div class="input-group" style="min-width: 260px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                                <input type="search" id="global-search" class="form-control bg-light border-0" placeholder="Buscar categoría o proyecto..." style="font-size: 0.85rem; box-shadow: none;">
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
                                    <th>Categoría</th>
                                    <th>Proyecto</th>
                                    <th class="text-center" style="width: 120px;">Panel Abierto</th>
                                    <th class="text-center" style="width: 100px;">Estado</th>
                                    <th class="text-center pe-4" style="width: 100px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCategorias">
                                {{-- Render inicial desde Servidor usando tu patrón de fragmentos --}}
                                @include('admin_formatos.categoria_modulos.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Footer con Paginación Homologada --}}
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $categorias->firstItem() ?? 0 }} a {{ $categorias->lastItem() ?? 0 }} de {{ $categorias->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de categorias">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Sincronizado dinámicamente por JS (Copia el esquema de carga.js) --}}
                        </ul>
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
                                   class="form-control border-gray-300 shadow-sm" 
                                   placeholder="Coloque el nombre de la categoría del módulo" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="proyecto" class="form-label fw-bold text-secondary">
                                <i class="fa fa-laptop me-1 text-dark"></i> Proyecto Relacionado:
                            </label>
                            <input type="text" name="proyecto" id="proyecto" 
                                   class="form-control border-gray-300 shadow-sm" 
                                   placeholder="Coloque el nombre del proyecto" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="colapsado" class="form-label fw-bold text-secondary">
                                <i class="fa fa-folder-open-o me-1 text-dark"></i> ¿Panel Abierto Inicialmente?
                            </label>
                            {{-- Siguiendo la recomendación de no dejar strings vacíos o textos harcodeados inconsistentes --}}
                            <select name="colapsado" id="colapsado" class="form-select border-gray-300 shadow-sm" required>
                                <option value="no" selected>No (Se muestra cerrado)</option>
                                <option value="si">Sí (Se muestra expandido)</option>
                            </select>
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

{{-- Inyección de activos encapsulada por Vite --}}
@vite(['resources/css/categoria_modulos/categoria.css', 'resources/js/categoria_modulos/categoria.js'])
@endsection