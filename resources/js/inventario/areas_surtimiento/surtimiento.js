/**
 * Lógica JavaScript para el módulo de Áreas de Surtimiento
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

    const inputNombre              = document.getElementById('nombre');
    const selectTipo               = document.getElementById('tipo');
    const feedbackDisponibilidad   = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner           = document.getElementById('loadingSpinner');
    const btnGuardar               = document.getElementById('btnGuardar');

    // ── 1. Alertas SweetAlert2 con sesión de Laravel ──────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito  = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'El área de surtimiento se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'El área de surtimiento se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // ── 2. Verificación AJAX de disponibilidad de nombre y tipo (debounce 300 ms) ────
    if (inputNombre && selectTipo && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        let timeoutId;

        const realizarVerificacion = () => {
            clearTimeout(timeoutId);
            const nombre = inputNombre.value.trim();
            const tipo = selectTipo.value;

            // Si falta alguno de los campos, limpiar feedback y permitir intentar enviar
            if (!nombre || !tipo) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/mAreaSurtimiento/verificar?nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}`, {
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
                        feedbackDisponibilidad.innerHTML =
                            '<span class="text-success-custom"><i class="fa fa-check-circle"></i> Nombre y tipo disponibles</span>';
                        inputNombre.classList.remove('is-invalid');
                        inputNombre.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML =
                            '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> Esta área ya se encuentra registrada con este tipo</span>';
                        inputNombre.classList.remove('is-valid');
                        inputNombre.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar área:', error);
                });
            }, 300);
        };

        inputNombre.addEventListener('input', realizarVerificacion);
        selectTipo.addEventListener('change', realizarVerificacion);
    }

    // ── 3. Confirmación SweetAlert2 para cambiar status (Activar / Desactivar) ─
    const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
    toggleStatusLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const url    = this.getAttribute('data-url');
            const nombre = this.getAttribute('data-nombre');
            const activo = parseInt(this.getAttribute('data-activo'));

            const accion         = activo === 1 ? 'desactivar' : 'activar';
            const iconType       = activo === 1 ? 'warning' : 'question';
            const confirmBtnText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `¿Desea ${accion} el área?`,
                    text: `El área "${nombre}" será ${activo === 1 ? 'desactivada' : 'activada'} en el sistema.`,
                    icon: iconType,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                // Fallback nativo
                if (confirm(`¿Está seguro de que desea ${accion} el área "${nombre}"?`)) {
                    window.location.href = url;
                }
            }
        });
    });

});
