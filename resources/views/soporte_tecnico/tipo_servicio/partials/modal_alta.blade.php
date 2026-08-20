{{-- Modal: Alta de Tipo de Servicio --}}
<div class="modal fade" id="modalAlta" tabindex="-1" aria-labelledby="modalAltaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalAltaLabel">
                    <i class="fa fa-plus-circle me-2 text-primary"></i>
                    <span id="modalAltaTitulo">Nuevo Tipo de Servicio</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formAlta" novalidate>
                @csrf
                {{-- ID oculto para modo edición --}}
                <input type="hidden" id="fEditId" value="">
                {{-- Método HTTP para edición --}}
                <input type="hidden" id="fMetodo" value="POST">

                <div class="modal-body px-4 pt-3">

                    {{-- Tipo de servicio --}}
                    <div class="mb-3">
                        <label for="fServicio" class="form-label fw-semibold">
                            Tipo de Servicio <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="fServicio" name="servicio"
                                  rows="3" placeholder="Describe el tipo de servicio técnico..."
                                  required maxlength="500"></textarea>
                        <div class="feedback-duplicado" id="feedbackDuplicado"></div>
                        <div class="invalid-feedback" id="errServicio"></div>
                    </div>

                    {{-- Área de soporte --}}
                    <div class="mb-3">
                        <label for="fArea" class="form-label fw-semibold">
                            Área de Soporte <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="fArea" name="id_area" required>
                            <option value="">— Selecciona un área —</option>
                            @foreach($areasActivas as $area)
                                <option value="{{ $area->id }}">{{ $area->area }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="errArea"></div>
                    </div>

                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnGuardar">
                        <i class="fa fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
