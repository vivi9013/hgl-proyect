@extends('layouts.app')
@section('title', 'Proyectos del Sistema - Hospital General')
@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-gestion-proyectos">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-folder me-2"></i>Proyectos
            </h1>
            <p class="text-muted mb-0">Catálogo</p>
        </div>
    </div>

    {{-- Fila de acciones --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card p-4 h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa fa-info-circle fa-lg text-dark"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Registra un nuevo proyecto</h6>
                        <p class="text-muted small mb-0">Complete la información del proyecto a registrar dentro de la plataforma.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card p-4 h-100 d-flex justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <button type="button"
                            class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap"
                            data-bs-toggle="modal" data-bs-target="#modalAltaProyecto">
                        <i class="fa fa-plus-circle me-2"></i>Registrar Nuevo Proyecto
                    </button>
                    <a href="{{ route('proyectos.reportes') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i>Reportes
                    </a>
                    <a href="{{ route('proyectos.graficas') }}" class="btn btn-outline-success px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-bar-chart me-2 text-success"></i>Gráficas
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de alta --}}
    <div class="modal fade" id="modalAltaProyecto" tabindex="-1" aria-labelledby="modalAltaProyectoLabel" aria-hidden="true" @if($errors->any()) data-auto-open="true" @endif>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-alta-proyecto">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalAltaProyectoLabel">
                        <i class="fa fa-edit me-2"></i>Registrar Nuevo Proyecto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formularioAltaProyecto" action="{{ route('proyectos.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="proyecto" class="form-label fw-bold text-secondary">Nombre del proyecto:</label>
                                <input type="text" name="proyecto" id="proyecto"
                                       class="form-control @error('proyecto') is-invalid @enderror"
                                       value="{{ old('proyecto') }}"
                                       placeholder="Coloque el nombre del proyecto" required autofocus>
                                @error('proyecto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 border" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardarProyecto" class="btn btn-primary px-4 py-2">
                            <i class="fa fa-save me-2"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tabla principal --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fa fa-list-ul me-2"></i>Lista de proyectos
                            </h5>
                            <span class="badge bg-dark rounded-pill px-3 py-2" id="totalProyectos">
                                {{ $proyectos->total() }} Registros
                            </span>
                        </div>
                        <div class="input-group search-group">
                            <input type="search" id="busqueda-global" class="form-control bg-light border-0"
                                   placeholder="Buscar proyecto...">
                            <span class="input-group-text bg-light border-0">
                                <i class="fa fa-search text-dark"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase thead-proyectos">
                                <tr>
                                    <th class="text-center" style="width:50px;">#</th>
                                    <th class="text-center" style="width:80px;">Editar</th>
                                    <th class="text-center" style="width:150px;">Agregar módulos</th>
                                    <th>Proyecto</th>
                                    <th class="text-center">Total módulos</th>
                                    <th class="text-center" style="width:100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaProyectos">
                                @include('admin_sistema.proyectos.partials.tabla')
                            </tbody>
                            <tfoot class="table-light text-uppercase tfoot-proyectos">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Agregar módulos</th>
                                    <th>Proyecto</th>
                                    <th class="text-center">Total módulos</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $proyectos->firstItem() ?? 0 }} a {{ $proyectos->lastItem() ?? 0 }} de {{ $proyectos->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de proyectos">
                        <div id="contenedorPaginacion">
                            {{ $proyectos->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>

</div>

@vite(['resources/css/proyectos/proyectos.css', 'resources/js/proyectos/proyectos.js'])
@endsection
