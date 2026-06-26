@extends('layouts.app')

@section('title', 'Editar Usuario - Hospital General')

@section('content')
<div class="container-fluid py-4 usuarios-edit">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pencil-square-o text-primary me-2"></i>Editar Usuario
            </h1>
            <p class="text-muted mb-0">Modifique los datos de acceso y preferencias del usuario seleccionado</p>
        </div>
    </div>

    {{-- Formulario de Edición --}}
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-0 px-0 pb-3 mb-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark">
                        Usuario: <span class="text-primary">{{ $usuario->persona ? $usuario->persona->ap_paterno . ' ' . $usuario->persona->nombre : $usuario->nombre_usuario }}</span>
                    </h5>
                </div>
                
                <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" id="usuario_id" value="{{ $usuario->id }}">

                    <div class="row g-3">
                        {{-- Nombre de usuario --}}
                        <div class="col-12 col-md-6">
                            <label for="username" class="form-label fw-bold text-secondary">
                                <i class="fa fa-user-circle-o me-1 text-dark"></i> Nombre de usuario:
                            </label>
                            <input type="text" name="nombre" id="username" 
                                   class="form-control border-gray-300 shadow-sm @error('nombre') is-invalid @enderror" 
                                   value="{{ old('nombre', $usuario->nombre_usuario) }}"
                                   placeholder="Nombre de usuario" 
                                   required>
                            <div id="feedbackDisponibilidad" class="mt-1 small fw-semibold"></div>
                            <div id="loadingSpinner" class="spinner-border spinner-border-sm text-primary mt-1" role="status" style="display: none;">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Perfil --}}
                        <div class="col-12 col-md-6">
                            <label for="perfil" class="form-label fw-bold text-secondary">
                                <i class="fa fa-users me-1 text-dark"></i> Selecciona el perfil:
                            </label>
                            <select name="perfil" id="perfil" class="form-select border-gray-300 shadow-sm @error('perfil') is-invalid @enderror" required>
                                @foreach($perfiles as $perf)
                                    <option value="{{ $perf->id }}" {{ $usuario->id_perfil == $perf->id ? 'selected' : '' }}>
                                        {{ $perf->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('perfil')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tema --}}
                        <div class="col-12 col-md-6">
                            <label for="tema" class="form-label fw-bold text-secondary">
                                <i class="fa fa-palette me-1 text-dark"></i> Tema de pantalla:
                            </label>
                            <select name="tema" id="tema" class="form-select border-gray-300 shadow-sm" required>
                                <option value="green" {{ $usuario->tema == 'green' ? 'selected' : '' }}>Verde</option>
                                <option value="black" {{ $usuario->tema == 'black' ? 'selected' : '' }}>Negro</option>
                                <option value="black-light" {{ $usuario->tema == 'black-light' ? 'selected' : '' }}>Negro ligero</option>
                                <option value="blue" {{ $usuario->tema == 'blue' ? 'selected' : '' }}>Azul</option>
                                <option value="blue-light" {{ $usuario->tema == 'blue-light' ? 'selected' : '' }}>Azul ligero</option>
                                <option value="green-light" {{ $usuario->tema == 'green-light' ? 'selected' : '' }}>Verde ligero</option>
                                <option value="yellow" {{ $usuario->tema == 'yellow' ? 'selected' : '' }}>Amarillo</option>
                                <option value="yellow-light" {{ $usuario->tema == 'yellow-light' ? 'selected' : '' }}>Amarillo ligero</option>
                                <option value="red" {{ $usuario->tema == 'red' ? 'selected' : '' }}>Rojo</option>
                                <option value="red-light" {{ $usuario->tema == 'red-light' ? 'selected' : '' }}>Rojo ligero</option>
                                <option value="purple" {{ $usuario->tema == 'purple' ? 'selected' : '' }}>Morado</option>
                                <option value="purple-light" {{ $usuario->tema == 'purple-light' ? 'selected' : '' }}>Morado ligero</option>
                                <option value="pink" {{ $usuario->tema == 'pink' ? 'selected' : '' }}>Rosa</option>
                                <option value="pink-light" {{ $usuario->tema == 'pink-light' ? 'selected' : '' }}>Rosa ligero</option>
                            </select>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('usuarios.index') }}" class="btn btn-light py-2 rounded-pill shadow-sm">
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

@vite(['resources/css/usuarios/usuarios.css', 'resources/js/usuarios/usuarios_edit.js'])
@endsection
