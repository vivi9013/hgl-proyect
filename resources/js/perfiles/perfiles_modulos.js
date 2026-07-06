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

    // 5. Paginación del lado del cliente
    const rows = Array.from(document.querySelectorAll('tbody tr')).filter(tr => tr.id && tr.id.startsWith('filaModulo'));
    const rowsPerPage = 10;
    let currentPage = 1;
    const totalRows = rows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);

    function showPage(page) {
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;

        const start = (currentPage - 1) * rowsPerPage;
        const end = Math.min(start + rowsPerPage, totalRows);

        rows.forEach((row, idx) => {
            if (idx >= start && idx < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Actualizar texto informativo
        const infoPaginacion = document.getElementById('infoPaginacion');
        if (infoPaginacion) {
            if (totalRows === 0) {
                infoPaginacion.textContent = 'Mostrando 0 a 0 de 0 registros';
            } else {
                infoPaginacion.textContent = `Mostrando ${start + 1} a ${end} de ${totalRows} registros`;
            }
        }

        renderPaginationButtons();
    }

    function renderPaginationButtons() {
        const container = document.getElementById('contenedorPaginacion');
        if (!container) return;
        container.innerHTML = '';

        if (totalPages <= 1) return;

        // Botón Anterior
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<button class="page-link" type="button" aria-label="Previous">&lsaquo;</button>`;
        if (currentPage > 1) {
            prevLi.querySelector('button').addEventListener('click', () => showPage(currentPage - 1));
        }
        container.appendChild(prevLi);

        // Páginas individuales
        for (let i = 1; i <= totalPages; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
            pageLi.innerHTML = `<button class="page-link" type="button">${i}</button>`;
            pageLi.querySelector('button').addEventListener('click', () => showPage(i));
            container.appendChild(pageLi);
        }

        // Botón Siguiente
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<button class="page-link" type="button" aria-label="Next">&rsaquo;</button>`;
        if (currentPage < totalPages) {
            nextLi.querySelector('button').addEventListener('click', () => showPage(currentPage + 1));
        }
        container.appendChild(nextLi);
    }

    // Inicializar la paginación
    if (totalRows > 0) {
        showPage(1);
    } else {
        const infoPaginacion = document.getElementById('infoPaginacion');
        if (infoPaginacion) infoPaginacion.textContent = 'Mostrando 0 a 0 de 0 registros';
    }
});
