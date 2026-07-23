@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa fa-pencil me-2"></i> Editar Área de Abastecimiento
            </h4>
            <p class="text-muted small mb-0">Modifica la información básica del área de abastecimiento seleccionada.</p>
        </div>
        <a href="{{ route('areas_abastecimiento.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fa fa-arrow-left me-1"></i> Regresar al Catálogo
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border shadow-sm">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title fw-bold text-dark mb-0 fs-6">
                        <i class="fa fa-edit me-1 text-primary"></i> Formulario de Edición
                    </h6>
                </div>
                <form action="{{ route('areas_abastecimiento.update', $area->id_area_abastecimiento) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="nombre" class="form-label small fw-semibold text-dark">Nombre del Área <span class="text-danger">*</span></label>
                            <input type="text" id="nombre" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror" value="{{ old('nombre', $area->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-light py-2 d-flex justify-content-end gap-2">
                        <a href="{{ route('areas_abastecimiento.index') }}" class="btn btn-sm btn-outline-secondary px-3">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/css/peticion_insumos/areas_abastecimiento/areas_abastecimiento.css', 'resources/js/peticion_insumos/areas_abastecimiento/areas_abastecimiento.js'])
@endpush
@endsection
