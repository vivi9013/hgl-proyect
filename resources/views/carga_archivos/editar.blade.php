@extends('layouts.app')

@section('title', 'Modificar Archivo - Hospital General')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado del Módulo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pencil text-primary me-2"></i>Modificar Archivo
            </h1>
            <p class="text-muted mb-0">Actualización de datos para el archivo: {{ $archivo->nombre }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
                <li class="breadcrumb-item"><a href="{{ route('carga_archivos.index') }}"><i class="fa fa-upload"></i> Administración de Archivos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Modificar</li>
            </ol>
        </nav>
    </div>

    <!-- Formulario de Edición -->
    <div class="row">
        <div class="col-12 col-lg-6 mx-auto">
            <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-edit text-secondary me-2"></i>Modifica la información del archivo
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <form action="{{ route('carga_archivos.update', $archivo->id_archivo) }}" method="POST" autocomplete="off">
                        @csrf
                        
                        <!-- Nombre del Archivo -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold text-secondary">
                                <i class="fa fa-file-text-o me-1 text-primary"></i> Nombre del archivo:
                            </label>
                            <input type="text" name="nombre" id="nombre" 
                                   class="form-control border-gray-300 shadow-sm" 
                                   placeholder="Coloca el nombre del archivo" 
                                   value="{{ old('nombre', $archivo->nombre) }}" 
                                   required>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Categoría -->
                            <div class="col-12 col-md-8">
                                <div class="form-group">
                                    <label for="tipo" class="form-label fw-bold text-secondary">
                                        <i class="fa fa-folder-open-o me-1 text-primary"></i> Categoría:
                                    </label>
                                    <select name="tipo" id="tipo" class="form-select border-gray-300 shadow-sm" required>
                                        @foreach($categorias as $categoria)
                                            <option value="{{ $categoria->id_catego_archivos }}" {{ old('tipo', $archivo->id_catego) == $categoria->id_catego_archivos ? 'selected' : '' }}>
                                                {{ $categoria->categoria }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Versión del Archivo -->
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="version" class="form-label fw-bold text-secondary">
                                        <i class="fa fa-code-fork me-1 text-primary"></i> Versión:
                                    </label>
                                    <input type="number" name="version" id="version" 
                                           class="form-control border-gray-300 shadow-sm" 
                                           placeholder="Ej. 1" 
                                           min="1" 
                                           value="{{ old('version', $archivo->version_archivo) }}" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="desc" class="form-label fw-bold text-secondary">
                                <i class="fa fa-info-circle me-1 text-primary"></i> Descripción:
                            </label>
                            <textarea name="desc" id="desc" rows="4" 
                                      class="form-control border-gray-300 shadow-sm" 
                                      placeholder="Ingrese una descripción del archivo" 
                                      required>{{ old('desc', $archivo->descripcion_archivo) }}</textarea>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('carga_archivos.index') }}" class="btn btn-light px-4 py-2 rounded-pill border shadow-sm">
                                <i class="fa fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                                <i class="fa fa-save me-2"></i>Actualizar Información
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/carga_archivos/carga.css'])
@endsection
