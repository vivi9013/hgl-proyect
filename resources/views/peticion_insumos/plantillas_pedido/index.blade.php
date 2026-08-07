@extends('layouts.app')

@section('title', 'Plantillas de Pedido - Petición de Insumos')

@push('styles')
@vite(['resources/css/peticion_insumos/plantillas_pedido/plantillas_pedido.css'])
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold text-dark">
                <i class="bi bi-clipboard2-check me-2 text-primary"></i>Plantillas de Pedido
            </h2>
            <small class="text-muted">
                Formatos de pedido de insumos por área de abastecimiento
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('plantillas_pedido.reportes') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-earmark-text me-1"></i> Reportes
            </a>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearPlantilla">
                <i class="bi bi-plus-lg me-1"></i> Nueva Plantilla
            </button>
        </div>
    </div>

    {{-- Barra de Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form id="form-filtros-plantillas" class="row g-2 align-items-end">
                {{-- Buscar --}}
                <div class="col-12 col-md-4">
                    <label for="buscar-plantilla" class="form-label small fw-bold text-secondary mb-1">
                        Buscar Plantilla / Área / Subárea
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscar-plantilla" name="buscar" class="form-control"
                               value="{{ $buscar }}" placeholder="Nombre...">
                    </div>
                </div>

                {{-- Filtro Área --}}
                <div class="col-12 col-md-3">
                    <label for="filter-area" class="form-label small fw-bold text-secondary mb-1">Área</label>
                    <select id="filter-area" class="form-select form-select-sm">
                        <option value="">— Todas las Áreas —</option>
                        @foreach($todasAreas as $area)
                            <option value="{{ $area->id_area_abastecimiento }}"
                                {{ $idArea == $area->id_area_abastecimiento ? 'selected' : '' }}>
                                {{ $area->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro Subárea --}}
                <div class="col-12 col-md-3">
                    <label for="filter-subarea" class="form-label small fw-bold text-secondary mb-1">Subárea</label>
                    <select id="filter-subarea" class="form-select form-select-sm">
                        <option value="">— Todas las Subáreas —</option>
                        @foreach($subareas as $subarea)
                            <option value="{{ $subarea->id_subarea_abastecimiento }}"
                                {{ $idSubarea == $subarea->id_subarea_abastecimiento ? 'selected' : '' }}>
                                {{ $subarea->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro Estatus --}}
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Estatus</label>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input filter-status-checkbox" type="checkbox"
                                   id="status-activo" value="Activo"
                                   {{ in_array('Activo', (array)$status) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="status-activo">Activa</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input filter-status-checkbox" type="checkbox"
                                   id="status-inactivo" value="Inactivo"
                                   {{ in_array('Inactivo', (array)$status) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="status-inactivo">Inactiva</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Contenedor Principal de la Tabla AJAX --}}
    <div id="contenedor-tabla-plantillas"
         data-endpoint="{{ route('plantillas_pedido.index') }}">
        @include('peticion_insumos.plantillas_pedido.partials.tabla')
    </div>
</div>

{{-- Modal: Crear Plantilla --}}
<div class="modal fade" id="modalCrearPlantilla" tabindex="-1" aria-labelledby="modalCrearPlantillaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('plantillas_pedido.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalCrearPlantillaLabel">
                        <i class="bi bi-plus-circle me-1 text-primary"></i> Nueva Plantilla de Pedido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold small">Nombre de la Plantilla <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                               id="nombre" name="nombre" value="{{ old('nombre') }}"
                               placeholder="Ej. Pedido Semanal Farmacia" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-bold small">Descripción (opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                                  placeholder="Descripción breve de la plantilla...">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="modal_id_area_abastecimiento" class="form-label fw-bold small">Área de Abastecimiento <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_area_abastecimiento') is-invalid @enderror"
                                id="modal_id_area_abastecimiento" name="id_area_abastecimiento" required>
                            <option value="">-- Seleccionar Área --</option>
                            @foreach($todasAreas as $area)
                                <option value="{{ $area->id_area_abastecimiento }}"
                                    {{ old('id_area_abastecimiento') == $area->id_area_abastecimiento ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_area_abastecimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="modal_id_subarea_abastecimiento" class="form-label fw-bold small">Subárea (opcional)</label>
                        <select class="form-select @error('id_subarea_abastecimiento') is-invalid @enderror"
                                id="modal_id_subarea_abastecimiento" name="id_subarea_abastecimiento">
                            <option value="">-- Sin subárea específica --</option>
                            @foreach($subareas as $subarea)
                                <option value="{{ $subarea->id_subarea_abastecimiento }}"
                                    {{ old('id_subarea_abastecimiento') == $subarea->id_subarea_abastecimiento ? 'selected' : '' }}>
                                    {{ $subarea->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_subarea_abastecimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="modal_id_area_almacen" class="form-label fw-bold small">Área de Almacén (opcional)</label>
                        <select class="form-select @error('id_area_almacen') is-invalid @enderror"
                                id="modal_id_area_almacen" name="id_area_almacen">
                            <option value="">-- Sin almacén específico --</option>
                            @foreach($areasAlmacen as $almacen)
                                <option value="{{ $almacen->id_area_almacen }}"
                                    {{ old('id_area_almacen') == $almacen->id_area_almacen ? 'selected' : '' }}>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_area_almacen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Guardar Plantilla</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Editar Plantilla --}}
<div class="modal fade" id="modalEditarPlantilla" tabindex="-1" aria-labelledby="modalEditarPlantillaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditarPlantilla" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalEditarPlantillaLabel">
                        <i class="bi bi-pencil-square me-1 text-warning"></i> Editar Plantilla
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label fw-bold small">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_descripcion" class="form-label fw-bold small">Descripción (opcional)</label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_id_area" class="form-label fw-bold small">Área <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_id_area" name="id_area_abastecimiento" required>
                            <option value="">-- Seleccionar Área --</option>
                            @foreach($todasAreas as $area)
                                <option value="{{ $area->id_area_abastecimiento }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_id_subarea" class="form-label fw-bold small">Subárea (opcional)</label>
                        <select class="form-select" id="edit_id_subarea" name="id_subarea_abastecimiento">
                            <option value="">-- Sin subárea --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_id_area_almacen" class="form-label fw-bold small">Área de Almacén (opcional)</label>
                        <select class="form-select" id="edit_id_area_almacen" name="id_area_almacen">
                            <option value="">-- Sin almacén específico --</option>
                            @foreach($areasAlmacen as $almacen)
                                <option value="{{ $almacen->id_area_almacen }}">{{ $almacen->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning text-white"><i class="bi bi-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/peticion_insumos/plantillas_pedido/plantillas_pedido.js'])
@if($errors->has('nombre') || $errors->has('id_area_abastecimiento') || $errors->has('id_subarea_abastecimiento'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('modalCrearPlantilla'));
        modal.show();
    });
</script>
@endif
@endpush
