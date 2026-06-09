@extends('layouts.app')

@section('title', 'Devoluciones Terminadas')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-check-circle text-success me-2"></i>Devoluciones Terminadas
            </h1>
            <p class="text-muted mb-0">Historial de devoluciones finalizadas</p>
        </div>
    </div>

    {{-- Subnavegación del Módulo --}}
    <div class="d-flex gap-2 mb-2 flex-wrap">
        <a href="{{ route('devoluciones.index') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-hourglass-half me-1 text-dark"></i>Pendientes
        </a>
        <a href="{{ route('devoluciones.terminadas') }}" class="btn btn-sm btn-primary py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-check-circle me-1"></i>Terminadas
        </a>
        <a href="{{ route('devoluciones.reportes') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-bar-chart me-1 text-dark"></i>Reportes
        </a>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Buscador + Filtros ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-9">
            <form method="GET" action="{{ route('devoluciones.terminadas') }}" id="formBuscarTerminadas">
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
                                <a href="{{ route('devoluciones.terminadas') }}" class="input-group-text bg-light border-0 text-decoration-none">
                                    <i class="fa fa-times text-danger"></i>
                                </a>
                            @endif
                            <button class="input-group-text bg-light border-0" type="submit">
                                <i class="fa fa-search text-dark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_inicio_term" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Inicio:
                        </label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio_term"
                               class="form-control bg-light" value="{{ $fechaInit }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_fin_term" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Fin:
                        </label>
                        <div class="input-group">
                            <input type="date" name="fecha_fin" id="fecha_fin_term"
                                   class="form-control bg-light" value="{{ $fechaFin }}">
                            @if($buscar || $fechaInit || $fechaFin)
                                <a href="{{ route('devoluciones.terminadas') }}" class="btn btn-outline-secondary" title="Limpiar">
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
                        <i class="fa fa-list text-secondary me-2"></i>Historial de devoluciones terminadas
                    </h5>
                    <span class="rounded-pill px-3 py-1 fw-bold" style="background-color: #e9ecef; font-size: 0.78rem;">
                        <span style="color: #000;">{{ $devoluciones->total() }}</span>
                        <span style="color: #495057;">{{ $devoluciones->total() === 1 ? 'Registro' : 'Registros' }}</span>
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
                                    <th>Área Abastecimiento</th>
                                    <th>Motivo</th>
                                    <th>Fecha</th>
                                    <th>Insumos</th>
                                    <th class="text-center pe-4" style="width: 130px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devoluciones as $devolucion)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            {{ ($devoluciones->currentPage() - 1) * $devoluciones->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <span style="font-family: Arial, sans-serif; font-size: 0.82rem; font-weight: 600; color: #15803d; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 3px 10px; display: inline-block;">
                                                DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </td>
                                        <td>{{ $devolucion->areaAlmacen->nombre ?? '—' }}</td>
                                        <td>{{ $devolucion->areaAbastecimiento->nombre ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-white" style="font-size: 0.8rem;">
                                                {{ $devolucion->motivo->descripcion ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : '—' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $devolucion->detalles->count() }} insumos
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('devoluciones.comprobante', $devolucion->id_devolucion) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Imprimir comprobante">
                                                <i class="fa fa-print me-1"></i>Imprimir
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay devoluciones terminadas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($devoluciones->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        <div class="text-muted small">
                            Mostrando {{ $devoluciones->firstItem() ?? 0 }} a {{ $devoluciones->lastItem() ?? 0 }} de {{ $devoluciones->total() }} devoluciones
                        </div>
                        <nav>
                            {{ $devoluciones->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/devoluciones/devoluciones.css', 'resources/js/inventario/devoluciones/devoluciones.js'])
@endpush
