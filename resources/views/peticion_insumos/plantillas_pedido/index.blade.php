@extends('layouts.app')

@section('title', 'Plantillas de Pedido por Área - Petición de Insumos')

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

    {{-- Barra de Búsqueda --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form id="form-filtros-plantillas" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <label for="buscar-area" class="form-label small fw-bold text-secondary mb-1">
                        Buscar Área de Abastecimiento
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscar-area" name="buscar" class="form-control"
                               value="{{ $buscar }}" placeholder="Nombre del área...">
                    </div>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-dark w-100">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Guardar Plantilla</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Asignar Insumo --}}
<div class="modal fade" id="modalAgregarInsumo" tabindex="-1" aria-labelledby="modalAgregarInsumoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAgregarInsumo">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAgregarInsumoLabel">
                        <i class="bi bi-box-seam me-1 text-primary"></i> Asignar Insumo a Plantilla: <span id="nombrePlantillaModal" class="text-primary"></span>
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
                    <div class="mb-3">
                        <label for="cantidad" class="form-label fw-bold small">Cantidad Prestablecida <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" value="1" min="1" required>
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
