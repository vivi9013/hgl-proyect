@extends('layouts.app')

@section('title', 'Trabajadores - Hospital General')

@section('content')
{{-- Flags para que el JS sepa si debe abrir modal por errores de validación --}}
<span id="hasFormErrors" data-errors="{{ session('hasFormErrors') ? '1' : '0' }}" style="display:none;"></span>
<span id="hasEditFormErrors" data-id="{{ session('hasEditFormErrors') ?? '' }}" style="display:none;"></span>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-male text-primary me-2"></i>Gestión de Trabajadores
            </h1>
            <p class="text-muted mb-0">Catálogo de expediens laborales y trabajadores de la institución</p>
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
                        <h6 class="fw-bold mb-1 text-dark">Panel de Control de Trabajadores</h6>
                        <p class="text-muted small mb-0">Administración del personal activo e inactivo del hospital. Asigna sedes, departamentos, puestos y categorías.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Registrar Nuevo Trabajador --}}
                    <button type="button" class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#modalRegistrarTrabajador">
                        <i class="fa fa-plus-circle me-2"></i> Registrar Trabajador
                    </button>

                    {{-- Gráficas --}}
                    <a href="{{ route('trabajadores.graficas') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-pie-chart me-2 text-primary"></i> Gráficas
                    </a>

                    {{-- Reportes --}}
                    <a href="{{ route('trabajadores.reportes') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
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

    <div class="row g-4" data-tabla-interactiva data-endpoint="{{ route('trabajadores.index') }}" data-tbody-target="tbodyArchivos" data-info-target="infoPaginacion" data-paginacion-target="contenedorPaginacion">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <h5 class="card-title mb-0 fw-bold text-dark">
                                    <i class="fa fa-list-ul text-secondary me-2"></i>Listado de Trabajadores
                                </h5>
                                <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalArchivos">
                                    {{ $trabajadores->total() }} Registros
                                </span>
                            </div>

                            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                                {{-- Búsqueda Reutilizable --}}
                                <x-filtro-buscar id="global-search" label="Buscar trabajador" placeholder="Buscar por no. empleado, persona, RFC..." clase="w-100" />
                                
                                {{-- Filtro por Estado --}}
                                <x-filtro-dropdown id="dropdownFiltros" label="Filtrar por estado" titulo="Estados" labelDefault="Todos los estados" clase="w-100">
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

                        {{-- Selectores de Filtro Adicionales (Departamento, Puesto, Tipo, Sede) --}}
                        <div class="row g-2 pt-2 border-top">
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-select form-select-sm filtro-select" data-filtro="id_departamento">
                                    <option value="">-- Todos los Departamentos --</option>
                                    @foreach($departamentos as $d)
                                        <option value="{{ $d->id }}" {{ $idDepartamento == $d->id ? 'selected' : '' }}>{{ $d->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-select form-select-sm filtro-select" data-filtro="id_puesto">
                                    <option value="">-- Todos los Puestos --</option>
                                    @foreach($puestos as $p)
                                        <option value="{{ $p->id }}" {{ $idPuesto == $p->id ? 'selected' : '' }}>{{ $p->puesto }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-select form-select-sm filtro-select" data-filtro="id_tipo_trabajador">
                                    <option value="">-- Todos los Tipos --</option>
                                    @foreach($tiposTrabajador as $t)
                                        <option value="{{ $t->id }}" {{ $idTipoTrabajador == $t->id ? 'selected' : '' }}>{{ $t->tipo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <select class="form-select form-select-sm filtro-select" data-filtro="id_sede">
                                    <option value="">-- Todas las Sedes --</option>
                                    @foreach($sedes as $s)
                                        <option value="{{ $s->id }}" {{ $idSede == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1 sticky-top bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 40px;">#</th>
                                    <th class="text-center" style="width: 70px;">Acciones</th>
                                    <th>No. Empleado</th>
                                    <th>Persona / Nombre Completo</th>
                                    <th>Sede</th>
                                    <th>Departamento</th>
                                    <th>Puesto</th>
                                    <th>Tipo de Trabajador</th>
                                    <th>Ingreso</th>
                                    <th class="text-center pe-4" style="width: 100px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyArchivos">
                                {{-- Carga inicial --}}
                                @include('admin_institucional.trabajadores.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $trabajadores->firstItem() ?? 0 }} a {{ $trabajadores->lastItem() ?? 0 }} de {{ $trabajadores->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de trabajadores">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Sincronizado por JavaScript --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Registrar Nuevo Trabajador -->
    <div class="modal fade" id="modalRegistrarTrabajador" tabindex="-1" aria-labelledby="modalRegistrarTrabajadorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalRegistrarTrabajadorLabel">
                        <i class="fa fa-plus-circle text-dark me-2"></i>Registrar nuevo trabajador
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: brightness(0);"></button>
                </div>
                <form id="formRegistrarTrabajador" action="{{ route('trabajadores.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            {{-- Número de Empleado --}}
                            <div class="col-12 col-md-6">
                                <label for="num_empleado" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-hashtag text-dark me-1"></i> Número de Empleado *
                                </label>
                                <input type="text"
                                       name="num_empleado"
                                       id="num_empleado"
                                       class="form-control border-gray-300 shadow-sm"
                                       value="{{ old('num_empleado') }}"
                                       placeholder="Ej. 171, EMP-0012..."
                                       required>
                                <div id="numEmpleadoFeedback" class="mt-1 small d-none"></div>
                            </div>

                            {{-- Persona --}}
                            <div class="col-12 col-md-6">
                                <label for="id_persona" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user text-dark me-1"></i> Persona Asignada *
                                </label>
                                <select name="id_persona" id="id_persona" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Persona --</option>
                                    @foreach($personas as $p)
                                        <option value="{{ $p->id }}" {{ old('id_persona') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nombre }} {{ $p->ap_paterno }} {{ $p->ap_materno }} (RFC: {{ $p->rfc ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div id="personaFeedback" class="mt-1 small d-none"></div>
                            </div>

                            {{-- Sede --}}
                            <div class="col-12 col-md-6">
                                <label for="id_sede" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-building text-dark me-1"></i> Sede *
                                </label>
                                <select name="id_sede" id="id_sede" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Sede --</option>
                                    @foreach($sedes as $s)
                                        <option value="{{ $s->id }}" {{ old('id_sede') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Departamento --}}
                            <div class="col-12 col-md-6">
                                <label for="id_departamento" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-sitemap text-dark me-1"></i> Departamento *
                                </label>
                                <select name="id_departamento" id="id_departamento" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Departamento --</option>
                                    @foreach($departamentos as $d)
                                        <option value="{{ $d->id }}" {{ old('id_departamento') == $d->id ? 'selected' : '' }}>{{ $d->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Puesto --}}
                            <div class="col-12 col-md-6">
                                <label for="id_puesto" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-briefcase text-dark me-1"></i> Puesto *
                                </label>
                                <select name="id_puesto" id="id_puesto" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Puesto --</option>
                                    @foreach($puestos as $p)
                                        <option value="{{ $p->id }}" {{ old('id_puesto') == $p->id ? 'selected' : '' }}>{{ $p->puesto }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tipo de Trabajador --}}
                            <div class="col-12 col-md-6">
                                <label for="id_tipo_trabajador" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-quote-left text-dark me-1"></i> Tipo de Trabajador *
                                </label>
                                <select name="id_tipo_trabajador" id="id_tipo_trabajador" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Tipo --</option>
                                    @foreach($tiposTrabajador as $t)
                                        <option value="{{ $t->id }}" {{ old('id_tipo_trabajador') == $t->id ? 'selected' : '' }}>{{ $t->tipo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Fecha de Ingreso --}}
                            <div class="col-12 col-md-6">
                                <label for="fecha_ingreso" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-calendar text-dark me-1"></i> Fecha de Ingreso *
                                </label>
                                <input type="date"
                                       name="fecha_ingreso"
                                       id="fecha_ingreso"
                                       class="form-control border-gray-300 shadow-sm"
                                       value="{{ old('fecha_ingreso', date('Y-m-d')) }}"
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary py-2 rounded-pill shadow-sm" style="border: 1.5px solid #000;">
                            <i class="fa fa-save me-2"></i>Guardar Trabajador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Trabajador -->
    <div class="modal fade" id="modalEditarTrabajador" tabindex="-1" aria-labelledby="modalEditarTrabajadorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditarTrabajadorLabel">
                        <i class="fa fa-pencil text-dark me-2"></i>Editar expediente de trabajador
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: brightness(0);"></button>
                </div>
                <form id="formEditarTrabajador" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_trabajador_id" name="trabajador_id">
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            {{-- Número de Empleado --}}
                            <div class="col-12 col-md-6">
                                <label for="edit_num_empleado" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-hashtag text-dark me-1"></i> Número de Empleado *
                                </label>
                                <input type="text"
                                       name="num_empleado"
                                       id="edit_num_empleado"
                                       class="form-control border-gray-300 shadow-sm"
                                       required>
                                <div id="editNumEmpleadoFeedback" class="mt-1 small d-none"></div>
                            </div>

                            {{-- Persona --}}
                            <div class="col-12 col-md-6">
                                <label for="edit_id_persona" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user text-dark me-1"></i> Persona Asignada *
                                </label>
                                <select name="id_persona" id="edit_id_persona" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Persona --</option>
                                    @foreach($personas as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->nombre }} {{ $p->ap_paterno }} {{ $p->ap_materno }} (RFC: {{ $p->rfc ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div id="editPersonaFeedback" class="mt-1 small d-none"></div>
                            </div>

                            {{-- Sede --}}
                            <div class="col-12 col-md-6">
                                <label for="edit_id_sede" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-building text-dark me-1"></i> Sede *
                                </label>
                                <select name="id_sede" id="edit_id_sede" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Sede --</option>
                                    @foreach($sedes as $s)
                                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Departamento --}}
                            <div class="col-12 col-md-6">
                                <label for="edit_id_departamento" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-sitemap text-dark me-1"></i> Departamento *
                                </label>
                                <select name="id_departamento" id="edit_id_departamento" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Departamento --</option>
                                    @foreach($departamentos as $d)
                                        <option value="{{ $d->id }}">{{ $d->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Puesto --}}
                            <div class="col-12 col-md-6">
                                <label for="edit_id_puesto" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-briefcase text-dark me-1"></i> Puesto *
                                </label>
                                <select name="id_puesto" id="edit_id_puesto" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Puesto --</option>
                                    @foreach($puestos as $p)
                                        <option value="{{ $p->id }}">{{ $p->puesto }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tipo de Trabajador --}}
                            <div class="col-12 col-md-6">
                                <label for="edit_id_tipo_trabajador" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-quote-left text-dark me-1"></i> Tipo de Trabajador *
                                </label>
                                <select name="id_tipo_trabajador" id="edit_id_tipo_trabajador" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="">-- Seleccionar Tipo --</option>
                                    @foreach($tiposTrabajador as $t)
                                        <option value="{{ $t->id }}">{{ $t->tipo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Fecha de Ingreso --}}
                            <div class="col-12 col-md-6">
                                <label for="edit_fecha_ingreso" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-calendar text-dark me-1"></i> Fecha de Ingreso *
                                </label>
                                <input type="date"
                                       name="fecha_ingreso"
                                       id="edit_fecha_ingreso"
                                       class="form-control border-gray-300 shadow-sm"
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnActualizar" class="btn btn-primary py-2 rounded-pill shadow-sm" style="border: 1.5px solid #000;">
                            <i class="fa fa-save me-2"></i>Actualizar Trabajador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/trabajadores/trabajadores.css', 'resources/js/trabajadores/trabajadores.js'])
@endsection
