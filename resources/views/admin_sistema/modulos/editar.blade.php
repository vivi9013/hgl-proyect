@extends('layouts.app')

@section('title', 'Editar Módulo - Hospital General')

@section('content')
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif

<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pencil-square-o text-primary me-2"></i>Editar Módulo
            </h1>
            <p class="text-muted mb-0">Actualización de datos del módulo: <strong>{{ $modulo->nombre }}</strong></p>
        </div>
        <a href="{{ route('modulos.index') }}"
           class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
            <i class="fa fa-arrow-left me-2"></i>Volver al Listado
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-cube text-secondary me-2"></i>Datos del Módulo
                    </h5>
                </div>
                <div class="card-body px-4 pb-4">

                    <form id="formEditarModulo"
                          action="{{ route('modulos.update', $modulo->id) }}"
                          method="POST" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Nombre --}}
                            <div class="col-12 col-md-6">
                                <label for="nombre" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-tag me-1 text-dark"></i> Nombre del Módulo:
                                </label>
                                <input type="text" name="nombre" id="nombre"
                                       class="form-control border-gray-300 shadow-sm @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $modulo->nombre) }}"
                                       placeholder="Nombre del módulo" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Carpeta --}}
                            <div class="col-12 col-md-6">
                                <label for="carpeta" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-folder-o me-1 text-dark"></i> Carpeta:
                                </label>
                                <input type="text" name="carpeta" id="carpeta"
                                       class="form-control border-gray-300 shadow-sm @error('carpeta') is-invalid @enderror"
                                       value="{{ old('carpeta', $modulo->carpeta) }}"
                                       placeholder="nombre-carpeta" required>
                                @error('carpeta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Categoría --}}
                            <div class="col-12 col-md-6">
                                <label for="id_CategoriaModulo" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-sitemap me-1 text-dark"></i> Categoría:
                                </label>
                                <select name="id_CategoriaModulo" id="id_CategoriaModulo"
                                        class="form-select border-gray-300 shadow-sm @error('id_CategoriaModulo') is-invalid @enderror"
                                        required>
                                    <option value="">— Selecciona una categoría —</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_CategoriaModulo }}"
                                            {{ old('id_CategoriaModulo', $modulo->id_CategoriaModulo) == $cat->id_CategoriaModulo ? 'selected' : '' }}>
                                            {{ $cat->categoria }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_CategoriaModulo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Color de Caja --}}
                            <div class="col-12 col-md-6">
                                <label for="color" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-paint-brush me-1 text-dark"></i> Color de Caja:
                                </label>
                                <select name="color" id="color"
                                        class="form-select border-gray-300 shadow-sm" required>
                                    @foreach([
                                        'red'=>'Rojo','yellow'=>'Amarillo','aqua'=>'Aqua',
                                        'blue'=>'Azul','light-blue'=>'Azul Claro','green'=>'Verde',
                                        'navy'=>'Militar','teal'=>'Verde Azulado','olive'=>'Verde Olivo',
                                        'lime'=>'Lima','orange'=>'Naranja','fuchsia'=>'Fucsia',
                                        'purple'=>'Morado','maroon'=>'Granada','black'=>'Negro',
                                        'red-active'=>'Rojo Activo','green-active'=>'Verde Activo',
                                        'blue-active'=>'Azul Activo','navy-active'=>'Militar Activo'
                                    ] as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ old('color', $modulo->color) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Icono Font Awesome --}}
                            <div class="col-12 col-md-6">
                                <label for="icono" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-smile-o me-1 text-dark"></i> Icono Font Awesome:
                                </label>
                                <input type="text" name="icono" id="icono"
                                       class="form-control border-gray-300 shadow-sm @error('icono') is-invalid @enderror"
                                       value="{{ old('icono', $modulo->icono) }}"
                                       placeholder="fa fa-cube" required>
                                @error('icono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Creador --}}
                            <div class="col-12 col-md-6">
                                <label for="creador" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user-o me-1 text-dark"></i> Creador:
                                </label>
                                <input type="text" name="creador" id="creador"
                                       class="form-control border-gray-300 shadow-sm @error('creador') is-invalid @enderror"
                                       value="{{ old('creador', $modulo->creador) }}"
                                       placeholder="Autor del módulo" required>
                                @error('creador')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Descripción --}}
                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-align-left me-1 text-dark"></i> Descripción:
                                </label>
                                <input type="text" name="descripcion" id="descripcion"
                                       class="form-control border-gray-300 shadow-sm @error('descripcion') is-invalid @enderror"
                                       value="{{ old('descripcion', $modulo->descripcion) }}"
                                       placeholder="Descripción general del módulo" required>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('modulos.index') }}"
                               class="btn btn-light py-2 rounded-pill shadow-sm">
                                <i class="fa fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary py-2 rounded-pill shadow-sm">
                                <i class="fa fa-save me-2"></i>Actualizar Módulo
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Lateral: Vista previa + Acciones rápidas --}}
        <div class="col-12 col-lg-4">

            {{-- Vista Previa del Módulo --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-eye text-secondary me-2"></i>Vista Previa
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="p-3 rounded-3 bg-light d-flex align-items-center gap-3" id="previewModulo">
                        <i id="previewIcono" class="{{ $modulo->icono }} fa-2x text-primary"></i>
                        <div>
                            <span class="fw-bold text-dark" id="previewNombre">{{ $modulo->nombre }}</span><br>
                            <small class="text-muted" id="previewDesc">{{ $modulo->descripcion }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones Rápidas --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-bolt text-secondary me-2"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 d-grid gap-2">
                    <a href="{{ route('modulos.proyectos', $modulo->id) }}"
                       class="btn btn-outline-primary rounded-pill py-2 text-start px-3">
                        <i class="fa fa-laptop me-2"></i>Gestionar Proyectos
                    </a>
                    <a href="{{ route('modulos.perfiles', $modulo->id) }}"
                       class="btn btn-outline-info rounded-pill py-2 text-start px-3">
                        <i class="fa fa-users me-2"></i>Gestionar Perfiles
                    </a>

                    {{-- Toggle de Estado --}}
                    <form action="{{ route('modulos.toggle', $modulo->id) }}"
                          method="POST" id="formToggleEstado">
                        @csrf
                        @method('PATCH')
                        @if($modulo->activo)
                            <button type="submit"
                                    class="btn btn-outline-warning rounded-pill py-2 w-100 text-start px-3"
                                    onclick="return confirm('¿Desactivar este módulo?')">
                                <i class="fa fa-toggle-on me-2"></i>Desactivar Módulo
                            </button>
                        @else
                            <button type="submit"
                                    class="btn btn-outline-success rounded-pill py-2 w-100 text-start px-3"
                                    onclick="return confirm('¿Activar este módulo?')">
                                <i class="fa fa-toggle-off me-2"></i>Activar Módulo
                            </button>
                        @endif
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
    window.ModulosConfig = {
        csrfToken: "{{ csrf_token() }}"
    };
</script>

@vite(['resources/css/modulos/modulos.css', 'resources/js/modulos/modulos.js'])
@endsection
