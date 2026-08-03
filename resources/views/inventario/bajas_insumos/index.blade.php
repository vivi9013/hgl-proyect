@extends('layouts.app')

@section('title', 'Bajas de Insumos')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-minus-circle text-primary me-2"></i>Bajas de Insumos
            </h1>
            <p class="text-muted mb-0">Registro y control de bajas de medicamentos y material de curación por área de almacén</p>
        </div>
    </div>

    <hr class="my-4 hr-modulo">

    {{-- ── Alertas SweetAlert2 ── --}}
    @if(session('exitog'))
        <div id="alertaExitog"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito"></div>
    @endif
    @if(session('error'))
        <div id="alertaError" data-message="{{ session('error') }}"></div>
    @endif

    {{-- ── Buscador y Filtros ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-8">
            <form method="GET" action="{{ route('bajas_insumos.index') }}" id="formBuscar">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-search me-1"></i>Buscar:</label>
                        <div class="input-group input-group-oscuro">
                            <input
                                type="text"
                                name="buscar"
                                id="inputBuscar"
                                class="form-control bg-light border-0 input-buscar-custom"
                                placeholder="Buscar por insumo, área o motivo..."
                                value="{{ $buscar }}"
                                autocomplete="off"
                            >
                            @if($buscar)
                                <a href="{{ route('bajas_insumos.index') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar Filtros">
                                    <i class="fa fa-times text-danger"></i>
                                </a>
                            @endif
                            <button class="input-group-text bg-light border-0" type="submit" title="Buscar">
                                <i class="fa fa-search text-dark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_inicio" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-calendar me-1"></i>Fecha Inicio:</label>
                        <input
                            type="date"
                            name="fecha_inicio"
                            id="fecha_inicio"
                            class="form-control bg-light"
                            value="{{ $fechaInit }}"
                        >
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_fin" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-calendar me-1"></i>Fecha Fin:</label>
                        <div class="input-group">
                            <input
                                type="date"
                                name="fecha_fin"
                                id="fecha_fin"
                                class="form-control bg-light"
                                value="{{ $fechaFin }}"
                            >
                            @if($buscar || $fechaInit || $fechaFin)
                                <a href="{{ route('bajas_insumos.index') }}" class="btn btn-outline-secondary" title="Limpiar Filtros">
                                    <i class="fa fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12 col-md-4 text-md-end d-flex justify-content-md-end align-items-center mt-2 mt-md-0 acciones-modulo-gap">
            <a href="{{ route('bajas_insumos.imprimir', request()->query()) }}"
               target="_blank"
               class="btn btn-outline-secondary rounded-pill shadow-sm btn-baja-accion"
               id="btnImprimirReporte">
                <i class="fa fa-print me-1 text-dark"></i> Imprimir Reporte
            </a>
            <button type="button" class="btn btn-primary rounded-pill shadow-sm btn-baja-accion" data-bs-toggle="modal" data-bs-target="#modalAltaBaja">
                <i class="fa fa-plus-circle me-1"></i>Registrar Baja
            </button>
        </div>
    </div>

    {{-- ── Tabla de Bajas ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Historial de bajas de insumos
                        </h5>
                        <span class="rounded-pill px-3 py-1 fw-bold align-middle d-inline-block badge-total-registros">
                            <span class="badge-total-num">{{ $bajas->total() }}</span> <span class="badge-total-label">{{ $bajas->total() === 1 ? 'Registro' : 'Registros' }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table id="tablaAreas" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">#</th>
                                    <th>Insumo</th>
                                    <th>Clave</th>
                                    <th>Área de Almacén</th>
                                    <th>Motivo</th>
                                    <th class="text-center" style="width: 100px;">Cantidad</th>
                                    <th>Fecha Baja</th>
                                    <th>Hora</th>
                                    <th class="text-center pe-4" style="width: 150px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bajas as $index => $baja)
                                    <tr class="{{ $baja->cancelado === 'Si' ? 'text-muted fst-italic' : '' }}">
                                        <td class="ps-4 fw-bold">{{ ($bajas->currentPage() - 1) * $bajas->perPage() + $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $baja->insumo->descripcion ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="clave-pill">{{ $baja->insumo->clave ?? '—' }}</span>
                                        </td>
                                        <td>{{ $baja->areaAlmacen->nombre ?? '—' }}</td>
                                        <td>
                                            <small class="text-truncate d-inline-block motivo-truncate" title="{{ $baja->motivo }}">
                                                {{ $baja->motivo }}
                                            </small>
                                        </td>
                                        <td class="text-center fw-bold">{{ $baja->cantidad }}</td>
                                        <td>{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '' }}</td>
                                        <td>{{ $baja->hora_baja }}</td>
                                        <td class="text-center pe-4">
                                            @if($baja->cancelado === 'Si')
                                                <a href="#"
                                                   class="btn-toggle-baja-status badge bg-danger text-decoration-none py-2 px-3 rounded-pill shadow-sm"
                                                   data-url="{{ route('bajas_insumos.toggle_status', $baja->id_baja_insumo) }}"
                                                   data-insumo="{{ $baja->insumo->descripcion ?? 'Insumo' }}"
                                                   data-cantidad="{{ $baja->cantidad }}"
                                                   data-accion="activar"
                                                   title="Haga clic para reactivar esta baja">
                                                    <i class="fa fa-times-circle me-1"></i> Cancelada
                                                </a>
                                            @else
                                                <a href="#"
                                                   class="btn-toggle-baja-status badge bg-success text-decoration-none py-2 px-3 rounded-pill shadow-sm"
                                                   data-url="{{ route('bajas_insumos.toggle_status', $baja->id_baja_insumo) }}"
                                                   data-insumo="{{ $baja->insumo->descripcion ?? 'Insumo' }}"
                                                   data-cantidad="{{ $baja->cantidad }}"
                                                   data-accion="cancelar"
                                                   title="Haga clic para cancelar esta baja">
                                                    <i class="fa fa-check-circle me-1"></i> Activa
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay bajas de insumos registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($bajas->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        <div class="text-muted small">
                            Mostrando {{ $bajas->firstItem() ?? 0 }} a {{ $bajas->lastItem() ?? 0 }} de {{ $bajas->total() }} bajas
                        </div>
                        <nav aria-label="Paginación de bajas de insumos">
                            {{ $bajas->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modal de Registro de Baja ── --}}
<div class="modal fade" id="modalAltaBaja" tabindex="-1" aria-labelledby="modalAltaBajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalAltaBajaLabel">
                    <i class="fa fa-plus-circle me-2"></i>Registrar nueva baja de insumo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('bajas_insumos.store') }}" novalidate id="formBaja">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">

                        {{-- Área de Almacén --}}
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="id_area_almacen" class="form-label fw-bold">
                                    Área de Almacén:
                                </label>
                                <select
                                    name="id_area_almacen"
                                    id="id_area_almacen"
                                    class="form-control @error('id_area_almacen') is-invalid @enderror"
                                    required
                                >
                                    <option value="">-- Seleccionar área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area_almacen }}"
                                            {{ old('id_area_almacen') == $area->id_area_almacen ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_area_almacen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Insumo --}}
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group position-relative">
                                <label for="buscarInsumo" class="form-label fw-bold">
                                    Insumo (clave o descripción):
                                </label>
                                <input
                                    type="text"
                                    id="buscarInsumo"
                                    class="form-control @error('id_insumo') is-invalid @enderror"
                                    placeholder="Buscar insumo… (doble clic para ver claves)"
                                    autocomplete="off"
                                    value="{{ old('buscarInsumo', '') }}"
                                    title="Doble clic para ver listado de claves disponibles"
                                >
                                <input type="hidden" name="id_insumo" id="id_insumo" value="{{ old('id_insumo') }}">
                                <div id="sugerenciasInsumo" class="list-group position-absolute w-100 sugerencias-dropdown" style="z-index: 1060;"></div>
                                <div id="infoStock" class="mt-1 small text-muted info-stock-container">
                                    <i class="fa fa-cubes me-1"></i>Stock disponible: <strong id="stockDisponible">0</strong> piezas
                                </div>
                                @error('id_insumo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                {{-- Panel de acceso rápido --}}
                                <x-panel-claves :input-id="'buscarInsumo'" :panel-id="'panelClaves'" :endpoint="'/bajas-insumos/buscar-insumos'" :area-input-id="'id_area_almacen'" :columna-extra="'stock'" />
                            </div>
                        </div>

                        {{-- Cantidad --}}
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="cantidad" class="form-label fw-bold">
                                    Cantidad a dar de baja:
                                </label>
                                <input
                                    type="number"
                                    name="cantidad"
                                    id="cantidad"
                                    class="form-control @error('cantidad') is-invalid @enderror"
                                    value="{{ old('cantidad') }}"
                                    placeholder="Ej. 5"
                                    min="1"
                                    required
                                >
                                @error('cantidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Motivo --}}
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="motivo" class="form-label fw-bold">
                                    Motivo de la baja:
                                </label>
                                <textarea
                                    name="motivo"
                                    id="motivo"
                                    class="form-control @error('motivo') is-invalid @enderror"
                                    rows="3"
                                    placeholder="Ej. Producto caducado, daño físico, pérdida..."
                                    maxlength="500"
                                    required
                                >{{ old('motivo') }}</textarea>
                                @error('motivo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" id="btnGuardar" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>Registrar Baja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('modalAltaBaja'));
            myModal.show();
        });
    </script>
@endif
@endsection

@push('scripts')
    @vite(['resources/css/inventario/bajas_insumos/bajas.css', 'resources/js/inventario/bajas_insumos/bajas.js'])
@endpush
