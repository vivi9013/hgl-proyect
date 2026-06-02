@extends('layouts.app')

@section('title', 'Áreas de Surtimiento')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-tags text-primary me-2"></i>Áreas de Surtimiento
            </h1>
            <p class="text-muted mb-0">Registro, edición y control de las áreas de surtimiento de medicamentos y material de curación</p>
        </div>
    </div>

    {{-- ── Información del módulo y accesos rápidos ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light p-3 rounded-circle text-primary">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Inventario de Medicamentos y Material de Curación</h6>
                        <p class="text-muted small mb-0">Catálogo de áreas destinadas al surtimiento de insumos (internas y externas)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    <a href="{{ route('areas_surtimiento.imprimir') }}"
                       target="_blank"
                       class="btn btn-outline-secondary px-4 py-2 rounded-pill shadow-sm">
                        <i class="fa fa-print me-2 text-dark"></i> Imprimir Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas SweetAlert2 ── --}}
    @if(session('exitog'))
        <div id="alertaExitog"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito"></div>
    @endif

    {{-- ── Formulario de Alta ── --}}
    <div class="row mb-4">
        <div class="col-xs-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-plus-circle me-2"></i>Registrar nueva área de surtimiento</h5>
                    <button class="btn btn-sm btn-outline-light" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseAlta">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseAlta">
                    <form method="POST" action="{{ route('areas_surtimiento.store') }}" novalidate>
                        @csrf
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="nombre" class="form-label">
                                            Nombre del área:
                                        </label>
                                        <input
                                            type="text"
                                            name="nombre"
                                            id="nombre"
                                            class="form-control @error('nombre') is-invalid @enderror"
                                            value="{{ old('nombre') }}"
                                            placeholder="Ej. Farmacia Interna, ISSSTE, IMSS..."
                                            autocomplete="off"
                                            maxlength="255"
                                            autofocus
                                            required
                                        >
                                        <div id="feedbackDisponibilidad" class="mt-1 small"></div>
                                        <div id="loadingSpinner" class="mt-1 small text-muted" style="display:none;">
                                            <i class="fa fa-spinner fa-spin me-1"></i>Verificando...
                                        </div>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="tipo" class="form-label">
                                            Tipo de área:
                                        </label>
                                        <select
                                            name="tipo"
                                            id="tipo"
                                            class="form-control @error('tipo') is-invalid @enderror"
                                            required
                                        >
                                            <option value="">-- Seleccionar --</option>
                                            <option value="Interno" {{ old('tipo') == 'Interno' ? 'selected' : '' }}>Interno</option>
                                            <option value="Externo" {{ old('tipo') == 'Externo' ? 'selected' : '' }}>Externo</option>
                                        </select>
                                        @error('tipo')
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

    {{-- ── Tabla de Áreas ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-list me-2"></i>Lista de áreas de surtimiento</h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseTabla">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseTabla">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tablaAreas" class="table table-condensed table-bordered table-striped align-middle mb-0">
                                <thead>
                                    <tr class="table-info">
                                        <th>#</th>
                                        <th>Editar</th>
                                        <th>Nombre de la área de surtimiento</th>
                                        <th>Tipo</th>
                                        <th>Fecha Registro</th>
                                        <th>Hora</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($areas as $index => $area)
                                        <tr class="{{ $area->activo == 0 ? 'text-muted fst-italic' : '' }}">
                                            <td>{{ ($areas->currentPage() - 1) * $areas->perPage() + $loop->iteration }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('areas_surtimiento.edit', $area->id_area_surtimiento) }}"
                                                   title="Editar">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                            <td>{{ $area->nombre }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $area->tipo }}</span>
                                            </td>
                                            <td>{{ $area->fecha_registro ? \Carbon\Carbon::parse($area->fecha_registro)->format('d/m/Y') : '' }}</td>
                                            <td>{{ $area->hora_registro }}</td>
                                            <td class="text-center">
                                                <a href="#"
                                                   class="btn-toggle-status"
                                                   data-url="{{ route('areas_surtimiento.status', $area->id_area_surtimiento) }}"
                                                   data-nombre="{{ $area->nombre }}"
                                                   data-activo="{{ $area->activo }}"
                                                   title="{{ $area->activo == 1 ? 'Desactivar' : 'Activar' }}">
                                                    @if($area->activo == 1)
                                                        <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                                    @else
                                                        <i class="fa fa-square-o" aria-hidden="true"></i>
                                                    @endif
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                                No hay áreas de surtimiento registradas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th>#</th>
                                        <th>Editar</th>
                                        <th>Nombre de la área de surtimiento</th>
                                        <th>Tipo</th>
                                        <th>Fecha Registro</th>
                                        <th>Hora</th>
                                        <th>Status</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @if($areas->total() > 0)
                        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                            <div class="text-muted small">
                                Mostrando {{ $areas->firstItem() ?? 0 }} a {{ $areas->lastItem() ?? 0 }} de {{ $areas->total() }} áreas de surtimiento
                            </div>
                            <nav aria-label="Paginación de áreas de surtimiento">
                                {{ $areas->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/areas_surtimiento/surtimiento.css', 'resources/js/inventario/areas_surtimiento/surtimiento.js'])
@endpush
