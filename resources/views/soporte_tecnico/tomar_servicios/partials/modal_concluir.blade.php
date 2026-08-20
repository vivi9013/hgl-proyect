{{-- Modal: modal_concluir.blade.php --}}
<div class="modal fade" id="modal-concluir-servicio" tabindex="-1" aria-labelledby="modal-concluir-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="modal-concluir-label">
                    <i class="fa fa-check-square-o text-success me-2"></i>Concluir y Resolver Servicio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                {{-- Tarjeta de Resumen del Ticket --}}
                <div class="card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body p-3 bg-white">
                        <div class="row g-2 align-items-center">
                            <div class="col-6 col-md-3">
                                <span class="text-muted small d-block">Folio:</span>
                                <strong id="concluir-label-folio" class="text-primary fs-6">#—</strong>
                            </div>
                            <div class="col-6 col-md-5">
                                <span class="text-muted small d-block">Solicitante:</span>
                                <strong id="concluir-label-solicitante" class="text-dark small d-block text-truncate">—</strong>
                            </div>
                            <div class="col-12 col-md-4 text-md-end">
                                <span class="badge bg-warning text-dark px-2 py-1">En Atención Activa</span>
                            </div>
                            <div class="col-12 mt-2 pt-2 border-top">
                                <span class="text-muted small d-block fw-semibold">Problema Reportado:</span>
                                <div class="p-2 bg-light rounded-2 small text-dark mt-1 border" id="concluir-label-desc" style="max-height: 80px; overflow-y: auto;">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="form-concluir-servicio">
                    @csrf
                    <input type="hidden" id="concluir-id-servicio" name="id">
                    <input type="hidden" id="concluir-input-id-mobiliario" name="id_mobiliario" value="">

                    {{-- 1. Tipo de Servicio --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-3">
                        <div class="card-body p-3 bg-white">
                            <label for="concluir-select-tipo" class="form-label fw-bold small text-dark mb-1">
                                <i class="fa fa-wrench me-1 text-secondary"></i> Tipo de Servicio / Mantenimiento Realizado <span class="text-danger">*</span>
                            </label>
                            <select id="concluir-select-tipo" name="id_tipo_servicio" class="form-select form-select-sm" required>
                                <option value="">-- Seleccionar Tipo de Servicio --</option>
                                @foreach($tiposServicio as $ts)
                                    <option value="{{ $ts->id }}">{{ $ts->servicio }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 2. Selector Visual Elegante de Mobiliario / Equipo --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-3">
                        <div class="card-body p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold small text-dark mb-0">
                                    <i class="fa fa-desktop me-1 text-secondary"></i> Equipo / Mobiliario Atendido (Opcional)
                                </label>
                                <button type="button" class="btn btn-xs btn-outline-dark rounded-pill px-3 py-1" id="btn-toggle-inventario-panel">
                                    <i class="fa fa-search me-1"></i> <span id="btn-toggle-inventario-text">Seleccionar del Inventario</span>
                                </button>
                            </div>

                            {{-- Estado del Equipo Seleccionado --}}
                            <div id="box-equipo-seleccionado" class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary px-2 py-1" id="badge-equipo-inv">Sin equipo</span>
                                    <div>
                                        <div class="fw-bold small text-dark" id="txt-equipo-desc">Servicio general / Sin equipo específico</div>
                                        <small class="text-muted d-block" id="txt-equipo-detalles">Soporte en sitio, red o software general</small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger d-none" id="btn-quitar-equipo" title="Quitar equipo seleccionado">
                                    <i class="fa fa-times"></i> Quitar
                                </button>
                            </div>

                            {{-- Panel Desplegable de Búsqueda de Inventario --}}
                            <div id="panel-buscar-inventario" class="mt-3 p-3 bg-light rounded-3 border d-none">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                    <input type="text" id="input-buscar-equipo-inline" class="form-control" placeholder="Filtrar por No. inventario, tipo, marca, modelo o responsable...">
                                </div>

                                <div class="table-responsive rounded-2 border bg-white" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-hover align-middle mb-0" id="tabla-inventario-modal" style="font-size: 0.82rem;">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="ps-2">No. Inventario</th>
                                                <th>Tipo / Descripción</th>
                                                <th>Marca & Modelo</th>
                                                <th>Responsable</th>
                                                <th class="text-center pe-2">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-inventario-modal">
                                            <tr>
                                                <td colspan="5" class="text-center py-3 text-muted">Cargando inventario del área...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- 3. Acciones Realizadas / Solución --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-2">
                        <div class="card-body p-3 bg-white">
                            <label for="concluir-input-accion" class="form-label fw-bold small text-dark mb-1">
                                <i class="fa fa-pencil-square-o me-1 text-secondary"></i> Descripción de Acciones y Solución Técnica <span class="text-danger">*</span>
                            </label>
                            <textarea id="concluir-input-accion"
                                      name="accion_realizada"
                                      class="form-control"
                                      rows="3"
                                      minlength="10"
                                      maxlength="3000"
                                      required
                                      placeholder="Detalla de forma clara el diagnóstico realizado, refacciones cambiadas o configuración aplicada..."></textarea>
                            <div class="form-text small text-muted">Mínimo 10 caracteres. Esta información se imprimirá en la Hoja de Servicio oficial.</div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer border-top bg-white py-3">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cancelar
                </button>
                <button type="submit" form="form-concluir-servicio" class="btn btn-sm btn-success px-4 fw-bold shadow-sm">
                    <i class="fa fa-check-circle me-1"></i>Concluir Servicio
                </button>
            </div>
        </div>
    </div>
</div>
