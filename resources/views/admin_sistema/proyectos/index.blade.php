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
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Lista de proyectos
                        </h5>
                    </div>

                    {{-- ── Panel de filtros ────────────────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">
                        <x-filtro-buscar id="filtro-buscar" label="Buscar proyecto" placeholder="Nombre del proyecto..." clase="col-12 col-md-4" />
                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registrar, Reportes, Gráficas) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalAltaProyecto">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Nuevo Proyecto
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('proyectos.graficas') }}"
                               class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-bar-chart me-1"></i>Gráficas
                            </a>
                            <a href="{{ route('proyectos.reportes') }}"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Reportes
                            </a>
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
                    <div class="text-muted small" id="infoPaginacionProyectos">
                        Mostrando {{ $proyectos->firstItem() ?? 0 }} a {{ $proyectos->lastItem() ?? 0 }} de {{ $proyectos->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de proyectos">
                        <div id="paginacionProyectos">
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