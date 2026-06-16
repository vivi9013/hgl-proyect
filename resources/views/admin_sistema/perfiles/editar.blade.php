@extends('layouts.app')

@section('title', 'Editar Perfil - Hospital General')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado de Navegación y Título --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inicio') }}" class="text-decoration-none">Panel de Control</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('perfiles.index') }}" class="text-decoration-none">Catálogo de Perfiles</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edición</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pencil-square-o text-primary me-2"></i>Actualización de Perfil
            </h1>
            <p class="text-muted mb-0">Modifique los parámetros y nombre del rol en el sistema</p>
        </div> 
    </div>

    {{-- Formulario de Actualización en Tarjeta Plana --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-edit text-secondary me-2"></i>Datos del Perfil
                    </h5>
                </div>

                <form id="formEditarPerfil" action="{{ route('perfiles.update', $perfil->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body px-4 py-4">
                        <div class="row g-3">

                            {{-- Nombre del Perfil --}}
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold text-secondary">
                                        <i class="fa fa-user me-1 text-dark"></i> Nombre del perfil:
                                    </label>
                                    <input type="text" name="nombre" id="nombre" 
                                           class="form-control border-gray-300 shadow-sm @error('nombre') is-invalid @enderror" 
                                           value="{{ old('nombre', $perfil->nombre) }}" 
                                           placeholder="Coloque el nombre del perfil" 
                                           autofocus required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Descripción --}}
                            <div class="col-12 col-md-8">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label fw-bold text-secondary">
                                        <i class="fa fa-comment-o me-1 text-dark"></i> Descripción detallada:
                                    </label>
                                    <input type="text" name="descripcion" id="descripcion" 
                                           class="form-control border-gray-300 shadow-sm @error('descripcion') is-invalid @enderror" 
                                           value="{{ old('descripcion', $perfil->descripcion) }}" 
                                           placeholder="Coloque la descripción detallada del perfil" 
                                           required>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Botones de Acción del Formulario --}}
                    <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2 border-top">
                        <a href="{{ route('perfiles.index') }}" class="btn btn-light py-2 rounded-pill shadow-sm">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" id="btnActualizar" class="btn btn-primary py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Actualizar Información
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/perfiles/perfiles.css'])
@endsection
