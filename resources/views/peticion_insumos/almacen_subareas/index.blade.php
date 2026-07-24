@extends('layouts.app')

@section('title', 'Almacén de Subáreas - Petición de Insumos')

@push('styles')
@vite(['resources/css/peticion_insumos/almacen_subareas/almacen_subareas.css'])
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Encabezado del Módulo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold text-dark">
                <i class="bi bi-shop me-2 text-primary"></i>Almacén de Subáreas
            </h2>
            <small class="text-muted">
                Gestión de inventario local, stock y fondo fijo por subárea de abastecimiento hospitalaria
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('almacen_subareas.reportes') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-text me-1"></i> Reportes
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearAlmacen">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Almacén de Subárea
            </button>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form id="form-filtros-almacen" class="row g-3 align-items-center">
                <div class="col-12 col-md-4">
                    <label for="buscar-almacen" class="form-label small fw-bold text-secondary mb-1">Buscar Insumo / Área / Subárea</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscar-almacen" name="buscar" class="form-control" value="{{ $buscar }}" placeholder="Clave, descripción, área...">
                    </div>
                </div>
                
                <div class="col-12 col-md-3">
                    <label for="filter-area" class="form-label small fw-bold text-secondary mb-1">Área de Abastecimiento</label>
                    <select id="filter-area" name="id_area_abastecimiento" class="form-select form-select-sm">
                        <option value="">-- Todas las Áreas --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id_area_abastecimiento }}" {{ $idArea == $area->id_area_abastecimiento ? 'selected' : '' }}>
                                {{ $area->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="filter-subarea" class="form-label small fw-bold text-secondary mb-1">Subárea de Abastecimiento</label>
                    <select id="filter-subarea" name="id_subarea_abastecimiento" class="form-select form-select-sm" {{ empty($idArea) ? 'disabled' : '' }}>
                        <option value="">-- Selecciona un Área primero --</option>
                        @foreach($subareas as $subarea)
                            <option value="{{ $subarea->id_subarea_abastecimiento }}" {{ $idSubarea == $subarea->id_subarea_abastecimiento ? 'selected' : '' }}>
                                {{ $subarea->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Estatus</label>
                    <div class="d-flex gap-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input filter-status-checkbox" type="checkbox" name="status[]" value="Activo" id="st-activo" checked>
                            <label class="form-check-label small" for="st-activo">Activo</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input filter-status-checkbox" type="checkbox" name="status[]" value="Inactivo" id="st-inactivo">
                            <label class="form-check-label small" for="st-inactivo">Inactivo</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla AJAX -->
    <div id="contenedor-tabla-almacenes"
         data-endpoint="{{ route('almacen_subareas.index') }}">
        @if(empty($idArea) && empty($idSubarea) && empty($buscar))
            {{-- Estado inicial: el usuario debe seleccionar filtros primero --}}
            <div class="text-center py-5 text-muted">
                <i class="bi bi-funnel" style="font-size: 3rem;"></i>
                <h5 class="mt-3 fw-semibold">Selecciona un filtro para ver los registros</h5>
                <p class="mb-0 small">Elige un <strong>Área de Abastecimiento</strong> en los filtros de arriba<br>y luego una <strong>Subárea</strong> para consultar su almacén.</p>
            </div>
        @else
            @include('peticion_insumos.almacen_subareas.partials.tabla')
        @endif
    </div>
</div>

<!-- Modal para Crear Almacén de Subárea -->
<div class="modal fade" id="modalCrearAlmacen" tabindex="-1" aria-labelledby="modalCrearAlmacenLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('almacen_subareas.guardar') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalCrearAlmacenLabel">
                        <i class="bi bi-plus-circle me-1 text-primary"></i> Registrar Almacén de Subárea
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_area_abastecimiento" class="form-label fw-bold small">Área de Abastecimiento <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_area_abastecimiento') is-invalid @enderror" id="id_area_abastecimiento" name="id_area_abastecimiento" required>
                            <option value="">-- Seleccionar Área --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id_area_abastecimiento }}" {{ old('id_area_abastecimiento') == $area->id_area_abastecimiento ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_area_abastecimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="id_subarea_abastecimiento" class="form-label fw-bold small">Subárea de Abastecimiento <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_subarea_abastecimiento') is-invalid @enderror" id="id_subarea_abastecimiento" name="id_subarea_abastecimiento" required>
                            <option value="">-- Seleccionar Subárea --</option>
                            @foreach($subareas as $subarea)
                                <option value="{{ $subarea->id_subarea_abastecimiento }}" {{ old('id_subarea_abastecimiento') == $subarea->id_subarea_abastecimiento ? 'selected' : '' }}>
                                    {{ $subarea->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_subarea_abastecimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Guardar Almacén</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Asignar Insumo a Subárea -->
<div class="modal fade" id="modalAgregarInsumo" tabindex="-1" aria-labelledby="modalAgregarInsumoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAgregarInsumo" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAgregarInsumoLabel">
                        <i class="bi bi-box-seam me-1 text-primary"></i> Asignar Insumo al Almacén
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_insumo" class="form-label fw-bold small">Seleccionar Insumo <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_insumo" name="id_insumo" required>
                            <option value="">-- Seleccionar Insumo --</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id_insumo }}">
                                    [{{ $insumo->clave }}] {{ $insumo->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="cantidad" class="form-label fw-bold small">Cantidad Inicial (Stock) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" value="0" min="0" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="fondo_fijo" class="form-label fw-bold small">Fondo Fijo (Meta) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="fondo_fijo" name="fondo_fijo" value="0" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Asignar Insumo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/peticion_insumos/almacen_subareas/almacen_subareas.js'])
@if(session('hasFormErrors'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('modalCrearAlmacen'));
        modal.show();
    });
</script>
@endif
@endpush
