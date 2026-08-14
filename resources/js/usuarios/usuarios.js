/**
 * Lógica Javascript para el módulo de Usuarios.
 * Paginación/búsqueda delegada a tabla-interactiva.js.
 */

document.addEventListener('DOMContentLoaded', function () {
    const inputNombre = document.getElementById('username');
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
            text: alertaExitog.getAttribute('data-message'),
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.getAttribute('data-message'),
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. VERIFICACIÓN DE DISPONIBILIDAD DE NOMBRE DE USUARIO (AJAX)
    // ─────────────────────────────────────────────────────────────────────────
    if (inputNombre && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        let timeoutId;

        inputNombre.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const username = this.value.trim();

            if (!username) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/usuarios/verificar?username=${encodeURIComponent(username)}`, {
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
                        feedbackDisponibilidad.innerHTML = '<span class="text-success-custom"><i class="fa fa-check-circle"></i> Nombre de usuario disponible</span>';
                        inputNombre.classList.remove('is-invalid');
                        inputNombre.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML = '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> Este usuario ya está en uso</span>';
                        inputNombre.classList.remove('is-valid');
                        inputNombre.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar usuario:', error);
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

        const id            = boton.dataset.id;
        const nombreUsuario = boton.dataset.nombre || '';
        const row           = boton.closest('tr');
        const esInactivo    = row?.classList.contains('text-muted');
        const activo        = esInactivo ? 0 : 1;
        const accion        = activo === 1 ? 'desactivar' : 'activar';
        const confirmText   = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';
        const iconType      = activo === 1 ? 'warning' : 'question';

        const ejecutar = () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(`/usuarios/${id}/status`, {
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
                title: `¿Desea ${accion} el usuario?`,
                text: `El usuario "${nombreUsuario}" será ${activo === 1 ? 'desactivado' : 'activado'} en el sistema.`,
                icon: iconType,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) ejecutar(); });
        } else {
            if (confirm(`¿Está seguro de que desea ${accion} el usuario "${nombreUsuario}"?`)) ejecutar();
        }
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 4. RESTABLECER CONTRASEÑA — delegación en document
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-restablecer-password');
        if (!boton) return;

        e.preventDefault();

        const id            = boton.dataset.id;
        const row           = boton.closest('tr');
        const nombreUsuario = row?.querySelector('td:nth-child(3)')?.textContent.trim() ?? '';

        const ejecutar = () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(`/usuarios/${id}/restablecer`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => { if (!r.ok) throw new Error('Error al restablecer la contraseña'); return r.json(); })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Operación Satisfactoria!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(() => {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo restablecer la contraseña.', 'error');
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Desea reiniciar la contraseña del usuario?',
                text: `La contraseña del usuario "${nombreUsuario}" será restablecida al valor predeterminado configurado en el sistema y se le solicitará cambiarla en su próximo inicio de sesión.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, reiniciar',
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) ejecutar(); });
        } else {
            if (confirm(`¿Está seguro de reiniciar la contraseña del usuario "${nombreUsuario}"?`)) ejecutar();
        }
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 5. SOLICITUDES DE RECUPERACIÓN DE CONTRASEÑA
    // ─────────────────────────────────────────────────────────────────────────
    const modalSolicitudes = document.getElementById('modalSolicitudesPendientes');

    function cargarSolicitudesPendientes() {
        const cuerpo = document.getElementById('cuerpoSolicitudesPendientes');
        if (!cuerpo) return;

        fetch('/usuarios/solicitudes-pendientes', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            cuerpo.innerHTML = data.html;
            actualizarBadgeSolicitudes(data.total);
            window.actualizarNotificaciones?.();
        })
        .catch(() => { cuerpo.innerHTML = '<p class="text-danger text-center py-4">No se pudieron cargar las solicitudes.</p>'; });
    }

    function actualizarBadgeSolicitudes(total) {
        let badge = document.getElementById('badgeSolicitudesPendientes');
        const boton = document.getElementById('btnSolicitudesPendientes');
        if (total > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.id = 'badgeSolicitudesPendientes';
                badge.className = 'badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle';
                boton?.appendChild(badge);
            }
            badge.textContent = total;
        } else if (badge) {
            badge.remove();
        }
    }

    if (modalSolicitudes) {
        modalSolicitudes.addEventListener('shown.bs.modal', cargarSolicitudesPendientes);
    }

    document.addEventListener('click', function (e) {
        const btnAprobar = e.target.closest('.btn-aprobar-solicitud');
        const btnRechazar = e.target.closest('.btn-rechazar-solicitud');
        if (!btnAprobar && !btnRechazar) return;

        const id = (btnAprobar || btnRechazar).dataset.id;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const enviarAccion = (endpoint, body = {}) => {
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body)
            })
            .then(r => r.json())
            .then(data => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ title: data.success ? 'Listo' : 'Aviso', text: data.message, icon: data.success ? 'success' : 'warning', timer: 1800, showConfirmButton: false });
                }
                cargarSolicitudesPendientes();
                window.actualizarNotificaciones?.();
            })
            .catch(() => { if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error'); });
        };

        if (btnAprobar) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Aprobar solicitud?',
                    text: 'Se restablecerá la contraseña de este usuario al valor por defecto.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, aprobar',
                    cancelButtonText: 'Cancelar'
                }).then(r => { if (r.isConfirmed) enviarAccion(`/usuarios/solicitudes/${id}/aprobar`); });
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Rechazar solicitud?',
                    input: 'textarea',
                    inputPlaceholder: 'Nota opcional...',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, rechazar',
                    cancelButtonText: 'Cancelar'
                }).then(r => { if (r.isConfirmed) enviarAccion(`/usuarios/solicitudes/${id}/rechazar`, { nota: r.value || null }); });
            }
        }
    });
});