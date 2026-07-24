@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 px-4">

    <!-- Header y Acciones -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold mb-0 text-dark">
                <i class="bi bi-calculator me-2"></i>Pedido de Insumos por Diferencia
            </h1>
            <p class="text-secondary small mb-0">Genere solicitudes automáticas a CENDIS calculando el déficit entre el Fondo Fijo y el Stock Actual</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('pedido_insumos_dif.reportes') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                <i class="bi bi-file-earmark-bar-graph me-1"></i>Reportes
            </a>
            <a href="{{ route('pedido_insumos_dif.graficas') }}" class="btn btn-outline-dark btn-sm fw-semibold">
                <i class="bi bi-pie-chart-fill me-1"></i>Gráficas
            </a>
        </div>
    </div>

    <!-- Sección 1: Selección de Área y Cálculo de Faltantes -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-3">
            <form id="formCalcularDiferencia" class="row g-3 align-items-end" onsubmit="return false;">
                
                <div class="col-md-3">
                    <label for="select_area_abastecimiento_dif" class="form-label small fw-bold text-secondary">ÁREA SOLICITANTE <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="select_area_abastecimiento_dif" name="id_area_abastecimiento" required>
                        <option value="">-- Seleccionar Área --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id_area_abastecimiento }}">{{ $area->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="select_subarea_abastecimiento_dif" class="form-label small fw-bold text-secondary">SUBÁREA</label>
                    <select class="form-select form-select-sm" id="select_subarea_abastecimiento_dif" name="id_subarea_abastecimiento">
                        <option value="">-- Todas / General --</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="select_area_almacen_dif" class="form-label small fw-bold text-secondary">ÁREA DE ALMACÉN <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="select_area_almacen_dif" name="id_area_almacen" required>
                        @foreach($almacenes as $alm)
                            <option value="{{ $alm->id_area_almacen }}">{{ $alm->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="checkSoloFaltantes" checked>
                            <label class="form-check-input-label small text-secondary fw-semibold" for="checkSoloFaltantes">Solo con Faltante</label>
                        </div>
                    </div>
                    <button type="button" class="btn btn-black btn-sm w-100 fw-semibold" id="btnCalcularDiferencias">
                        <i class="bi bi-arrow-repeat me-1"></i>Calcular Diferencias
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Sección 2: Resumen y Tabla de Insumos Calculados -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-list-check me-2"></i>Insumos con Faltante en el Área 
                <span id="badgeTotalItems" class="badge bg-secondary ms-2">0 ítems</span>
            </h6>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnGuardarBorradorDif">
                    <i class="bi bi-save me-1"></i>Guardar Borrador
                </button>
                <button type="button" class="btn btn-primary btn-sm px-3 fw-semibold" id="btnGenerarPedidoDif">
                    <i class="bi bi-send-check me-1"></i>Generar y Enviar a CENDIS
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div id="alertDiferenciaError" class="alert alert-danger mx-3 mt-3 d-none small" role="alert"></div>
            @include('peticion_insumos.pedido_insumos_dif.partials.tabla_diferencias')
        </div>
    </div>

    <!-- Sección 3: Historial Reciente de Pedidos por Diferencia -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-clock-history me-2"></i>Historial Reciente de Pedidos Generados
            </h6>
        </div>
        <div class="card-body p-0">
            <div id="contenedor-tabla-historial">
                @include('peticion_insumos.pedido_insumos_dif.partials.tabla_historial')
            </div>
        </div>
        <div class="card-footer bg-white py-2 border-top d-flex align-items-center justify-content-between">
            <div class="small text-muted">
                Mostrando <span id="pag-desde">0</span> a <span id="pag-hasta">0</span> de <span id="pag-total">0</span> pedidos
            </div>
            <nav aria-label="Navegación de historial">
                <ul class="pagination pagination-sm mb-0" id="paginador-historial"></ul>
            </nav>
        </div>
    </div>

</div>
@endsection

@vite([
    'resources/css/peticion_insumos/pedido_insumos_dif/diferencia.css',
    'resources/js/peticion_insumos/pedido_insumos_dif/diferencia.js'
])
