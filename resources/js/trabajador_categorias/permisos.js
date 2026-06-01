/**
 * Lógica Javascript para el módulo de Permisos de Archivos
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mostrar SweetAlert2 si existen los divs de alerta de sesión
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitog.getAttribute('data-mensaje') || 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.getAttribute('data-mensaje') || 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // 2. Interacciones en la matriz de asignación de categorías
    const checkboxes = document.querySelectorAll('.chk-permiso');
    const contador = document.getElementById('contadorSeleccionados');
    const btnMarcarTodos = document.getElementById('btnMarcarTodos');
    const btnDesmarcarTodos = document.getElementById('btnDesmarcarTodos');

    function actualizarContador() {
        if (!contador) return;
        const checkedCount = document.querySelectorAll('.chk-permiso:checked').length;
        contador.textContent = checkedCount;
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const row = this.closest('.fila-categoria');
            if (row) {
                if (this.checked) {
                    row.classList.add('table-success-soft');
                } else {
                    row.classList.remove('table-success-soft');
                }
            }
            actualizarContador();
        });
    });

    if (btnMarcarTodos) {
        btnMarcarTodos.addEventListener('click', function (e) {
            e.preventDefault();
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                const row = checkbox.closest('.fila-categoria');
                if (row) row.classList.add('table-success-soft');
            });
            actualizarContador();
        });
    }

    if (btnDesmarcarTodos) {
        btnDesmarcarTodos.addEventListener('click', function (e) {
            e.preventDefault();
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                const row = checkbox.closest('.fila-categoria');
                if (row) row.classList.remove('table-success-soft');
            });
            actualizarContador();
        });
    }

    // Ejecución inicial para establecer el contador correctamente al cargar la página
    actualizarContador();
});
