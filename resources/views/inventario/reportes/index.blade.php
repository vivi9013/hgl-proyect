@extends('layouts.app')

@section('title', 'Reportes de Inventario')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-file-text-o text-primary me-2"></i>Reportes de Inventario
            </h1>
            <p class="text-muted mb-0">Generación e impresión de reportes de consumo mensual de medicamentos y material de curación</p>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    <div class="row">
        {{-- ── SECCIÓN 1: Reporte Diario de Entregas ── --}}
        <div class="col-12 col-lg-6">
            <div class="report-section">
                <h2 class="report-section-title">
                    <i class="fa fa-print me-2"></i>Reporte Diario de Entregas
                </h2>
                <p class="text-muted small mb-4">
                    Obtenga un desglose diario detallado de los insumos entregados por área de almacén y área asignada en la fecha seleccionada.
                </p>

                <form id="formEntregas">
                    {{-- Área de Almacén --}}
                    <div class="mb-3">
                        <label for="almacen1" class="form-label fw-bold">Área de Almacén:</label>
                        <select id="almacen1" class="form-select" style="width: 100%;">
                            <option value="">Cargando...</option>
                        </select>
                    </div>

                    {{-- Área Asignada --}}
                    <div class="mb-3">
                        <label for="cmbArea" class="form-label fw-bold">Área Asignada:</label>
                        <select id="cmbArea" class="form-select" style="width: 100%;">
                            <option value="">Cargando...</option>
                        </select>
                    </div>

                    {{-- Fecha Diaria --}}
                    <div class="mb-3">
                        <label for="txtFecha1" class="form-label fw-bold">Fecha del Reporte:</label>
                        <input type="date" id="txtFecha1" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" id="btnImprimirEntregas" class="btn btn-primary" disabled>
                            <i class="fa fa-print me-1"></i>Imprimir Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── SECCIÓN 2: Concentrado CENDIS ── --}}
        <div class="col-12 col-lg-6">
            <div class="report-section">
                <h2 class="report-section-title">
                    <i class="fa fa-print me-2"></i>Concentrado CENDIS
                </h2>
                <p class="text-muted small mb-4">
                    Obtenga un consolidado mensual por área de almacén y múltiples áreas asignadas seleccionadas, listando el consumo mensual.
                </p>

                <form id="formConcentrado">
                    <div class="mb-3">
                        <label for="areaalmacen" class="form-label fw-bold">Área de Almacén:</label>
                        <select id="areaalmacen" class="form-select" style="width: 100%;">
                            <option value="">Cargando...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="cmbArea2" class="form-label fw-bold mb-0">Áreas Asignadas:</label>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="chkSelectAllAreas">
                                <label class="form-check-label small fw-bold" for="chkSelectAllAreas">
                                    Seleccionar todas
                                </label>
                            </div>
                        </div>
                        <select id="cmbArea2" class="form-select" style="width: 100%;" multiple="multiple">
                            <!-- Se carga dinámicamente -->
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6 mb-3">
                            <label for="cmbMes2" class="form-label fw-bold">Mes:</label>
                            <select id="cmbMes2" class="form-select">
                                <option value="">Seleccione...</option>
                                <option value="1" {{ date('n') == 1 ? 'selected' : '' }}>Enero</option>
                                <option value="2" {{ date('n') == 2 ? 'selected' : '' }}>Febrero</option>
                                <option value="3" {{ date('n') == 3 ? 'selected' : '' }}>Marzo</option>
                                <option value="4" {{ date('n') == 4 ? 'selected' : '' }}>Abril</option>
                                <option value="5" {{ date('n') == 5 ? 'selected' : '' }}>Mayo</option>
                                <option value="6" {{ date('n') == 6 ? 'selected' : '' }}>Junio</option>
                                <option value="7" {{ date('n') == 7 ? 'selected' : '' }}>Julio</option>
                                <option value="8" {{ date('n') == 8 ? 'selected' : '' }}>Agosto</option>
                                <option value="9" {{ date('n') == 9 ? 'selected' : '' }}>Septiembre</option>
                                <option value="10" {{ date('n') == 10 ? 'selected' : '' }}>Octubre</option>
                                <option value="11" {{ date('n') == 11 ? 'selected' : '' }}>Noviembre</option>
                                <option value="12" {{ date('n') == 12 ? 'selected' : '' }}>Diciembre</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 mb-3">
                            <label for="cmbAno2" class="form-label fw-bold">Año:</label>
                            <input type="number" id="cmbAno2" class="form-control" value="{{ date('Y') }}" min="2000" max="{{ date('Y') + 5 }}">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" id="btnImprimirConcentrado" class="btn btn-primary" disabled>
                            <i class="fa fa-print me-1"></i>Imprimir Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
    <!-- Select2 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <!-- jQuery and Select2 JS CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite(['resources/css/inventario/reportes/reportes.css', 'resources/js/inventario/reportes/reportes.js'])
@endpush
