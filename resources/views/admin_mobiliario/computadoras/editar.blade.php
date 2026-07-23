@extends('layouts.app')

@section('title', 'Editar Computadora')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-desktop text-dark me-2"></i>Computadoras (Mobiliario y Equipo)
            </h1>
            <p class="text-muted mb-0">Modificación de equipo de cómputo y especificaciones técnicas</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('computadoras.index') }}">Computadoras</a>
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
                        <i class="fa fa-pencil-square-o me-2 text-white"></i>Actualizar Datos del Equipo: {{ $computadora->inventario }}
                    </h5>
                </div>
                <form action="{{ route('computadoras.update', $computadora->id_computadora) }}" method="POST" novalidate>
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
                            {{-- Sección 1: Datos Generales (Mobiliario) --}}
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">1. Información Administrativa (Mobiliario)</h6>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="inventario" class="form-label fw-bold">No. de Inventario *</label>
                                <input type="text" name="inventario" id="inventario" class="form-control @error('inventario') is-invalid @enderror" 
                                       value="{{ old('inventario', $computadora->inventario) }}" required>
                                @error('inventario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tipo" class="form-label fw-bold">Tipo *</label>
                                <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                    <option value="CPU" {{ old('tipo', $computadora->tipo) == 'CPU' ? 'selected' : '' }}>CPU (Escritorio)</option>
                                    <option value="Laptop" {{ old('tipo', $computadora->tipo) == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="marca" class="form-label fw-bold">Marca *</label>
                                <input type="text" name="marca" id="marca" class="form-control @error('marca') is-invalid @enderror" 
                                       value="{{ old('marca', $computadora->mobiliario ? $computadora->mobiliario->marca : '') }}" required>
                                @error('marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold">Modelo *</label>
                                <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror" 
                                       value="{{ old('modelo', $computadora->mobiliario ? $computadora->mobiliario->modelo : '') }}" required>
                                @error('modelo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label fw-bold">No. de Serie</label>
                                <input type="text" name="serie" id="serie" class="form-control @error('serie') is-invalid @enderror" 
                                       value="{{ old('serie', $computadora->mobiliario ? $computadora->mobiliario->serie : '') }}">
                                @error('serie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="id_area" class="form-label fw-bold">Área Asignada *</label>
                                <select name="id_area" id="id_area" class="form-select @error('id_area') is-invalid @enderror" required>
                                    <option value="">-- Seleccione Área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ old('id_area', $computadora->mobiliario ? $computadora->mobiliario->id_area : '') == $area->id ? 'selected' : '' }}>
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
                                        <option value="{{ $dep->id }}" {{ old('id_departamento', $computadora->mobiliario ? $computadora->mobiliario->id_departamento : '') == $dep->id ? 'selected' : '' }}>
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
                                        <option value="{{ $per->id }}" {{ old('id_persona', $computadora->mobiliario ? $computadora->mobiliario->id_persona : '') == $per->id ? 'selected' : '' }}>
                                            {{ $per->nombre }} {{ $per->ap_paterno }} {{ $per->ap_materno }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-bold">Descripción / Observaciones</label>
                                <textarea name="descripcion" id="descripcion" rows="2" class="form-control">{{ old('descripcion', $computadora->mobiliario ? $computadora->mobiliario->descripcion : '') }}</textarea>
                            </div>

                            {{-- Sección 2: Especificaciones Técnicas (Computadora) --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">2. Especificaciones Técnicas (Computadora)</h6>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="nombre_equipo" class="form-label fw-bold">Nombre del Equipo</label>
                                <input type="text" name="nombre_equipo" id="nombre_equipo" class="form-control" 
                                       value="{{ old('nombre_equipo', $computadora->nombre_equipo) }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="so" class="form-label fw-bold">Sistema Operativo</label>
                                <select name="so" id="so" class="form-select">
                                    <option value="Windows 10" {{ old('so', $computadora->so) == 'Windows 10' ? 'selected' : '' }}>Windows 10</option>
                                    <option value="Windows 11" {{ old('so', $computadora->so) == 'Windows 11' ? 'selected' : '' }}>Windows 11</option>
                                    <option value="Windows 7" {{ old('so', $computadora->so) == 'Windows 7' ? 'selected' : '' }}>Windows 7</option>
                                    <option value="Windows XP" {{ old('so', $computadora->so) == 'Windows XP' ? 'selected' : '' }}>Windows XP</option>
                                    <option value="Linux / Unix" {{ old('so', $computadora->so) == 'Linux / Unix' ? 'selected' : '' }}>Linux / Unix</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="ip" class="form-label fw-bold">Dirección IP</label>
                                <input type="text" name="ip" id="ip" class="form-control" 
                                       value="{{ old('ip', $computadora->ip) }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="ram" class="form-label fw-bold">Memoria RAM (MB)</label>
                                <input type="text" name="ram" id="ram" class="form-control" 
                                       value="{{ old('ram', $computadora->ram) }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="disco_duro" class="form-label fw-bold">Capacidad Disco Duro</label>
                                <input type="text" name="disco_duro" id="disco_duro" class="form-control" 
                                       value="{{ old('disco_duro', $computadora->disco_duro) }}">
                            </div>
                        </div>

                    </div>
                    
                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('computadoras.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
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
    @vite(['resources/css/computadoras/computadoras.css'])
@endpush
