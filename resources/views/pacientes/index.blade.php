@extends('layouts.app')

@section('title', 'Estudios RX - Hospital General')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pacientes/pacientes.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- CSRF Meta -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ── Encabezado Principal ── --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="h2 text-dark fw-bold mb-1">
                <i class="bi bi-journal-medical text-primary me-2"></i>Estudios RX
            </h1>
            <p class="text-muted mb-0">Gestión interactiva de expedientes de pacientes y estudios de radiología.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button id="btn-nuevo-paciente" class="btn btn-theme shadow-sm px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPaciente">
                <i class="bi bi-person-plus-fill me-2"></i>Registrar Paciente
            </button>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Panel de Contenidos Principal ── --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Pestañas de Navegación -->
        <div class="card-header bg-white border-0 py-3 px-4">
            <ul class="nav nav-pills card-header-pills" id="rxTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-2.5 fw-semibold d-flex align-items-center" id="pacientes-tab" data-bs-toggle="tab" data-bs-target="#pacientes-panel" type="button" role="tab" aria-controls="pacientes-panel" aria-selected="true">
                        <i class="bi bi-people-fill me-2"></i>Expedientes de Pacientes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2.5 fw-semibold d-flex align-items-center ms-2" id="estudios-tab" data-bs-toggle="tab" data-bs-target="#estudios-panel" type="button" role="tab" aria-controls="estudios-panel" aria-selected="false">
                        <i class="bi bi-file-earmark-medical-fill me-2"></i>Historial de Estudios
                    </button>
                </li>
            </ul>
        </div>
        
        <!-- Contenido de las Pestañas -->
        <div class="card-body p-0">
            <div class="tab-content" id="rxTabContent">
                
                {{-- ── Pestaña Pacientes ── --}}
                <div class="tab-pane fade show active p-4" id="pacientes-panel" role="tabpanel" aria-labelledby="pacientes-tab">
                    <!-- Búsqueda y Estadísticas -->
                    <div class="row align-items-center mb-4 gap-3 gap-md-0">
                        <div class="col-md-7">
                            <div class="input-group search-box shadow-sm rounded-2">
                                <span class="input-group-text bg-white border-end-0 text-muted px-3">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="buscar-paciente" class="form-control border-start-0 ps-0 shadow-none py-2.5" placeholder="Buscar paciente por nombre, apellidos, NHC, RFC...">
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <span id="total-pacientes-badge" class="badge bg-theme-subtle text-theme border border-theme px-3 py-2.5 fs-6 rounded-pill fw-semibold">
                                Total: 0 pacientes
                            </span>
                        </div>
                    </div>
                    
                    <!-- Tabla de Pacientes -->
                    <div class="table-responsive rounded-3 border border-light">
                        <table id="tabla-pacientes" class="table table-hover align-middle mb-0 table-striped-columns">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="ps-4 py-3 fw-bold" style="width: 130px;">NHC</th>
                                    <th class="py-3 fw-bold">Nombre Completo</th>
                                    <th class="py-3 fw-bold" style="width: 120px;">Sexo</th>
                                    <th class="py-3 fw-bold" style="width: 140px;">F. Nacimiento</th>
                                    <th class="py-3 fw-bold">RFC</th>
                                    <th class="py-3 fw-bold" style="width: 140px;">Seguro Popular</th>
                                    <th class="py-3 fw-bold">Teléfono</th>
                                    <th class="text-center pe-4 py-3 fw-bold" style="width: 250px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena asíncronamente con JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2 px-2">
                        <div></div>
                        <div id="paginacion-pacientes"></div>
                    </div>
                </div>
                
                {{-- ── Pestaña Estudios ── --}}
                <div class="tab-pane fade p-4" id="estudios-panel" role="tabpanel" aria-labelledby="estudios-tab">
                    <!-- Búsqueda y Estadísticas -->
                    <div class="row align-items-center mb-4 gap-3 gap-md-0">
                        <div class="col-md-7">
                            <div class="input-group search-box shadow-sm rounded-2">
                                <span class="input-group-text bg-white border-end-0 text-muted px-3">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="buscar-estudio" class="form-control border-start-0 ps-0 shadow-none py-2.5" placeholder="Buscar por paciente, NHC o detalle del estudio...">
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <span id="total-estudios-badge" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2.5 fs-6 rounded-pill fw-semibold">
                                Total: 0 estudios
                            </span>
                        </div>
                    </div>
                    
                    <!-- Tabla de Estudios -->
                    <div class="table-responsive rounded-3 border border-light">
                        <table id="tabla-estudios" class="table table-hover align-middle mb-0 table-striped-columns">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="ps-4 py-3 fw-bold" style="width: 130px;">Fecha</th>
                                    <th class="py-3 fw-bold" style="width: 120px;">NHC</th>
                                    <th class="py-3 fw-bold">Paciente</th>
                                    <th class="py-3 fw-bold" style="width: 130px;">Origen</th>
                                    <th class="py-3 fw-bold">Regiones Estudiadas</th>
                                    <th class="py-3 fw-bold">Detalle/Especificado</th>
                                    <th class="py-3 fw-bold" style="width: 80px;">CDs</th>
                                    <th class="py-3 fw-bold">Médico RX</th>
                                    <th class="text-center pe-4 py-3 fw-bold" style="width: 180px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena asíncronamente con JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2 px-2">
                        <div></div>
                        <div id="paginacion-estudios"></div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

{{-- ── Modales ── --}}

<!-- Modal Paciente -->
<div class="modal fade" id="modalPaciente" tabindex="-1" aria-labelledby="modalPacienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-theme text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold" id="modalPacienteLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Paciente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-paciente" novalidate>
                @csrf
                <input type="hidden" id="paciente-id" name="id_paciente">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="paciente-nombre" class="form-label fw-semibold text-muted">Nombre *</label>
                            <input type="text" class="form-control py-2" id="paciente-nombre" name="nombre" required placeholder="Nombre del paciente">
                        </div>
                        <div class="col-md-4">
                            <label for="paciente-ap-paterno" class="form-label fw-semibold text-muted">Apellido Paterno *</label>
                            <input type="text" class="form-control py-2" id="paciente-ap-paterno" name="ap_paterno" required placeholder="Primer apellido">
                        </div>
                        <div class="col-md-4">
                            <label for="paciente-ap-materno" class="form-label fw-semibold text-muted">Apellido Materno</label>
                            <input type="text" class="form-control py-2" id="paciente-ap-materno" name="ap_materno" placeholder="Segundo apellido">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="paciente-sexo" class="form-label fw-semibold text-muted">Sexo *</label>
                            <select class="form-select py-2" id="paciente-sexo" name="sexo" required>
                                <option value="" selected disabled>Seleccionar...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="paciente-fecha-nacimiento" class="form-label fw-semibold text-muted">Fecha de Nacimiento</label>
                            <input type="date" class="form-control py-2" id="paciente-fecha-nacimiento" name="fecha_nacimiento">
                        </div>
                        <div class="col-md-4">
                            <label for="paciente-telefono" class="form-label fw-semibold text-muted">Teléfono</label>
                            <input type="tel" class="form-control py-2" id="paciente-telefono" name="telefono" placeholder="Ej: 3312345678">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="paciente-domicilio" class="form-label fw-semibold text-muted">Domicilio</label>
                            <input type="text" class="form-control py-2" id="paciente-domicilio" name="domicilio" placeholder="Calle, número, colonia...">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="paciente-rfc" class="form-label fw-semibold text-muted">RFC (10 u 13 dígitos)</label>
                            <input type="text" class="form-control py-2 font-monospace" id="paciente-rfc" name="rfc" placeholder="ABCD123456" maxlength="13">
                        </div>
                        <div class="col-md-6">
                            <label for="paciente-homoclave" class="form-label fw-semibold text-muted">Homoclave (3 caracteres)</label>
                            <input type="text" class="form-control py-2 font-monospace" id="paciente-homoclave" name="homoclave" placeholder="1A2" maxlength="3">
                        </div>

                        <!-- Switches interactivos -->
                        <div class="col-md-6 mt-4">
                            <div class="form-check form-switch card-switch p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-3 float-start" type="checkbox" role="switch" id="tiene_nhc" name="tiene_nhc">
                                <label class="form-check-label fw-bold text-dark" for="tiene_nhc">
                                    ¿Tiene Número de Historia Clínica (NHC)?
                                    <span class="d-block small text-muted fw-normal">Activa esta casilla si el paciente ya tiene NHC asignado en el Hospital.</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mt-4">
                            <div class="form-check form-switch card-switch p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-3 float-start" type="checkbox" role="switch" id="tiene_sp" name="tiene_sp">
                                <label class="form-check-label fw-bold text-dark" for="tiene_sp">
                                    ¿Tiene Seguro Popular / Insabi?
                                    <span class="d-block small text-muted fw-normal">Activa esta opción para registrar la póliza o número del historial médico.</span>
                                </label>
                            </div>
                        </div>

                        <!-- Campos condicionales -->
                        <div class="col-md-6 input-nhc-wrapper d-none">
                            <div class="p-3 bg-light rounded-3 border-start border-primary border-3">
                                <label for="paciente-nhc-hgl" class="form-label fw-semibold text-primary">NHC HGL *</label>
                                <input type="text" class="form-control py-2" id="paciente-nhc-hgl" name="nhc_hgl" placeholder="Ingresar NHC asignado">
                            </div>
                        </div>
                        
                        <div class="col-md-6 input-sp-wrapper d-none">
                            <div class="p-3 bg-light rounded-3 border-start border-success border-3">
                                <label for="paciente-sp" class="form-label fw-semibold text-success">Póliza Seguro Popular *</label>
                                <input type="text" class="form-control py-2" id="paciente-sp" name="sp" placeholder="Ingresar número de póliza">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3">
                    <button type="button" class="btn btn-outline-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-guardar-paciente" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                        <span id="text-guardar-paciente">Guardar Paciente</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Estudio -->
<div class="modal fade" id="modalEstudio" tabindex="-1" aria-labelledby="modalEstudioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold" id="modalEstudioLabel">
                    <i class="bi bi-journal-plus me-2"></i>Registrar Cita / Estudio de Radiología
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-estudio" novalidate>
                @csrf
                <input type="hidden" id="estudio-id" name="id_estudios">
                
                <!-- Campos ocultos de paciente vinculados automáticamente -->
                <input type="hidden" id="estudio-nhc" name="nhc">
                <input type="hidden" id="estudio-nombre" name="nombre">
                <input type="hidden" id="estudio-ap-paterno" name="ap_paterno">
                <input type="hidden" id="estudio-ap-materno" name="ap_materno">
                <input type="hidden" id="estudio-nacimiento" name="nacimiento">
                <input type="hidden" id="estudio-sexo" name="sexo">
                <input type="hidden" id="estudio-sp" name="sp">

                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Box Informativo de Paciente -->
                        <div class="col-12">
                            <div class="p-3.5 bg-success-subtle border border-success-subtle text-success-emphasis rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <h6 class="mb-1 fw-bold text-success fs-5">
                                        <i class="bi bi-person-fill me-1"></i> Paciente: <span id="estudio-paciente-display">---</span>
                                    </h6>
                                    <span class="small font-monospace text-muted d-inline-block me-3">
                                        NHC: <strong id="estudio-nhc-display">---</strong>
                                    </span>
                                    <span class="small text-muted d-inline-block me-3">
                                        F. Nacimiento: <strong id="estudio-nacimiento-display">---</strong>
                                    </span>
                                    <span class="small text-muted">
                                        Sexo: <strong id="estudio-sexo-display">---</strong>
                                    </span>
                                </div>
                                <span class="badge bg-success px-3.5 py-2.5 fs-6 rounded-pill fw-bold">
                                    <i class="bi bi-journal-check me-1.5"></i>Regiones: <span id="badge-total-estudios">0</span>
                                </span>
                            </div>
                        </div>

                        <!-- Datos Clínicos Propios de Estudio -->
                        <div class="col-md-3">
                            <label for="estudio-fecha-estudio" class="form-label fw-semibold text-muted">Fecha del Estudio *</label>
                            <input type="date" class="form-control py-2 fw-semibold" id="estudio-fecha-estudio" name="fecha_estudio" required value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="estudio-hgl" class="form-label fw-semibold text-muted">Origen/Servicio *</label>
                            <select class="form-select py-2 fw-medium" id="estudio-hgl" name="hgl" required>
                                <option value="" selected disabled>Seleccionar origen...</option>
                                <option value="Consulta Externa">Consulta Externa</option>
                                <option value="Urgencias">Urgencias</option>
                                <option value="Hospitalización">Hospitalización</option>
                                <option value="Externo">Externo (Fuera del HGL)</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="estudio-especialidad" class="form-label fw-semibold text-muted">Especialidad RX *</label>
                            <select class="form-select py-2 fw-medium" id="estudio-especialidad" name="especialidad" required>
                                <option value="" selected disabled>Seleccionar especialidad...</option>
                                @foreach($especialidades as $esp)
                                    <option value="{{ $esp->id_especialidad }}">{{ $esp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="estudio-medico" class="form-label fw-semibold text-muted">Médico Radiólogo *</label>
                            <select class="form-select py-2 fw-medium" id="estudio-medico" name="medico" required>
                                <option value="" selected disabled>Seleccionar médico...</option>
                                @foreach($medicos as $med)
                                    <option value="{{ $med->id_medicos }}">{{ $med->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Checkboxes de Regiones Anatómicas -->
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-dark mb-2.5"><i class="bi bi-body-text me-1 text-success"></i> Regiones Anatómicas Estudiadas (Selecciona al menos una)</label>
                            <div class="row g-2.5">
                                <div class="col-sm-6 col-md-4">
                                    <div class="p-2.5 bg-light border rounded-3 d-flex align-items-center">
                                        <input class="form-check-input checkbox-estudio ms-1 me-3 fs-5 cursor-pointer" type="checkbox" id="est-craneo" name="craneo">
                                        <label class="form-check-label fw-semibold text-dark cursor-pointer flex-grow-1 py-1" for="est-craneo">Cráneo</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="p-2.5 bg-light border rounded-3 d-flex align-items-center">
                                        <input class="form-check-input checkbox-estudio ms-1 me-3 fs-5 cursor-pointer" type="checkbox" id="est-tx" name="tx">
                                        <label class="form-check-label fw-semibold text-dark cursor-pointer flex-grow-1 py-1" for="est-tx">Tórax (TX)</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="p-2.5 bg-light border rounded-3 d-flex align-items-center">
                                        <input class="form-check-input checkbox-estudio ms-1 me-3 fs-5 cursor-pointer" type="checkbox" id="est-abd" name="abd">
                                        <label class="form-check-label fw-semibold text-dark cursor-pointer flex-grow-1 py-1" for="est-abd">Abdomen (ABD)</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="p-2.5 bg-light border rounded-3 d-flex align-items-center">
                                        <input class="form-check-input checkbox-estudio ms-1 me-3 fs-5 cursor-pointer" type="checkbox" id="est-col" name="col">
                                        <label class="form-check-label fw-semibold text-dark cursor-pointer flex-grow-1 py-1" for="est-col">Columna</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="p-2.5 bg-light border rounded-3 d-flex align-items-center">
                                        <input class="form-check-input checkbox-estudio ms-1 me-3 fs-5 cursor-pointer" type="checkbox" id="est-m-sup" name="m_sup">
                                        <label class="form-check-label fw-semibold text-dark cursor-pointer flex-grow-1 py-1" for="est-m-sup">Miembro Superior</label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="p-2.5 bg-light border rounded-3 d-flex align-items-center">
                                        <input class="form-check-input checkbox-estudio ms-1 me-3 fs-5 cursor-pointer" type="checkbox" id="est-m-inf" name="m_inf">
                                        <label class="form-check-label fw-semibold text-dark cursor-pointer flex-grow-1 py-1" for="est-m-inf">Miembro Inferior</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="p-2.5 bg-light border rounded-3 d-flex align-items-center border-dashed">
                                        <input class="form-check-input checkbox-estudio ms-1 me-3 fs-5 cursor-pointer" type="checkbox" id="est-contraste" name="contraste">
                                        <label class="form-check-label fw-bold text-dark cursor-pointer flex-grow-1 py-1" for="est-contraste">
                                            Estudio Realizado Con Medio de Contraste
                                            <span class="d-block small text-muted fw-normal">Activar si el estudio requiere o utilizó contraste endovenoso, oral, etc.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total de CDs e Info Extra -->
                        <div class="col-md-4 d-flex flex-column justify-content-between">
                            <div>
                                <label for="estudio-total-cds" class="form-label fw-semibold text-muted">Total de Placas / CDs Grabados</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-disc text-muted"></i></span>
                                    <input type="number" class="form-control py-2 fw-bold" id="estudio-total-cds" name="total_cds" min="0" value="0">
                                </div>
                                <small class="text-muted d-block mt-1">Registra la cantidad física de medios entregados al paciente.</small>
                            </div>
                            
                            <div class="mt-3">
                                <label for="estudio-especificado" class="form-label fw-semibold text-muted">Especificación Anatómica / Observaciones rápidas</label>
                                <textarea class="form-control shadow-none" id="estudio-especificado" name="especificado" rows="3" placeholder="Ej: RX de Cráneo AP y Lateral, miembro inferior rodilla izq..."></textarea>
                            </div>
                        </div>

                        <!-- Observaciones Adicionales -->
                        <div class="col-md-12">
                            <label for="estudio-otros-datos" class="form-label fw-semibold text-muted">Notas Clínicas Adicionales / Antecedentes</label>
                            <textarea class="form-control shadow-none" id="estudio-otros-datos" name="otros_datos" rows="3" placeholder="Antecedentes patológicos, motivo del estudio, hallazgos rápidos preliminares..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3">
                    <button type="button" class="btn btn-outline-secondary px-3 py-2 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-guardar-estudio" class="btn btn-success px-4 py-2 fw-semibold shadow-sm">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                        <span id="text-guardar-estudio">Guardar Estudio</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalles de Estudio -->
<div class="modal fade" id="modalDetalleEstudio" tabindex="-1" aria-labelledby="modalDetalleEstudioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold" id="modalDetalleEstudioLabel">
                    <i class="bi bi-eye-fill me-2"></i>Detalles Completos del Estudio RX
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Banner de Paciente -->
                <div class="bg-light p-4 border-bottom d-flex gap-4 align-items-center">
                    <div class="avatar-circle-det d-none d-sm-flex align-items-center justify-content-center text-white bg-dark fs-3 fw-bold rounded-circle" style="width: 65px; height: 65px;">
                        <i class="bi bi-person-bounding-box"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 id="det-paciente" class="fw-bold mb-1 text-dark">---</h4>
                        <div class="d-flex flex-wrap gap-3 mt-1.5">
                            <span class="small font-monospace bg-white border border-light px-2.5 py-1 rounded shadow-none text-muted">
                                NHC: <strong id="det-nhc" class="text-dark">---</strong>
                            </span>
                            <span class="small text-muted">Edad: <strong id="det-edad" class="text-dark">---</strong></span>
                            <span class="small text-muted">Sexo: <strong id="det-sexo" class="text-dark">---</strong></span>
                            <span class="small text-muted">Póliza SP: <strong id="det-sp" class="text-dark">---</strong></span>
                        </div>
                    </div>
                </div>
                
                <!-- Grid de Info Técnica -->
                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-sm-6 col-md-4">
                            <div class="detail-card p-3 rounded-3 border bg-white shadow-none">
                                <span class="d-block small text-muted fw-semibold mb-1 uppercase tracking-wider" style="font-size:0.75rem;">FECHA DE ESTUDIO</span>
                                <span id="det-fecha" class="fw-bold text-dark fs-5">---</span>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="detail-card p-3 rounded-3 border bg-white shadow-none">
                                <span class="d-block small text-muted fw-semibold mb-1 uppercase tracking-wider" style="font-size:0.75rem;">ORIGEN / SERVICIO</span>
                                <span id="det-origen" class="fw-bold text-primary fs-5">---</span>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="detail-card p-3 rounded-3 border bg-white shadow-none">
                                <span class="d-block small text-muted fw-semibold mb-1 uppercase tracking-wider" style="font-size:0.75rem;">PLACAS / CDS GRABADOS</span>
                                <span class="fw-bold text-dark fs-5"><i class="bi bi-disc text-muted me-1"></i> <span id="det-cds">0</span> uds</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-card p-3 rounded-3 border bg-white shadow-none">
                                <span class="d-block small text-muted fw-semibold mb-1.5 uppercase tracking-wider" style="font-size:0.75rem;">ESPECIALIDAD RX</span>
                                <span id="det-especialidad" class="fw-semibold text-dark">---</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-card p-3 rounded-3 border bg-white shadow-none">
                                <span class="d-block small text-muted fw-semibold mb-1.5 uppercase tracking-wider" style="font-size:0.75rem;">MÉDICO RADIÓLOGO</span>
                                <span id="det-medico" class="fw-semibold text-dark">---</span>
                            </div>
                        </div>

                        <!-- Regiones Anatómicas -->
                        <div class="col-12">
                            <div class="p-3.5 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small text-muted fw-bold uppercase tracking-wider" style="font-size:0.75rem;">REGIONES ANATÓMICAS REGISTRADAS</span>
                                    <span class="badge bg-dark rounded-circle px-2 py-1"><span id="det-total-estudios">0</span></span>
                                </div>
                                <div id="det-badges-regiones" class="d-flex flex-wrap gap-2">
                                    <!-- Badges dinámicos con JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Especificación -->
                        <div class="col-12">
                            <div class="p-3.5 bg-light rounded-3 border">
                                <span class="d-block small text-muted fw-bold mb-2 uppercase tracking-wider" style="font-size:0.75rem;">ESPECIFICACIÓN ANATÓMICA</span>
                                <div id="det-especificado" class="fw-medium text-dark bg-white p-3 rounded-2 border border-light font-monospace small" style="min-height: 50px;">---</div>
                            </div>
                        </div>
                        
                        <!-- Notas Clínicas -->
                        <div class="col-12">
                            <div class="p-3.5 bg-light rounded-3 border">
                                <span class="d-block small text-muted fw-bold mb-2 uppercase tracking-wider" style="font-size:0.75rem;">OBSERVACIONES / ANTECEDENTES</span>
                                <div id="det-notas" class="text-dark bg-white p-3 rounded-2 border border-light small" style="min-height: 60px; white-space: pre-wrap;">---</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Metadatos de Registro -->
                <div class="bg-light p-3 px-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 text-muted small">
                    <div>
                        <i class="bi bi-person-fill-check me-1"></i> Registrado por: <strong id="det-usuario">---</strong>
                    </div>
                    <div>
                        <i class="bi bi-clock-history me-1"></i> Fecha registro: <span id="det-fecha-registro">---</span> &nbsp; <span id="det-hora-registro"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2.5 px-4 rounded-bottom-3">
                <button type="button" class="btn btn-dark px-4 fw-semibold" data-bs-dismiss="modal">Entendido / Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/js/pacientes/pacientes.js') }}"></script>
@endpush
