document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS DE SESIÓN (SweetAlert2)
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text:  alertaExitog.dataset.message || 'Los cambios se han guardado correctamente.',
            icon:  'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar',
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. REFERENCIAS DOM
    // ─────────────────────────────────────────────────────────────────────────
    const cuerpoTabla    = document.getElementById('cuerpoTabla');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const paginacionDiv  = document.getElementById('paginacion');
    const filtroBuscar   = document.getElementById('filtro-buscar');
    const dropdownFiltros= document.getElementById('dropdownFiltros');
    const csrf           = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Modal alta/edición
    const modalAltaEl    = document.getElementById('modalAlta');
    const modalAlta      = modalAltaEl ? new bootstrap.Modal(modalAltaEl) : null;
    const formAlta       = document.getElementById('formAlta');
    const fServicio      = document.getElementById('fServicio');
    const fArea          = document.getElementById('fArea');
    const fEditId        = document.getElementById('fEditId');
    const btnGuardar     = document.getElementById('btnGuardar');
    const modalTitulo    = document.getElementById('modalAltaTitulo');
    const feedbackDup    = document.getElementById('feedbackDuplicado');
    const errServicio    = document.getElementById('errServicio');
    const errArea        = document.getElementById('errArea');

    // ─────────────────────────────────────────────────────────────────────────
    // C. UTILIDADES
    // ─────────────────────────────────────────────────────────────────────────
    function debounce(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    function obtenerFiltros() {
        return {
            buscar:  filtroBuscar?.value.trim() ?? '',
            estatus: Array.from(document.querySelectorAll('.chk-estatus:checked')).map(el => el.value),
            areas:   Array.from(document.querySelectorAll('.chk-area:checked')).map(el => el.value),
        };
    }

    function limpiarErroresModal() {
        [fServicio, fArea].forEach(el => el?.classList.remove('is-invalid', 'is-valid'));
        if (errServicio) errServicio.textContent = '';
        if (errArea) errArea.textContent = '';
        if (feedbackDup) { feedbackDup.textContent = ''; feedbackDup.className = 'feedback-duplicado'; }
    }

    function resetModal() {
        if (formAlta) formAlta.reset();
        if (fEditId) fEditId.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Nuevo Tipo de Servicio';
        limpiarErroresModal();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. TABLA AJAX + PAGINACIÓN
    // ─────────────────────────────────────────────────────────────────────────
    function bindPagination(container) {
        if (!container) return;
        container.querySelectorAll('a.page-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const p = new URL(a.href).searchParams.get('page');
                if (p) cargarTabla(p);
            });
        });
    }

    function cargarTabla(pagina = 1) {
        if (!cuerpoTabla) return;
        const f = obtenerFiltros();
        const params = new URLSearchParams({ page: pagina });
        if (f.buscar) params.set('buscar', f.buscar);
        f.estatus.forEach(v => params.append('estatus[]', v));
        f.areas.forEach(v   => params.append('areas[]', v));

        cuerpoTabla.style.opacity    = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/soporte-tecnico/tipo-servicio?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(datos => {
            cuerpoTabla.style.opacity = '1';
            cuerpoTabla.innerHTML     = datos.html;
            if (infoPaginacion) infoPaginacion.textContent = datos.info;
            if (paginacionDiv) {
                paginacionDiv.innerHTML = datos.links;
                bindPagination(paginacionDiv);
            }
        })
        .catch(() => { cuerpoTabla.style.opacity = '1'; });
    }

    // Buscador con debounce
    if (filtroBuscar) {
        filtroBuscar.addEventListener('input', debounce(() => cargarTabla(1), 320));
    }

    // Dropdown de filtros
    if (dropdownFiltros) {
        dropdownFiltros.addEventListener('filtros:aplicar', () => cargarTabla(1));
        dropdownFiltros.addEventListener('filtros:limpiar', () => {
            if (filtroBuscar) filtroBuscar.value = '';
            cargarTabla(1);
        });
    }

    // Paginación SSR inicial
    bindPagination(paginacionDiv);

    // ─────────────────────────────────────────────────────────────────────────
    // E. TOGGLE DE ESTADO (event delegation)
    // ─────────────────────────────────────────────────────────────────────────
    if (cuerpoTabla) {
        cuerpoTabla.addEventListener('click', function (e) {
            // ── Toggle estatus ─────────────────────────────────────────────
            const btnStatus = e.target.closest('.btn-toggle-status');
            if (btnStatus) {
                e.preventDefault();
                const id = btnStatus.dataset.id;
                if (!id) return;

                fetch(`/soporte-tecnico/tipo-servicio/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(datos => {
                    if (datos.success) {
                        cargarTabla();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '¡Estado Actualizado!',
                                text:  datos.message,
                                icon:  'success',
                                timer: 1600,
                                showConfirmButton: false,
                            });
                        }
                    }
                })
                .catch(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
                    }
                });
                return;
            }

            // ── Abrir modal edición ────────────────────────────────────────
            const btnEditar = e.target.closest('.btn-editar');
            if (btnEditar) {
                e.preventDefault();
                resetModal();
                fEditId.value  = btnEditar.dataset.id;
                fServicio.value= btnEditar.dataset.servicio || '';
                fArea.value    = btnEditar.dataset.area || '';
                if (modalTitulo) modalTitulo.textContent = 'Editar Tipo de Servicio';
                modalAlta?.show();
            }
        });
    }

    // Limpiar modal al cerrarse
    if (modalAltaEl) {
        modalAltaEl.addEventListener('hidden.bs.modal', resetModal);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // F. VALIDACIÓN DE DUPLICADO EN TIEMPO REAL
    // ─────────────────────────────────────────────────────────────────────────
    let verificarTimer = null;

    function verificarDuplicado() {
        const servicio  = fServicio?.value.trim()  ?? '';
        const idArea    = fArea?.value              ?? '';
        const excluirId = fEditId?.value            ?? '';

        if (!feedbackDup) return;

        if (servicio.length < 3 || !idArea) {
            feedbackDup.textContent = '';
            feedbackDup.className   = 'feedback-duplicado';
            fServicio?.classList.remove('is-invalid', 'is-valid');
            return;
        }

        clearTimeout(verificarTimer);
        verificarTimer = setTimeout(() => {
            const params = new URLSearchParams({ servicio, id_area: idArea });
            if (excluirId) params.set('excluir_id', excluirId);

            fetch(`/soporte-tecnico/tipo-servicio/verificar?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    feedbackDup.textContent = '⚠ Ya existe un tipo de servicio con ese nombre en esta área.';
                    feedbackDup.className   = 'feedback-duplicado text-danger';
                    fServicio?.classList.add('is-invalid');
                    fServicio?.classList.remove('is-valid');
                    if (btnGuardar) btnGuardar.disabled = true;
                } else {
                    feedbackDup.textContent = '';
                    feedbackDup.className   = 'feedback-duplicado';
                    fServicio?.classList.remove('is-invalid');
                    if (btnGuardar) btnGuardar.disabled = false;
                }
            })
            .catch(() => {});
        }, 350);
    }

    fServicio?.addEventListener('input',  verificarDuplicado);
    fArea?.addEventListener('change', verificarDuplicado);

    // ─────────────────────────────────────────────────────────────────────────
    // G. SUBMIT DEL FORMULARIO (ALTA Y EDICIÓN)
    // ─────────────────────────────────────────────────────────────────────────
    if (formAlta) {
        formAlta.addEventListener('submit', function (e) {
            e.preventDefault();
            limpiarErroresModal();

            const id       = fEditId?.value ?? '';
            const esEdicion= id !== '';
            const url      = esEdicion
                ? `/soporte-tecnico/tipo-servicio/${id}`
                : `/soporte-tecnico/tipo-servicio`;

            const body = new FormData();
            body.append('_token', csrf);
            body.append('servicio', fServicio?.value.trim() ?? '');
            body.append('id_area',  fArea?.value ?? '');
            if (esEdicion) body.append('_method', 'PUT');

            if (btnGuardar) btnGuardar.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body,
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    modalAlta?.hide();
                    cargarTabla(1);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Listo!',
                            text:  data.message,
                            icon:  'success',
                            timer: 1800,
                            showConfirmButton: false,
                        });
                    }
                } else {
                    // Mostrar errores de validación
                    if (data.errors?.servicio) {
                        fServicio?.classList.add('is-invalid');
                        if (errServicio) errServicio.textContent = data.errors.servicio[0];
                    }
                    if (data.errors?.id_area) {
                        fArea?.classList.add('is-invalid');
                        if (errArea) errArea.textContent = data.errors.id_area[0];
                    }
                    if (!data.errors && data.message && typeof Swal !== 'undefined') {
                        Swal.fire('Error', data.message, 'error');
                    }
                }
            })
            .catch(() => {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Ocurrió un error inesperado.', 'error');
            })
            .finally(() => {
                if (btnGuardar) btnGuardar.disabled = false;
            });
        });
    }

});
