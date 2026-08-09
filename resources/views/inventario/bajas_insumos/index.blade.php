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

    {{-- ── Buscador, Filtros y Acciones ── --}}
    <div class="mb-4">
        <form method="GET" action="{{ route('bajas_insumos.index') }}" id="formBuscar">
            {{-- Fila 1: Buscar | Área Asignada | Fecha Inicio | Fecha Fin | Botón limpiar --}}
            <div class="row g-2 align-items-end mb-2">
                {{-- Buscar --}}
                <div class="col-12 col-md-4 position-relative">
                    <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark">
                        <i class="fa fa-search me-1"></i>Buscar:
                    </label>
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
                            <a href="{{ route('bajas_insumos.index', array_filter(['id_area_abastecimiento' => $filtroArea, 'id_categoria' => $filtroCategoria, 'fecha_inicio' => $fechaInit, 'fecha_fin' => $fechaFin])) }}"
                               class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar búsqueda">
                                <i class="fa fa-times text-danger"></i>
                            </a>
                        @endif
                        <button class="input-group-text bg-light border-0" type="submit" title="Buscar">
                            <i class="fa fa-search text-dark"></i>
                        </button>
                    </div>
                </div>

                {{-- Área Asignada --}}
                <div class="col-12 col-md-2">
                    <label for="filtro_area" class="form-label small fw-bold mb-1 text-dark">
                        <i class="fa fa-building me-1"></i>Área Asignada:
                    </label>
                    <select name="id_area_abastecimiento" id="filtro_area" class="form-select bg-light border-0" style="font-size: 0.9rem;" onchange="this.form.submit()">
                        <option value="">Todas las Áreas</option>
                        @foreach($areasAbastecimiento as $areaAbast)
                            <option value="{{ $areaAbast->id_area_abastecimiento }}" {{ $filtroArea == $areaAbast->id_area_abastecimiento ? 'selected' : '' }}>
                                {{ $areaAbast->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Categoría del Insumo --}}
                <div class="col-12 col-md-2">
                    <label for="filtro_categoria" class="form-label small fw-bold mb-1 text-dark">
                        <i class="fa fa-tag me-1"></i>Categoría:
                    </label>
                    <select name="id_categoria" id="filtro_categoria" class="form-select bg-light border-0" style="font-size: 0.9rem;" onchange="this.form.submit()">
                        <option value="">Todas las Categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id_categoria }}" {{ $filtroCategoria == $cat->id_categoria ? 'selected' : '' }}>
                                {{ $cat->nombre_categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Fecha Inicio --}}
                <div class="col-6 col-md-2">
                    <label for="fecha_inicio" class="form-label small fw-bold mb-1 text-dark">
                        <i class="fa fa-calendar me-1"></i>Desde:
                    </label>
                    <input
                        type="date"
                        name="fecha_inicio"
                        id="fecha_inicio"
                        class="form-control bg-light border-0"
                        value="{{ $fechaInit }}"
                    >
                </div>

                {{-- Fecha Fin --}}
                <div class="col-6 col-md-2">
                    <label for="fecha_fin" class="form-label small fw-bold mb-1 text-dark">
                        <i class="fa fa-calendar me-1"></i>Hasta:
                    </label>
                    <input
                        type="date"
                        name="fecha_fin"
                        id="fecha_fin"
                        class="form-control bg-light border-0"
                        value="{{ $fechaFin }}"
                    >
                </div>

                {{-- Botón Aplicar / Limpiar --}}
                <div class="col-12 col-md-1 d-flex gap-1 align-items-end">
                    <button type="submit" class="btn btn-dark btn-sm w-100" title="Aplicar filtros">
                        <i class="fa fa-filter"></i>
                    </button>
                    @if($buscar || $fechaInit || $fechaFin || $filtroArea || $filtroCategoria)
                        <a href="{{ route('bajas_insumos.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar todos los filtros">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Fila 2: Botones de Acción a la derecha --}}
        <div class="d-flex justify-content-end gap-2 flex-wrap mt-1">
            <button type="button"
                    class="btn btn-outline-success rounded-pill shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#modalExcelArea"
                    title="Exportar a Excel por Área">
                <i class="fa fa-file-excel-o me-1"></i> Excel por Área
            </button>
            <a href="{{ route('bajas_insumos.imprimir', request()->query()) }}"
               target="_blank"
               class="btn btn-outline-secondary rounded-pill shadow-sm"
               id="btnImprimirReporte"
               title="Imprimir Reporte">
                <i class="fa fa-print me-1"></i> Imprimir Reporte
            </a>
            <button type="button"
                    class="btn btn-primary rounded-pill shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#modalAltaBaja">
                <i class="fa fa-plus-circle me-1"></i> Registrar Baja
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
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="tablaAreas" class="table table-hover align-middle mb-0" style="width: 100%; min-width: 1020px; font-size: 0.85rem;">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-3 text-center text-nowrap" style="width: 3.5%; min-width: 45px;">#</th>
                                    <th class="text-nowrap" style="width: 28%; min-width: 250px;">Insumo</th>
                                    <th class="text-nowrap" style="width: 13%; min-width: 125px;">Clave</th>
                                    <th class="text-nowrap" style="width: 11%; min-width: 115px;">Área Asignada</th>
                                    <th class="text-nowrap" style="width: 12%; min-width: 120px;">Motivo</th>
                                    <th class="text-center text-nowrap" style="width: 8%; min-width: 85px;">Cantidad</th>
                                    <th class="text-center text-nowrap" style="width: 9.5%; min-width: 105px;">Fecha Baja</th>
                                    <th class="text-center text-nowrap" style="width: 6.5%; min-width: 75px;">Hora</th>
                                    <th class="text-center text-nowrap pe-3" style="width: 8.5%; min-width: 100px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bajas as $index => $baja)
                                    <tr class="{{ $baja->cancelado === 'Si' ? 'text-muted fst-italic' : '' }}">
                                        <td class="ps-3 text-center fw-bold text-nowrap">{{ ($bajas->currentPage() - 1) * $bajas->perPage() + $loop->iteration }}</td>
                                        <td>
                                            <span class="badge {{ $baja->insumo->meta_tipo['badgeClass'] ?? 'bg-secondary' }} text-nowrap me-1">
                                                {{ $baja->insumo->tipo ?? '' }}
                                            </span>
                                            <span class="fw-semibold text-dark">{{ $baja->insumo->descripcion ?? '—' }}</span>
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="clave-pill">{{ $baja->insumo->clave ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $baja->areaAbastecimiento->nombre ?? $baja->insumo->areaAbastecimiento->nombre ?? $baja->areaAlmacen->nombre ?? 'Sin Asignar' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-truncate d-inline-block motivo-truncate" style="max-width: 160px;" title="{{ $baja->motivo }}">
                                                {{ $baja->motivo }}
                                            </small>
                                        </td>
                                        <td class="text-center fw-bold text-nowrap">{{ $baja->cantidad }}</td>
                                        <td class="text-center text-nowrap">{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '' }}</td>
                                        <td class="text-center text-nowrap">{{ $baja->hora_baja }}</td>
                                        <td class="text-center text-nowrap pe-3">
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
                                <div id="infoAreaAsignada" class="mt-1 small text-muted info-stock-container ms-2" style="display: none;">
                                    <i class="fa fa-building me-1"></i>Área Asignada: <strong id="nombreAreaAsignada" class="text-primary">—</strong>
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

                        {{-- Área de Asignación --}}
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="id_area_abastecimiento_baja" class="form-label fw-bold">
                                    Área de Asignación:
                                    <small class="text-muted fw-normal">(opcional — área a la que corresponde esta baja; no modifica el catálogo de insumos)</small>
                                </label>
                                <select
                                    name="id_area_abastecimiento"
                                    id="id_area_abastecimiento_baja"
                                    class="form-select @error('id_area_abastecimiento') is-invalid @enderror"
                                >
                                    <option value="">-- Sin cambio (usar la asignada) --</option>
                                    @foreach($areasAbastecimiento as $ab)
                                        <option value="{{ $ab->id_area_abastecimiento }}" {{ old('id_area_abastecimiento') == $ab->id_area_abastecimiento ? 'selected' : '' }}>
                                            {{ $ab->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_area_abastecimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Motivo --}}
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="motivo" class="form-label fw-bold">
                                    Motivo de la baja: <span class="text-danger">*</span>
                                </label>
                                <select
                                    name="motivo"
                                    id="motivo"
                                    class="form-select @error('motivo') is-invalid @enderror"
                                    required
                                >
                                    <option value="">-- Seleccionar motivo --</option>
                                    @foreach($motivos as $m)
                                        <option value="{{ $m->descripcion }}" {{ old('motivo') == $m->descripcion ? 'selected' : '' }}>
                                            {{ $m->descripcion }}
                                        </option>
                                    @endforeach
                                    <option value="Otro" {{ old('motivo') == 'Otro' || (old('motivo_otro') && !empty(old('motivo_otro'))) ? 'selected' : '' }}>
                                        Otro (Especificar)
                                    </option>
                                </select>
                                <div class="mt-2" id="container_motivo_otro" style="display: {{ (old('motivo') == 'Otro' || old('motivo_otro')) ? 'block' : 'none' }};">
                                    <input
                                        type="text"
                                        name="motivo_otro"
                                        id="motivo_otro"
                                        class="form-control @error('motivo_otro') is-invalid @enderror"
                                        value="{{ old('motivo_otro') }}"
                                        placeholder="Escriba el motivo específico de la baja..."
                                        maxlength="500"
                                    >
                                    @error('motivo_otro')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                @error('motivo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Iniciales del Paciente --}}
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="iniciales_paciente" class="form-label fw-bold">
                                    Iniciales del Paciente (opcional):
                                </label>
                                <input
                                    type="text"
                                    name="iniciales_paciente"
                                    id="iniciales_paciente"
                                    class="form-control @error('iniciales_paciente') is-invalid @enderror"
                                    value="{{ old('iniciales_paciente') }}"
                                    placeholder="Ej. J.P.M."
                                    maxlength="100"
                                >
                                @error('iniciales_paciente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- No. de Expediente --}}
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="no_expediente" class="form-label fw-bold">
                                    No. de Expediente (opcional):
                                </label>
                                <input
                                    type="text"
                                    name="no_expediente"
                                    id="no_expediente"
                                    class="form-control @error('no_expediente') is-invalid @enderror"
                                    value="{{ old('no_expediente') }}"
                                    placeholder="Ej. 354494"
                                    maxlength="100"
                                >
                                @error('no_expediente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Doctor que lo receta --}}
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="doctor_nombre" class="form-label fw-bold">
                                    Doctor que lo receta (opcional):
                                </label>
                                <input
                                    type="text"
                                    name="doctor_nombre"
                                    id="doctor_nombre"
                                    class="form-control @error('doctor_nombre') is-invalid @enderror"
                                    value="{{ old('doctor_nombre') }}"
                                    placeholder="Ej. Dr. Juan Pérez"
                                    maxlength="200"
                                >
                                @error('doctor_nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Persona quien entrega --}}
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="persona_entrega" class="form-label fw-bold">
                                    Persona quien entrega (opcional):
                                </label>
                                <input
                                    type="text"
                                    name="persona_entrega"
                                    id="persona_entrega"
                                    class="form-control @error('persona_entrega') is-invalid @enderror"
                                    value="{{ old('persona_entrega') }}"
                                    placeholder="Ej. Enf. María López / Iniciales"
                                    maxlength="200"
                                >
                                @error('persona_entrega')
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

{{-- ── Modal Excel por Área Asignada ── --}}
<div class="modal fade" id="modalExcelArea" tabindex="-1" aria-labelledby="modalExcelAreaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalExcelAreaLabel">
                    <i class="fa fa-file-excel-o me-2"></i>Exportar Bajas por Área Asignada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('bajas_insumos.exportar_excel') }}" target="_blank">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Área Asignada (Abastecimiento) --}}
                        <div class="col-12">
                            <label for="modal_id_area_abastecimiento" class="form-label fw-bold">
                                Área Asignada al Insumo:
                            </label>
                            <select name="id_area_abastecimiento" id="modal_id_area_abastecimiento" class="form-select bg-light">
                                <option value="">Todas las Áreas</option>
                                @foreach($areasAbastecimiento as $areaAbast)
                                    <option value="{{ $areaAbast->id_area_abastecimiento }}">
                                        {{ $areaAbast->nombre }} {{ $areaAbast->siglas ? '('.$areaAbast->siglas.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Categoría del Insumo --}}
                        <div class="col-12">
                            <label for="modal_id_categoria" class="form-label fw-bold">
                                Categoría del Insumo:
                            </label>
                            <select name="id_categoria" id="modal_id_categoria" class="form-select bg-light">
                                <option value="">Todas las Categorías</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id_categoria }}">
                                        {{ $cat->nombre_categoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rango de Fechas --}}
                        <div class="col-6">
                            <label for="excel_fecha_inicio" class="form-label fw-bold">Fecha Inicio:</label>
                            <input type="date" name="fecha_inicio" id="excel_fecha_inicio" class="form-control bg-light" value="{{ $fechaInit }}">
                        </div>
                        <div class="col-6">
                            <label for="excel_fecha_fin" class="form-label fw-bold">Fecha Fin:</label>
                            <input type="date" name="fecha_fin" id="excel_fecha_fin" class="form-control bg-light" value="{{ $fechaFin }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success text-white">
                        <i class="fa fa-download me-1"></i>Descargar Excel
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
