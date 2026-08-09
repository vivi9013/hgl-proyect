/**
 * Lógica Javascript para el módulo de Categoría de Archivos
 */
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

    const alertaExito = document.getElementById('alertaExito');
    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.dataset.message || 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    const alertaError = document.getElementById('alertaError');
    if (alertaError && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Error!',
            text: alertaError.dataset.message || 'Hubo un error al procesar la categoría.',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. REFERENCIAS AL DOM
    // ─────────────────────────────────────────────────────────────────────────
    const inputCategoria         = document.getElementById('categoria');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner         = document.getElementById('loadingSpinner');
    const btnGuardar             = document.getElementById('btnGuardar');

    const editInputCategoria     = document.getElementById('edit_categoria');
    const editFeedback           = document.getElementById('editFeedbackDisponibilidad');
    const editLoading            = document.getElementById('editLoadingSpinner');
    const btnActualizar          = document.getElementById('btnActualizar');

    // ─────────────────────────────────────────────────────────────────────────
    // C. HELPER — debounce
    // ─────────────────────────────────────────────────────────────────────────
    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. VERIFICACIÓN DE DISPONIBILIDAD DE NOMBRE EN TIEMPO REAL (ALTA Y EDICIÓN)
    // ─────────────────────────────────────────────────────────────────────────
    if (inputCategoria && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        inputCategoria.addEventListener('input', demorarEjecucion(function () {
            const nombre = this.value.trim();

            if (!nombre) {
                feedbackDisponibilidad.innerHTML = '';
                inputCategoria.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            loadingSpinner.style.display = 'block';
            feedbackDisponibilidad.innerHTML = '';

            fetch(`/categoria-archivos/verificar?categoria=${encodeURIComponent(nombre)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => { if (!r.ok) throw new Error(); return r.json(); })
            .then(datos => {
                loadingSpinner.style.display = 'none';
                if (datos.disponible) {
                    feedbackDisponibilidad.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Categoría disponible</span>';
                    inputCategoria.classList.remove('is-invalid');
                    inputCategoria.classList.add('is-valid');
                    btnGuardar.disabled = false;
                } else {
                    feedbackDisponibilidad.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> Esta categoría ya existe</span>';
                    inputCategoria.classList.remove('is-valid');
                    inputCategoria.classList.add('is-invalid');
                    btnGuardar.disabled = true;
                }
            })
            .catch(() => { loadingSpinner.style.display = 'none'; });
        }, 300));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. EDICIÓN DE CATEGORÍA VÍA MODAL Y AJAX (DELEGADO)
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-editar-categoria');
        if (!btnEdit) return;

        e.preventDefault();
        const categoriaId = btnEdit.getAttribute('data-id');

        fetch(`/categoria-archivos/${categoriaId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.categoria) {
                const cat = data.categoria;
                const formEdit = document.getElementById('formEditarCategoria');

                if (formEdit) {
                    formEdit.action = `/categoria-archivos/${cat.id_catego_archivos}`;
                    document.getElementById('edit_categoria_id').value = cat.id_catego_archivos;
                    if (editInputCategoria) editInputCategoria.value = cat.categoria || '';

                    const modalEl = document.getElementById('modalEditarCategoria');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }
            }
        })
        .catch(err => {
            console.error('Error al cargar datos de la categoría:', err);
        });
    });

    // Reabrir modal de edición si regresaron errores en sesión
    const editPageErrors = document.getElementById('hasEditFormErrors');
    if (editPageErrors && editPageErrors.dataset.id) {
        const catId = editPageErrors.dataset.id;
        const formEdit = document.getElementById('formEditarCategoria');
        if (formEdit) formEdit.action = `/categoria-archivos/${catId}`;
        const modalEl = document.getElementById('modalEditarCategoria');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // F. CAMBIO DE ESTATUS (activar / desactivar) con confirmación — DELEGADO
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.btn-toggle-status');
        if (!toggleBtn) return;

        e.preventDefault();

        const url    = toggleBtn.getAttribute('data-url');
        const nombre = toggleBtn.getAttribute('data-nombre');
        const activo = parseInt(toggleBtn.getAttribute('data-activo'));

        const accion         = activo === 1 ? 'desactivar' : 'activar';
        const iconType       = activo === 1 ? 'warning' : 'question';
        const confirmBtnText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';

        const submitStatusForm = () => {
            const form = document.createElement('form');
            form.action = url;
            form.method = 'POST';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                           || document.querySelector('input[name="_token"]')?.value
                           || '';
            const csrfInput = document.createElement('input');
            csrfInput.type  = 'hidden';
            csrfInput.name  = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type  = 'hidden';
            methodInput.name  = '_method';
            methodInput.value = 'PATCH';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title:             `¿Desea ${accion} la categoría?`,
                text:              `La categoría "${nombre}" será ${activo === 1 ? 'desactivada' : 'activada'} en el sistema.`,
                icon:              iconType,
                showCancelButton:  true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor:  '#d33',
                confirmButtonText: confirmBtnText,
                cancelButtonText:  'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) submitStatusForm();
            });
        } else if (confirm(`¿Está seguro de que desea ${accion} la categoría "${nombre}"?`)) {
            submitStatusForm();
        }
    });

});