/**
 * Lógica Javascript para la pantalla de Edición de Usuario
 */

document.addEventListener('DOMContentLoaded', function () {
    const inputNombre = document.getElementById('username');
    const inputId = document.getElementById('usuario_id');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnActualizar = document.getElementById('btnActualizar');

    if (inputNombre && inputId && feedbackDisponibilidad && loadingSpinner && btnActualizar) {
        let timeoutId;
        const originalUsername = inputNombre.value.trim().toLowerCase();

        inputNombre.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const username = this.value.trim();
            const id = inputId.value;

            if (!username) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                btnActualizar.disabled = false;
                return;
            }

            // Si es el mismo nombre original, no hacemos petición AJAX
            if (username.toLowerCase() === originalUsername) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                btnActualizar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/usuarios/verificar?username=${encodeURIComponent(username)}&id=${id}`, {
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
                        btnActualizar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML = '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> Este usuario ya está en uso</span>';
                        inputNombre.classList.remove('is-valid');
                        inputNombre.classList.add('is-invalid');
                        btnActualizar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar usuario:', error);
                });
            }, 300);
        });
    }
});
