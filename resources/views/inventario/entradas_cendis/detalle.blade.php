@extends('layouts.app')

@section('title', 'Detalle de Entrada – ENT-' . str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('entradas_cendis.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa fa-arrow-left me-1"></i>Regresar a Pendientes
            </a>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-boxes text-primary me-2"></i>
                Entrada: <span style="color: #1d4ed8;">ENT-{{ str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT) }}</span>
            </h1>
            <p class="text-muted mb-0">
                Área de Almacén: <strong>{{ $entrada->areaAlmacen->nombre ?? '—' }}</strong>
                &nbsp;|&nbsp;
                Área de Surtimiento: <strong>{{ $entrada->areaSurtimiento->nombre ?? '—' }}</strong>
                &nbsp;|&nbsp;
                Fecha: <strong>{{ $entrada->fecha_entrada ? \Carbon\Carbon::parse($entrada->fecha_entrada)->format('d/m/Y') : '—' }}</strong>
                &nbsp;|&nbsp;
                <span class="badge {{ $entrada->status === 'En proceso' ? 'bg-warning text-dark' : 'bg-success' }}">
                    {{ $entrada->status }}
                </span>
            </p>
        </div>
        @if($entrada->status === 'En proceso')
            <div class="d-flex gap-2 align-items-center">
                <form method="POST" action="{{ route('entradas_cendis.finalizar', $entrada->id_entrada) }}" id="formFinalizar">
                    @csrf
                    <button type="button" id="btnFinalizar" class="btn btn-success rounded-pill shadow-sm"
                            style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                        <i class="fa fa-check-circle me-1"></i>Finalizar Entrada
                    </button>
                </form>
            </div>
        @endif
    </div>

    <hr class="my-3" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas ── --}}
    @if(session('exitog'))
        <div id="alertaExitog" data-message="{{ session('exitog') }}"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito" data-message="{{ session('exito') }}"></div>
    @endif
    @if(session('error'))
        <div id="alertaError" data-message="{{ session('error') }}"></div>
    @endif

    {{-- Input oculto para que el JS sepa el área de almacén activa (para consultar stock) --}}
    <input type="hidden" id="id_area_almacen_active" value="{{ $entrada->id_area_almacen }}">

    <div class="row g-4">

        {{-- ── Panel izquierdo: Agregar insumo ── --}}
        @if($entrada->status === 'En proceso')
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa fa-plus-circle me-2"></i>Agregar Insumo
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('detalle_entradas_cendis.store') }}" novalidate id="formAgregarInsumo">
                        @csrf
                        <input type="hidden" name="id_entrada" value="{{ $entrada->id_entrada }}">

                        {{-- Buscador de insumo --}}
                        <div class="mb-3 position-relative">
                            <label for="buscarInsumoDetalle" class="form-label fw-bold small">
                                Insumo (clave o descripción): <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="buscarInsumoDetalle"
                                   class="form-control"
                                   placeholder="Buscar insumo… (doble clic para ver claves)"
                                   autocomplete="off">
                            <input type="hidden" name="id_insumo" id="id_insumo_detalle">
                            <div id="sugerenciasDetalle" class="list-group position-absolute w-100"
                                 style="z-index:1060; display:none; max-height:220px; overflow-y:auto; box-shadow: 0 4px 10px rgba(0,0,0,0.12);">
                            </div>
                            <x-panel-claves :input-id="'buscarInsumoDetalle'" :panel-id="'panelClavesDetalle'" :endpoint="'/entradas-cendis/buscar-insumos'" :area-input-id="'id_area_almacen_active'" :columna-extra="'stock'" />
                            @error('id_insumo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Descripción del insumo seleccionado (read-only) --}}
                        <div class="mb-3">
                            <label for="descripcion_insumo" class="form-label fw-bold small">Descripción:</label>
                            <input type="text" id="descripcion_insumo" class="form-control bg-light" readonly placeholder="Se llenará al seleccionar">
                        </div>

                        {{-- Stock actual --}}
                        <div class="mb-3">
                            <label for="stock_insumo" class="form-label fw-bold small">
                                Stock actual en almacén:
                            </label>
                            <input type="number" id="stock_insumo" class="form-control bg-light" readonly value="0">
                        </div>

                        {{-- Cantidad Solicitada --}}
                        <div class="mb-3">
                            <label for="solicitado_detalle" class="form-label fw-bold small">
                                Cantidad Solicitada: <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="solicitado"
                                   id="solicitado_detalle"
                                   class="form-control @error('solicitado') is-invalid @enderror"
                                   placeholder="Ej. 10"
                                   min="0"
                                   value="{{ old('solicitado', 0) }}"
                                   disabled>
                            @error('solicitado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cantidad Entregada --}}
                        <div class="mb-3">
                            <label for="cantidad_detalle" class="form-label fw-bold small">
                                Cantidad Entregada: <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="cantidad"
                                   id="cantidad_detalle"
                                   class="form-control @error('cantidad') is-invalid @enderror"
                                   placeholder="Ej. 8"
                                   min="0"
                                   value="{{ old('cantidad', 0) }}"
                                   disabled>
                            @error('cantidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Faltante (calculado automáticamente) --}}
                        <div class="mb-4">
                            <label for="faltante_detalle" class="form-label fw-bold small">Faltante (calculado):</label>
                            <input type="number"
                                   name="faltante"
                                   id="faltante_detalle"
                                   class="form-control bg-light"
                                   readonly
                                   value="{{ old('faltante', 0) }}">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-plus me-1"></i>Agregar Insumo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Panel derecho: Lista de insumos ── --}}
        <div class="col-12 {{ $entrada->status === 'En proceso' ? 'col-lg-8' : '' }}">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 pb-2 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Insumos en esta entrada
                        </h6>
                        <span class="rounded-pill px-3 py-1 fw-bold" style="background-color: #e9ecef; font-size: 0.78rem;">
                            {{ $entrada->detalles->count() }} {{ $entrada->detalles->count() === 1 ? 'insumo' : 'insumos' }}
                        </span>
                    </div>
                    @if($entrada->status === 'Terminado')
                        <a href="{{ route('entradas_cendis.comprobante', $entrada->id_entrada) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-print me-1"></i>Imprimir Comprobante
                        </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaDetalles">
                            <thead class="table-light text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Solicitado</th>
                                    <th class="text-center">Entregado</th>
                                    <th class="text-center">Faltante</th>
                                    @if($entrada->status === 'En proceso')
                                        <th class="text-center pe-4">Acción</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entrada->detalles as $detalle)
                                    @php
                                        $solicitado = $detalle->solicitado ?? $detalle->cantidad;
                                        $faltante = $detalle->faltante ?? max(0, $solicitado - $detalle->cantidad);
                                    @endphp
                                    <tr data-id="{{ $detalle->id_detalle_entrada }}">
                                        <td class="ps-4 fw-bold">{{ $loop->iteration }}</td>
                                        <td>
                                            <span style="font-family: Arial, sans-serif; font-size: 0.8rem; font-weight: 600; color: #374151; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 8px; display: inline-block;">
                                                {{ $detalle->insumo->clave ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $detalle->insumo->descripcion ?? '—' }}</td>
                                        <td class="text-center solicitado-col" data-explicit="{{ $detalle->solicitado !== null ? '1' : '0' }}">{{ $solicitado }}</td>
                                        @if($entrada->status === 'En proceso')
                                            <td class="text-center">
                                                <input type="number"
                                                       class="form-control form-control-sm text-center cantidad-tabla-input"
                                                       style="width: 80px; margin: 0 auto;"
                                                       value="{{ $detalle->cantidad }}"
                                                       min="0"
                                                       data-prev="{{ $detalle->cantidad }}"
                                                       data-url="{{ route('detalle_entradas_cendis.update', $detalle->id_detalle_entrada) }}">
                                            </td>
                                            <td class="text-center faltante-col fw-bold {{ $faltante > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $faltante }}
                                            </td>
                                            <td class="text-center pe-4">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-eliminar-detalle"
                                                        data-id="{{ $detalle->id_detalle_entrada }}"
                                                        data-insumo="{{ $detalle->insumo->descripcion ?? 'Insumo' }}"
                                                        data-url="{{ route('detalle_entradas_cendis.destroy', $detalle->id_detalle_entrada) }}"
                                                        title="Quitar insumo de la entrada">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        @else
                                            <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                                            <td class="text-center fw-bold {{ $faltante > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $faltante }}
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr id="filaVacia">
                                        <td colspan="{{ $entrada->status === 'En proceso' ? 7 : 6 }}" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay insumos agregados todavía.
                                            @if($entrada->status === 'En proceso')
                                                Utilice el formulario de la izquierda para agregar.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($entrada->detalles->count() > 0)
                            @php
                                $totalSolicitado = $entrada->detalles->sum(fn($d) => $d->solicitado ?? $d->cantidad);
                                $totalCantidad = $entrada->detalles->sum('cantidad');
                                $totalFaltante = $entrada->detalles->sum(fn($d) => $d->faltante ?? max(0, ($d->solicitado ?? $d->cantidad) - $d->cantidad));
                            @endphp
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="ps-4 fw-bold text-end text-dark">Totales:</td>
                                    <td class="text-center fw-bold" id="totalSolicitadoTabla">{{ $totalSolicitado }}</td>
                                    <td class="text-center fw-bold" id="totalCantidadTabla">{{ $totalCantidad }}</td>
                                    <td class="text-center fw-bold {{ $totalFaltante > 0 ? 'text-danger' : 'text-success' }}" id="totalFaltanteTabla">
                                        {{ $totalFaltante }}
                                    </td>
                                    @if($entrada->status === 'En proceso')
                                        <td></td>
                                    @endif
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2" id="paginacionTablaDetallesContainer">
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span>Mostrando <strong id="pagInicioTablaDetalles">0</strong> a <strong id="pagFinTablaDetalles">0</strong> de <strong id="pagTotalTablaDetalles">{{ $entrada->detalles->count() }}</strong> insumos</span>
                        <span class="ms-2">|</span>
                        <label for="selectLimitTablaDetalles" class="ms-1 me-1 mb-0">Mostrar:</label>
                        <select id="selectLimitTablaDetalles" class="form-select form-select-sm d-inline-block w-auto py-0 px-2 style-select-limit">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="all">Todos</option>
                        </select>
                    </div>
                    <nav aria-label="Navegación de insumos en la entrada">
                        <ul class="pagination pagination-sm mb-0" id="ulPaginacionTablaDetalles">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Formulario oculto para finalizar --}}
<form method="POST" id="formFinalizarHidden" action="{{ route('entradas_cendis.finalizar', $entrada->id_entrada) }}" style="display:none;">
    @csrf
</form>

@endsection

@push('scripts')
    @vite(['resources/css/inventario/entradas_cendis/entradas.css', 'resources/js/inventario/entradas_cendis/entradas.js'])
@endpush
