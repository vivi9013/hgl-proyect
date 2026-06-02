@extends('layouts.app')
 
@section('title', 'Categoría de Archivos')
 
@section('content')
<div class="container-fluid py-4">
 
    {{-- ── Encabezado + acceso a Reportes (patrón buscador_archivos) ──────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-folder-open text-primary me-2"></i>Categorías de Archivos
            </h1>
            <p class="text-muted mb-0">Catálogo</p>
        </div>
    </div>
 
    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">
 
    {{-- ── Botón de Reportes del Módulo (igual que buscador_archivos) ─────── --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-8"></div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-md-end">
                    <a href="{{ route('categoria_archivos.reportes') }}"
                       class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-sm">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes del Módulo
                    </a>
                </div>
            </div>
        </div>
    </div>
 
    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">
 
    {{-- ── Alertas SweetAlert2 ─────────────────────────────────────────────── --}}
    @if(session('exitog'))
        <div id="alertaExitog"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito"></div>
    @endif
 
    {{-- ── Formulario de Alta ──────────────────────────────────────────────── --}}
    <div class="row mb-4">
        <div class="col-xs-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-plus-circle me-2"></i>Registra una nueva categoría</h5>
                    <button class="btn btn-sm btn-outline-light" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseAlta">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseAlta">
                    <form method="POST" action="{{ route('categoria_archivos.store') }}" novalidate>
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label id="Info" for="categoria" class="form-label">
                                            Nombre de la categoría:
                                        </label>
                                        <input
                                            type="text"
                                            name="categoria"
                                            id="categoria"
                                            class="form-control @error('categoria') is-invalid @enderror"
                                            value="{{ old('categoria') }}"
                                            placeholder="Coloque el nombre de la categoría"
                                            autocomplete="off"
                                            maxlength="255"
                                            autofocus
                                            required
                                        >
                                        <div id="feedbackDisponibilidad" class="mt-1 small"></div>
                                        <div id="loadingSpinner" class="mt-1 small text-muted" style="display:none;">
                                            <i class="fa fa-spinner fa-spin me-1"></i>Verificando...
                                        </div>
                                        @error('categoria')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" id="btnGuardar" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i>Guardar Información
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
 
    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Tabla de Categorías ─────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-list me-2"></i>Lista de categorías</h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseTabla">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseTabla">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tablaCategorias" class="table table-condensed table-bordered table-striped align-middle mb-0">
                                <thead>
                                    <tr class="table-info">
                                        <th>#</th>
                                        <th>Editar</th>
                                        <th>Categoría</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categorias as $index => $cat)
                                        <tr class="{{ $cat->activo == 0 ? 'text-muted fst-italic' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('categoria_archivos.edit', $cat->id_catego_archivos) }}"
                                                   title="Editar">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                            <td>{{ $cat->categoria }}</td>
                                            <td>{{ $cat->fecha_registro }}</td>
                                            <td>{{ $cat->hora_registro }}</td>
                                            <td class="text-center">
                                                <a href="#"
                                                   class="btn-toggle-status"
                                                   data-url="{{ route('categoria_archivos.status', $cat->id_catego_archivos) }}"
                                                   data-nombre="{{ $cat->categoria }}"
                                                   data-activo="{{ $cat->activo }}"
                                                   title="{{ $cat->activo == 1 ? 'Desactivar' : 'Activar' }}">
                                                    @if($cat->activo == 1)
                                                        <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                                    @else
                                                        <i class="fa fa-square-o" aria-hidden="true"></i>
                                                    @endif
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                                No hay categorías registradas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th>#</th>
                                        <th>Editar</th>
                                        <th>Categoría</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Status</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
</div>
@endsection
 
@push('scripts')
    @vite(['resources/css/categoria_archivos/categoria.css', 'resources/js/categoria_archivos/categoria.js'])
@endpush
