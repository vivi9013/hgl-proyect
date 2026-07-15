@extends('layouts.app')

@section('title', 'Editar Monitor')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-television text-dark me-2"></i>Monitores (Mobiliario y Equipo)
            </h1>
            <p class="text-muted mb-0">Modificación de especificaciones técnicas del monitor</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard text-dark"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('monitores.index') }}" class="text-dark">Monitores</a>
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
                        <i class="fa fa-pencil-square-o me-2 text-white"></i>Actualizar Datos del Monitor: {{ $monitor->inventario }}
                    </h5>
                </div>
                <form action="{{ route('monitores.update', $monitor->id_monitor) }}" method="POST" novalidate>
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
                            <div class="col-12 col-md-6">
                                <label for="inventario" class="form-label fw-bold">No. de Inventario</label>
                                <input type="text" class="form-control bg-light text-muted" id="inventario" 
                                       value="{{ $monitor->inventario }}" readonly>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label fw-bold">No. de Serie</label>
                                <input type="text" name="serie" id="serie" class="form-control @error('serie') is-invalid @enderror" 
                                       value="{{ old('serie', $monitor->serie) }}">
                                @error('serie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="marca" class="form-label fw-bold">Marca *</label>
                                <select name="marca" id="marca" class="form-select @error('marca') is-invalid @enderror" required>
                                    @foreach($marcas as $marcaOption)
                                        <option value="{{ $marcaOption }}" {{ old('marca', $monitor->marca) == $marcaOption ? 'selected' : '' }}>
                                            {{ $marcaOption }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold">Modelo *</label>
                                <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror" 
                                       value="{{ old('modelo', $monitor->modelo) }}" required>
                                @error('modelo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="descripcion" class="form-label fw-bold">Descripción</label>
                                <input type="text" name="descripcion" id="descripcion" class="form-control @error('descripcion') is-invalid @enderror" 
                                       value="{{ old('descripcion', $monitor->descripcion) }}">
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tipo" class="form-label fw-bold">Tipo *</label>
                                <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                    @foreach($tipos as $tipoOption)
                                        <option value="{{ $tipoOption }}" {{ old('tipo', $monitor->tipo) == $tipoOption ? 'selected' : '' }}>
                                            {{ $tipoOption }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                    
                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('monitores.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                            <i class="fa fa-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" id="btnGuardar" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm text-white" style="border: 1.5px solid #000; background-color: #2b6cb0;">
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
    @vite(['resources/css/monitores/monitores.css'])
@endpush
