@extends('layouts.app')

@section('title', 'Catálogo de Insumos')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-list text-primary me-2"></i>Insumos
            </h1>
            <p class="text-muted mb-0">Catálogo de medicamentos y material de curación del hospital</p>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas SweetAlert2 ── --}}
    @if(session('exitog'))
        <div id="alertaExitog" data-message="{{ session('exitog') }}"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito" data-message="{{ session('exito') }}"></div>
    @endif

    {{-- ── Buscador y Acciones ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-8">
            <form method="GET" action="{{ route('insumos.index') }}" id="formBuscar">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-search me-1"></i>Buscar:
                        </label>
                        <div class="input-group" style="border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                            <input
                                type="text"
                                name="buscar"
                                id="inputBuscar"
                                class="form-control bg-light border-0"
                                placeholder="Buscar por clave o descripción..."
                                value="{{ $buscar }}"
                                autocomplete="off"
                                style="font-size: 0.9rem; box-shadow: none;"
                            >
                            @if($buscar)
                                <a href="{{ route('insumos.index') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar Filtros">
                                    <i class="fa fa-times text-danger"></i>
                                </a>
                            @endif
                            <button class="input-group-text bg-light border-0" type="submit" title="Buscar en Base de Datos">
                                <i class="fa fa-search text-dark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12 col-md-4 text-md-end d-flex justify-content-md-end align-items-center mt-2 mt-md-0" style="gap: 0.75rem;">
            <a href="{{ route('insumos.imprimir', request()->query()) }}"
               target="_blank"
               class="btn btn-outline-secondary rounded-pill shadow-sm"
               id="btnImprimirReporte"
               style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-print me-1 text-dark"></i> Imprimir Reporte
            </a>
            <button type="button"
                    class="btn btn-primary rounded-pill shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalRegistrarInsumo"
                    style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-plus-circle me-1"></i>Registrar Insumo
            </button>
        </div>
    </div>

    {{-- ── Tabla de Insumos ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Lista de Insumos
                        </h5>
                        <span class="rounded-pill px-3 py-1 fw-bold align-middle d-inline-block" style="background-color: #e9ecef; font-size: 0.78rem; letter-spacing: 0.03em;">
                            <span style="color: #000000;">{{ $insumos->total() }}</span> 
                            <span style="color: #495057;">{{ $insumos->total() === 1 ? 'Insumo' : 'Insumos' }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table id="tablaInsumos" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">#</th>
                                    <th class="text-center" style="width: 100px;">Editar</th>
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th>Tipo</th>
                                    <th class="text-center pe-4" style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($insumos as $insumo)
                                    <tr class="{{ $insumo->activo == 0 ? 'text-muted fst-italic bg-light bg-opacity-50' : '' }}">
                                        <td class="ps-4 fw-bold">
                                            {{ ($insumos->currentPage() - 1) * $insumos->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('insumos.edit', $insumo->id_insumo) }}"
                                               class="btn btn-sm btn-outline-primary rounded-circle"
                                               title="Editar Insumo"
                                               style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $insumo->clave }}</td>
                                        <td>{{ $insumo->descripcion }}</td>
                                        <td>
                                            <span class="badge-tipo">
                                                {{ $insumo->tipo }}
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            @if($insumo->activo == 1)
                                                <a href="#"
                                                   class="btn-toggle-status badge-status-activo"
                                                   data-url="{{ route('insumos.status', $insumo->id_insumo) }}"
                                                   data-clave="{{ $insumo->clave }}"
                                                   data-activo="1"
                                                   title="Haga clic para desactivar">
                                                    <i class="fa fa-check-circle"></i> Activo
                                                </a>
                                            @else
                                                <a href="#"
                                                   class="btn-toggle-status badge-status-inactivo"
                                                   data-url="{{ route('insumos.status', $insumo->id_insumo) }}"
                                                   data-clave="{{ $insumo->clave }}"
                                                   data-activo="0"
                                                   title="Haga clic para activar">
                                                    <i class="fa fa-times-circle"></i> Inactivo
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No se encontraron insumos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($insumos->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        <div class="text-muted small">
                            Mostrando {{ $insumos->firstItem() ?? 0 }} a {{ $insumos->lastItem() ?? 0 }} de {{ $insumos->total() }} insumos
                        </div>
                        <nav aria-label="Paginación de insumos">
                            {{ $insumos->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modal de Registro de Insumo ── --}}
<div class="modal fade" id="modalRegistrarInsumo" tabindex="-1" aria-labelledby="modalRegistrarInsumoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalRegistrarInsumoLabel">
                    <i class="fa fa-plus-circle me-2"></i>Registrar nuevo Insumo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('insumos.store') }}" novalidate id="formNuevoInsumo">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        
                        {{-- Clave del insumo --}}
                        <div class="col-12">
                            <label for="clave" class="form-label fw-bold">
                                Clave del Insumo: <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="clave"
                                id="clave"
                                class="form-control @error('clave') is-invalid @enderror"
                                value="{{ old('clave') }}"
                                placeholder="Coloque la clave del insumo"
                                autocomplete="off"
                                maxlength="255"
                                required
                            >
                            <div id="feedbackDisponibilidad" class="mt-1 small"></div>
                            <div id="loadingSpinner" class="mt-1 small text-muted" style="display:none;">
                                <i class="fa fa-spinner fa-spin me-1"></i>Verificando...
                            </div>
                            @error('clave')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-bold">
                                Descripción: <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="descripcion"
                                id="descripcion"
                                class="form-control @error('descripcion') is-invalid @enderror"
                                value="{{ old('descripcion') }}"
                                placeholder="Coloque la descripción del insumo"
                                autocomplete="off"
                                required
                            >
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tipo --}}
                        <div class="col-12">
                            <label for="tipo" class="form-label fw-bold">
                                Tipo de Insumo: <span class="text-danger">*</span>
                            </label>
                            <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                <option value="">-- Seleccionar tipo --</option>
                                <option value="Material de curación" {{ old('tipo') === 'Material de curación' ? 'selected' : '' }}>Material de curación</option>
                                <option value="Medicamento" {{ old('tipo') === 'Medicamento' ? 'selected' : '' }}>Medicamento</option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" id="btnGuardar" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>Guardar Información
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('modalRegistrarInsumo'));
            myModal.show();
        });
    </script>
@endif
@endsection

@push('scripts')
    @vite(['resources/css/inventario/insumos/insumos.css', 'resources/js/inventario/insumos/insumos.js'])
@endpush
