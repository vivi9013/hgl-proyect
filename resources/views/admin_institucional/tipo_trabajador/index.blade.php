@extends('layouts.app')

@section('title', 'Tipos de Trabajador - Hospital General')

@section('content')
{{-- Flag para que el JS sepa si debe abrir el modal (errores de validación) --}}
<span id="hasFormErrors" data-errors="{{ $errors->any() ? '1' : '0' }}" style="display:none;"></span>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-quote-left text-primary me-2"></i>Tipos de Trabajador
            </h1>
            <p class="text-muted mb-0">Catálogo de tipos de trabajador institucionales del hospital</p>
        </div>
    </div>

    {{-- Información de módulo y Acciones --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light p-3 rounded-circle text-primary">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Panel de Control de Tipos de Trabajador</h6>
                        <p class="text-muted small mb-0">Administración del catálogo de tipos de trabajador. Permite registrar, editar, activar/desactivar y exportar reportes.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Registrar Nuevo Tipo --}}
                    <button type="button" class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#modalRegistrarTipo">
                        <i class="fa fa-plus-circle me-2"></i> Registrar Tipo
                    </button>

                    {{-- Gráficas --}}
                    <a href="{{ route('tipo_trabajador.graficas') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-pie-chart me-2 text-primary"></i> Gráficas
                    </a>

                    {{-- Reportes --}}
                    <a href="{{ route('tipo_trabajador.reportes') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Notificaciones Flash --}}
    @if(session('exitog'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle me-2 fs-4"></i>
                <div>
                    <strong>¡Operación Satisfactoria!</strong> {{ session('exitog') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('exito'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle me-2 fs-4"></i>
                <div>
                    <strong>¡Operación Satisfactoria!</strong> {{ session('exito') }}
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

    <div class="row g-4" data-tabla-interactiva data-endpoint="{{ route('tipo_trabajador.index') }}" data-tbody-target="tbodyArchivos" data-info-target="infoPaginacion" data-paginacion-target="contenedorPaginacion" data-btn-imprimir="#btnImprimir">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Tipos de Trabajador
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalArchivos">
                                {{ $tipos->total() }} Registros
                            </span>
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                            {{-- Búsqueda Reutilizable --}}
                            <x-filtro-buscar id="global-search" label="Buscar tipo de trabajador" placeholder="Escribe el nombre del tipo..." clase="w-100" />
                            
                            {{-- Filtro Reutilizable por Estado --}}
                            <x-filtro-dropdown id="dropdownFiltros" label="Filtrar por estado" titulo="Estados" labelDefault="Todos los tipos" clase="w-100">
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-status" type="checkbox" value="Activo" id="chkStatusActivo" data-filtro="status">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkStatusActivo">Activos</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-status" type="checkbox" value="Inactivo" id="chkStatusInactivo" data-filtro="status">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkStatusInactivo">Inactivos</label>
                                </div>
                            </x-filtro-dropdown>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1 sticky-top bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">#</th>
                                    <th class="text-center" style="width: 80px;">Acciones</th>
                                    <th>Nombre del Tipo</th>
                                    <th>Fecha de Modificación</th>
                                    <th>Hora</th>
                                    <th class="text-center pe-4" style="width: 110px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyArchivos">
                                {{-- Carga inicial --}}
                                @include('admin_institucional.tipo_trabajador.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $tipos->firstItem() ?? 0 }} a {{ $tipos->lastItem() ?? 0 }} de {{ $tipos->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de tipos de trabajador">
                        <div id="contenedorPaginacion">
                            {{ $tipos->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Registrar Nuevo Tipo -->
    <div class="modal fade" id="modalRegistrarTipo" tabindex="-1" aria-labelledby="modalRegistrarTipoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalRegistrarTipoLabel">
                        <i class="fa fa-plus-circle text-dark me-2"></i>Registrar nuevo tipo de trabajador
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: brightness(0);"></button>
                </div>
                <form id="formRegistrarTipo" action="{{ route('tipo_trabajador.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="tipo" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-tag text-dark me-1"></i> Nombre del Tipo de Trabajador *
                                </label>
                                <input type="text"
                                       name="tipo"
                                       id="tipo"
                                       class="form-control border-gray-300 shadow-sm"
                                       value="{{ old('tipo') }}"
                                       placeholder="Ej. Confianza, Base, Contrato..."
                                       required>
                                {{-- Mensaje de validación en tiempo real --}}
                                <div id="tipoFeedback" class="mt-1 small d-none"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary py-2 rounded-pill shadow-sm" style="border: 1.5px solid #000;">
                            <i class="fa fa-save me-2"></i>Guardar Tipo de Trabajador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/tipo_trabajador/tipo_trabajador.css', 'resources/js/tipo_trabajador/tipo_trabajador.js'])
@endsection
