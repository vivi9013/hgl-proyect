<div class="modal fade" id="modalCrearPedido" tabindex="-1" aria-labelledby="modalCrearPedidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-black text-white py-3">
                <h5 class="modal-title fs-6 fw-bold" id="modalCrearPedidoLabel">
                    <i class="bi bi-clipboard-plus me-2"></i>Nuevo Pedido de Insumos a CENDIS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <form id="formCrearPedido">
                @csrf
                <div class="modal-body p-4">
                    
                    <!-- Fila 1: Selección de Área, Subárea, Almacén y Cargar Plantilla -->
                    <div class="card mb-4 bg-light border-0">
                        <div class="card-body p-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="select_area_abastecimiento" class="form-label small fw-bold text-secondary">ÁREA SOLICITANTE <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="select_area_abastecimiento" name="id_area_abastecimiento" required>
                                        <option value="">-- Seleccionar Área --</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id_area_abastecimiento }}">{{ $area->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="select_subarea_abastecimiento" class="form-label small fw-bold text-secondary">SUBÁREA</label>
                                    <select class="form-select form-select-sm" id="select_subarea_abastecimiento" name="id_subarea_abastecimiento">
                                        <option value="">-- Todas / General --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="select_area_almacen" class="form-label small fw-bold text-secondary">ÁREA DE ALMACÉN <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="select_area_almacen" name="id_area_almacen" required>
                                        @foreach($almacenes as $alm)
                                            <option value="{{ $alm->id_area_almacen }}">{{ $alm->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="select_plantilla_pedido" class="form-label small fw-bold text-secondary">IMPORTAR DE PLANTILLA</label>
                                    <div class="input-group input-group-sm">
                                        <select class="form-select" id="select_plantilla_pedido">
                                            <option value="">-- Plantillas --</option>
                                            @foreach($plantillas as $plantilla)
                                                <option value="{{ $plantilla->id_plantilla_pedido }}">{{ $plantilla->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-dark" type="button" id="btnCargarPlantilla">
                                            <i class="bi bi-box-arrow-in-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Búsqueda reactiva de insumos -->
                    <div class="card mb-4 border">
                        <div class="card-header bg-white py-2">
                            <span class="fw-bold small text-uppercase text-secondary"><i class="bi bi-search me-1"></i>Buscar y Agregar Insumo</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-7 position-relative">
                                    <input type="text" class="form-control form-control-sm" id="inputBuscarInsumo" placeholder="Escriba clave o nombre del medicamento / material..." autocomplete="off">
                                    <div id="dropdownInsumosResult" class="dropdown-menu w-100 shadow" style="max-height: 250px; overflow-y: auto; display: none; z-index: 1035;"></div>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control form-control-sm" id="inputCantidadInsumo" placeholder="Cantidad" min="1" value="1">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-black btn-sm w-100" id="btnAgregarInsumoLista">
                                        <i class="bi bi-plus-lg me-1"></i>Agregar a Solicitud
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3: Tabla de insumos agregados al pedido -->
                    <div class="table-responsive border rounded" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover table-striped align-middle mb-0" id="tablaPedidoInsumos" style="font-size: 0.85rem;">
                            <thead class="bg-dark text-white sticky-top">
                                <tr>
                                    <th style="width: 120px;">CLAVE</th>
                                    <th>DESCRIPCIÓN DEL INSUMO</th>
                                    <th class="text-center" style="width: 110px;">STOCK HGL</th>
                                    <th class="text-center" style="width: 110px;">FONDO FIJO</th>
                                    <th class="text-center" style="width: 140px;">CANTIDAD PEDIDA</th>
                                    <th class="text-end" style="width: 80px;">ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPedidoInsumos">
                                <tr id="trEmptyPedido">
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-basket fs-3 d-block mb-1 text-secondary"></i>
                                        No hay insumos agregados a esta solicitud de pedido.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Alerta de validación interna -->
                    <div id="alertCrearPedidoError" class="alert alert-danger mt-3 d-none mb-0 small" role="alert"></div>

                </div>
                
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="btnGuardarBorrador">
                        <i class="bi bi-save me-1"></i>Guardar Borrador
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-3" id="btnEnviarPedido">
                        <i class="bi bi-send-check me-1"></i>Finalizar y Enviar a CENDIS
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
