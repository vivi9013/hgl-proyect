@extends('layouts.app')

@section('title', 'Editar Área de Surtimiento')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-tags text-primary me-2"></i>Áreas de Surtimiento
            </h1>
            <p class="text-muted mb-0">Actualización / Edición</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('areas_surtimiento.index') }}">Áreas de Surtimiento</a>
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
                    <h5 class="mb-0 fw-semibold"><i class="fa fa-pencil-square-o me-2"></i>Actualizar datos del área</h5>
                </div>
                <form action="{{ route('areas_surtimiento.update', $area->id_area_surtimiento) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label for="nombre" class="form-label fw-semibold text-secondary">Nombre del área de surtimiento:</label>
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre', $area->nombre) }}"
                                placeholder="Ej. Farmacia Interna, ISSSTE, IMSS..."
                                autocomplete="off"
                                maxlength="255"
                                autofocus
                                required
                            >
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="tipo" class="form-label fw-semibold text-secondary">Tipo de área:</label>
                            <select
                                name="tipo"
                                id="tipo"
                                class="form-control @error('tipo') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Seleccionar --</option>
                                <option value="Interno" {{ old('tipo', $area->tipo) == 'Interno' ? 'selected' : '' }}>Interno</option>
                                <option value="Externo" {{ old('tipo', $area->tipo) == 'Externo' ? 'selected' : '' }}>Externo</option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('areas_surtimiento.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
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
    @vite(['resources/css/inventario/areas_surtimiento/surtimiento.css'])
@endpush
