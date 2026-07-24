<div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-labelledby="modalDetallePedidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fs-6 fw-bold" id="modalDetallePedidoLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Detalle de Pedido <span id="lblIdPedidoDetalle" class="font-monospace text-warning">#</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row g-3 mb-4 bg-light p-3 rounded border">
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase font-monospace fw-bold">Área Solicitante</div>
                        <div id="lblAreaDetalle" class="fw-semibold text-dark fs-6">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase font-monospace fw-bold">Subárea / Almacén</div>
                        <div id="lblSubareaAlmacenDetalle" class="fw-semibold text-dark fs-6">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase font-monospace fw-bold">Fecha / Solicitado Por</div>
                        <div id="lblFechaUsuarioDetalle" class="text-dark">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted text-uppercase font-monospace fw-bold">Estado del Pedido</div>
                        <div id="lblStatusDetalle" class="mt-1">-</div>
                    </div>
                </div>

                <h6 class="fw-bold small text-uppercase text-secondary mb-2">Insumos Solicitados</h6>
                <div class="table-responsive border rounded" style="max-height: 280px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th>CLAVE</th>
                                <th>INSUMO</th>
                                <th class="text-center">SOLICITADO</th>
                                <th class="text-center">SURTIDO CENDIS</th>
                                <th class="text-center">FALTANTE</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDetallesConsultar">
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Cargando detalles...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <a href="#" id="btnImprimirModalDetalle" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i>Imprimir Comprobante
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
