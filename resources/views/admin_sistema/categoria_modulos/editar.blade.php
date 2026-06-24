@extends('layouts.app')

@section('title', 'Editar Categoría de Módulos - Hospital General')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado de Navegación y Título --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inicio') }}" class="text-decoration-none">Panel de Control</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('categoria_modulos.index') }}" class="text-decoration-none">Categoría de Módulos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edición</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pencil-square-o text-primary me-2"></i>Actualización de Categoría
            </h1>
            <p class="text-muted mb-0">Modifica los parámetros de configuración y asignación del módulo</p>
        </div> 
    </div>

    {{-- Formulario de Actualización en Tarjeta Plana --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-edit text-secondary me-2"></i>Datos de la Categoría
                    </h5>
                </div>

                {{-- Enviamos por PATCH mapeando al ID correspondiente --}}
                <form id="formEditarCategoria" action="{{ route('categoria_modulos.update', $categoria->id_CategoriaModulo) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PATCH')
                    
                    <div class="card-body px-4 py-4">
                        <div class="row g-3">

                            {{-- Nombre de la Categoría --}}
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label for="categoria" class="form-label fw-bold text-secondary">
                                        <i class="fa fa-tag me-1 text-dark"></i> Nombre de la categoría:
                                    </label>
                                    <input type="text" name="categoria" id="categoria" 
                                           class="form-control border-gray-300 shadow-sm" 
                                           value="{{ old('categoria', $categoria->categoria) }}" 
                                           placeholder="Coloque el nombre de la categoría del módulo" 
                                           autofocus required>
                                </div>
                            </div>

                            {{-- Proyecto --}}
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label for="proyecto" class="form-label fw-bold text-secondary">
                                        <i class="fa fa-laptop me-1 text-dark"></i> Proyecto:
                                    </label>
                                    <input type="text" name="proyecto" id="proyecto" 
                                           class="form-control border-gray-300 shadow-sm" 
                                           value="{{ old('proyecto', $categoria->proyecto) }}" 
                                           placeholder="Coloque el nombre del proyecto respecto a la categoría" 
                                           required>
                                </div>
                            </div>

                            {{-- Panel Abierto (Optimizado sin duplicar opciones) --}}
                            <div class="col-12 col-md-2">
                                <div class="mb-3">
                                    <label for="colapsado" class="form-label fw-bold text-secondary">
                                        <i class="fa fa-folder-open-o me-1 text-dark"></i> Panel Abierto:
                                    </label>
                                    <select name="colapsado" id="colapsado" class="form-select border-gray-300 shadow-sm" required>
                                        <option value="no" {{ old('colapsado', $categoria->colapsado) == 'no' ? 'selected' : '' }}>Sí</option>
                                        <option value="si" {{ old('colapsado', $categoria->colapsado) == 'si' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Orden / Posición --}}
                            <div class="col-12 col-md-2">
                                <div class="mb-3">
                                    <label for="orden" class="form-label fw-bold text-secondary">
                                        <i class="fa fa-sort me-1 text-dark"></i> Orden / Posición:
                                    </label>
                                    <input type="number" name="orden" id="orden" 
                                           class="form-control border-gray-300 shadow-sm @error('orden') is-invalid @enderror" 
                                           value="{{ old('orden', $categoria->orden) }}" 
                                           min="1" required>
                                    @error('orden')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Botones de Acción del Formulario --}}
                    <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2 border-top">
                        <a href="{{ route('categoria_modulos.index') }}" class="btn btn-light py-2 rounded-pill shadow-sm">
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

{{-- Inyección modularizada de estilos y comportamientos JS específicos para la edición --}}
@vite(['resources/css/categoria_modulos/categoria.css', 'resources/js/categoria_modulos/categoria_edit.js'])
@endsection