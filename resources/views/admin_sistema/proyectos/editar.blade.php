@extends('layouts.app')
@section('title', 'Configurar Proyecto - Hospital General')
@section('content')

@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-gestion-proyectos">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-pencil-square-o me-2"></i>Configurar Proyecto
            </h1>
            <p class="text-muted mb-0">
                <strong>{{ $proyecto->proyecto }}</strong> &mdash; Edición y asignación de módulos
            </p>
        </div>
        <a href="{{ route('proyectos.index') }}" class="btn btn-light px-4">
            <i class="fa fa-arrow-left me-2"></i>Volver al Listado
        </a>
    </div>

    <div class="row g-4">

        {{-- Columna principal --}}
        <div class="col-12 col-lg-8">

            {{-- Sección 1: Datos del Proyecto --}}
            <div class="card mb-3">
                <div class="card-header bg-white pt-3 px-4 pb-3" style="cursor:pointer;"
                     data-bs-toggle="collapse" data-bs-target="#collapseDatos"
                     aria-expanded="true" aria-controls="collapseDatos">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-cube fa-fw text-dark"></i>
                            <h5 class="mb-0 fw-bold">Datos del Proyecto</h5>
                        </div>
                        <i class="fa fa-chevron-up text-muted" id="chevronDatos"></i>
                    </div>
                </div>
                <div class="collapse show" id="collapseDatos">
                    <div class="card-body px-4 pb-4 border-top">
                        <form id="formEditarProyecto"
                              action="{{ route('proyectos.update', $proyecto->id_proyecto) }}"
                              method="POST" autocomplete="off">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="proyecto" class="form-label fw-bold text-secondary">Nombre del Proyecto:</label>
                                    <input type="text" name="proyecto" id="proyecto"
                                           class="form-control @error('proyecto') is-invalid @enderror"
                                           value="{{ old('proyecto', $proyecto->proyecto) }}" required autofocus>
                                    @error('proyecto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('proyectos.index') }}" class="btn btn-light py-2">
                                    <i class="fa fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary py-2">
                                    <i class="fa fa-save me-2"></i>Actualizar Información
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sección 2: Asociación de Módulos --}}
            <div class="card mb-3">
                <div class="card-header bg-white pt-3 px-4 pb-3" style="cursor:pointer;"
                     data-bs-toggle="collapse"
                     data-bs-target="#collapseModulos"
                     aria-expanded="{{ request()->query('seccion') === 'modulos' ? 'true' : 'false' }}"
                     aria-controls="collapseModulos">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-laptop fa-fw text-dark"></i>
                            <h5 class="mb-0 fw-bold">Asociación de Módulos</h5>
                            <span class="badge bg-dark rounded-pill px-3">{{ $asignadosModulos->count() }} asignados</span>
                        </div>
                        <i class="fa fa-chevron-down text-muted" id="chevronModulos"></i>
                    </div>
                </div>
                <div class="collapse {{ request()->query('seccion') === 'modulos' ? 'show' : '' }}" id="collapseModulos">
                    <div class="card-body px-4 pb-4 border-top">
                        <form id="formModulos"
                              action="{{ route('proyectos.modulos.sync', $proyecto->id_proyecto) }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-uppercase" style="font-size:0.75rem;">
                                        <tr>
                                            <th class="ps-3" style="width:60px;">
                                                <input type="checkbox" id="seleccionarTodosModulos" class="form-check-input">
                                            </th>
                                            <th>#</th>
                                            <th>Módulo</th>
                                            <th class="text-center">Icono</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($modulos as $index => $modulo)
                                            @php $estaAsignado = $asignadosModulos->contains($modulo->id); @endphp
                                            <tr class="{{ $estaAsignado ? 'table-success' : '' }}" id="filaModulo{{ $modulo->id }}">
                                                <td class="ps-3">
                                                    <input type="checkbox" name="modulos[]"
                                                           class="form-check-input casilla-modulo"
                                                           value="{{ $modulo->id }}"
                                                           id="casillaModulo{{ $modulo->id }}"
                                                           {{ $estaAsignado ? 'checked' : '' }}>
                                                </td>
                                                <td class="text-muted small fw-bold">{{ $index + 1 }}</td>
                                                <td class="fw-semibold">{{ $modulo->nombre }}</td>
                                                <td class="text-center"><i class="fa {{ $modulo->icono }} fa-lg"></i></td>
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
                                                <td colspan="5" class="text-center text-muted py-4">No hay módulos registrados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <div class="d-flex gap-2">
                                    <button type="button" id="btnSeleccionarTodosModulos" class="btn btn-light btn-sm px-3">Marcar todos</button>
                                    <button type="button" id="btnDeseleccionarTodosModulos" class="btn btn-light btn-sm px-3">Desmarcar todos</button>
                                </div>
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="fa fa-save me-2"></i>Guardar Asignación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        {{-- Columna lateral: Toggle de Estado --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa fa-bolt me-2"></i>Estado del Proyecto
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-3 p-3 rounded-3 {{ $proyecto->activo ? 'bg-success' : 'bg-secondary' }} bg-opacity-10">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa {{ $proyecto->activo ? 'fa-check-circle text-success' : 'fa-times-circle text-secondary' }} fa-lg"></i>
                            <span class="fw-semibold">{{ $proyecto->activo ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                    </div>
                    <form action="{{ route('proyectos.status', $proyecto->id_proyecto) }}"
                          method="POST"
                          id="formToggleEstado"
                          data-nombre="{{ $proyecto->proyecto }}"
                          data-activo="{{ $proyecto->activo }}">
                        @csrf
                        @method('PATCH')
                        @if($proyecto->activo)
                            <button type="submit" class="btn btn-outline-warning w-100 text-start px-3 py-2">
                                <i class="fa fa-toggle-on me-2"></i>Desactivar Proyecto
                            </button>
                        @else
                            <button type="submit" class="btn btn-outline-success w-100 text-start px-3 py-2">
                                <i class="fa fa-toggle-off me-2"></i>Activar Proyecto
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@vite(['resources/css/proyectos/proyectos.css', 'resources/js/proyectos/proyectos.js'])
@endsection
