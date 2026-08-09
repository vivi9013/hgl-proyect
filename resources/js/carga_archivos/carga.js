document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitog.dataset.message || 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    const alertaError = document.getElementById('alertaError');
    if (alertaError && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Error!',
            text: alertaError.dataset.message || 'Hubo un error al procesar el archivo.',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. REFERENCIAS AL DOM
    // ─────────────────────────────────────────────────────────────────────────
    const inputNombre  = document.getElementById('nombre');
    const inputVersion = document.getElementById('version');
    const selectTipo   = document.getElementById('tipo');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar     = document.getElementById('btnGuardar');

    const editInputNombre  = document.getElementById('edit_nombre');
    const editInputVersion = document.getElementById('edit_version');
    const editSelectTipo   = document.getElementById('edit_tipo');
    const editInputDesc    = document.getElementById('edit_desc');

    // ─────────────────────────────────────────────────────────────────────────
    // C. HELPER — debounce
    // ─────────────────────────────────────────────────────────────────────────
    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. VERIFICAR DISPONIBILIDAD DE NOMBRE + VERSIÓN (ALTA) — AJAX
    // ─────────────────────────────────────────────────────────────────────────
    if (inputNombre && inputVersion) {
        function verificarDisponibilidad() {
            const nombre  = inputNombre.value.trim();
            const version = inputVersion.value.trim();
            const tipo    = selectTipo ? selectTipo.value.trim() : '';

            if (!nombre || !version || !tipo) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                if (btnGuardar) btnGuardar.disabled = false;
                return;
            }

            loadingSpinner.style.display = 'block';
            feedbackDisponibilidad.innerHTML = '';

            fetch(`/carga-archivos/verificar-nombre?nombre=${encodeURIComponent(nombre)}&version=${encodeURIComponent(version)}&tipo=${encodeURIComponent(tipo)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => { if (!r.ok) throw new Error(); return r.json(); })
            .then(datos => {
                loadingSpinner.style.display = 'none';
                if (datos.disponible) {
                    feedbackDisponibilidad.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Nombre y versión disponibles</span>';
                    inputNombre.classList.remove('is-invalid');
                    inputNombre.classList.add('is-valid');
                    if (btnGuardar) btnGuardar.disabled = false;
                } else {
                    feedbackDisponibilidad.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> Este nombre y versión ya existen en esta categoría</span>';
                    inputNombre.classList.remove('is-valid');
                    inputNombre.classList.add('is-invalid');
                    if (btnGuardar) btnGuardar.disabled = true;
                }
            })
            .catch(() => { loadingSpinner.style.display = 'none'; });
        }

        const verificarConDebounce = demorarEjecucion(verificarDisponibilidad, 300);

        inputNombre.addEventListener('blur', verificarDisponibilidad);
        inputNombre.addEventListener('input', verificarConDebounce);
        inputVersion.addEventListener('change', verificarDisponibilidad);
        inputVersion.addEventListener('input', verificarConDebounce);
        if (selectTipo) selectTipo.addEventListener('change', verificarDisponibilidad);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. EDICIÓN DE ARCHIVO VÍA MODAL Y AJAX (DELEGADO)
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-editar-archivo');
        if (!btnEdit) return;

        e.preventDefault();
        const archivoId = btnEdit.getAttribute('data-id');

        fetch(`/carga-archivos/editar/${archivoId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.archivo) {
                const a = data.archivo;
                const formEdit = document.getElementById('formEditarArchivo');

                if (formEdit) {
                    formEdit.action = `/carga-archivos/actualizar/${a.id_archivo}`;
                    document.getElementById('edit_archivo_id').value = a.id_archivo;

                    // Limpiar posibles estados de validación anteriores
                    formEdit.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    const feedback = document.getElementById('editFeedbackDisponibilidad');
                    if (feedback) feedback.innerHTML = '';

                    if (editInputNombre)  editInputNombre.value  = a.nombre || '';
                    if (editInputVersion) editInputVersion.value = a.version_archivo || 1;
                    if (editSelectTipo)   editSelectTipo.value   = a.id_catego || '';
                    if (editInputDesc)    editInputDesc.value    = a.descripcion_archivo || '';

                    const modalEl = document.getElementById('modalEditarArchivo');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }
            }
        })
        .catch(err => {
            console.error('Error al cargar datos del archivo:', err);
        });
    });

    // Reabrir modal de edición si regresaron errores en sesión
    const editPageErrors = document.getElementById('hasEditFormErrors');
    if (editPageErrors && editPageErrors.dataset.id) {
        const archivoId = editPageErrors.dataset.id;
        const formEdit = document.getElementById('formEditarArchivo');
        if (formEdit) formEdit.action = `/carga-archivos/actualizar/${archivoId}`;

        // Si por alguna razón la vista no tenía old(), consultamos por AJAX los datos de respaldo
        if (editInputNombre && !editInputNombre.value) {
            fetch(`/carga-archivos/editar/${archivoId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.archivo) {
                    const a = data.archivo;
                    if (editInputNombre  && !editInputNombre.value)  editInputNombre.value  = a.nombre || '';
                    if (editInputVersion && !editInputVersion.value) editInputVersion.value = a.version_archivo || 1;
                    if (editSelectTipo   && !editSelectTipo.value)   editSelectTipo.value   = a.id_catego || '';
                    if (editInputDesc    && !editInputDesc.value)    editInputDesc.value    = a.descripcion_archivo || '';
                }
            });
        }

        const modalEl = document.getElementById('modalEditarArchivo');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

});