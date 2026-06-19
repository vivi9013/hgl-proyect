@extends('layouts.app')

@section('title', 'Reportes de Stock por Área')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-line-chart text-primary me-2"></i> Reporte de Stock de Insumos
            </h1>
            <p class="text-muted mb-0">Consulte y filtre los niveles de abastecimiento por área de almacén.</p>
        </div>
        <div>
            <a href="{{ route('insumos_area.index') }}" class="btn btn-outline-secondary rounded-pill shadow-sm" style="font-weight: 700;">
                <i class="fa fa-arrow-left me-1"></i> Volver a Asignaciones
            </a>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    <div class="row g-4">
        {{-- ── Panel de Filtros (Izquierda) ── --}}
        <div class="col-12 col-lg-4">
            <div class="card card-premium h-100">
                <div class="card-premium-header bg-light">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-sliders text-secondary me-2"></i> Filtros del Reporte
                    </h5>
                </div>
                <div class="card-premium-body">
                    {{-- Selección de Área --}}
                    <div class="mb-4">
                        <label for="area_almacen_reporte" class="form-label fw-bold">
                            Área de Almacén: <span class="text-danger">*</span>
                        </label>
                        <select id="area_almacen_reporte" class="form-select border-dark" onchange="llenarListaReporte()">
                            <option value="">-- Seleccionar Área --</option>
                            @foreach($areasAlmacen as $area)
                                <option value="{{ $area->id_area_almacen }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Niveles de Stock --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3 d-block">Niveles de Abastecimiento a Incluir:</label>

                        <!-- Muy Bajo (0 - 24%) -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="chkMuyBajo" onchange="llenarListaReporte()" checked>
                            <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="chkMuyBajo">
                                <span class="fw-semibold text-danger"><i class="fa fa-thermometer-empty me-2"></i>Muy Bajo (0-24%)</span>
                                <span class="badge bg-danger rounded-pill">Crítico</span>
                            </label>
                        </div>

                        <!-- Bajo (25 - 49%) -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="chkBajo" onchange="llenarListaReporte()" checked>
                            <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="chkBajo">
                                <span class="fw-semibold" style="color: #e67e22;"><i class="fa fa-thermometer-quarter me-2"></i>Bajo (25-49%)</span>
                                <span class="badge rounded-pill" style="background-color: #e67e22;">Alerta</span>
                            </label>
                        </div>

                        <!-- Regular (50 - 74%) -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="chkRegular" onchange="llenarListaReporte()" checked>
                            <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="chkRegular">
                                <span class="fw-semibold text-warning"><i class="fa fa-thermometer-half me-2"></i>Regular (50-74%)</span>
                                <span class="badge bg-warning text-dark rounded-pill">Medio</span>
                            </label>
                        </div>

                        <!-- Suficiente (75 - 100%) -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="chkSuficiente" onchange="llenarListaReporte()" checked>
                            <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="chkSuficiente">
                                <span class="fw-semibold text-success"><i class="fa fa-thermometer-three-quarters me-2"></i>Suficiente (75-100%)</span>
                                <span class="badge bg-success rounded-pill">Óptimo</span>
                            </label>
                        </div>

                        <!-- Excedido (> 100%) -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="chkExcedido" onchange="llenarListaReporte()" checked>
                            <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="chkExcedido">
                                <span class="fw-semibold text-primary"><i class="fa fa-thermometer-full me-2"></i>Excedido (>100%)</span>
                                <span class="badge bg-primary rounded-pill">Sobre-abasto</span>
                            </label>
                        </div>
                    </div>

                    {{-- Acción de Impresión --}}
                    <div class="pt-3 border-top mt-3">
                        <button type="button" id="btnImprimirReporte" onclick="imprimirReporte()" class="btn btn-dark w-100 py-2.5 rounded-pill shadow-sm" disabled>
                            <i class="fa fa-print me-2"></i> Generar Impresión PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Resultados (Derecha) ── --}}
        <div class="col-12 col-lg-8">
            <div class="card card-premium h-100">
                <div class="card-premium-header bg-white pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-list text-secondary me-2"></i> Resultados de Consulta
                    </h5>
                    <span id="total_insumos" class="badge bg-light text-dark border border-secondary px-3 py-2 fw-bold" style="font-size: 0.9rem;">
                        Total en stock: 0
                    </span>
                </div>
                <div class="card-premium-body">
                    {{-- Spinner de carga --}}
                    <div id="loadingSpinnerReporte" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Consultando datos...</span>
                        </div>
                        <p class="mt-2 mb-0 text-muted">Consultando niveles de stock...</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-insumos-area align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th class="text-center">Clave</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Área</th>
                                    <th class="text-center" style="width: 100px;">Stock</th>
                                    <th class="text-center" style="width: 110px;">Fondo Fijo</th>
                                    <th class="text-center" style="width: 90px;">%</th>
                                </tr>
                            </thead>
                            <tbody id="tablaReporteCuerpo">
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="fa fa-building fa-2x mb-2 d-block"></i>
                                        Por favor, seleccione un área de almacén para mostrar los insumos correspondientes.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/insumos_area/insumos_area.css', 'resources/js/inventario/insumos_area/insumos_area.js'])
@endpush
