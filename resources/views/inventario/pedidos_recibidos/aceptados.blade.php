@extends('layouts.app')

@section('title', 'Pedidos Recibidos - Aceptados')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-list text-primary me-2"></i>Pedidos Recibidos
            </h1>
            <p class="text-muted mb-0">Gestión de pedidos de insumos médicos y material de curación solicitados por los pisos/áreas</p>
        </div>
    </div>

    <div class="d-flex gap-2 mb-2 flex-wrap">
        <a href="{{ route('pedidos_recibidos.index') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-hourglass-half me-1 text-dark"></i>Pendientes
        </a>
        <a href="{{ route('pedidos_recibidos.aceptados') }}" class="btn btn-sm btn-primary py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-check-circle me-1"></i>Aceptados
        </a>
        <a href="{{ route('pedidos_recibidos.cancelados') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-ban me-1 text-dark"></i>Cancelados
        </a>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    @if(session('exitog'))<div id="alertaExitog" data-message="{{ session('exitog') }}"></div>@endif
    @if(session('exito'))<div id="alertaExito" data-message="{{ session('exito') }}"></div>@endif
    @if(session('error'))<div id="alertaError" data-message="{{ session('error') }}"></div>@endif

    {{-- Auto-abrir comprobante si se acaba de liberar --}}
    @if(session('abrir_comprobante'))
        <div id="abrirComprobanteAuto" data-url="{{ route('pedidos_recibidos.comprobante', session('abrir_comprobante')) }}"></div>
    @endif

    <div class="row mb-4 align-items-end g-3">
        <div class="col-12">
            <form method="GET" action="{{ route('pedidos_recibidos.aceptados') }}" id="formBuscar">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-search me-1"></i>Buscar:
                        </label>
                        <div class="input-group" style="border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                            <input type="text" name="buscar" id="inputBuscar" class="form-control bg-light border-0"
                                   placeholder="Buscar por área, subárea, solicitante..."
                                   value="{{ $buscar }}" autocomplete="off" style="font-size: 0.9rem; box-shadow: none;">
                            @if($buscar)
                                <a href="{{ route('pedidos_recibidos.aceptados') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar">
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
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control bg-light" value="{{ $fechaInit }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_fin" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-calendar me-1"></i>Fecha Fin:</label>
                        <div class="input-group">
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control bg-light" value="{{ $fechaFin }}">
                            @if($buscar || $fechaInit || $fechaFin)
                                <a href="{{ route('pedidos_recibidos.aceptados') }}" class="btn btn-outline-secondary" title="Limpiar Filtros">
                                    <i class="fa fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-check-circle text-success me-2"></i>Pedidos Surtidos / Aceptados
                        </h5>
                        <span class="rounded-pill px-3 py-1 fw-bold align-middle d-inline-block" style="background-color: #e9ecef; font-size: 0.78rem;">
                            <span style="color: #000000;">{{ $pedidos->total() }}</span>
                            <span style="color: #495057;">{{ $pedidos->total() === 1 ? 'Registro' : 'Registros' }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.04em;">
                                <tr>
                                    <th class="ps-4" style="width: 60px;">#</th>
                                    <th>Folio</th>
                                    <th>Área Abastecimiento</th>
                                    <th>Subárea</th>
                                    <th>Almacén Origen</th>
                                    <th>Fecha Registro</th>
                                    <th>Fecha Entrega</th>
                                    <th>% Surtido</th>
                                    <th class="text-center pe-4" style="width: 160px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pedidos as $pedido)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            {{ ($pedidos->currentPage() - 1) * $pedidos->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <span style="font-family: Arial, sans-serif; font-size: 0.82rem; font-weight: 600; color: #1d4ed8; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 3px 10px; display: inline-block;">
                                                PED-{{ str_pad($pedido->id_pedido, 5, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </td>
                                        <td>{{ $pedido->areaAbastecimiento->nombre ?? '—' }}</td>
                                        <td>{{ $pedido->subareaAbastecimiento->nombre ?? '—' }}</td>
                                        <td>{{ $pedido->areaAlmacen->nombre ?? 'CENDIS' }}</td>
                                        <td>{{ $pedido->fecha_registro ? \Carbon\Carbon::parse($pedido->fecha_registro)->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $pedido->fecha_entrega ? \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') : '—' }}</td>
                                        <td>
                                            <span class="badge {{ $pedido->porcentaje_entrega >= 100 ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $pedido->porcentaje_entrega }}%
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('pedidos_recibidos.detalle', $pedido->id_pedido) }}"
                                               class="btn btn-sm btn-outline-dark py-1 px-2 me-1" title="Ver detalle">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('pedidos_recibidos.comprobante', $pedido->id_pedido) }}"
                                               target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2" title="Imprimir comprobante">
                                                <i class="fa fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay pedidos aceptados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($pedidos->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        <div class="text-muted small">
                            Mostrando {{ $pedidos->firstItem() ?? 0 }} a {{ $pedidos->lastItem() ?? 0 }} de {{ $pedidos->total() }} pedidos
                        </div>
                        <nav>{{ $pedidos->appends(request()->except('page'))->links('pagination::bootstrap-5') }}</nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/pedidos_recibidos/pedidos.css', 'resources/js/inventario/pedidos_recibidos/pedidos.js'])
@endpush
