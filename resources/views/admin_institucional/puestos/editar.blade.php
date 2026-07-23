@extends('layouts.app')

@section('title', 'Editar Puesto - Hospital General')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pencil-square-o text-primary me-2"></i>Editar Puesto
            </h1>
            <p class="text-muted mb-0">Modificar el nombre del puesto seleccionado</p>
        </div>
        <a href="{{ route('puestos.index') }}" class="btn btn-light rounded-pill shadow-sm">
            <i class="fa fa-arrow-left me-2"></i>Volver al catálogo
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-triangle me-2 fs-4"></i>
                <div>
                    <strong>¡Atención!</strong> Por favor corrige los siguientes errores:
                    <ul class="mb-0 mt-1 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-edit text-secondary me-2"></i>Datos del Puesto
                    </h5>
                </div>

                <form action="{{ route('puestos.update', $puesto->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="card-body px-4 py-4">
                        <div class="row g-3">

                            <div class="col-12">
                                <label for="puesto" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-briefcase text-dark me-1"></i> Nombre del Puesto *
                                </label>
                                <input type="text"
                                       name="puesto"
                                       id="puesto"
                                       class="form-control border-gray-300 shadow-sm @error('puesto') is-invalid @enderror"
                                       value="{{ old('puesto', $puesto->puesto) }}"
                                       placeholder="Ej. Médico General, Enfermera Auxiliar..."
                                       required>
                                @error('puesto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <hr class="my-2" style="border-color: rgba(0,0,0,0.1);">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-bold text-secondary small">
                                            <i class="fa fa-calendar text-dark me-1"></i> Fecha de actualización
                                        </label>
                                        <input type="text" class="form-control border-0 bg-light text-muted" value="{{ $puesto->fecha }}" disabled>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold text-secondary small">
                                            <i class="fa fa-clock-o text-dark me-1"></i> Hora de actualización
                                        </label>
                                        <input type="text" class="form-control border-0 bg-light text-muted" value="{{ $puesto->hora }}" disabled>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <a href="{{ route('puestos.index') }}" class="btn btn-light py-2 rounded-pill shadow-sm">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary py-2 rounded-pill shadow-sm" style="border: 1.5px solid #000;">
                            <i class="fa fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@vite(['resources/css/puestos/puestos.css'])
@endsection
