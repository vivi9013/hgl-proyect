@extends('layouts.app')

@section('title', 'Proyectos del Módulo - Hospital General')

@section('content')
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif

<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-laptop text-primary me-2"></i>Asignación de Proyectos
            </h1>
            <p class="text-muted mb-0">
                Módulo: <strong>{{ $modulo->nombre }}</strong> &mdash;
                selecciona los proyectos que tienen acceso a este módulo
            </p>
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
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Proyectos
                        </h5>
                        <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold">
                            {{ $proyectos->count() }} Disponibles
                        </span>
                        <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm fw-bold">
                            {{ $asignados->count() }} Asignados
                        </span>
                    </div>
                </div>

                <div class="card-body px-4 pb-4">
                    <form id="formProyectos"
                          action="{{ route('modulos.proyectos.sync', $modulo->id) }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.05rem;">
                                    <tr>
                                        <th class="ps-3" style="width: 60px;">
                                            <div class="form-check mb-0">
                                                <input type="checkbox" id="checkAll"
                                                       class="form-check-input"
                                                       title="Marcar / Desmarcar todos">
                                            </div>
                                        </th>
                                        <th>#</th>
                                        <th>Proyecto</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Asignado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($proyectos as $index => $proyecto)
                                        @php $estaAsignado = $asignados->contains($proyecto->id_proyecto); @endphp
                                        <tr class="{{ $estaAsignado ? 'table-success' : '' }}" id="rowProyecto{{ $proyecto->id_proyecto }}">
                                            <td class="ps-3">
                                                <div class="form-check mb-0">
                                                    <input type="checkbox"
                                                           name="proyectos[]"
                                                           class="form-check-input check-proyecto"
                                                           value="{{ $proyecto->id_proyecto }}"
                                                           id="chkProy{{ $proyecto->id_proyecto }}"
                                                           {{ $estaAsignado ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-muted small fw-bold">{{ $index + 1 }}</td>
                                            <td>
                                                <span class="fw-semibold text-dark">{{ $proyecto->proyecto }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($proyecto->activo)
                                                    <span class="badge bg-success rounded-pill px-3">Activo</span>
                                                @else
                                                    <span class="badge bg-secondary rounded-pill px-3">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($estaAsignado)
                                                    <i class="fa fa-check-circle text-success fa-lg"></i>
                                                @else
                                                    <i class="fa fa-circle-o text-muted fa-lg"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                                No hay proyectos registrados en el sistema.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <div class="d-flex gap-2">
                                <button type="button" id="btnMarcarTodos"
                                        class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    <i class="fa fa-check-square-o me-1"></i>Marcar todos
                                </button>
                                <button type="button" id="btnDesmarcarTodos"
                                        class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    <i class="fa fa-square-o me-1"></i>Desmarcar todos
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                                <i class="fa fa-save me-2"></i>Guardar Asignación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Lateral --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-cube text-secondary me-2"></i>Datos del Módulo
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="p-3 rounded-3 bg-light d-flex align-items-center gap-3 mb-3">
                        <i class="{{ $modulo->icono }} fa-2x text-primary"></i>
                        <div>
                            <span class="fw-bold text-dark d-block">{{ $modulo->nombre }}</span>
                            <small class="text-muted">{{ $modulo->descripcion }}</small>
                        </div>
                    </div>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-1"><i class="fa fa-folder-o me-2"></i>Carpeta: <strong>{{ $modulo->carpeta }}</strong></li>
                        <li class="mb-1"><i class="fa fa-paint-brush me-2"></i>Color: <strong>{{ $modulo->color }}</strong></li>
                        <li><i class="fa fa-user-o me-2"></i>Creador: <strong>{{ $modulo->creador }}</strong></li>
                    </ul>
                </div>
            </div>

            {{-- Acciones Rápidas --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-bolt text-secondary me-2"></i>Otras Acciones
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 d-grid gap-2">
                    <a href="{{ route('modulos.edit', $modulo->id) }}"
                       class="btn btn-outline-secondary rounded-pill py-2 text-start px-3">
                        <i class="fa fa-pencil me-2"></i>Editar Módulo
                    </a>
                    <a href="{{ route('modulos.perfiles', $modulo->id) }}"
                       class="btn btn-outline-info rounded-pill py-2 text-start px-3">
                        <i class="fa fa-users me-2"></i>Gestionar Perfiles
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.ModulosConfig = { csrfToken: "{{ csrf_token() }}" };
</script>
@vite(['resources/css/modulos/modulos.css', 'resources/js/modulos/modulos.js'])
@endsection
