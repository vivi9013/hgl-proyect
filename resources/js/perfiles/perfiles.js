/**
 * Lógica Javascript para el catálogo de Perfiles.
 * Paginación/búsqueda delegada a tabla-interactiva.js.
 */

document.addEventListener('DOMContentLoaded', function () {
    const inputNombre = document.getElementById('nombre');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar = document.getElementById('btnGuardar');

    // ─────────────────────────────────────────────────────────────────────────
    // 1. ALERTAS SWEETALERT2 DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────────────────
    // 2. VERIFICACIÓN DE DISPONIBILIDAD DE NOMBRE DE PERFIL (AJAX)
    // ─────────────────────────────────────────────────────────────────────────
    if (inputNombre && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        let timeoutId;

        inputNombre.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const nombre = this.value.trim();

            if (!nombre) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/perfiles/verificar?nombre=${encodeURIComponent(nombre)}`, {
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
                        feedbackDisponibilidad.innerHTML = '<span class="text-success-custom"><i class="fa fa-check-circle"></i> Nombre disponible</span>';
                        inputNombre.classList.remove('is-invalid');
                        inputNombre.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML = '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> Este perfil ya existe</span>';
                        inputNombre.classList.remove('is-valid');
                        inputNombre.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar perfil:', error);
                });
            }, 300);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. ALTERNAR ESTADO (AJAX) — delegación en document
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-toggle-status');
        if (!boton) return;

        e.preventDefault();

        const id     = boton.dataset.id;
        const nombre = boton.dataset.nombre || '';
        const row    = boton.closest('tr');
        const esInactivo  = row?.classList.contains('text-muted');
        const activo      = esInactivo ? 0 : 1;
        const accion      = activo === 1 ? 'desactivar' : 'activar';
        const confirmText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';
        const iconType    = activo === 1 ? 'warning' : 'question';

        const ejecutar = () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(`/perfiles/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => { if (!r.ok) throw new Error('Error al actualizar el estado'); return r.json(); })
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

                    // Refrescar tabla manteniendo el filtro activo
                    document.querySelector('[data-tabla-interactiva]')
                        ?.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));
                }
            })
            .catch(() => {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `¿Desea ${accion} el perfil?`,
                text: `El perfil "${nombre}" será ${activo === 1 ? 'desactivado' : 'activado'} en el sistema y podría limitar accesos.`,
                icon: iconType,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) ejecutar(); });
        } else {
            if (confirm(`¿Está seguro de que desea ${accion} el perfil "${nombre}"?`)) ejecutar();
        }
    });
});