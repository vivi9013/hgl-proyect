@extends('layouts.app')

@section('title', 'Administración de Archivos - Hospital General')

@section('content')

@if(session('success'))
    <div id="alertaExitog" data-message="{{ session('success') }}" style="display:none;"></div>
@endif
@if($errors->any())
    <div id="alertaError" data-message="{{ $errors->first() }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-carga-archivos">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-upload me-2"></i>Administración de Archivos
            </h1>
            <p class="text-muted mb-0">Carga y gestión de formatos y documentos institucionales</p>
        </div>
    </div>

    {{-- ─── Modal: REGISTRAR ARCHIVO ────────────────────────────────────────── --}}
    <div class="modal fade" id="modalCargaArchivo" tabindex="-1" aria-labelledby="modalCargaArchivoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px; overflow:hidden;">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalCargaArchivoLabel">
                        <i class="fa fa-edit me-2"></i>Registrar Nuevo Archivo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCargaArchivo" action="{{ route('carga_archivos.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="tipo" class="form-label fw-bold text-secondary">
                                <i class="fa fa-folder-open-o me-1"></i> Categoría:
                            </label>
                            <select name="tipo" id="tipo" class="form-select" required>
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
                                <span><i class="fa fa-file-text-o me-1"></i> Nombre del archivo:</span>
                                <span id="feedbackDisponibilidad" class="small fw-normal"></span>
                            </label>
                            <div class="input-group">
                                <input type="text" name="nombre" id="nombre"
                                       class="form-control"
                                       placeholder="Coloca el nombre del archivo"
                                       value="{{ old('nombre') }}"
                                       required>
                                <span class="input-group-text bg-white" id="loadingSpinner" style="display: none;">
                                    <i class="fa fa-spinner fa-spin"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="version" class="form-label fw-bold text-secondary">
                                <i class="fa fa-code-fork me-1"></i> Versión:
                            </label>
                            <input type="number" name="version" id="version"
                                   class="form-control"
                                   placeholder="Ej. 1"
                                   min="1"
                                   value="{{ old('version', 1) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="desc" class="form-label fw-bold text-secondary">
                                <i class="fa fa-info-circle me-1"></i> Descripción:
                            </label>
                            <textarea name="desc" id="desc" rows="3"
                                      class="form-control"
                                      placeholder="Ingrese una descripción del archivo"
                                      required>{{ old('desc') }}</textarea>
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

    {{-- ─── Tabla unificada de Archivos ────────────────────────────────────── --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                {{-- Cabecera tarjeta --}}
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Lista de Archivos
                        </h5>
                    </div>

                    {{-- ── Panel de filtros ────────────────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">

                        {{-- Búsqueda por nombre/descripción --}}
                        <x-filtro-buscar id="filtro-buscar" label="Buscar archivo" placeholder="Nombre o descripción..." clase="col-12 col-md-4" />

                        {{-- Filtro Desplegable por Categoría --}}
                        <x-filtro-dropdown id="dropdownFiltros" label="Filtrar por categoría" titulo="Categorías" labelDefault="Todas las categorías" clase="col-12 col-md-4">
                            <div class="mb-2">
                                @foreach($categorias as $categoria)
                                    <div class="form-check py-1">
                                        <input class="form-check-input chk-categoria" type="checkbox" value="{{ $categoria->id_catego_archivos }}" id="chkCategoria{{ $categoria->id_catego_archivos }}">
                                        <label class="form-check-label text-dark cursor-pointer" for="chkCategoria{{ $categoria->id_catego_archivos }}">{{ $categoria->categoria }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </x-filtro-dropdown>

                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registrar, Reportes, Gráficas) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalCargaArchivo">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Nuevo Archivo
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('carga_archivos.graficas') }}"
                               class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-bar-chart me-1"></i>Gráficas
                            </a>
                            <a href="{{ route('carga_archivos.reportes') }}"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Reportes
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Tabla de archivos --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-secondary">
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
                        <tbody id="cuerpoTablaArchivos">
                            @include('admin_formatos.carga_archivos.partials.tabla')
                        </tbody>
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionArchivos">
                        Mostrando {{ $archivos->firstItem() ?? 0 }} a {{ $archivos->lastItem() ?? 0 }}
                        de {{ $archivos->total() }} registros
                    </div>
                    <nav>
                        <div id="paginacionArchivos">
                            {{ $archivos->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

</div>

@vite(['resources/css/carga_archivos/carga.css', 'resources/js/carga_archivos/carga.js'])
@endsection