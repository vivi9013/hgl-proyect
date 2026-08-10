@extends('layouts.app')

@section('title', 'Personas - Hospital General de Linares')

@section('content')
{{-- Alertas de Sesión --}}
@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display: none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-personas">
    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-id-card text-primary me-2"></i>Catálogo de Personas
            </h1>
            <p class="text-muted mb-0">Gestione el padrón de personas registradas en el sistema hospitalario</p>
        </div>
    </div>

    {{-- Área Principal: Tabla de Registros --}}
    <div class="row g-4"
         data-tabla-interactiva
         data-endpoint="{{ route('personas.index') }}"
         data-tbody-target="cuerpoTablaPersonas"
         data-info-target="infoPaginacionPersonas"
         data-paginacion-target="paginacionPersonas"
         data-btn-imprimir="#btnImprimirPersonas">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fa fa-list-ul me-2"></i>Lista de Personas
                            </h5>
                            <span class="badge bg-dark rounded-pill px-3 py-2" id="totalPersonas">
                                {{ $personas->total() }} Registros
                            </span>
                        </div>
                    </div>

                    {{-- ── Panel de filtros ──────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">

                        {{-- Búsqueda por persona --}}
                        <x-filtro-buscar id="filtro-buscar" label="Buscar persona" placeholder="Nombre, RFC, CURP, email..." />

                        {{-- Filtro Desplegable Premium --}}
                        <x-filtro-dropdown id="dropdownFiltros" label="Filtrar por categoría" labelDefault="Todas las personas">
                            <!-- Grupo: Sexo -->
                            <div class="mb-3">
                                <span class="text-muted fw-bold d-block mb-1 small text-uppercase" style="font-size:0.7rem; letter-spacing: 0.5px;">Sexo</span>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-sexo" type="checkbox" value="M" id="chkSexoM" data-filtro="sexo">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkSexoM">Masculino</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-sexo" type="checkbox" value="F" id="chkSexoF" data-filtro="sexo">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkSexoF">Femenino</label>
                                </div>
                            </div>

                            <!-- Grupo: Estado / Estatus -->
                            <div class="mb-2">
                                <span class="text-muted fw-bold d-block mb-1 small text-uppercase" style="font-size:0.7rem; letter-spacing: 0.5px;">Estado</span>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-status" type="checkbox" value="1" id="chkStatusActivo" data-filtro="status">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkStatusActivo">Activos</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-status" type="checkbox" value="0" id="chkStatusInactivo" data-filtro="status">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkStatusInactivo">Inactivos</label>
                                </div>
                            </div>
                        </x-filtro-dropdown>

                        {{-- Filtro por Estado de la República --}}
                        <x-filtro-dropdown id="dropdownEstado" label="Filtrar por entidad" labelDefault="Todos los estados">
                            @foreach($estados as $estado)
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-estado" type="checkbox" value="{{ $estado }}" id="chkEstado{{ \Str::slug($estado) }}" data-filtro="estado">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkEstado{{ \Str::slug($estado) }}">{{ $estado }}</label>
                                </div>
                            @endforeach
                        </x-filtro-dropdown>
                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registrar, Reportes, Gráficas) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalAltaPersona">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Nueva Persona
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('personas.graficas') }}"
                               class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-bar-chart me-1"></i>Gráficas
                            </a>
                            <a id="btnImprimirPersonas"
                               href="{{ route('personas.imprimir') }}"
                               target="_blank" rel="noopener"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Reportes
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Tabla Asíncrona --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 55px;">#</th>
                                <th class="text-center" style="width: 100px;">Acciones</th>
                                <th class="text-center" style="width: 300px;">Nombre Completo</th>
                                <th style="width: 80px;">Sexo</th>
                                <th style="width: 80px;">Edad</th>
                                <th class="text-center" style="width: 180px;">Estado/Municipio</th>
                                <th class="text-center" style="width: 110px;">Estudiante</th>
                                <th class="text-center" style="width: 90px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaPersonas">
                            @include('admin_sistema.personas.partials.tabla')
                        </tbody>
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionPersonas">
                        Mostrando {{ $personas->firstItem() ?? 0 }} a {{ $personas->lastItem() ?? 0 }} de {{ $personas->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de personas">
                        <div id="paginacionPersonas">
                            {{ $personas->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Registrar Nueva Persona --}}
    <div class="modal fade" id="modalAltaPersona" tabindex="-1" aria-labelledby="modalAltaPersonaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3 modal-alta">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalAltaPersonaLabel">
                        <i class="fa fa-user-plus text-primary me-2"></i>Registrar Nueva Persona
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formAltaPersona" action="{{ route('personas.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-3">

                        {{-- Sección: Datos Personales --}}
                        <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3">
                            <i class="fa fa-user me-1"></i> Datos Personales
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="nombre" class="form-label fw-semibold small text-secondary">Nombre(s) <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" class="form-control shadow-sm" placeholder="Nombre(s)" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="ap_paterno" class="form-label fw-semibold small text-secondary">Apellido Paterno <span class="text-danger">*</span></label>
                                <input type="text" name="ap_paterno" id="ap_paterno" class="form-control shadow-sm" placeholder="Apellido paterno" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="ap_materno" class="form-label fw-semibold small text-secondary">Apellido Materno <span class="text-danger">*</span></label>
                                <input type="text" name="ap_materno" id="ap_materno" class="form-control shadow-sm" placeholder="Apellido materno" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3">
                                <label for="fecha_nac" class="form-label fw-semibold small text-secondary">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_nac" id="fecha_nac" class="form-control shadow-sm" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="sexo" class="form-label fw-semibold small text-secondary">Sexo <span class="text-danger">*</span></label>
                                <select name="sexo" id="sexo" class="form-select shadow-sm" required>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="ecivil" class="form-label fw-semibold small text-secondary">Estado Civil <span class="text-danger">*</span></label>
                                <select name="ecivil" id="ecivil" class="form-select shadow-sm" required>
                                    <option value="Soltero(a)">Soltero(a)</option>
                                    <option value="Casado(a)">Casado(a)</option>
                                    <option value="Viudo(a)">Viudo(a)</option>
                                    <option value="Divorciado(a)">Divorciado(a)</option>
                                    <option value="Union Libre">Unión Libre</option>
                                    <option value="No especificado">No especificado</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="telefono" class="form-label fw-semibold small text-secondary">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" name="telefono" id="telefono" class="form-control shadow-sm" placeholder="Casa o Celular" required>
                            </div>
                        </div>

                        {{-- Sección: Identificación --}}
                        <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 mt-2">
                            <i class="fa fa-id-card-o me-1"></i> Identificación
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="rfc" class="form-label fw-semibold small text-secondary">RFC <span class="text-danger">*</span></label>
                                <input type="text" name="rfc" id="rfc" class="form-control shadow-sm text-uppercase" placeholder="RFC (se guardará en mayúsculas)" maxlength="13" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="curp" class="form-label fw-semibold small text-secondary">CURP <span class="text-danger">*</span></label>
                                <input type="text" name="curp" id="curp" class="form-control shadow-sm text-uppercase" placeholder="CURP (se guardará en mayúsculas)" maxlength="18" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="e_mail" class="form-label fw-semibold small text-secondary">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="e_mail" id="e_mail" class="form-control shadow-sm" placeholder="correo@ejemplo.com" required>
                            </div>
                        </div>

                        {{-- Sección: Domicilio --}}
                        <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 mt-2">
                            <i class="fa fa-map-marker me-1"></i> Domicilio
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3">
                                <label for="colonia" class="form-label fw-semibold small text-secondary">Colonia <span class="text-danger">*</span></label>
                                <input type="text" name="colonia" id="colonia" class="form-control shadow-sm" placeholder="Colonia" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="calle" class="form-label fw-semibold small text-secondary">Calle <span class="text-danger">*</span></label>
                                <input type="text" name="calle" id="calle" class="form-control shadow-sm" placeholder="Nombre de la calle" required>
                            </div>
                            <div class="col-12 col-md-1">
                                <label for="numero" class="form-label fw-semibold small text-secondary">No. <span class="text-danger">*</span></label>
                                <input type="text" name="numero" id="numero" class="form-control shadow-sm" placeholder="No." required>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="estado_sel" class="form-label fw-semibold small text-secondary">Estado <span class="text-danger">*</span></label>
                                <select name="estado" id="estado_sel" class="form-select shadow-sm" required>
                                    <option value="">-- Seleccionar --</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="municipio_sel" class="form-label fw-semibold small text-secondary">Municipio <span class="text-danger">*</span></label>
                                <select name="municipio" id="municipio_sel" class="form-select shadow-sm" required>
                                    <option value="">-- Seleccionar Estado --</option>
                                </select>
                            </div>
                        </div>

                        {{-- Sección: Rol --}}
                        <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 mt-2">
                            <i class="fa fa-graduation-cap me-1"></i> Rol en el Sistema
                        </h6>
                        <div class="form-check ps-0">
                            <input type="checkbox" class="form-check-input ms-0 me-2" id="estudiante" name="estudiante" value="1" checked>
                            <label class="form-check-label fw-semibold text-secondary" for="estudiante">
                                ¿La persona tendrá un rol de <strong>estudiante</strong> dentro del sistema?
                            </label>
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

    {{-- Modal: Editar Persona --}}
    <div class="modal fade" id="modalEditarPersona" tabindex="-1" aria-labelledby="modalEditarPersonaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditarPersonaLabel">
                        <i class="fa fa-pencil-square-o text-primary me-2"></i>Editar Persona
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formEditarPersona" action="" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="modal-body px-4 py-3">

                        {{-- Sección: Datos Personales --}}
                        <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3">
                            <i class="fa fa-user me-1"></i> Datos Personales
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="edit_nombre" class="form-label fw-semibold small text-secondary">Nombre(s) <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="edit_nombre" class="form-control shadow-sm" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="edit_ap_paterno" class="form-label fw-semibold small text-secondary">Apellido Paterno <span class="text-danger">*</span></label>
                                <input type="text" name="ap_paterno" id="edit_ap_paterno" class="form-control shadow-sm" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="edit_ap_materno" class="form-label fw-semibold small text-secondary">Apellido Materno <span class="text-danger">*</span></label>
                                <input type="text" name="ap_materno" id="edit_ap_materno" class="form-control shadow-sm" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3">
                                <label for="edit_fecha_nac" class="form-label fw-semibold small text-secondary">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_nac" id="edit_fecha_nac" class="form-control shadow-sm" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="edit_sexo" class="form-label fw-semibold small text-secondary">Sexo <span class="text-danger">*</span></label>
                                <select name="sexo" id="edit_sexo" class="form-select shadow-sm" required>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="edit_ecivil" class="form-label fw-semibold small text-secondary">Estado Civil <span class="text-danger">*</span></label>
                                <select name="ecivil" id="edit_ecivil" class="form-select shadow-sm" required>
                                    <option value="Soltero(a)">Soltero(a)</option>
                                    <option value="Casado(a)">Casado(a)</option>
                                    <option value="Viudo(a)">Viudo(a)</option>
                                    <option value="Divorciado(a)">Divorciado(a)</option>
                                    <option value="Union Libre">Unión Libre</option>
                                    <option value="No especificado">No especificado</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="edit_telefono" class="form-label fw-semibold small text-secondary">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" name="telefono" id="edit_telefono" class="form-control shadow-sm" required>
                            </div>
                        </div>

                        {{-- Sección: Identificación --}}
                        <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 mt-2">
                            <i class="fa fa-id-card-o me-1"></i> Identificación
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="edit_rfc" class="form-label fw-semibold small text-secondary">RFC <span class="text-danger">*</span></label>
                                <input type="text" name="rfc" id="edit_rfc" class="form-control shadow-sm text-uppercase" maxlength="13" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="edit_curp" class="form-label fw-semibold small text-secondary">CURP <span class="text-danger">*</span></label>
                                <input type="text" name="curp" id="edit_curp" class="form-control shadow-sm text-uppercase" maxlength="18" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="edit_e_mail" class="form-label fw-semibold small text-secondary">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="e_mail" id="edit_e_mail" class="form-control shadow-sm" required>
                            </div>
                        </div>

                        {{-- Sección: Domicilio --}}
                        <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 mt-2">
                            <i class="fa fa-map-marker me-1"></i> Domicilio
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3">
                                <label for="edit_colonia" class="form-label fw-semibold small text-secondary">Colonia <span class="text-danger">*</span></label>
                                <input type="text" name="colonia" id="edit_colonia" class="form-control shadow-sm" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="edit_calle" class="form-label fw-semibold small text-secondary">Calle <span class="text-danger">*</span></label>
                                <input type="text" name="calle" id="edit_calle" class="form-control shadow-sm" required>
                            </div>
                            <div class="col-12 col-md-1">
                                <label for="edit_numero" class="form-label fw-semibold small text-secondary">No. <span class="text-danger">*</span></label>
                                <input type="text" name="numero" id="edit_numero" class="form-control shadow-sm" required>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="edit_estado" class="form-label fw-semibold small text-secondary">Estado <span class="text-danger">*</span></label>
                                <select name="estado" id="edit_estado" class="form-select shadow-sm" required>
                                    <option value="">-- Seleccionar --</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label for="edit_municipio" class="form-label fw-semibold small text-secondary">Municipio <span class="text-danger">*</span></label>
                                <select name="municipio" id="edit_municipio" class="form-select shadow-sm" required>
                                    <option value="">-- Seleccionar Estado --</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnActualizarPersona" class="btn btn-primary py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Actualizar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@vite(['resources/css/personas/personas.css', 'resources/js/personas/personas.js'])
@endsection