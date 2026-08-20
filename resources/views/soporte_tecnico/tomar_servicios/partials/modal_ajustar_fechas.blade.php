{{-- Modal: modal_ajustar_fechas.blade.php --}}
<div class="modal fade" id="modal-ajustar-fechas" tabindex="-1" aria-labelledby="modal-ajustar-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modal-ajustar-label">
                    <i class="fa fa-calendar-times-o me-2"></i>Ajustar Fechas / Horas con Auditoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Modificar fechas u horas de registro quedará registrado en la bitácora de auditoría del servicio <strong id="ajustar-label-folio">#—</strong>.
                </p>

                <form id="form-ajustar-fechas">
                    @csrf
                    <input type="hidden" id="ajustar-id-servicio" name="id">

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Fecha Petición</label>
                            <input type="date" id="ajustar-fecha-pet" name="fecha_peticion" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Hora Petición</label>
                            <input type="time" id="ajustar-hora-pet" name="hora_peticion" class="form-control form-control-sm" step="1" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Fecha Tomado</label>
                            <input type="date" id="ajustar-fecha-tom" name="fecha_tomado" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Hora Tomado</label>
                            <input type="time" id="ajustar-hora-tom" name="hora_tomado" class="form-control form-control-sm" step="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ajustar-motivo" class="form-label fw-semibold small">
                            Motivo / Justificación del Ajuste <span class="text-danger">*</span>
                        </label>
                        <textarea id="ajustar-motivo"
                                  name="motivo_modificado"
                                  class="form-control"
                                  rows="3"
                                  minlength="5"
                                  maxlength="500"
                                  required
                                  placeholder="Explica la razón del cambio de fecha/hora..."></textarea>
                        <div class="form-text small text-muted">Obligatorio para fines de trazabilidad y auditoría.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cancelar
                </button>
                <button type="submit" form="form-ajustar-fechas" class="btn btn-sm btn-dark px-4">
                    <i class="fa fa-save me-1"></i>Guardar Ajuste
                </button>
            </div>
        </div>
    </div>
</div>
