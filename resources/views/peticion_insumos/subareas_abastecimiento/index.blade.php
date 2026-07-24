@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header / Título -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa fa-sitemap me-2"></i> Subáreas de Abastecimiento
            </h4>
            <p class="text-muted small mb-0">Catálogo de subáreas asociadas a cada área principal de abastecimiento de insumos.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAltaSubarea">
                <i class="fa fa-plus-circle me-1"></i> Registrar Subárea
            </button>
            <a href="{{ route('subareas_abastecimiento.reportes') }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fa fa-print me-1"></i> Reportes
            </a>
            <a href="{{ route('subareas_abastecimiento.graficas') }}" class="btn btn-outline-success btn-sm px-3 shadow-sm">
                <i class="fa fa-chart-pie me-1"></i> Gráficas
            </a>
        </div>
    </div>

    <!-- Alert de Notificaciones -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm py-2" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filtros y Búsqueda -->
    <form id="formFiltros" class="mb-3" onsubmit="return false;">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                    <input type="text" id="buscar" name="buscar" class="form-control form-control-sm border-start-0 ps-0" placeholder="Buscar por subárea o siglas..." autocomplete="off">
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-4">
                <select id="id_area_abastecimiento_filtro" name="id_area_abastecimiento" class="form-select form-select-sm">
                    <option value="">-- Todas las Áreas de Abastecimiento --</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id_area_abastecimiento }}">{{ $area->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 col-lg-4 d-flex align-items-center justify-content-md-end gap-3 mt-2 mt-md-0">
                <span class="small fw-semibold text-muted">Estatus:</span>
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input filtro-status" type="checkbox" name="status[]" value="Activo" id="chkActivo" checked>
                    <label class="form-check-label small text-dark" for="chkActivo">Activos</label>
                </div>
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input filtro-status" type="checkbox" name="status[]" value="Inactivo" id="chkInactivo" checked>
                    <label class="form-check-label small text-dark" for="chkInactivo">Inactivos</label>
                </div>
            </div>
        </div>
    </form>

    <!-- Contenedor Tabla AJAX -->
    <div class="card border shadow-sm">
        <div id="contenedor-tabla">
            @include('peticion_insumos.subareas_abastecimiento.partials.tabla')
        </div>
    </div>
</div>

<!-- Modal Registro de Subárea -->
<div class="modal fade" id="modalAltaSubarea" tabindex="-1" aria-labelledby="modalAltaSubareaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalAltaSubareaLabel">
                    <i class="fa fa-plus-circle me-1 text-primary"></i> Registrar Nueva Subárea de Abastecimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('subareas_abastecimiento.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="modal_id_area" class="form-label small fw-semibold text-dark">Área Principal de Abastecimiento <span class="text-danger">*</span></label>
                        <select id="modal_id_area" name="id_area_abastecimiento" class="form-select form-select-sm @error('id_area_abastecimiento') is-invalid @enderror" required>
                            <option value="">-- Seleccionar Área --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id_area_abastecimiento }}" {{ old('id_area_abastecimiento') == $area->id_area_abastecimiento ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_area_abastecimiento')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="modal_nombre" class="form-label small fw-semibold text-dark">Nombre de la Subárea <span class="text-danger">*</span></label>
                        <input type="text" id="modal_nombre" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" placeholder="Ej. Quimio, Consulta Externa, Almacén 1..." required>
                        <div id="feedback_modal_nombre" class="invalid-feedback small">
                            @error('nombre') {{ $message }} @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_siglas" class="form-label small fw-semibold text-dark">Siglas (Opcional)</label>
                        <input type="text" id="modal_siglas" name="siglas" class="form-control form-control-sm @error('siglas') is-invalid @enderror" value="{{ old('siglas') }}" placeholder="Ej. QUI, CEXT, ALM1...">
                        @error('siglas')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">Guardar Subárea</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/css/peticion_insumos/subareas_abastecimiento/subareas_abastecimiento.css', 'resources/js/peticion_insumos/subareas_abastecimiento/subareas_abastecimiento.js'])
@endpush
@endsection
