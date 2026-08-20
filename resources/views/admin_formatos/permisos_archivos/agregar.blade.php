@extends('layouts.app')

@section('title', 'Asignar Categorías a Trabajador')

@section('content')
<div class="container-fluid py-4" id="modulo-permisos-asignar">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-user-plus me-2"></i>Acceso a Categorías de Archivos
            </h1>
            <p class="text-muted mb-0">Agregar o quitar registros de permisos por trabajador</p>
        </div>
        <a href="{{ route('trabajador_categorias.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Volver a la Lista
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <form id="formAsignarCategorias" method="POST" action="{{ route('trabajador_categorias.guardar', $trabajador->id) }}">
                @csrf

                <div class="card shadow-sm border-0">

                    {{-- Cabecera tarjeta: trabajador + filtro --}}
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fa fa-user me-2"></i>
                                Trabajador -> ( {{ $trabajador->ap_paterno }} {{ $trabajador->ap_materno }} {{ $trabajador->nombre }} )
                            </h5>
                        </div>

                        {{-- ── Panel de filtros ──────────────────────────── --}}
                        <div class="row g-2 align-items-end" id="panelFiltros">
                            <x-filtro-buscar id="filtro-buscar" label="Buscar categoría" placeholder="Nombre de categoría..." />
                        </div>

                        {{-- Acciones secundarias --}}
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                            <div class="d-flex gap-3">
                                <button type="button" id="btnMarcarTodos" class="btn btn-sm btn-link text-decoration-none fw-bold p-0">
                                    <i class="fa fa-check-square"></i> Marcar todos en esta página
                                </button>
                                <span class="text-muted">|</span>
                                <button type="button" id="btnDesmarcarTodos" class="btn btn-sm btn-link text-decoration-none fw-bold text-secondary p-0">
                                    <i class="fa fa-minus-square"></i> Desmarcar todos en esta página
                                </button>
                            </div>
                            <div class="text-muted small fw-medium">
                                <span id="contadorSeleccionados" class="text-primary fw-bold">0</span> categorías seleccionadas en total
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de categorías --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase small text-secondary">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">#</th>
                                    <th>Categorías</th>
                                    <th class="text-center pe-4" style="width: 180px;">Agregar/Quitar</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyAsignacion">
                                @include('admin_formatos.permisos_archivos.partials.tabla_asignacion')
                            </tbody>
                        </table>
                    </div>

                    {{-- Contenedor oculto para inyección dinámica del formulario --}}
                    <div id="inputsDestinoOcultos"></div>

                    {{-- IDs de categorías ya asignadas (para inicializar el Set en JS sin depender de la página actual) --}}
                    <div id="categoriasAsignadasIniciales"
                         data-ids="{{ $trabajador->categorias->pluck('id_catego_archivos')->toJson() }}"
                         style="display:none;"></div>

                    {{-- Pie: info + paginación + guardar --}}
                    <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3 border-top">
                        <div class="text-muted small" id="infoPaginacion">
                            Mostrando {{ $categorias->firstItem() ?? 0 }} a {{ $categorias->lastItem() ?? 0 }} de {{ $categorias->total() }} categorías
                        </div>
                        <nav aria-label="Paginación de asignación">
                            <div id="contenedorPaginacion">
                                {{ $categorias->links('pagination::bootstrap-4') }}
                            </div>
                        </nav>
                        <button type="submit" id="btnGuardarPermisos" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm fw-bold">
                            <i class="fa fa-save me-2"></i> Agregar o quitar categoría
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
    @vite(['resources/css/trabajador_categorias/permisos.css', 'resources/js/trabajador_categorias/permisos.js'])
@endpush
@endsection