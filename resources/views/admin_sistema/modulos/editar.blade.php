@extends('layouts.app')

@section('title', 'Configurar Módulo - Hospital General')

@section('content')
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-gestion-modulos">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pencil-square-o text-primary me-2"></i>Configurar Módulo
            </h1>
            <p class="text-muted mb-0">
                <i class="{{ $modulo->icono }} me-1"></i>
                <strong>{{ $modulo->nombre }}</strong>
                &mdash; Edición, proyectos y perfiles desde una sola pantalla
            </p>
        </div>
        <a href="{{ route('modulos.index') }}"
           class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
            <i class="fa fa-arrow-left me-2"></i>Volver al Listado
        </a>
    </div>

    <div class="row g-4">

        {{-- Columna principal: 3 secciones colapsables --}}
        <div class="col-12 col-lg-8">

            {{-- ── SECCIÓN 1: Datos del Módulo ────────────────────────── --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
                <div class="card-header bg-white border-0 pt-3 px-4 pb-3"
                     style="cursor: pointer;"
                     data-bs-toggle="collapse" data-bs-target="#collapseDatos"
                     aria-expanded="true" aria-controls="collapseDatos">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill p-2">
                                <i class="fa fa-cube fa-fw"></i>
                            </span>
                            <h5 class="mb-0 fw-bold text-dark">Datos del Módulo</h5>
                        </div>
                        <i class="fa fa-chevron-up text-muted collapse-chevron" id="chevronDatos"></i>
                    </div>
                </div>
                <div class="collapse show" id="collapseDatos">
                    <div class="card-body px-4 pb-4 border-top">
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

            {{-- ── SECCIÓN 2: Gestionar Proyectos ─────────────────────── --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
                <div class="card-header bg-white border-0 pt-3 px-4 pb-3"
                     style="cursor: pointer;"
                     data-bs-toggle="collapse" data-bs-target="#collapseProyectos"
                     aria-expanded="false" aria-controls="collapseProyectos">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill p-2">
                                <i class="fa fa-laptop fa-fw"></i>
                            </span>
                            <h5 class="mb-0 fw-bold text-dark">Gestionar Proyectos</h5>
                            <span class="badge bg-success rounded-pill px-3">
                                {{ $asignadosProyectos->count() }} asignados
                            </span>
                            <span class="badge bg-secondary rounded-pill px-3">
                                {{ $proyectos->count() }} disponibles
                            </span>
                        </div>
                        <i class="fa fa-chevron-down text-muted collapse-chevron" id="chevronProyectos"></i>
                    </div>
                </div>
                <div class="collapse" id="collapseProyectos">
                    <div class="card-body px-4 pb-4 border-top">
                        <form id="formProyectos"
                              action="{{ route('modulos.proyectos.sync', $modulo->id) }}"
                              method="POST">
                            @csrf
                            @method('PUT')

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-uppercase text-secondary"
                                           style="font-size: 0.75rem; letter-spacing: 0.05rem;">
                                        <tr>
                                            <th class="ps-3" style="width: 60px;">
                                                <div class="form-check mb-0">
                                                    <input type="checkbox" id="seleccionarTodosProyectos"
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
                                            @php $estaAsignado = $asignadosProyectos->contains($proyecto->id_proyecto); @endphp
                                            <tr class="{{ $estaAsignado ? 'table-success' : '' }}"
                                                id="filaProyecto{{ $proyecto->id_proyecto }}">
                                                <td class="ps-3">
                                                    <div class="form-check mb-0">
                                                        <input type="checkbox"
                                                               name="proyectos[]"
                                                               class="form-check-input casilla-proyecto"
                                                               value="{{ $proyecto->id_proyecto }}"
                                                               id="casillaProy{{ $proyecto->id_proyecto }}"
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
                                    <button type="button" id="btnSeleccionarTodosProyectos"
                                             class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                         <i class="fa fa-check-square-o me-1"></i>Marcar todos
                                     </button>
                                     <button type="button" id="btnDeseleccionarTodosProyectos"
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

            {{-- ── SECCIÓN 3: Asignación de Perfiles ──────────────────── --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
                <div class="card-header bg-white border-0 pt-3 px-4 pb-3"
                     style="cursor: pointer;"
                     data-bs-toggle="collapse" data-bs-target="#collapsePerfiles"
                     aria-expanded="false" aria-controls="collapsePerfiles">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info rounded-pill p-2">
                                <i class="fa fa-users fa-fw"></i>
                            </span>
                            <h5 class="mb-0 fw-bold text-dark">Asignación de Perfiles</h5>
                            <span class="badge bg-success rounded-pill px-3">
                                {{ $asignadosPerfiles->count() }} asignados
                            </span>
                            <span class="badge bg-secondary rounded-pill px-3">
                                {{ $perfiles->count() }} disponibles
                            </span>
                        </div>
                        <i class="fa fa-chevron-down text-muted collapse-chevron" id="chevronPerfiles"></i>
                    </div>
                </div>
                <div class="collapse" id="collapsePerfiles">
                    <div class="card-body px-4 pb-4 border-top">
                        <form id="formPerfiles"
                              action="{{ route('modulos.perfiles.sync', $modulo->id) }}"
                              method="POST">
                            @csrf
                            @method('PUT')

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-uppercase text-secondary"
                                           style="font-size: 0.75rem; letter-spacing: 0.05rem;">
                                        <tr>
                                            <th class="ps-3" style="width: 60px;">
                                                <div class="form-check mb-0">
                                                    <input type="checkbox" id="seleccionarTodosPerfiles"
                                                           class="form-check-input"
                                                           title="Marcar / Desmarcar todos">
                                                </div>
                                            </th>
                                            <th>#</th>
                                            <th>Perfil</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Asignado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($perfiles as $index => $perfil)
                                            @php $estaAsignado = $asignadosPerfiles->contains($perfil->id); @endphp
                                            <tr class="{{ $estaAsignado ? 'table-success' : '' }}"
                                                id="filaPerfil{{ $perfil->id }}">
                                                <td class="ps-3">
                                                    <div class="form-check mb-0">
                                                        <input type="checkbox"
                                                               name="perfiles[]"
                                                               class="form-check-input casilla-perfil"
                                                               value="{{ $perfil->id }}"
                                                               id="casillaPerfil{{ $perfil->id }}"
                                                               {{ $estaAsignado ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td class="text-muted small fw-bold">{{ $index + 1 }}</td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $perfil->perfil ?? $perfil->nombre ?? 'Sin nombre' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if(isset($perfil->activo) && $perfil->activo)
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
                                                    No hay perfiles registrados en el sistema.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <div class="d-flex gap-2">
                                    <button type="button" id="btnSeleccionarTodosPerfiles"
                                            class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        <i class="fa fa-check-square-o me-1"></i>Marcar todos
                                    </button>
                                    <button type="button" id="btnDeseleccionarTodosPerfiles"
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

        </div>

        {{-- Panel Lateral --}}
        <div class="col-12 col-lg-4">

            {{-- Vista Previa del Módulo --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-eye text-secondary me-2"></i>Vista Previa
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="small-box bg-{{ $modulo->color ?? 'blue' }} w-100 mb-0" id="vistaPreviaTarjeta" style="min-height: 100px;">
                        <div class="inner" style="padding: 10px 10px 0 10px;">
                            <h3>&nbsp;</h3>
                            <span class="progress-description fw-bold" id="vistaPreviaNombre">{{ $modulo->nombre }}</span>
                        </div>
                        <div class="icon" style="top: -5px; right: 10px; font-size: 65px;">
                            <i class="{{ $modulo->icono ?? 'fa fa-cube' }}" id="vistaPreviaIcono"></i>
                        </div>
                        <a href="#" class="small-box-footer py-2" style="margin-top: 10px;">
                            Ingresar al Módulo <i class="fa fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                    <ul class="list-unstyled small text-muted mb-0 mt-3">
                        <li class="mb-1"><i class="fa fa-folder-o me-2"></i>Carpeta: <strong id="vistaPreviaCarpetaTexto">{{ $modulo->carpeta }}</strong></li>
                        <li class="mb-1"><i class="fa fa-paint-brush me-2"></i>Color: <strong id="vistaPreviaColorTexto">{{ $modulo->color }}</strong></li>
                        <li><i class="fa fa-user-o me-2"></i>Creador: <strong id="vistaPreviaCreadorTexto">{{ $modulo->creador }}</strong></li>
                    </ul>
                </div>
            </div>

            {{-- Toggle de Estado --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-bolt text-secondary me-2"></i>Estado del Módulo
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="mb-3 p-3 rounded-3 {{ $modulo->activo ? 'bg-success' : 'bg-secondary' }} bg-opacity-10">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa {{ $modulo->activo ? 'fa-check-circle text-success' : 'fa-times-circle text-secondary' }} fa-lg"></i>
                            <span class="fw-semibold">{{ $modulo->activo ? 'Módulo Activo' : 'Módulo Inactivo' }}</span>
                        </div>
                    </div>
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

@vite(['resources/css/modulos/modulos.css', 'resources/js/modulos/modulos.js'])
@endsection
