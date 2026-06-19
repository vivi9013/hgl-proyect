@extends('layouts.app')

@section('title', 'Entradas Pendientes - CENDIS')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-boxes text-primary me-2"></i>Entradas a CENDIS
            </h1>
            <p class="text-muted mb-0">Entradas en proceso registradas en el sistema</p>
        </div>
    </div>

    {{-- Subnavegación del Módulo --}}
    <div class="d-flex gap-2 mb-2 flex-wrap">
        <a href="{{ route('entradas_cendis.index') }}" class="btn btn-sm btn-primary py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-hourglass-half me-1"></i>Pendientes
        </a>
        <a href="{{ route('entradas_cendis.terminadas') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-check-circle me-1 text-dark"></i>Terminadas
        </a>
        <a href="{{ route('entradas_cendis.reportes') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-bar-chart me-1 text-dark"></i>Reportes
        </a>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas SweetAlert2 ── --}}
    @if(session('exitog'))
        <div id="alertaExitog" data-message="{{ session('exitog') }}"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito" data-message="{{ session('exito') }}"></div>
    @endif
    @if(session('error'))
        <div id="alertaError" data-message="{{ session('error') }}"></div>
    @endif

    {{-- ── Buscador + Filtros + Botones ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-8">
            <form method="GET" action="{{ route('entradas_cendis.index') }}" id="formBuscar">
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
                                placeholder="Buscar por ID, área..."
                                value="{{ $buscar }}"
                                autocomplete="off"
                                style="font-size: 0.9rem; box-shadow: none;"
                            >
                            @if($buscar)
                                <a href="{{ route('entradas_cendis.index') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar">
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
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control bg-light" value="{{ $fechaInit }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_fin" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Fin:
                        </label>
                        <div class="input-group">
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control bg-light" value="{{ $fechaFin }}">
                            @if($buscar || $fechaInit || $fechaFin)
                                <a href="{{ route('entradas_cendis.index') }}" class="btn btn-outline-secondary" title="Limpiar Filtros">
                                    <i class="fa fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12 col-md-4 text-md-end d-flex justify-content-md-end align-items-center mt-2 mt-md-0">
            <button type="button"
                    class="btn btn-primary rounded-pill shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNuevaEntrada"
                    style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-plus-circle me-1"></i>Nueva Entrada
            </button>
        </div>
    </div>

    {{-- ── Tabla de Entradas Pendientes ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Entradas en proceso
                        </h5>
                        <span class="rounded-pill px-3 py-1 fw-bold align-middle d-inline-block" style="background-color: #e9ecef; font-size: 0.78rem; letter-spacing: 0.03em;">
                            <span style="color: #000000;">{{ $entradas->total() }}</span>
                            <span style="color: #495057;">{{ $entradas->total() === 1 ? 'Registro' : 'Registros' }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table id="tablaEntradas" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 60px;">#</th>
                                    <th>Folio</th>
                                    <th>Área de Almacén</th>
                                    <th>Área Surtimiento</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th class="text-center pe-4" style="width: 110px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entradas as $entrada)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            {{ ($entradas->currentPage() - 1) * $entradas->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <span style="font-family: Arial, sans-serif; font-size: 0.82rem; font-weight: 600; color: #2b6cb0; background-color: #ebf8ff; border: 1px solid #bee3f8; border-radius: 6px; padding: 3px 10px; display: inline-block;">
                                                ENT-{{ str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </td>
                                        <td>{{ $entrada->areaAlmacen->nombre ?? '—' }}</td>
                                        <td>{{ $entrada->areaSurtimiento->nombre ?? '—' }}</td>
                                        <td>
                                            @if($entrada->status === 'En proceso')
                                                <span class="badge bg-warning text-dark" style="font-size: 0.8rem;">Pendiente</span>
                                            @else
                                                <span class="badge bg-danger text-white" style="font-size: 0.8rem;">Cancelada</span>
                                            @endif
                                        </td>
                                        <td>{{ $entrada->fecha_entrada ? \Carbon\Carbon::parse($entrada->fecha_entrada)->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $entrada->hora_entrada ?? '—' }}</td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('entradas_cendis.detalle', $entrada->id_entrada) }}"
                                                   class="btn btn-sm btn-outline-primary rounded-circle"
                                                   title="Ver detalle y agregar insumos"
                                                   style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                    <i class="fa fa-folder-open"></i>
                                                </a>
                                                @if($entrada->status === 'En proceso')
                                                    <a href="{{ route('entradas_cendis.toggle_status', $entrada->id_entrada) }}"
                                                       class="btn btn-sm btn-outline-danger rounded-circle btn-cancelar-entrada"
                                                       data-folio="ENT-{{ str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT) }}"
                                                       title="Cancelar entrada"
                                                       style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                          <i class="fa fa-ban"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('entradas_cendis.toggle_status', $entrada->id_entrada) }}"
                                                       class="btn btn-sm btn-outline-success rounded-circle btn-reactivar-entrada"
                                                       data-folio="ENT-{{ str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT) }}"
                                                       title="Reactivar entrada"
                                                       style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                         <i class="fa fa-undo"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay entradas pendientes en proceso.
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
                        <nav aria-label="Paginación de entradas">
                            {{ $entradas->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modal: Nueva Entrada ── --}}
<div class="modal fade" id="modalNuevaEntrada" tabindex="-1" aria-labelledby="modalNuevaEntradaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalNuevaEntradaLabel">
                    <i class="fa fa-plus-circle me-2"></i>Nueva Entrada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('entradas_cendis.store') }}" novalidate id="formNuevaEntrada">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">

                        {{-- Área de Almacén --}}
                        <div class="col-12 col-md-6">
                            <label for="id_area_almacen" class="form-label fw-bold">
                                <i class="fa fa-building me-1"></i>Área de Almacén: <span class="text-danger">*</span>
                            </label>
                            <select name="id_area_almacen" id="id_area_almacen"
                                    class="form-control @error('id_area_almacen') is-invalid @enderror" required>
                                <option value="">-- Seleccionar área --</option>
                                @foreach($areasAlmacen as $area)
                                    <option value="{{ $area->id_area_almacen }}"
                                        {{ old('id_area_almacen') == $area->id_area_almacen ? 'selected' : '' }}>
                                        {{ $area->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_area_almacen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">El área de almacén es obligatoria.</div>
                            @enderror
                        </div>

                        {{-- Área de Surtimiento --}}
                        <div class="col-12 col-md-6">
                            <label for="id_area_surtimiento" class="form-label fw-bold">
                                <i class="fa fa-building-o me-1"></i>Área de Surtimiento: <span class="text-danger">*</span>
                            </label>
                            <select name="id_area_surtimiento" id="id_area_surtimiento"
                                    class="form-control @error('id_area_surtimiento') is-invalid @enderror" required>
                                <option value="">-- Seleccionar área --</option>
                                @foreach($areasSurtimiento as $area)
                                    <option value="{{ $area->id_area_surtimiento }}"
                                        {{ old('id_area_surtimiento') == $area->id_area_surtimiento ? 'selected' : '' }}>
                                        {{ $area->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_area_surtimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">El área de surtimiento es obligatoria.</div>
                            @enderror
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnCrearEntrada" class="btn btn-primary">
                        <i class="fa fa-plus me-1"></i>Crear Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('modalNuevaEntrada'));
            myModal.show();
        });
    </script>
@endif
@endsection

@push('scripts')
    @vite(['resources/css/inventario/entradas_cendis/entradas.css', 'resources/js/inventario/entradas_cendis/entradas.js'])
@endpush
