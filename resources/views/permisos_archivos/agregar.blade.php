@extends('layouts.app')

@section('title', 'Asignar Categorías a Trabajador')

@section('content')
<div class="container-fluid py-4">

    {{-- ── 1. Encabezado Dinámico (Guiño al Legacy con Estilo Moderno) ───── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-user-plus text-primary me-2"></i>Acceso a Categorías de Archivos
            </h1>
            <p class="text-muted mb-0">Agregar o quitar registros de permisos por trabajador</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('trabajador_categorias.index') }}">Lista</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Categorías</li>
            </ol>
        </nav>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── 2. Barra de Navegación Interna (Módulo en el Cuerpo) ────────────── --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex align-items-center">
                    <a href="{{ route('trabajador_categorias.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa fa-arrow-left me-1"></i> Volver a la Lista de Trabajadores
                    </a>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── 3. Matriz de Asignación de Permisos ────────────────────────────── --}}
    <div class="row">
        <div class="col-12">
            {{-- Formulario moderno apuntando al método Store/Sync --}}
            <form id="formAsignarCategorias" method="POST" action="{{ route('trabajador_categorias.guardar') }}">
                @csrf
                {{-- Campo oculto para el ID del trabajador ($pro del legacy) --}}
                <input type="hidden" name="id_trabajador" value="{{ $trabajador->id }}">

                <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                    
                    {{-- Cabecera con el Nombre del Trabajador (Réplica exacta del encabezado legacy) --}}
                    <div class="card-header bg-light border-0 py-4 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase font-size-xs text-secondary d-block mb-1 letter-spacing-1 fw-bold">Trabajador</span>
                                <h3 class="h4 mb-0 fw-bold text-dark">
                                    <i class="fa fa-user text-primary opacity-75 me-2"></i>
                                    Trabajador -> ( {{ $trabajador->ap_paterno }} {{ $trabajador->ap_materno }} {{ $trabajador->nombre }} )
                                </h3>
                            </div>
                            <span class="badge bg-white text-primary border border-primary px-3 py-2 rounded-pill fw-bold shadow-sm">
                                <i class="fa fa-id-card-o me-1"></i> ID: {{ $trabajador->id }}
                            </span>
                        </div>
                    </div>

                    {{-- Controles Globales de Selección Rápida (Marcar/Desmarcar todos) --}}
                    <div class="bg-white border-top border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex gap-3">
                            <button type="button" id="btnMarcarTodos" class="btn btn-sm btn-link text-decoration-none fw-bold p-0">
                                <i class="fa fa-check-square"></i> Marcar todos
                            </button>
                            <span class="text-muted">|</span>
                            <button type="button" id="btnDesmarcarTodos" class="btn btn-sm btn-link text-decoration-none fw-bold text-secondary p-0">
                                <i class="fa fa-minus-square"></i> Desmarcar todos
                            </button>
                        </div>
                        <div class="text-muted small fw-medium">
                            <span id="contadorSeleccionados" class="text-primary fw-bold">0</span> de {{ $categorias->count() }} categorías seleccionadas
                        </div>
                    </div>

                    {{-- Tabla de Categorías --}}
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tablaAsignacionPermisos" class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                    <tr>
                                        <th class="ps-4" style="width: 80px;">#</th>
                                        <th>Categorías</th>
                                        <th class="text-center pe-4" style="width: 180px;">Agregar/Quitar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categorias as $index => $cat)
                                        {{-- Evaluamos si el trabajador ya tiene la categoría --}}
                                        @php
                                            $tieneAcceso = $trabajador->categorias->contains($cat->id_catego_archivos);
                                        @endphp
                                        <tr class="fila-categoria {{ $tieneAcceso ? 'table-success-soft' : '' }}">
                                            <td class="ps-4 fw-medium text-secondary">{{ $index + 1 }}</td>
                                            
                                            <td>
                                                <div class="fw-bold text-dark fs-6 label-categoria">
                                                    {{ $cat->categoria }}
                                                </div>
                                                <small class="text-muted">Estatus en catálogo: Activo</small>
                                            </td>

                                            {{-- Control de Checkbox tipo Switch estilizado con Bootstrap 5 --}}
                                            <td class="text-center pe-4">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input 
                                                        class="form-check-input chk-permiso" 
                                                        type="checkbox" 
                                                        name="categorias[]" 
                                                        value="{{ $cat->id_catego_archivos }}" 
                                                        id="cat_{{ $cat->id_catego_archivos }}"
                                                        {{ $tieneAcceso ? 'checked' : '' }}
                                                    >
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-5">
                                                <div class="py-4">
                                                    <i class="fa fa-folder-open-o fa-3x mb-3 text-secondary opacity-40"></i>
                                                    <p class="mb-0 fw-medium text-secondary">No hay categorías registradas en el sistema.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer de la Caja con el botón procesador --}}
                    <div class="card-footer bg-white border-top py-4 px-4 d-flex justify-content-end">
                        <button type="submit" id="btnGuardarPermisos" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm fw-bold">
                            <i class="fa fa-save me-2"></i> Agregar o quitar categoría
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    {{-- Mantenemos el desacoplamiento apuntando a los recursos modales compilados por Vite --}}
    @vite(['resources/css/trabajador_categorias/permisos.css', 'resources/js/trabajador_categorias/permisos.js'])
@endpush