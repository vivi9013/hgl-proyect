{{--
    Partial: modal_detalle.blade.php
    Modal compartido de detalle completo de un servicio.
    Alimentado por AJAX desde solicitar_servicio.js
--}}

<div class="modal fade" id="modal-detalle-servicio" tabindex="-1"
     aria-labelledby="modal-detalle-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">

            <div class="modal-header" style="border-bottom: 2px solid #000;">
                <h5 class="modal-title fw-bold" id="modal-detalle-label">
                    <i class="fa fa-info-circle me-2"></i>
                    Detalle del Servicio — Folio <span id="modal-det-folio">—</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Indicadores de progreso --}}
                <div class="d-flex justify-content-center gap-4 py-3 mb-2 border-bottom">
                    <div class="text-center">
                        <div class="fs-5" id="ind-pendiente">○</div>
                        <div class="small text-muted mt-1">Pendiente</div>
                    </div>
                    <div class="text-muted pt-2">→</div>
                    <div class="text-center">
                        <div class="fs-5" id="ind-proceso">○</div>
                        <div class="small text-muted mt-1">En Proceso</div>
                    </div>
                    <div class="text-muted pt-2">→</div>
                    <div class="text-center">
                        <div class="fs-5" id="ind-terminado">○</div>
                        <div class="small text-muted mt-1">Terminado</div>
                    </div>
                </div>

                {{-- Sección: Solicitud --}}
                <p class="modal-section-title mt-3">Información de la Solicitud</p>

                <div class="detalle-item">
                    <i class="fa fa-tag"></i>
                    <span class="detalle-label">Área solicitada:</span>
                    <span class="detalle-value" id="modal-det-area">—</span>
                </div>
                <div class="detalle-item">
                    <i class="fa fa-align-left"></i>
                    <span class="detalle-label">Descripción:</span>
                    <span class="detalle-value" id="modal-det-descripcion">—</span>
                </div>
                <div class="detalle-item">
                    <i class="fa fa-calendar"></i>
                    <span class="detalle-label">Fecha / Hora petición:</span>
                    <span class="detalle-value" id="modal-det-fecha-pet">—</span>
                </div>

                {{-- Sección: Atención --}}
                <p class="modal-section-title mt-3">Información del Técnico</p>

                <div class="detalle-item">
                    <i class="fa fa-user"></i>
                    <span class="detalle-label">Técnico asignado:</span>
                    <span class="detalle-value" id="modal-det-servidor">—</span>
                </div>
                <div class="detalle-item">
                    <i class="fa fa-phone"></i>
                    <span class="detalle-label">Extensión:</span>
                    <span class="detalle-value" id="modal-det-ext">—</span>
                </div>
                <div class="detalle-item">
                    <i class="fa fa-play-circle"></i>
                    <span class="detalle-label">Tomado el:</span>
                    <span class="detalle-value" id="modal-det-fecha-tom">—</span>
                </div>

                {{-- Sección: Resolución --}}
                <p class="modal-section-title mt-3">Resolución</p>

                <div class="detalle-item">
                    <i class="fa fa-check-circle"></i>
                    <span class="detalle-label">Terminado el:</span>
                    <span class="detalle-value" id="modal-det-fecha-ter">—</span>
                </div>
                <div class="detalle-item">
                    <i class="fa fa-folder"></i>
                    <span class="detalle-label">Clasificación:</span>
                    <span class="detalle-value" id="modal-det-clasificacion">—</span>
                </div>
                <div class="detalle-item">
                    <i class="fa fa-wrench"></i>
                    <span class="detalle-label">Tipo de servicio:</span>
                    <span class="detalle-value" id="modal-det-tipo">—</span>
                </div>
                <div class="detalle-item">
                    <i class="fa fa-comment"></i>
                    <span class="detalle-label">Acción realizada:</span>
                    <span class="detalle-value" id="modal-det-accion">—</span>
                </div>

            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
