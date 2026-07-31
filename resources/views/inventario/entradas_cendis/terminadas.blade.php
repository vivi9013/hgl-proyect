@extends('layouts.app')

@section('title', 'Entradas Terminadas - CENDIS')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-check-circle text-success me-2"></i>Entradas Terminadas al CENDIS
            </h1>
            <p class="text-muted mb-0">Historial de entradas finalizadas con stock aplicado</p>
        </div>
    </div>

    {{-- Subnavegación del Módulo --}}
    @include('inventario.entradas_cendis.partials.subnav', ['activo' => 'terminadas'])

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

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

    {{-- ── Buscador + Filtros ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-9">
            <form method="GET" action="{{ route('entradas_cendis.terminadas') }}" id="formBuscar">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label for="inputBuscarTerm" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-search me-1"></i>Buscar:
                        </label>
                        <div class="input-group" style="border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                            <input type="text" name="buscar" id="inputBuscarTerm"
                                   class="form-control bg-light border-0"
                                   placeholder="Buscar por folio o área..."
                                   value="{{ $buscar }}"
                                   autocomplete="off"
                                   style="font-size: 0.9rem; box-shadow: none;">
                            @if($buscar)
                                <a href="{{ route('entradas_cendis.terminadas') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar">
                                    <i class="fa fa-times text-danger"></i>
                                </a>
                            @endif
                            <button class="input-group-text bg-light border-0" type="submit" title="Buscar">
                                <i class="fa fa-search text-dark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_inicio" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Inicio:
                        </label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                               class="form-control bg-light" value="{{ $fechaInit }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_fin" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Fin:
                        </label>
                        <div class="input-group">
                            <input type="date" name="fecha_fin" id="fecha_fin"
                                   class="form-control bg-light" value="{{ $fechaFin }}">
                            @if($buscar || $fechaInit || $fechaFin)
                                <a href="{{ route('entradas_cendis.terminadas') }}" class="btn btn-outline-secondary" title="Limpiar Filtros">
                                    <i class="fa fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Tabla de Terminadas ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex align-items-center gap-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-list text-secondary me-2"></i>Historial de entradas terminadas
                    </h5>
                    <span class="rounded-pill px-3 py-1 fw-bold" style="background-color: #e9ecef; font-size: 0.78rem;">
                        <span style="color: #000;">{{ $entradas->total() }}</span>
                        <span style="color: #495057;">{{ $entradas->total() === 1 ? 'Registro' : 'Registros' }}</span>
                    </span>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Folio</th>
                                    <th>Área de Almacén</th>
                                    <th>Área de Surtimiento</th>
                                    <th class="text-center">Insumos</th>
                                    <th class="text-center">Cant. Total</th>
                                    <th>Fecha</th>
                                    <th class="text-center pe-4" style="width: 80px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entradas as $entrada)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            {{ ($entradas->currentPage() - 1) * $entradas->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <span class="folio-badge">
                                                ENT-{{ str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </td>
                                        <td>{{ $entrada->areaAlmacen->nombre ?? '—' }}</td>
                                        <td>{{ $entrada->areaSurtimiento->nombre ?? '—' }}</td>
                                        <td class="text-center fw-bold">{{ $entrada->total_productos }}</td>
                                        <td class="text-center fw-bold">{{ $entrada->total_cantidad }}</td>
                                        <td>{{ $entrada->fecha_entrada ? \Carbon\Carbon::parse($entrada->fecha_entrada)->format('d/m/Y') : '—' }}</td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('entradas_cendis.detalle', $entrada->id_entrada) }}"
                                                   class="btn btn-sm btn-outline-primary rounded-circle"
                                                   title="Ver detalle de la entrada"
                                                   style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                    <i class="fa fa-folder-open"></i>
                                                </a>
                                                <a href="{{ route('entradas_cendis.comprobante', $entrada->id_entrada) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-secondary rounded-circle"
                                                   title="Imprimir comprobante"
                                                   style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay entradas terminadas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($entradas->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        <div class="text-muted small">
                            Mostrando {{ $entradas->firstItem() ?? 0 }} a {{ $entradas->lastItem() ?? 0 }} de {{ $entradas->total() }} entradas
                        </div>
                        <nav>
                            {{ $entradas->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/entradas_cendis/entradas.css', 'resources/js/inventario/entradas_cendis/entradas.js'])
@endpush
