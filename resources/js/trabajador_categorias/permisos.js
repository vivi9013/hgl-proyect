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

    // ─────────────────────────────────────────────────────────
    // 3. MOTOR DE PAGINACIÓN ASÍNCRONA (patrón idéntico a carga_archivos)
    // ─────────────────────────────────────────────────────────
    const tbody = document.getElementById('tbodyTrabajadores');
    const totalBadge = document.getElementById('totalTrabajadores');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');

    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        // Efecto visual de carga
        tbody.style.opacity = '0.5';

        fetch(`/permisos-archivo?page=${numeroPagina}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text();
        })
        .then(html => {
            tbody.style.opacity = '1';
            tbody.innerHTML = html;

            // Sincronizar transporte de datos
            const elTransporte = document.getElementById('datosPaginacionTransporte');

            if (elTransporte) {
                const totalGlobal = parseInt(elTransporte.getAttribute('data-total'));
                const textoInfo = elTransporte.getAttribute('data-info');
                const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;

                if (totalBadge) totalBadge.textContent = `${totalGlobal} ${totalGlobal === 1 ? 'Registro' : 'Registros'}`;
                if (infoPaginacion) infoPaginacion.textContent = textoInfo;

                if (contenedorPaginacion) {
                    contenedorPaginacion.innerHTML = htmlLinks;
                    asignarEventosEnlaces();
                }
            }
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando módulo de permisos:', err);
        });
    }

    function asignarEventosEnlaces() {
        if (!contenedorPaginacion) return;
        const enlaces = contenedorPaginacion.querySelectorAll('a.page-link');

        enlaces.forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();

                // Extraemos dinámicamente la página de la URL nativa de Laravel
                const urlObj = new URL(this.href);
                const paginaDestino = urlObj.searchParams.get('page');

                if (paginaDestino) {
                    cargarPagina(paginaDestino);
                }
            });
        });
    }

    // Disparar render inicial de botones de paginación al entrar al módulo
    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    if (elTransporteInicial && contenedorPaginacion) {
        const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;
        contenedorPaginacion.innerHTML = htmlLinks;
        asignarEventosEnlaces();
    }
});
