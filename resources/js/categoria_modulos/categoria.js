/**
 * Lógica Javascript para el módulo de Categoría de Módulos
 * Incluye: verificación de nombre en tiempo real, toggle status (con confirmación),
 * toggle colapsar (sin confirmación), edición modal y SweetAlert2.
 * La paginación y búsqueda AJAX son manejadas por tabla-interactiva.js.
 */

document.addEventListener('DOMContentLoaded', function () {
    const inputCategoria         = document.getElementById('categoria');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner         = document.getElementById('loadingSpinner');
    const btnGuardar             = document.getElementById('btnGuardar');

    // 1. Mostrar SweetAlert2 si existen los divs de alerta de sesión
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito  = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // 2. Verificación de disponibilidad de nombre en tiempo real (AJAX)
    if (inputCategoria && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        let timeoutId;

        inputCategoria.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const nombre = this.value.trim();

            if (!nombre) {
                feedbackDisponibilidad.innerHTML = '';
                inputCategoria.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/categoria-modulos/verificar?categoria=${encodeURIComponent(nombre)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error de servidor');
                    return response.json();
                })
                .then(data => {
                    loadingSpinner.style.display = 'none';

                    if (data.disponible) {
                        feedbackDisponibilidad.innerHTML = '<span class="text-success-custom"><i class="fa fa-check-circle"></i> Categoría disponible</span>';
                        inputCategoria.classList.remove('is-invalid');
                        inputCategoria.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML = '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> Esta categoría ya existe</span>';
                        inputCategoria.classList.remove('is-valid');
                        inputCategoria.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar categoría:', error);
                });
            }, 300);
        });
    }

    // ── Helper para refrescar la tabla vía tabla-interactiva.js ─────────────
    function refrescarTabla() {
        document.querySelector('[data-tabla-interactiva]')
            ?.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));
    }

    // ── Toggle Status — delegación con confirmación SweetAlert2 ─────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-toggle-status');
        if (!boton) return;

        const id     = boton.dataset.id;
        const nombre = boton.dataset.nombre ?? '';
        const icono  = boton.querySelector('i');
        const esActivo = icono && icono.classList.contains('text-success');
        const accion = esActivo ? 'desactivar' : 'activar';
        const iconType = esActivo ? 'warning' : 'question';

        const runFetch = () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(`/categoria-modulos/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Error al actualizar el estado');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Operación Satisfactoria!',
                            text: data.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                    refrescarTabla();
                }
            })
            .catch(err => {
                console.error(err);
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `¿Desea ${accion} la categoría?`,
                text: `La categoría "${nombre}" será ${esActivo ? 'desactivada' : 'activada'} en el sistema.`,
                icon: iconType,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) runFetch(); });
        } else {
            if (confirm(`¿Está seguro de que desea ${accion} la categoría "${nombre}"?`)) runFetch();
        }
    });

    // ── Toggle Colapsar — delegación sin confirmación ────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-toggle-colapsar');
        if (!boton) return;

        const id   = boton.dataset.id;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(`/categoria-modulos/${id}/colapsar`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Error al actualizar la configuración del panel');
            return res.json();
        })
        .then(data => {
            if (data.success) refrescarTabla();
        })
        .catch(err => console.error('Error al colapsar:', err));
    });

    // ── Modal Editar Categoría — delegación ──────────────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-editar-categoria');
        if (!boton) return;

        const id       = boton.dataset.id;
        const formEdit = document.getElementById('formEditarCategoria');
        if (!formEdit) return;

        formEdit.action = `/categoria-modulos/${id}`;

        fetch(`/categoria-modulos/${id}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Error al cargar la categoría');
            return res.json();
        })
        .then(data => {
            if (data.success) {
                const cat = data.categoria;
                document.getElementById('edit_categoria').value = cat.categoria || '';
                document.getElementById('edit_proyecto').value  = cat.proyecto  || '';
                document.getElementById('edit_colapsado').value = cat.colapsado || 'no';
                document.getElementById('edit_orden').value     = cat.orden     || 1;

                const modalEl = document.getElementById('modalEditarCategoria');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            }
        })
        .catch(err => console.error('Error al cargar la categoría:', err));
    });

    // ── Spinner al guardar formulario de edición ─────────────────────────────
    const formEdit = document.getElementById('formEditarCategoria');
    if (formEdit) {
        formEdit.addEventListener('submit', function () {
            const btn = document.getElementById('btnActualizarCategoria') || document.getElementById('btnActualizar');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Actualizando...';
            }
        });
    }
});