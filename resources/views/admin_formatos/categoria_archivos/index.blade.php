@extends('layouts.app')

@section('title', 'Categoría de Archivos')

@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display:none;"></div>
@endif
@if($errors->any())
    <div id="alertaError" data-message="{{ $errors->first() }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-categoria-archivos">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-folder-open me-2"></i>Categorías de Archivos
            </h1>
            <p class="text-muted mb-0">Registro, edición y consulta de las categorías de archivos</p>
        </div>
    </div>

    {{-- ─── Modal: REGISTRAR CATEGORÍA ──────────────────────────────────────── --}}
    <div class="modal fade" id="modalAltaCategoria" tabindex="-1" aria-labelledby="modalAltaCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px; overflow:hidden;">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalAltaCategoriaLabel">
                        <i class="fa fa-plus-circle me-2"></i>Registrar Nueva Categoría
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('categoria_archivos.store') }}" autocomplete="off" novalidate>
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="categoria" class="form-label fw-bold text-secondary">Nombre de la categoría *:</label>
                                <input
                                    type="text"
                                    name="categoria"
                                    id="categoria"
                                    class="form-control @error('categoria') is-invalid @enderror"
                                    value="{{ old('categoria') }}"
                                    placeholder="Coloque el nombre de la categoría"
                                    autocomplete="off"
                                    maxlength="255"
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
                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 border rounded-pill" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary px-4 py-2 rounded-pill">
                            <i class="fa fa-save me-2"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ─── Tabla unificada de Categorías ──────────────────────────────────── --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                {{-- Cabecera tarjeta --}}
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Lista de Categorías
                        </h5>
                    </div>

                    {{-- ── Panel de filtros ────────────────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">
                        <x-filtro-buscar id="filtro-buscar" label="Buscar categoría" placeholder="Nombre de la categoría..." clase="col-12 col-md-4" />
                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registrar y Reportes) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalAltaCategoria">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Nueva Categoría
                            </button>
                        </div>
                        <div>
                            <a href="{{ route('categoria_archivos.reportes') }}"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Reportes
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Tabla de categorías --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                         <thead class="table-light text-uppercase small text-secondary">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 80px;" class="text-center">Editar</th>
                                <th>Categoría</th>
                                <th style="width: 150px;">Fecha</th>
                                <th style="width: 150px;">Hora</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaCategorias">
                            @include('admin_formatos.categoria_archivos.partials.tabla')
                        </tbody>
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionCategorias">
                        Mostrando {{ $categorias->firstItem() ?? 0 }} a {{ $categorias->lastItem() ?? 0 }}
                        de {{ $categorias->total() }} registros
                    </div>
                    <nav>
                        <div id="paginacionCategorias">
                            {{ $categorias->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

</div>

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('modalAltaCategoria'));
            myModal.show();
        });
    </script>
@endif

@vite(['resources/css/categoria_archivos/categoria.css', 'resources/js/categoria_archivos/categoria.js'])
@endsection