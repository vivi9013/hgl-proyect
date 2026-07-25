@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header / Título -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa fa-boxes me-2"></i> Áreas de Abastecimiento
            </h4>
            <p class="text-muted small mb-0">Catálogo de áreas principales destinadas a la gestión y solicitud de insumos.</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAltaArea">
                <i class="fa fa-plus-circle me-1"></i> Registrar Área
            </button>
            <a href="{{ route('areas_abastecimiento.relacionar') }}" class="btn btn-outline-dark btn-sm px-3 shadow-sm">
                <i class="fa fa-link me-1"></i> Relación de Áreas
            </a>
            <a href="{{ route('areas_abastecimiento.reportes') }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fa fa-print me-1"></i> Reportes
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
            <div class="col-12 col-md-6 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                    <input type="text" id="buscar" name="buscar" class="form-control form-control-sm border-start-0 ps-0" placeholder="Buscar área..." autocomplete="off">
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-8 d-flex align-items-center justify-content-md-end gap-3 mt-2 mt-md-0">
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
            @include('peticion_insumos.areas_abastecimiento.partials.tabla')
        </div>
    </div>
</div>

<!-- Modal Registro de Área -->
<div class="modal fade" id="modalAltaArea" tabindex="-1" aria-labelledby="modalAltaAreaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalAltaAreaLabel">
                    <i class="fa fa-plus-circle me-1 text-primary"></i> Registrar Nueva Área de Abastecimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('areas_abastecimiento.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="modal_nombre" class="form-label small fw-semibold text-dark">Nombre del Área <span class="text-danger">*</span></label>
                        <input type="text" id="modal_nombre" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" placeholder="Ej. Farmacia Central, Urgencias, Almacén General..." required>
                        <div id="feedback_modal_nombre" class="invalid-feedback small">
                            @error('nombre') {{ $message }} @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modal_siglas" class="form-label small fw-semibold text-dark">Siglas (Opcional)</label>
                        <input type="text" id="modal_siglas" name="siglas" class="form-control form-control-sm @error('siglas') is-invalid @enderror" value="{{ old('siglas') }}" placeholder="Ej. FARM, URG, ALM...">
                        @error('siglas')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">Guardar Área</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/css/peticion_insumos/areas_abastecimiento/areas_abastecimiento.css', 'resources/js/peticion_insumos/areas_abastecimiento/areas_abastecimiento.js'])
@endpush
@endsection
