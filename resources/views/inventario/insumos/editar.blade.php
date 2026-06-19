@extends('layouts.app')

@section('title', 'Editar Insumo')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-list text-primary me-2"></i>Insumos
            </h1>
            <p class="text-muted mb-0">Actualización / Edición de Insumo</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('insumos.index') }}">Insumos</a>
                </li>
                <li class="breadcrumb-item active">Edición</li>
            </ol>
        </nav>
    </div>

    {{-- ── Formulario de Edición ── --}}
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="fa fa-pencil-square-o me-2"></i>Actualizar datos del Insumo</h5>
                </div>
                <form action="{{ route('insumos.update', $insumo->id_insumo) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Id del insumo oculto para verificación JS --}}
                    <input type="hidden" id="insumo_id" value="{{ $insumo->id_insumo }}">

                    <div class="card-body p-4">
                        <div class="row g-3">
                            
                            {{-- Clave del Insumo --}}
                            <div class="col-12">
                                <label for="clave" class="form-label fw-semibold text-secondary">Clave del Insumo: <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="clave"
                                    id="clave"
                                    class="form-control @error('clave') is-invalid @enderror"
                                    value="{{ old('clave', $insumo->clave) }}"
                                    placeholder="Coloque la clave del insumo"
                                    autocomplete="off"
                                    maxlength="255"
                                    required
                                >
                                <div id="feedbackDisponibilidad" class="mt-1 small"></div>
                                <div id="loadingSpinner" class="mt-1 small text-muted" style="display:none;">
                                    <i class="fa fa-spinner fa-spin me-1"></i>Verificando...
                                </div>
                                @error('clave')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Descripción --}}
                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-semibold text-secondary">Descripción: <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="descripcion"
                                    id="descripcion"
                                    class="form-control @error('descripcion') is-invalid @enderror"
                                    value="{{ old('descripcion', $insumo->descripcion) }}"
                                    placeholder="Coloque la descripción del insumo"
                                    autocomplete="off"
                                    required
                                >
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tipo --}}
                            <div class="col-12">
                                <label for="tipo" class="form-label fw-semibold text-secondary">Tipo de Insumo: <span class="text-danger">*</span></label>
                                <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar tipo --</option>
                                    <option value="Material de curación" {{ old('tipo', $insumo->tipo) === 'Material de curación' ? 'selected' : '' }}>Material de curación</option>
                                    <option value="Medicamento" {{ old('tipo', $insumo->tipo) === 'Medicamento' ? 'selected' : '' }}>Medicamento</option>
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('insumos.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                            <i class="fa fa-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" id="btnGuardar" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-1"></i> Actualizar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/insumos/insumos.css', 'resources/js/inventario/insumos/insumos.js'])
@endpush
