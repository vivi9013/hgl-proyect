@extends('layouts.app')

@section('title', 'Lista de Insumos — ' . $plantilla->nombre)

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item">
                <a href="{{ route('plantillas_pedido.index') }}">Plantillas de Pedido</a>
            </li>
            <li class="breadcrumb-item active">Lista de Insumos</li>
        </ol>
    </nav>

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-0 fw-bold text-dark">
                <i class="bi bi-box-seam me-2 text-primary"></i>
                Editar Plantillas &mdash; Lista de Insumos
            </h2>
            <p class="text-muted small mb-0 mt-1">
                Plantilla /
                <strong>{{ $plantilla->areaAbastecimiento->nombre ?? '—' }}</strong>
                @if($plantilla->subareaAbastecimiento)
                    , {{ $plantilla->subareaAbastecimiento->nombre }}
                @endif
                &rarr; ({{ $plantilla->nombre }})
            </p>
        </div>
        <a href="{{ route('plantillas_pedido.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Regresar
        </a>
    </div>

    {{-- Alerta de éxito --}}
    @if(session('exitog'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('exitog') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Buscar insumo --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('plantillas_pedido.insumos', $plantilla->id_plantilla_pedido) }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="buscar-insumo" class="form-label small fw-bold text-secondary mb-1">
                        Buscar Insumo
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscar-insumo" name="buscar" class="form-control"
                               value="{{ $buscarInsumo }}" placeholder="Clave o descripción...">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-dark">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                </div>
                @if($buscarInsumo)
                    <div class="col-auto">
                        <a href="{{ route('plantillas_pedido.insumos', $plantilla->id_plantilla_pedido) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Tabla de insumos con formulario --}}
    <form method="POST" action="{{ route('plantillas_pedido.insumos.guardar', $plantilla->id_plantilla_pedido) }}">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark text-uppercase" style="font-size:0.72rem;">
                        <tr>
                            <th class="ps-3" style="width:50px;">#</th>
                            <th style="width:130px;">Clave</th>
                            <th>Nombre</th>
                            <th style="width:140px;">Tipo</th>
                            <th class="text-center" style="width:110px;">Agregar/Quitar</th>
                            <th class="text-center" style="width:120px;">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($insumos as $index => $insumo)
                            @php
                                $detalle   = $detallesMap->get($insumo->id_insumo);
                                $enPlantilla = !is_null($detalle);
                                $cantidad    = $enPlantilla ? $detalle->cantidad : 1;
                            @endphp
                            <tr class="{{ $enPlantilla ? 'table-success bg-opacity-25' : '' }}">
                                {{-- # --}}
                                <td class="ps-3 text-muted">{{ $insumos->firstItem() + $index }}</td>

                                {{-- Clave --}}
                                <td><code>{{ $insumo->clave }}</code></td>

                                {{-- Descripción --}}
                                <td class="fw-semibold">{{ $insumo->descripcion }}</td>

                                {{-- Tipo --}}
                                <td class="text-muted">{{ $insumo->tipo ?? '—' }}</td>

                                {{-- Toggle Si/No --}}
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio"
                                               class="btn-check"
                                               name="incluido[{{ $insumo->id_insumo }}]"
                                               id="si_{{ $insumo->id_insumo }}"
                                               value="1"
                                               {{ $enPlantilla ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success" for="si_{{ $insumo->id_insumo }}">Sí</label>

                                        <input type="radio"
                                               class="btn-check"
                                               name="incluido[{{ $insumo->id_insumo }}]"
                                               id="no_{{ $insumo->id_insumo }}"
                                               value="0"
                                               {{ !$enPlantilla ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="no_{{ $insumo->id_insumo }}">No</label>
                                    </div>
                                </td>

                                {{-- Cantidad --}}
                                <td class="text-center">
                                    <input type="number"
                                           class="form-control form-control-sm text-center mx-auto"
                                           style="width:80px;"
                                           name="cantidad[{{ $insumo->id_insumo }}]"
                                           value="{{ $cantidad }}"
                                           min="1">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-2 d-block mb-2"></i>
                                    No se encontraron insumos activos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pie: info + paginación + guardar --}}
            <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top flex-wrap gap-2">
                <div class="text-muted small">
                    Mostrando {{ $insumos->firstItem() ?? 0 }} a {{ $insumos->lastItem() ?? 0 }}
                    de {{ $insumos->total() }} registros
                </div>
                <nav>
                    {{ $insumos->withQueryString()->links('pagination::bootstrap-4') }}
                </nav>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
