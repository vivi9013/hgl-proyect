{{-- Modal: modal_tomar.blade.php --}}
<div class="modal fade" id="modal-tomar-servicio" tabindex="-1" aria-labelledby="modal-tomar-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modal-tomar-label">
                    <i class="fa fa-hand-paper-o me-2"></i>Tomar Solicitud de Servicio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border d-flex flex-column gap-1 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Folio:</span>
                        <strong id="tomar-label-folio" class="text-primary fs-6">#—</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Solicitante:</span>
                        <strong id="tomar-label-solicitante" class="text-dark small">—</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Área:</span>
                        <span id="tomar-label-area" class="badge bg-secondary">—</span>
                    </div>
                    <div class="mt-2 pt-2 border-top">
                        <span class="text-muted small d-block">Descripción del Problema:</span>
                        <div class="p-2 bg-white rounded small border mt-1" id="tomar-label-desc" style="max-height: 120px; overflow-y: auto;">—</div>
                    </div>
                </div>

                <form id="form-tomar-servicio">
                    @csrf
                    <input type="hidden" id="tomar-id-servicio" name="id">

                    <div class="mb-3">
                        <label for="input-clasificacion" class="form-label fw-semibold small">
                            Prioridad / Clasificación del Servicio <span class="text-danger">*</span>
                        </label>
                        <select id="input-clasificacion" name="clasificacion_servicio" class="form-select" required>
                            <option value="Servicio Ordinario" selected>Servicio Ordinario</option>
                            <option value="Servicio Urgente">Servicio Urgente</option>
                            <option value="Mantenimiento Preventivo">Mantenimiento Preventivo</option>
                            <option value="Mantenimiento Correctivo">Mantenimiento Correctivo</option>
                            <option value="Instalación / Configuración">Instalación / Configuración</option>
                            <option value="Soporte Red / Conectividad">Soporte Red / Conectividad</option>
                        </select>
                        <div class="form-text small text-muted">Indica la prioridad de atención asignada a este ticket.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cancelar
                </button>
                <button type="submit" form="form-tomar-servicio" class="btn btn-sm btn-dark px-4">
                    <i class="fa fa-check me-1"></i>Confirmar y Tomar Servicio
                </button>
            </div>
        </div>
    </div>
</div>
