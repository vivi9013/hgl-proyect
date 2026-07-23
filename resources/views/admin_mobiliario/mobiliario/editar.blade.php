@extends('layouts.app')

@section('title', 'Editar Mobiliario General')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-cubes text-dark me-2"></i>Mobiliario General
            </h1>
            <p class="text-muted mb-0">Modificación de los datos y asignación del mobiliario general</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('mobiliario.index') }}">Mobiliario General</a>
                </li>
                <li class="breadcrumb-item active">Edición</li>
            </ol>
        </nav>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- Formulario de Edición --}}
    <div class="row">
        <div class="col-12 col-lg-10">
            <div class="card shadow-sm border-0 rounded-3 bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-semibold text-white">
                        <i class="fa fa-pencil-square-o me-2 text-white"></i>Actualizar Datos del Mobiliario: {{ $mobiliario->inventario }}
                    </h5>
                </div>
                <form action="{{ route('mobiliario.update', $mobiliario->id) }}" method="POST" autocomplete="off" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body p-4">
                        
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">Información del Activo</h6>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="inventario" class="form-label fw-bold">No. de Inventario *</label>
                                <input type="text" name="inventario" id="inventario" class="form-control @error('inventario') is-invalid @enderror" 
                                       value="{{ old('inventario', $mobiliario->inventario) }}" required>
                                @error('inventario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="id_tipo_mobiliario" class="form-label fw-bold">Tipo Mobiliario *</label>
                                <select name="id_tipo_mobiliario" id="id_tipo_mobiliario" class="form-select @error('id_tipo_mobiliario') is-invalid @enderror" required>
                                    <option value="">-- Seleccione Tipo --</option>
                                    @foreach($tiposMobiliario as $tipo)
                                        <option value="{{ $tipo->id }}" {{ old('id_tipo_mobiliario', $mobiliario->id_tipo_mobiliario) == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->tipo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_tipo_mobiliario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="marca" class="form-label fw-bold">Marca *</label>
                                <input type="text" name="marca" id="marca" class="form-control @error('marca') is-invalid @enderror" 
                                       value="{{ old('marca', $mobiliario->marca) }}" required>
                                @error('marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold">Modelo *</label>
                                <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror" 
                                       value="{{ old('modelo', $mobiliario->modelo) }}" required>
                                @error('modelo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label fw-bold">No. de Serie</label>
                                <input type="text" name="serie" id="serie" class="form-control @error('serie') is-invalid @enderror" 
                                       value="{{ old('serie', $mobiliario->serie) }}">
                                @error('serie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="id_area" class="form-label fw-bold">Área Asignada *</label>
                                <select name="id_area" id="id_area" class="form-select @error('id_area') is-invalid @enderror" required>
                                    <option value="">-- Seleccione Área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ old('id_area', $mobiliario->id_area) == $area->id ? 'selected' : '' }}>
                                            {{ $area->area }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="id_departamento" class="form-label fw-bold">Departamento *</label>
                                <select name="id_departamento" id="id_departamento" class="form-select @error('id_departamento') is-invalid @enderror" required>
                                    <option value="">-- Seleccione Departamento --</option>
                                    @foreach($departamentos as $dep)
                                        <option value="{{ $dep->id }}" {{ old('id_departamento', $mobiliario->id_departamento) == $dep->id ? 'selected' : '' }}>
                                            {{ $dep->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_departamento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="id_persona" class="form-label fw-bold">Persona Responsable *</label>
                                <select name="id_persona" id="id_persona" class="form-select @error('id_persona') is-invalid @enderror" required>
                                    <option value="">-- Seleccione Persona --</option>
                                    @foreach($personas as $per)
                                        <option value="{{ $per->id }}" {{ old('id_persona', $mobiliario->id_persona) == $per->id ? 'selected' : '' }}>
                                            {{ $per->nombre }} {{ $per->ap_paterno }} {{ $per->ap_materno }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-bold">Descripción *</label>
                                <input type="text" name="descripcion" id="descripcion" class="form-control @error('descripcion') is-invalid @enderror" 
                                       value="{{ old('descripcion', $mobiliario->descripcion) }}" required>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="otros" class="form-label fw-bold">Observaciones / Detalles adicionales</label>
                                <textarea name="otros" id="otros" rows="3" class="form-control">{{ old('otros', $mobiliario->otros) }}</textarea>
                            </div>
                        </div>

                    </div>
                    
                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('mobiliario.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                            <i class="fa fa-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" id="btnGuardar" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" style="border: 1.5px solid #000;">
                            <i class="fa fa-save me-1 text-white"></i> Actualizar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/mobiliario/mobiliario.css'])
@endpush
