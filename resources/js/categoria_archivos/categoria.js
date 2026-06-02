/**
 * Lógica Javascript para el módulo de Categoría de Archivos
 */

document.addEventListener('DOMContentLoaded', function () {
    const inputCategoria = document.getElementById('categoria');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar = document.getElementById('btnGuardar');

    // 1. Mostrar SweetAlert2 si existen los divs de alerta de sesión
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');

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

            // Pequeño debounce para no saturar al servidor
            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/mCategoArchivos/verificar?categoria=${encodeURIComponent(nombre)}`, {
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

    // 3. Confirmación de SweetAlert2 al cambiar estatus (Activar / Desactivar)
    const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
    toggleStatusLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('data-url');
            const nombre = this.getAttribute('data-nombre');
            const activo = parseInt(this.getAttribute('data-activo'));

            const accion = (activo === 1) ? 'desactivar' : 'activar';
            const iconType = (activo === 1) ? 'warning' : 'question';
            const confirmBtnText = (activo === 1) ? 'Sí, desactivar' : 'Sí, activar';

            const submitStatusForm = () => {
                const form = document.createElement('form');
                form.action = url;
                form.method = 'POST';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PATCH';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `¿Desea ${accion} la categoría?`,
                    text: `La categoría "${nombre}" será ${activo === 1 ? 'desactivada' : 'activada'} en el sistema.`,
                    icon: iconType,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitStatusForm();
                    }
                });
            } else {
                // Fallback por si SweetAlert2 no está disponible
                if (confirm(`¿Está seguro de que desea ${accion} la categoría "${nombre}"?`)) {
                    submitStatusForm();
                }
            }
        });
    });
});
