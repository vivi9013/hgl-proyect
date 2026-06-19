@extends('layouts.app')

@section('title', 'Asignación de Insumos por Área')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-cubes text-primary me-2"></i>Insumos por Área
            </h1>
            <p class="text-muted mb-0">Gestione y distribuya los insumos en las diferentes áreas de almacén.</p>
        </div>
        <div>
            <a href="{{ route('insumos_area.reportes') }}" class="btn btn-outline-primary rounded-pill shadow-sm" style="font-weight: 700;">
                <i class="fa fa-line-chart me-1"></i> Ver Panel de Reportes
            </a>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas SweetAlert2 ── --}}
    @if(session('exitog'))
        <div id="alertaExitog" data-message="{{ session('exitog') }}"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito" data-message="{{ session('exito') }}"></div>
    @endif

    {{-- ── Tarjeta: Asignar Insumo a Área ── --}}
    <div class="card card-premium">
        <div class="card-premium-header bg-light">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa fa-plus-circle text-primary me-2"></i>Asignar insumos a un Área
            </h5>
        </div>
        <div class="card-premium-body">
            <form action="{{ route('insumos_area.store') }}" method="POST" id="formAsignarInsumo">
                @csrf
                <input type="hidden" name="id_insumo" id="id_insumo" value="{{ old('id_insumo') }}">

                <div class="row g-3">
                    {{-- Combo: Área de Almacén --}}
                    <div class="col-12 col-md-4">
                        <label for="area_almacen_select" class="form-label fw-bold">Área de Almacén: <span class="text-danger">*</span></label>
                        <select name="id_area_almacen" id="area_almacen_select" class="form-select @error('id_area_almacen') is-invalid @enderror" required>
                            <option value="">-- Seleccionar Área --</option>
                            @foreach($areasAlmacen as $area)
                                <option value="{{ $area->id_area_almacen }}" {{ old('id_area_almacen') == $area->id_area_almacen ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_area_almacen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Clave del insumo (Doble clic abre modal) --}}
                    <div class="col-12 col-md-4">
                        <label for="cve_insumo" class="form-label fw-bold">
                            Clave del Insumo: <span class="text-danger">*</span>
                            <small class="text-muted fw-normal">(Doble clic para buscar)</small>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa fa-key text-muted"></i></span>
                            <input 
                                type="text" 
                                name="cve_insumo" 
                                id="cve_insumo" 
                                class="form-control @error('id_insumo') is-invalid @enderror" 
                                value="{{ old('cve_insumo') }}" 
                                placeholder="Escriba clave y presione Enter" 
                                disabled 
                                required
                                autocomplete="off"
                            >
                            @error('id_insumo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Fondo Fijo --}}
                    <div class="col-12 col-md-2">
                        <label for="fondo_fijo_insumo" class="form-label fw-bold">Fondo Fijo: <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            name="fondo_fijo" 
                            id="fondo_fijo_insumo" 
                            class="form-control @error('fondo_fijo') is-invalid @enderror" 
                            value="{{ old('fondo_fijo') }}" 
                            placeholder="Ej. 100" 
                            disabled 
                            required 
                            min="1"
                        >
                        @error('fondo_fijo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Stock Inicial --}}
                    <div class="col-12 col-md-2">
                        <label for="stock_inicial_insumo" class="form-label fw-bold">Stock Inicial: <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            name="stock" 
                            id="stock_inicial_insumo" 
                            class="form-control @error('stock') is-invalid @enderror" 
                            value="{{ old('stock', 0) }}" 
                            disabled 
                            required 
                            min="0"
                        >
                        @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Datos Informativos Deshabilitados --}}
                    <div class="col-12 col-md-8">
                        <label for="descripcion_insumo" class="form-label fw-bold">Descripción del Insumo:</label>
                        <input type="text" id="descripcion_insumo" class="form-control bg-light" placeholder="Se rellenará automáticamente" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="tipo" class="form-label fw-bold">Tipo:</label>
                        <input type="text" id="tipo" class="form-control bg-light" placeholder="Se rellenará automáticamente" readonly>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" id="btnGuardarInfo" class="btn btn-primary px-4 py-2" disabled>
                        <i class="fa fa-save me-1"></i> Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Tarjeta: Filtro de Visualización e Historial ── --}}
    <div class="card card-premium">
        <div class="card-premium-header bg-white pb-0">
            <div class="row w-100 align-items-center g-3">
                <div class="col-12 col-md-8">
                    <form method="GET" action="{{ route('insumos_area.index') }}" id="formBuscarFiltros">
                        <div class="row g-2 align-items-end">
                            {{-- Buscar --}}
                            <div class="col-12 col-md-5">
                                <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark">
                                    <i class="fa fa-search me-1"></i>Buscar Insumo:
                                </label>
                                <div class="input-group border border-1 border-dark rounded-3 overflow-hidden">
                                    <input
                                        type="text"
                                        name="buscar"
                                        id="inputBuscar"
                                        class="form-control bg-light border-0"
                                        placeholder="Clave o descripción..."
                                        value="{{ $buscar }}"
                                        autocomplete="off"
                                        style="font-size: 0.9rem;"
                                    >
                                    @if($buscar)
                                        <a href="{{ route('insumos_area.index', ['id_area_almacen' => $filtroArea]) }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar búsqueda">
                                            <i class="fa fa-times text-danger"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            {{-- Filtrar por Área --}}
                            <div class="col-12 col-md-5">
                                <label for="filtro_area" class="form-label small fw-bold mb-1 text-dark">
                                    <i class="fa fa-filter me-1"></i>Filtrar por Área:
                                </label>
                                <select name="id_area_almacen" id="filtro_area" class="form-select bg-light border-dark rounded-3" style="font-size: 0.9rem;">
                                    <option value="">Todas las Áreas</option>
                                    @foreach($areasAlmacen as $area)
                                        <option value="{{ $area->id_area_almacen }}" {{ $filtroArea == $area->id_area_almacen ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Botones --}}
                            <div class="col-12 col-md-2">
                                <button type="submit" class="btn btn-dark w-100 rounded-3" style="font-size: 0.9rem; padding: 0.45rem;">
                                    <i class="fa fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <span class="rounded-pill px-3 py-1 fw-bold bg-light border text-dark align-middle d-inline-block" style="font-size: 0.82rem;">
                        {{ $insumosArea->total() }} {{ $insumosArea->total() === 1 ? 'Insumo asignado' : 'Insumos asignados' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="card-premium-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-insumos-area mb-0">
                    <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                        <tr>
                            <th class="text-center" style="width: 60px;">#</th>
                            <th class="text-center" style="width: 80px;">Acción</th>
                            <th class="text-center">Clave</th>
                            <th>Descripción</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Área de Almacén</th>
                            <th class="text-center" style="width: 140px;">
                                Stock <kbd class="kbd-hint">Enter</kbd>
                            </th>
                            <th class="text-center" style="width: 140px;">
                                Fondo Fijo <kbd class="kbd-hint">Enter</kbd>
                            </th>
                            <th class="text-center" style="width: 60px;">Estado</th>
                            <th class="text-center" style="width: 90px;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($insumosArea as $ia)
                            @php
                                $stockVal = (int) $ia->stock;
                                $ffVal = (int) $ia->fondo_fijo;
                                $porcentaje = $ffVal > 0 ? round(($stockVal * 100) / $ffVal, 1) : 0;

                                if ($porcentaje < 25) {
                                    $iconClass = "fa fa-thermometer-empty fa-2x thermometer-icon";
                                    $iconColor = "#d63031";
                                    $stockClass = "stock-muy-bajo";
                                } elseif ($porcentaje >= 25 && $porcentaje < 50) {
                                    $iconClass = "fa fa-thermometer-quarter fa-2x thermometer-icon";
                                    $iconColor = "#e67e22";
                                    $stockClass = "stock-bajo";
                                } elseif ($porcentaje >= 50 && $porcentaje < 75) {
                                    $iconClass = "fa fa-thermometer-half fa-2x thermometer-icon";
                                    $iconColor = "#f1c40f";
                                    $stockClass = "stock-regular";
                                } elseif ($porcentaje >= 75 && $porcentaje <= 100) {
                                    $iconClass = "fa fa-thermometer-three-quarters fa-2x thermometer-icon";
                                    $iconColor = "#27ae60";
                                    $stockClass = "stock-suficiente";
                                } else {
                                    $iconClass = "fa fa-thermometer-full fa-2x thermometer-icon";
                                    $iconColor = "#2980b9";
                                    $stockClass = "stock-excedido";
                                }
                            @endphp
                            <tr>
                                <td class="text-center fw-bold text-muted">
                                    {{ ($insumosArea->currentPage() - 1) * $insumosArea->perPage() + $loop->iteration }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('insumos_area.edit', $ia->id_insumo_area) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-circle" 
                                       title="Editar área asignada"
                                       style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
                                <td class="text-center fw-bold text-dark">{{ $ia->insumo->clave ?? '—' }}</td>
                                <td>{{ $ia->insumo->descripcion ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary" style="font-size: 0.8rem;">
                                        {{ $ia->insumo->tipo ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $ia->areaAlmacen->nombre ?? '—' }}</td>
                                <td class="text-center">
                                    <input 
                                        type="number" 
                                        id="stock_inicial_insumo{{ $ia->id_insumo_area }}" 
                                        class="input-table-edit {{ $stockClass }}" 
                                        value="{{ $ia->stock }}"
                                        onkeypress="guardarStockInicial('{{ $ia->id_insumo_area }}', '{{ $ia->fondo_fijo }}', event)" 
                                        onblur="guardarStockInicial2('{{ $ia->id_insumo_area }}', '{{ $ia->fondo_fijo }}')"
                                        min="0"
                                    >
                                </td>
                                <td class="text-center">
                                    <input 
                                        type="number" 
                                        id="fondo_fijo{{ $ia->id_insumo_area }}" 
                                        class="input-table-edit" 
                                        value="{{ $ia->fondo_fijo }}"
                                        onkeypress="guardarFondoFijo('{{ $ia->id_insumo_area }}', event)"
                                        min="1"
                                    >
                                </td>
                                <td class="text-center">
                                    <i id="icono{{ $ia->id_insumo_area }}" class="{{ $iconClass }}" style="color: {{ $iconColor }};" aria-hidden="true"></i>
                                </td>
                                <td class="text-center fw-bold" id="porcentaje_fondof{{ $ia->id_insumo_area }}">
                                    {{ $porcentaje }} %
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                    No se encontraron insumos asignados a áreas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($insumosArea->total() > 0)
                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                    <div class="text-muted small">
                        Mostrando {{ $insumosArea->firstItem() ?? 0 }} a {{ $insumosArea->lastItem() ?? 0 }} de {{ $insumosArea->total() }} asignaciones
                    </div>
                    <nav aria-label="Paginación de asignaciones">
                        {{ $insumosArea->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ── MODAL: Catálogo de Insumos (Doble Clic) ── --}}
<div class="modal fade" id="modalInsumos" tabindex="-1" aria-labelledby="modalInsumosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-premium">
            <div class="modal-header modal-header-premium bg-dark text-white">
                <h5 class="modal-title fw-bold text-white" id="modalInsumosLabel">
                    <i class="fa fa-list me-2"></i> Seleccionar Insumo del Catálogo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-premium p-0" id="listaInsumosContenido">
                <div class="text-center py-4">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted mb-2"></i>
                    <p class="mb-0 text-muted">Cargando catálogo de insumos...</p>
                </div>
            </div>
            <div class="modal-footer modal-footer-premium bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@if(session('exitog') || session('exito'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                const msg = "{{ session('exitog') ?? session('exito') }}";
                Swal.fire({
                    title: '¡Operación Satisfactoria!',
                    text: msg,
                    icon: 'success',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    </script>
@endif
@endsection

@push('scripts')
    @vite(['resources/css/inventario/insumos_area/insumos_area.css', 'resources/js/inventario/insumos_area/insumos_area.js'])
@endpush
