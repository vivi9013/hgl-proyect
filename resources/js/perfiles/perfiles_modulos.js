/**
 * Lógica Javascript para la asignación de módulos en Perfiles
 */

document.addEventListener('DOMContentLoaded', function () {
    const btnMarcarTodos = document.getElementById('btnMarcarTodos');
    const btnDesmarcarTodos = document.getElementById('btnDesmarcarTodos');
    const casillas = document.querySelectorAll('.casilla-modulo');

    // 1. Mostrar SweetAlert2 si existe la alerta de sesión
    const alertaExito = document.getElementById('alertaExito');
    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.getAttribute('data-message'),
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // 2. Colorear filas al cambiar checkboxes
    casillas.forEach(casilla => {
        casilla.addEventListener('change', function () {
            const fila = document.getElementById(`filaModulo${this.value}`);
            if (this.checked) {
                if (fila) fila.classList.add('table-success-custom');
            } else {
                if (fila) fila.classList.remove('table-success-custom');
            }
        });
    });

    // 3. Marcar todos
    if (btnMarcarTodos) {
        btnMarcarTodos.addEventListener('click', function () {
            casillas.forEach(casilla => {
                casilla.checked = true;
                const fila = document.getElementById(`filaModulo${casilla.value}`);
                if (fila) fila.classList.add('table-success-custom');
            });
        });
    }

    // 4. Desmarcar todos
    if (btnDesmarcarTodos) {
        btnDesmarcarTodos.addEventListener('click', function () {
            casillas.forEach(casilla => {
                casilla.checked = false;
                const fila = document.getElementById(`filaModulo${casilla.value}`);
                if (fila) fila.classList.remove('table-success-custom');
            });
        });
    }
});
