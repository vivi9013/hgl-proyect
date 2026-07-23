@extends('layouts.app')

@section('title', 'Médicos RX - Estudios Radiológicos')

@section('content')
<div class="container-fluid py-4" id="modulo-rx-medicos">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Encabezado Principal --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-user-md text-primary me-2"></i>Médicos RX
            </h1>
            <p class="text-muted mb-0">Catálogo de médicos radiólogos encargados de realizar e interpretar los estudios.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            {{-- Botón Reportes --}}
            <a href="{{ route('rx_medicos.reportes') }}"
               class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm me-2 text-nowrap">
                <i class="fa fa-file-pdf-o me-2 text-danger"></i>Reportes
            </a>
            {{-- Botón Gráficas --}}
            <a href="{{ route('rx_medicos.graficas') }}"
               class="btn btn-outline-success px-3 py-2 rounded-pill shadow-sm text-nowrap">
                <i class="fa fa-bar-chart me-2 text-success"></i>Gráficas
            </a>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── 1. Formulario de Alta ── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-pencil text-secondary me-2"></i>Registra un nuevo Médico
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    <form id="frmAltaMedico" novalidate>
                        <div class="row g-3">
                            <div class="col-12 col-md-8 col-lg-9">
                                <label for="nombreMed" class="form-label small fw-bold mb-1 text-dark">Nombre del Médico *</label>
                                <input type="text" id="nombreMed" name="nombre" class="form-control bg-light py-2" placeholder="Coloque el nombre del Médico" required>
                                <div class="invalid-feedback" id="feedback-nombre"></div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-3">
                                <label for="abre" class="form-label small fw-bold mb-1 text-dark">Abreviatura *</label>
                                <input type="text" id="abre" name="abreviatura" class="form-control bg-light py-2 font-monospace text-uppercase" placeholder="SOLO CUATRO LETRAS" maxlength="4" required style="letter-spacing: 0.1em;">
                                <div class="invalid-feedback" id="feedback-abreviatura"></div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" style="font-weight: 600;">
                                <i class="fa fa-save me-1"></i>Guardar Información
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Listado de Médicos ── --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Lista de Médicos
                        </h5>
                        <span id="total-registros-badge" class="rounded-pill px-3 py-1 fw-bold text-dark" style="background-color: #e9ecef; font-size: 0.78rem;">
                            Cargando...
                        </span>
                    </div>

                    {{-- Buscador integrado --}}
                    <div class="input-group" style="width: 280px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                        <input type="text" id="buscar-medico" class="form-control bg-light border-0 py-2" placeholder="Buscar por nombre o abreviatura..." style="font-size: 0.9rem; box-shadow: none;">
                        <span class="input-group-text bg-light border-0">
                            <i class="bi bi-search text-dark"></i>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table id="tablaMedicos" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4" style="width: 60px;">#</th>
                                    <th class="text-center" style="width: 80px;">Editar</th>
                                    <th>Nombre del Médico</th>
                                    <th>Abreviatura</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th class="text-center pe-4" style="width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Se llena dinámicamente con AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center border-top mt-2">
                    <div id="info-paginacion" class="text-muted small">
                        Cargando información de página...
                    </div>
                    <div id="paginador-medicos">
                        {{-- Se llena dinámicamente --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 3. Modal de Edición ── --}}
<div id="modalEditar" class="modal fade" role="dialog" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark mb-0" id="modalEditarLabel">
                    <i class="fa fa-edit text-dark me-2"></i>Editar Información del Médico
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="frmEditarMedico" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" id="idRegistro">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="EnombreMed" class="form-label small fw-bold mb-1 text-dark">Nombre del Médico *</label>
                            <input type="text" id="EnombreMed" name="nombre" class="form-control bg-light py-2" placeholder="Coloque el nombre del Médico" required>
                            <div class="invalid-feedback" id="feedback-editar-nombre"></div>
                        </div>
                        <div class="col-12">
                            <label for="Eabre" class="form-label small fw-bold mb-1 text-dark">Abreviatura *</label>
                            <input type="text" id="Eabre" name="abreviatura" class="form-control bg-light py-2 font-monospace text-uppercase" placeholder="SOLO CUATRO LETRAS" maxlength="4" required style="letter-spacing: 0.1em;">
                            <div class="invalid-feedback" id="feedback-editar-abreviatura"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-dark px-3 py-2 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 py-2 rounded-pill text-white" style="font-weight: 600;">Editar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/pacientes/medicos/medicos.js'])
@endpush
