@extends('layouts.app')

@section('title', 'Departamentos - Hospital General')

@section('content')
{{-- Flag para que el JS sepa si debe abrir el modal (errores de validación) --}}
<span id="hasFormErrors" data-errors="{{ $errors->any() ? '1' : '0' }}" style="display:none;"></span>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-cube text-primary me-2"></i>Departamentos
            </h1>
            <p class="text-muted mb-0">Catálogo de departamentos institucionales del hospital</p>
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
                        <h6 class="fw-bold mb-1 text-dark">Panel de Control de Departamentos</h6>
                        <p class="text-muted small mb-0">Administración del catálogo de departamentos del hospital. Permite registrar, editar, activar/desactivar y exportar reportes.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Registrar Nuevo Departamento --}}
                    <button type="button" class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#modalRegistrarDepartamento">
                        <i class="fa fa-plus-circle me-2"></i> Registrar Departamento
                    </button>

                    {{-- Gráficas --}}
                    <a href="{{ route('departamentos.graficas') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-pie-chart me-2 text-primary"></i> Gráficas
                    </a>

                    {{-- Reportes --}}
                    <a href="{{ route('departamentos.reportes') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
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

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Departamentos
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalArchivos">
                                {{ $departamentos->total() }} Registros
                            </span>
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            {{-- Búsqueda Global --}}
                            <div class="input-group" style="min-width: 240px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                                <input type="search" id="global-search" class="form-control bg-light border-0" placeholder="Buscar departamento..." style="font-size: 0.85rem; box-shadow: none;" value="{{ $buscar }}">
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
                                    <th class="text-center" style="width: 80px;">Acciones</th>
                                    <th>Nombre</th>
                                    <th>Abreviatura</th>
                                    <th>Extensión</th>
                                    <th>Responsable</th>
                                    <th>Fecha</th>
                                    <th class="text-center pe-4" style="width: 110px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyArchivos">
                                {{-- Carga inicial --}}
                                @include('admin_institucional.departamentos.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $departamentos->firstItem() ?? 0 }} a {{ $departamentos->lastItem() ?? 0 }} de {{ $departamentos->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de departamentos">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Sincronizado por JavaScript --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Registrar Nuevo Departamento -->
    <div class="modal fade" id="modalRegistrarDepartamento" tabindex="-1" aria-labelledby="modalRegistrarDepartamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalRegistrarDepartamentoLabel">
                        <i class="fa fa-plus-circle text-dark me-2"></i>Registrar nuevo departamento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: brightness(0);"></button>
                </div>
                <form id="formRegistrarDepartamento" action="{{ route('departamentos.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="nombre" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-building text-dark me-1"></i> Nombre del Departamento *
                                </label>
                                <input type="text"
                                       name="nombre"
                                       id="nombre"
                                       class="form-control border-gray-300 shadow-sm"
                                       value="{{ old('nombre') }}"
                                       placeholder="Ej. Sistemas, Dirección..."
                                       required>
                                {{-- Mensaje de validación en tiempo real --}}
                                <div id="nombreFeedback" class="mt-1 small d-none"></div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="abreviatura" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-font text-dark me-1"></i> Abreviatura *
                                </label>
                                <input type="text"
                                       name="abreviatura"
                                       id="abreviatura"
                                       class="form-control border-gray-300 shadow-sm"
                                       value="{{ old('abreviatura') }}"
                                       placeholder="Ej. SIS, DR..."
                                       maxlength="10"
                                       required>
                                <div id="abreviaturaFeedback" class="mt-1 small d-none"></div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="extension" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-phone text-dark me-1"></i> Extensión
                                </label>
                                <input type="text"
                                       name="extension"
                                       id="extension"
                                       class="form-control border-gray-300 shadow-sm"
                                       value="{{ old('extension') }}"
                                       placeholder="Ej. 36121"
                                       maxlength="50">
                            </div>
                            <div class="col-12">
                                <label for="id_persona" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user text-dark me-1"></i> Responsable / Jefe de Departamento
                                </label>
                                <select name="id_persona" id="id_persona" class="form-select border-gray-300 shadow-sm">
                                    <option value="">-- Sin asignar --</option>
                                    @foreach($personas as $p)
                                        <option value="{{ $p->id }}" {{ old('id_persona') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nombre }} {{ $p->ap_paterno }} {{ $p->ap_materno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary py-2 rounded-pill shadow-sm" style="border: 1.5px solid #000;">
                            <i class="fa fa-save me-2"></i>Guardar Departamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/departamentos/departamentos.css', 'resources/js/departamentos/departamentos.js'])
@endsection
