@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa fa-link me-2 text-primary"></i> Relación de Áreas de Abastecimiento
            </h4>
            <p class="text-muted small mb-0">Vincula subáreas a cada área principal de abastecimiento. Los cambios se reflejan en tiempo real.</p>
        </div>
        <a href="{{ route('areas_abastecimiento.index') }}" class="btn btn-outline-secondary btn-sm px-3 mt-2 mt-md-0">
            <i class="fa fa-arrow-left me-1"></i> Regresar al Catálogo
        </a>
    </div>

    {{-- Tabla de Áreas --}}
    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="width: 80px;" class="text-center">Agregar</th>
                        <th>Áreas de Abastecimiento</th>
                        <th class="text-center" style="width: 180px;">Total de Subáreas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($areas as $index => $area)
                        <tr class="area-row" id="area-row-{{ $area->id_area_abastecimiento }}">
                            <td class="text-center fw-bold text-secondary">{{ $areas->firstItem() + $index }}</td>
                            <td class="text-center">
                                <button type="button"
                                    class="btn btn-primary btn-sm px-2 py-1 btn-agregar-subarea"
                                    data-id="{{ $area->id_area_abastecimiento }}"
                                    data-nombre="{{ $area->nombre }}"
                                    title="Agregar Subárea a {{ $area->nombre }}">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark mb-1">{{ $area->nombre }}</div>
                                {{-- Lista de subáreas vinculadas (inline) --}}
                                <div id="subareas-list-{{ $area->id_area_abastecimiento }}" class="d-flex flex-wrap gap-1">
                                    @foreach($area->subareas as $sub)
                                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 subarea-badge"
                                              id="badge-{{ $area->id_area_abastecimiento }}-{{ $sub->id_subarea_abastecimiento }}">
                                            {{ $sub->siglas ?? $sub->nombre }}
                                            <button type="button"
                                                class="btn-close btn-close-sm ms-1 btn-desvincular"
                                                style="font-size: 0.5rem;"
                                                data-id-area="{{ $area->id_area_abastecimiento }}"
                                                data-id-subarea="{{ $sub->id_subarea_abastecimiento }}"
                                                title="Desvincular {{ $sub->nombre }}">
                                            </button>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary fs-6 px-3 py-2 counter-subareas"
                                      id="count-{{ $area->id_area_abastecimiento }}">
                                    {{ $area->subareas_count }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fa fa-folder-open me-2"></i> No hay áreas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($areas->hasPages())
                    <tfoot>
                        <tr>
                            <td colspan="4" class="py-2 px-3 border-top">
                                {{ $areas->links() }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

{{-- Modal: Vincular Subárea --}}
<div class="modal fade" id="modalVincularSubarea" tabindex="-1" aria-labelledby="modalVincularSubareaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalVincularSubareaLabel">
                    <i class="fa fa-plus-circle me-1 text-primary"></i>
                    Agregar Subárea a: <span id="lblNombreAreaModal" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="alertVincularError" class="alert alert-danger d-none py-2 small"></div>
                <div id="alertVincularSuccess" class="alert alert-success d-none py-2 small"></div>
                <div class="mb-3">
                    <label for="selectSubareaVincular" class="form-label small fw-semibold text-dark">
                        Subárea a Vincular <span class="text-danger">*</span>
                    </label>
                    <select id="selectSubareaVincular" class="form-select form-select-sm">
                        <option value="">-- Seleccionar Subárea --</option>
                        @foreach($todasSubareas as $sub)
                            <option value="{{ $sub->id_subarea_abastecimiento }}"
                                    data-nombre="{{ $sub->nombre }}"
                                    data-siglas="{{ $sub->siglas }}">
                                {{ $sub->nombre }}{{ $sub->siglas ? ' [' . $sub->siglas . ']' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted small mt-1">Solo se muestran subáreas activas no vinculadas a esta área.</div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarVincular" class="btn btn-sm btn-primary px-3">
                    <i class="fa fa-link me-1"></i> Vincular Subárea
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let idAreaActual = null;
    let subareasVinculadasActuales = [];

    const modal = new bootstrap.Modal(document.getElementById('modalVincularSubarea'));
    const selectSubarea = document.getElementById('selectSubareaVincular');
    const btnConfirmar = document.getElementById('btnConfirmarVincular');
    const alertError = document.getElementById('alertVincularError');
    const alertSuccess = document.getElementById('alertVincularSuccess');

    // ── Abrir modal al presionar "+" ──
    document.querySelectorAll('.btn-agregar-subarea').forEach(btn => {
        btn.addEventListener('click', function () {
            idAreaActual = this.dataset.id;
            document.getElementById('lblNombreAreaModal').textContent = this.dataset.nombre;

            // Obtener IDs de subáreas ya vinculadas en esa fila
            const badges = document.querySelectorAll(`#subareas-list-${idAreaActual} .subarea-badge`);
            subareasVinculadasActuales = Array.from(badges).map(b => {
                const btn = b.querySelector('.btn-desvincular');
                return btn ? btn.dataset.idSubarea : null;
            }).filter(Boolean);

            // Filtrar opciones del select: ocultar las ya vinculadas
            Array.from(selectSubarea.options).forEach(opt => {
                if (!opt.value) return;
                opt.hidden = subareasVinculadasActuales.includes(opt.value);
            });
            selectSubarea.value = '';
            alertError.classList.add('d-none');
            alertSuccess.classList.add('d-none');
            modal.show();
        });
    });

    // ── Confirmar vínculo ──
    btnConfirmar.addEventListener('click', function () {
        const idSubarea = selectSubarea.value;
        if (!idSubarea) {
            alertError.textContent = 'Debe seleccionar una subárea.';
            alertError.classList.remove('d-none');
            return;
        }
        alertError.classList.add('d-none');
        btnConfirmar.disabled = true;

        fetch(`/peticion-insumos/areas-abastecimiento/${idAreaActual}/subareas`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ id_subarea_abastecimiento: idSubarea })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Agregar badge a la fila
                const list = document.getElementById(`subareas-list-${idAreaActual}`);
                const badgeId = `badge-${idAreaActual}-${data.subarea.id}`;
                const badgeHtml = `
                    <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 subarea-badge"
                          id="${badgeId}">
                        ${data.subarea.siglas || data.subarea.nombre}
                        <button type="button"
                            class="btn-close btn-close-sm ms-1 btn-desvincular"
                            style="font-size: 0.5rem;"
                            data-id-area="${idAreaActual}"
                            data-id-subarea="${data.subarea.id}"
                            title="Desvincular ${data.subarea.nombre}">
                        </button>
                    </span>
                `;
                list.insertAdjacentHTML('beforeend', badgeHtml);

                // Actualizar contador
                const counter = document.getElementById(`count-${idAreaActual}`);
                if (counter) counter.textContent = parseInt(counter.textContent) + 1;

                // Ocultar la opción en el select
                const opt = selectSubarea.querySelector(`option[value="${data.subarea.id}"]`);
                if (opt) opt.hidden = true;
                selectSubarea.value = '';

                alertSuccess.textContent = data.mensaje;
                alertSuccess.classList.remove('d-none');
                setTimeout(() => alertSuccess.classList.add('d-none'), 2000);
            } else {
                alertError.textContent = data.mensaje || 'Error al vincular.';
                alertError.classList.remove('d-none');
            }
        })
        .catch(() => {
            alertError.textContent = 'Error de red al procesar la solicitud.';
            alertError.classList.remove('d-none');
        })
        .finally(() => { btnConfirmar.disabled = false; });
    });

    // ── Desvincular subárea (event delegation) ──
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-desvincular');
        if (!btn) return;

        const idArea    = btn.dataset.idArea;
        const idSubarea = btn.dataset.idSubarea;

        if (!confirm('¿Deseas desvincular esta subárea del área?')) return;

        fetch(`/peticion-insumos/areas-abastecimiento/${idArea}/subareas/${idSubarea}/desvincular`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remover el badge del DOM
                const badge = document.getElementById(`badge-${idArea}-${idSubarea}`);
                if (badge) badge.remove();

                // Decrementar contador
                const counter = document.getElementById(`count-${idArea}`);
                if (counter) counter.textContent = Math.max(0, parseInt(counter.textContent) - 1);

                // Reactivar opción en el select si el modal de esa área está abierto
                if (idAreaActual === idArea) {
                    const opt = selectSubarea.querySelector(`option[value="${idSubarea}"]`);
                    if (opt) opt.hidden = false;
                }
            }
        })
        .catch(() => alert('Error de red al desvincular.'));
    });
});
</script>
@endpush
@endsection
