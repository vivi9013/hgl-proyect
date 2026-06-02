document.addEventListener('DOMContentLoaded', function () {
    const inputNombre = document.getElementById('nombre');
    const selectTipo = document.getElementById('tipo');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar = document.getElementById('btnGuardar');

    if (!inputNombre || !selectTipo) return;

    function verificarDisponibilidad() {
        const nombre = inputNombre.value.trim();
        const categoriaId = selectTipo.value;

        // Limpiar feedback si no hay valores completos
        if (!nombre || !categoriaId) {
            feedbackDisponibilidad.innerHTML = '';
            btnGuardar.disabled = false;
            return;
        }

        // Mostrar indicador de carga
        loadingSpinner.style.display = 'block';
        feedbackDisponibilidad.innerHTML = '';

        // Petición AJAX (Fetch API) a la ruta de verificación
        fetch(`/mCargaArchivos/verificar-nombre?nombre=${encodeURIComponent(nombre)}&id_catego=${encodeURIComponent(categoriaId)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en el servidor');
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
                feedbackDisponibilidad.innerHTML = '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> El nombre ya existe en esta categoría</span>';
                inputNombre.classList.remove('is-valid');
                inputNombre.classList.add('is-invalid');
                // Deshabilitar botón de guardar si ya existe
                btnGuardar.disabled = true;
            }
        })
        .catch(error => {
            loadingSpinner.style.display = 'none';
            console.error('Error verificando disponibilidad:', error);
        });
    }

    // Eventos para validar en tiempo real
    inputNombre.addEventListener('blur', verificarDisponibilidad);
    selectTipo.addEventListener('change', verificarDisponibilidad);
});
