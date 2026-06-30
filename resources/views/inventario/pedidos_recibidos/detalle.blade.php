@extends('layouts.app')

@section('title', 'Detalle de Pedido – PED-' . str_pad($pedido->id_pedido, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado con regresar dinámico --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            @php
                $backRoute = route('pedidos_recibidos.index');
                if ($pedido->status === 'Aceptado') {
                    $backRoute = route('pedidos_recibidos.aceptados');
                } elseif ($pedido->status === 'cancelado') {
                    $backRoute = route('pedidos_recibidos.cancelados');
                }
            @endphp
            <a href="{{ $backRoute }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa fa-arrow-left me-1"></i>Regresar
            </a>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-list text-primary me-2"></i>
                Pedido: <span style="color: #1d4ed8;">PED-{{ str_pad($pedido->id_pedido, 5, '0', STR_PAD_LEFT) }}</span>
            </h1>
            <p class="text-muted mb-0">
                Área Solicitante: <strong>{{ $pedido->areaAbastecimiento->nombre ?? '—' }}</strong>
                (Subárea: <strong>{{ $pedido->subareaAbastecimiento->nombre ?? '—' }}</strong>)
                &nbsp;|&nbsp;
                Almacén: <strong>{{ $pedido->areaAlmacen->nombre ?? 'CENDIS' }}</strong>
                &nbsp;|&nbsp;
                Fecha: <strong>{{ $pedido->fecha_registro ? \Carbon\Carbon::parse($pedido->fecha_registro)->format('d/m/Y') : '—' }} {{ $pedido->hora_registro ?? '' }}</strong>
            </p>
            <p class="text-muted mb-0 mt-1">
                Solicitante: <strong>
                    @if($pedido->usuario && $pedido->usuario->persona)
                        {{ $pedido->usuario->persona->nombre }} {{ $pedido->usuario->persona->ap_paterno }} {{ $pedido->usuario->persona->ap_materno }}
                    @else
                        {{ $pedido->usuario->nombre_usuario ?? '—' }}
                    @endif
                </strong>
                &nbsp;|&nbsp;
                Estado:
                @if($pedido->status === 'terminado')
                    <span class="badge bg-warning text-dark">Pendiente por Surtir</span>
                @elseif($pedido->status === 'Aceptado')
                    <span class="badge bg-success">Surtido ({{ $pedido->porcentaje_entrega }}%)</span>
                @else
                    <span class="badge bg-danger">Cancelado</span>
                @endif
            </p>
        </div>

        {{-- Acciones según estado --}}
        <div class="d-flex gap-2 align-items-center">
            @if($pedido->status === 'terminado')
                <button type="button" id="btnLiberarPedido" class="btn btn-success rounded-pill shadow-sm"
                        style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;" disabled>
                    <i class="fa fa-check-circle me-1"></i>Liberar Pedido
                </button>
                <button type="button" id="btnCancelarPedido" class="btn btn-danger rounded-pill shadow-sm"
                        style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                    <i class="fa fa-ban me-1"></i>Cancelar Pedido
                </button>
            @elseif($pedido->status === 'Aceptado')
                <a href="{{ route('pedidos_recibidos.comprobante', $pedido->id_pedido) }}" target="_blank"
                   class="btn btn-primary rounded-pill shadow-sm"
                   style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                    <i class="fa fa-print me-1"></i>Imprimir Comprobante
                </a>
                <button type="button" id="btnCancelarPedido" class="btn btn-danger rounded-pill shadow-sm"
                        style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                    <i class="fa fa-ban me-1"></i>Cancelar Pedido
                </button>
            @endif
        </div>
    </div>

    <hr class="my-3" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    @if(session('exitog'))<div id="alertaExitog" data-message="{{ session('exitog') }}"></div>@endif
    @if(session('exito'))<div id="alertaExito" data-message="{{ session('exito') }}"></div>@endif
    @if(session('error'))<div id="alertaError" data-message="{{ session('error') }}"></div>@endif

    {{-- Tabla de insumos del pedido --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 pb-2 px-4 d-flex align-items-center gap-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-list text-secondary me-2"></i>Medicamentos y Materiales Solicitados
                    </h6>
                    <span class="rounded-pill px-3 py-1 fw-bold" style="background-color: #e9ecef; font-size: 0.78rem;">
                        {{ $pedido->detalles->count() }} {{ $pedido->detalles->count() === 1 ? 'insumo' : 'insumos' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaDetallesPedido">
                            <thead class="table-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th class="text-center" style="width: 100px;">Solicitado</th>
                                    <th class="text-center" style="width: 100px;">Stock CENDIS</th>
                                    <th class="text-center" style="width: 130px;">Surtido</th>
                                    <th class="text-center" style="width: 100px;">Faltante</th>
                                    @if($pedido->status === 'terminado')
                                        <th class="text-center pe-4" style="width: 100px;">Habilitar</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pedido->detalles as $detalle)
                                    @php
                                        $stockDisponible = $detalle->insumoArea->stock ?? 0;
                                        $isSurtido = ($detalle->surtido ?? 0) > 0;
                                        $inputUrl = route('pedidos_recibidos.guardar_surtido', $detalle->id_detalle_pedido);
                                    @endphp
                                    <tr data-id="{{ $detalle->id_detalle_pedido }}"
                                        data-cantidad="{{ $detalle->cantidad }}"
                                        data-stock="{{ $stockDisponible }}"
                                        data-url="{{ $inputUrl }}">
                                        <td class="ps-4 fw-bold">{{ $loop->iteration }}</td>
                                        <td>
                                            <span style="font-family: Arial, sans-serif; font-size: 0.8rem; font-weight: 600; color: #374151; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 8px; display: inline-block;">
                                                {{ $detalle->insumoArea->insumo->clave ?? $detalle->cve_insumo }}
                                            </span>
                                        </td>
                                        <td>{{ $detalle->insumoArea->insumo->descripcion ?? '—' }}</td>
                                        <td class="text-center fw-bold text-dark">{{ $detalle->cantidad }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $stockDisponible > 0 ? 'bg-light text-dark border' : 'bg-danger-subtle text-danger' }}" style="font-size: 0.82rem;">
                                                {{ $stockDisponible }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   class="form-control text-center mx-auto input-surtido"
                                                   style="max-width: 90px; font-weight: bold; border: 1.5px solid #cbd5e1; border-radius: 8px;"
                                                   value="{{ $detalle->surtido ?? 0 }}"
                                                   min="0"
                                                   max="{{ min($detalle->cantidad, $stockDisponible) }}"
                                                   {{ $pedido->status !== 'terminado' || !$isSurtido ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-center fw-bold text-secondary text-faltante">
                                            {{ $detalle->faltante ?? $detalle->cantidad }}
                                        </td>
                                        @if($pedido->status === 'terminado')
                                            <td class="text-center pe-4">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input check-habilitar"
                                                           type="checkbox" role="switch"
                                                           style="cursor: pointer; width: 2.2em; height: 1.1em;"
                                                           {{ $isSurtido ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $pedido->status === 'terminado' ? 8 : 7 }}" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay insumos registrados en este pedido.
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

{{-- Formularios ocultos para acciones --}}
@if($pedido->status === 'terminado')
    <form method="POST" id="formLiberarPedido" action="{{ route('pedidos_recibidos.liberar', $pedido->id_pedido) }}" style="display:none;">
        @csrf
    </form>
@endif

@if($pedido->status === 'terminado' || $pedido->status === 'Aceptado')
    <form method="POST" id="formCancelarPedido" action="{{ route('pedidos_recibidos.cancelar', $pedido->id_pedido) }}" style="display:none;">
        @csrf
    </form>
@endif

@endsection

@push('scripts')
    @vite(['resources/css/inventario/pedidos_recibidos/pedidos.css', 'resources/js/inventario/pedidos_recibidos/pedidos.js'])
@endpush
