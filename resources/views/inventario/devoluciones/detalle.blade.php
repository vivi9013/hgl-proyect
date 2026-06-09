@extends('layouts.app')

@section('title', 'Detalle de Devolución – DEV-' . str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('devoluciones.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa fa-arrow-left me-1"></i>Regresar a Pendientes
            </a>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-undo text-primary me-2"></i>
                Devolución: <span style="color: #1d4ed8;">DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}</span>
            </h1>
            <p class="text-muted mb-0">
                Área de Almacén: <strong>{{ $devolucion->areaAlmacen->nombre ?? '—' }}</strong>
                &nbsp;|&nbsp;
                Fecha: <strong>{{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : '—' }}</strong>
                &nbsp;|&nbsp;
                Motivo: <strong>{{ $devolucion->motivo->descripcion ?? '—' }}</strong>
                &nbsp;|&nbsp;
                <span class="badge {{ $devolucion->status === 'En proceso' ? 'bg-warning text-dark' : 'bg-success' }}">
                    {{ $devolucion->status }}
                </span>
            </p>
        </div>
        @if($devolucion->status === 'En proceso')
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('devoluciones.comprobante', $devolucion->id_devolucion) }}"
                   target="_blank"
                   class="btn btn-outline-secondary rounded-pill shadow-sm"
                   style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                    <i class="fa fa-print me-1"></i>Imprimir
                </a>
                <form method="POST" action="{{ route('devoluciones.finalizar', $devolucion->id_devolucion) }}" id="formFinalizar">
                    @csrf
                    <button type="button" id="btnFinalizar" class="btn btn-success rounded-pill shadow-sm"
                            style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                        <i class="fa fa-check-circle me-1"></i>Finalizar Devolución
                    </button>
                </form>
            </div>
        @else
            <a href="{{ route('devoluciones.comprobante', $devolucion->id_devolucion) }}"
               target="_blank"
               class="btn btn-outline-secondary rounded-pill shadow-sm"
               style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                <i class="fa fa-print me-1"></i>Imprimir Comprobante
            </a>
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

    <div class="row g-4">

        {{-- ── Panel izquierdo: Agregar insumo ── --}}
        @if($devolucion->status === 'En proceso')
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa fa-plus-circle me-2"></i>Agregar Insumo
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('detalle_devoluciones.store') }}" novalidate id="formAgregarInsumo">
                        @csrf
                        <input type="hidden" name="id_devolucion" value="{{ $devolucion->id_devolucion }}">

                        {{-- Buscador de insumo --}}
                        <div class="mb-3 position-relative">
                            <label for="buscarInsumoDetalle" class="form-label fw-bold small">
                                Insumo (clave o descripción): <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="buscarInsumoDetalle"
                                   class="form-control"
                                   placeholder="Buscar insumo… (mín. 2 caracteres)"
                                   autocomplete="off">
                            <input type="hidden" name="id_insumo" id="id_insumo_detalle">
                            <div id="sugerenciasDetalle" class="list-group position-absolute w-100"
                                 style="z-index:1060; display:none; max-height:200px; overflow-y:auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            </div>
                            @error('id_insumo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cantidad --}}
                        <div class="mb-3">
                            <label for="cantidad_detalle" class="form-label fw-bold small">
                                Cantidad: <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="cantidad"
                                   id="cantidad_detalle"
                                   class="form-control @error('cantidad') is-invalid @enderror"
                                   placeholder="Ej. 5"
                                   min="1"
                                   value="{{ old('cantidad') }}"
                                   required>
                            @error('cantidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Sin campo motivo (la tabla detalle_devoluciones no tiene esa columna) --}}

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
        <div class="col-12 {{ $devolucion->status === 'En proceso' ? 'col-lg-8' : '' }}">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 pb-2 px-4 d-flex align-items-center gap-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-list text-secondary me-2"></i>Insumos en esta devolución
                    </h6>
                    <span class="rounded-pill px-3 py-1 fw-bold" style="background-color: #e9ecef; font-size: 0.78rem;">
                        {{ $devolucion->detalles->count() }} {{ $devolucion->detalles->count() === 1 ? 'insumo' : 'insumos' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaDetalles">
                            <thead class="table-light text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    @if($devolucion->status === 'En proceso')
                                        <th class="text-center pe-4">Acción</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devolucion->detalles as $index => $detalle)
                                    <tr data-id="{{ $detalle->id_detalle_devolucion }}">
                                        <td class="ps-4 fw-bold">{{ $loop->iteration }}</td>
                                        <td>
                                            <span style="font-family: Arial, sans-serif; font-size: 0.8rem; font-weight: 600; color: #374151; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 8px; display: inline-block;">
                                                {{ $detalle->insumo->clave ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $detalle->insumo->descripcion ?? '—' }}</td>
                                        <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                                        @if($devolucion->status === 'En proceso')
                                            <td class="text-center pe-4">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-eliminar-detalle"
                                                        data-id="{{ $detalle->id_detalle_devolucion }}"
                                                        data-insumo="{{ $detalle->insumo->descripcion ?? 'Insumo' }}"
                                                        data-url="{{ route('detalle_devoluciones.destroy', $detalle->id_detalle_devolucion) }}"
                                                        title="Eliminar insumo de la devolución">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr id="filaVacia">
                                        <td colspan="{{ $devolucion->status === 'En proceso' ? 5 : 4 }}" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay insumos agregados todavía.
                                            @if($devolucion->status === 'En proceso')
                                                Utilice el formulario de la izquierda para agregar.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Formulario oculto para finalizar (CSRF) --}}
<form method="POST" id="formFinalizarHidden" action="{{ route('devoluciones.finalizar', $devolucion->id_devolucion) }}" style="display:none;">
    @csrf
</form>

@endsection

@push('scripts')
    @vite(['resources/css/inventario/devoluciones/devoluciones.css', 'resources/js/inventario/devoluciones/devoluciones.js'])
@endpush
