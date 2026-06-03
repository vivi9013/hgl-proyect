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
                        <p class="text-muted small mb-0">Registro de salidas de insumos por área: caducidad, daño, pérdida u otros motivos</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    <a href="{{ route('bajas_insumos.imprimir') }}"
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
    @if(session('error'))
        <div id="alertaError" data-message="{{ session('error') }}"></div>
    @endif

    {{-- ── Formulario de Alta de Baja ── --}}
    <div class="row mb-4">
        <div class="col-xs-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-plus-circle me-2"></i>Registrar nueva baja de insumo</h5>
                    <button class="btn btn-sm btn-outline-light" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseAlta">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseAlta">
                    <form method="POST" action="{{ route('bajas_insumos.store') }}" novalidate id="formBaja">
                        @csrf
                        <div class="card-body">
                            <div class="row g-3">

                                {{-- Área de Almacén --}}
                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="id_area_almacen" class="form-label">
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
                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="buscarInsumo" class="form-label">
                                            Insumo (clave o descripción):
                                        </label>
                                        <input
                                            type="text"
                                            id="buscarInsumo"
                                            class="form-control @error('id_insumo') is-invalid @enderror"
                                            placeholder="Buscar insumo..."
                                            autocomplete="off"
                                            value="{{ old('buscarInsumo', '') }}"
                                        >
                                        <input type="hidden" name="id_insumo" id="id_insumo" value="{{ old('id_insumo') }}">
                                        <div id="sugerenciasInsumo" class="list-group position-absolute w-100" style="z-index:1000; display:none; max-height:220px; overflow-y:auto;"></div>
                                        <div id="infoStock" class="mt-1 small text-muted" style="display:none;">
                                            <i class="fa fa-cubes me-1"></i>Stock disponible: <strong id="stockDisponible">0</strong> piezas
                                        </div>
                                        @error('id_insumo')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Cantidad --}}
                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="cantidad" class="form-label">
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
                                        <label for="motivo" class="form-label">
                                            Motivo de la baja:
                                        </label>
                                        <textarea
                                            name="motivo"
                                            id="motivo"
                                            class="form-control @error('motivo') is-invalid @enderror"
                                            rows="2"
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
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" id="btnGuardar" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i>Registrar Baja
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Buscador ── --}}
    <div class="row mb-3">
        <div class="col-xs-12 col-sm-12 col-md-6">
            <form method="GET" action="{{ route('bajas_insumos.index') }}" id="formBuscar">
                <div class="input-group">
                    <input
                        type="text"
                        name="buscar"
                        id="inputBuscar"
                        class="form-control"
                        placeholder="Buscar por insumo, área o motivo..."
                        value="{{ $buscar }}"
                        autocomplete="off"
                    >
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                    @if($buscar)
                        <a href="{{ route('bajas_insumos.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ── Tabla de Bajas ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-list me-2"></i>Historial de bajas de insumos</h5>
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
                                        <th>Cancelar</th>
                                        <th>Insumo</th>
                                        <th>Clave</th>
                                        <th>Área de Almacén</th>
                                        <th>Motivo</th>
                                        <th>Cantidad</th>
                                        <th>Fecha Baja</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bajas as $index => $baja)
                                        <tr class="{{ $baja->cancelado === 'Si' ? 'text-muted fst-italic' : '' }}">
                                            <td>{{ ($bajas->currentPage() - 1) * $bajas->perPage() + $loop->iteration }}</td>
                                            <td class="text-center">
                                                @if($baja->cancelado === 'No')
                                                    <a href="#"
                                                       class="btn-cancelar-baja"
                                                       data-url="{{ route('bajas_insumos.cancelar', $baja->id_baja_insumo) }}"
                                                       data-insumo="{{ $baja->insumo->descripcion ?? 'Insumo' }}"
                                                       data-cantidad="{{ $baja->cantidad }}"
                                                       title="Cancelar baja">
                                                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted" title="Baja cancelada">
                                                        <i class="fa fa-ban" aria-hidden="true"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $baja->insumo->descripcion ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $baja->insumo->clave ?? '—' }}</span>
                                            </td>
                                            <td>{{ $baja->areaAlmacen->nombre ?? '—' }}</td>
                                            <td>{{ $baja->motivo }}</td>
                                            <td class="text-center">{{ $baja->cantidad }}</td>
                                            <td>{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '' }}</td>
                                            <td>{{ $baja->hora_baja }}</td>
                                            <td class="text-center">
                                                @if($baja->cancelado === 'Si')
                                                    <span class="badge bg-danger">Cancelada</span>
                                                @else
                                                    <span class="badge bg-success">Activa</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                                No hay bajas de insumos registradas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th>#</th>
                                        <th>Cancelar</th>
                                        <th>Insumo</th>
                                        <th>Clave</th>
                                        <th>Área de Almacén</th>
                                        <th>Motivo</th>
                                        <th>Cantidad</th>
                                        <th>Fecha Baja</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @if($bajas->total() > 0)
                        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
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

</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/bajas_insumos/bajas.css', 'resources/js/inventario/bajas_insumos/bajas.js'])
@endpush
