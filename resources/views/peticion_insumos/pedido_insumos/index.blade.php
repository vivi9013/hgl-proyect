@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 px-4">

    <!-- Header y Acciones Principales -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold mb-0 text-dark">
                <i class="bi bi-clipboard-check me-2"></i>Pedido de Insumos a CENDIS
            </h1>
            <p class="text-secondary small mb-0">Gestione y solicite medicamentos y material de curación a la Central de Distribución</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('pedido_insumos.reportes') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                <i class="bi bi-file-earmark-bar-graph me-1"></i>Reportes
            </a>
            <button type="button" class="btn btn-primary btn-sm fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#modalCrearPedido">
                <i class="bi bi-plus-lg me-1"></i>Nuevo Pedido
            </button>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda Reactiva -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-3">
            <form id="formFiltrosPedidos" class="row g-2 align-items-center" onsubmit="return false;">
                
                <!-- Buscador de texto -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" id="inputBuscarPedido" class="form-control" placeholder="Buscar por ID, área, insumo o solicitante..." value="{{ $buscar }}">
                    </div>
                </div>

                <!-- Filtro por Estado -->
                <div class="col-md-3">
                    <select id="selectFiltroStatus" class="form-select form-select-sm">
                        <option value="">-- Todos los Estados --</option>
                        <option value="terminado" {{ $status === 'terminado' ? 'selected' : '' }}>Enviados a CENDIS (Pendientes)</option>
                        <option value="Aceptado" {{ $status === 'Aceptado' ? 'selected' : '' }}>Surtidos y Aceptados</option>
                        <option value="borrador" {{ $status === 'borrador' ? 'selected' : '' }}>Borradores</option>
                        <option value="cancelado" {{ $status === 'cancelado' ? 'selected' : '' }}>Cancelados</option>
                    </select>
                </div>

                <!-- Rango de Fechas -->
                <div class="col-md-2">
                    <input type="date" id="inputFechaInicio" class="form-control form-control-sm" value="{{ $fechaInit }}" placeholder="Fecha Inicio">
                </div>
                <div class="col-md-2">
                    <input type="date" id="inputFechaFin" class="form-control form-control-sm" value="{{ $fechaFin }}" placeholder="Fecha Fin">
                </div>

                <!-- Botón Limpiar -->
                <div class="col-md-1 text-end">
                    <button type="button" id="btnResetFiltros" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar Filtros">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla AJAX -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div id="contenedor-tabla-pedidos">
                @include('peticion_insumos.pedido_insumos.partials.tabla')
            </div>
        </div>
        
        <!-- Paginador global estandarizado -->
        <div class="card-footer bg-white py-2 border-top d-flex align-items-center justify-content-between">
            <div class="small text-muted" id="info-paginacion">
                Mostrando <span id="pag-desde">0</span> a <span id="pag-hasta">0</span> de <span id="pag-total">0</span> pedidos
            </div>
            <nav aria-label="Navegación de tabla">
                <ul class="pagination pagination-sm mb-0" id="paginador-pedidos">
                    <!-- Generado dinámicamente por helpers.js / window.renderPaginacion -->
                </ul>
            </nav>
        </div>
    </div>

</div>

<!-- Modales -->
@include('peticion_insumos.pedido_insumos.partials.modal_crear')
@include('peticion_insumos.pedido_insumos.partials.modal_detalle')

@endsection

@vite([
    'resources/css/peticion_insumos/pedido_insumos/pedidos.css',
    'resources/js/peticion_insumos/pedido_insumos/pedidos.js'
])
