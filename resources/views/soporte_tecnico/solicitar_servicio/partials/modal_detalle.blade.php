{{--
    Partial: modal_detalle.blade.php
    Modal compartido de detalle completo de un servicio.
    Alimentado por AJAX desde solicitar_servicio.js
--}}

<div class="modal fade" id="modal-detalle-servicio" tabindex="-1"
     aria-labelledby="modal-detalle-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modal-detalle-label">
                    <i class="fa fa-ticket me-2"></i>
                    Detalle del Servicio — Folio <span id="modal-det-folio" class="text-warning">—</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- Indicadores de progreso --}}
                <div class="d-flex justify-content-center align-items-center gap-3 py-2 mb-3 bg-light rounded-3 border">
                    <div class="text-center px-2">
                        <div class="fw-bold" id="ind-pendiente">○</div>
                        <div class="small text-muted">1. Pendiente</div>
                    </div>
                    <div class="text-muted"><i class="fa fa-chevron-right"></i></div>
                    <div class="text-center px-2">
                        <div class="fw-bold" id="ind-proceso">○</div>
                        <div class="small text-muted">2. En Proceso</div>
                    </div>
                    <div class="text-muted"><i class="fa fa-chevron-right"></i></div>
                    <div class="text-center px-2">
                        <div class="fw-bold" id="ind-terminado">○</div>
                        <div class="small text-muted">3. Terminado</div>
                    </div>
                    <div class="text-muted"><i class="fa fa-chevron-right"></i></div>
                    <div class="text-center px-2">
                        <div class="fw-bold" id="ind-liberado">○</div>
                        <div class="small text-muted">4. Liberado</div>
                    </div>
                </div>

                <div class="row g-3">
                    {{-- Columna Izquierda: Solicitud --}}
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">
                            <i class="fa fa-file-text-o me-1"></i> Datos de la Solicitud
                        </h6>

                        <div class="mb-2">
                            <span class="text-muted small d-block">Área de Soporte:</span>
                            <span class="fw-semibold" id="modal-det-area">—</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Descripción del Problema:</span>
                            <div class="p-2 bg-light rounded small border" id="modal-det-descripcion" style="white-space: pre-wrap;">—</div>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Fecha y Hora de Petición:</span>
                            <span class="fw-semibold small" id="modal-det-fecha-pet">—</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Estatus Actual:</span>
                            <span class="badge bg-dark" id="modal-det-estatus">—</span>
                        </div>
                    </div>

                    {{-- Columna Derecha: Atención y Resolución --}}
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">
                            <i class="fa fa-wrench me-1"></i> Atención y Resolución
                        </h6>

                        <div class="mb-2">
                            <span class="text-muted small d-block">Técnico Asignado:</span>
                            <span class="fw-semibold text-primary" id="modal-det-servidor">—</span>
                            <span class="badge bg-light text-dark border ms-1" id="modal-det-ext">Ext: —</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Fecha de Atención:</span>
                            <span class="small" id="modal-det-fecha-tom">—</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Equipo / Mobiliario Atendido:</span>
                            <span class="fw-semibold small" id="modal-det-equipo">Sin equipo específico</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Tipo de Servicio:</span>
                            <span class="badge bg-info text-dark" id="modal-det-tipo">—</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Acción / Solución Realizada:</span>
                            <div class="p-2 bg-light rounded small border" id="modal-det-accion" style="white-space: pre-wrap;">—</div>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Fecha de Conclusión:</span>
                            <span class="small" id="modal-det-fecha-ter">—</span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-top d-flex justify-content-between">
                <a href="#" target="_blank" class="btn btn-sm btn-outline-dark" id="modal-btn-imprimir-hoja">
                    <i class="fa fa-print me-1"></i>Ver Hoja de Servicio
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
