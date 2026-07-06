document.addEventListener('DOMContentLoaded', function () {

    // === ELEMENTOS FILTRO Y BUSCADOR (DERECHA) ===
    const filtroArea = document.getElementById('filtroArea');
    const filtroTipo = document.getElementById('filtroTipo');
    const searchInput = document.getElementById('global-search');
    const tbody = document.getElementById('tbodyArchivos');
    const totalBadge = document.getElementById('totalArchivos');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');
    const btnImprimirReporte = document.getElementById('btnImprimirReporte');

    // ─────────────────────────────────────────────────────────
    // LÓGICA: REPOSITORIO DE CARGA FILTRADA ASÍNCROMA (AJAX)
    // ─────────────────────────────────────────────────────────
    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        const area = filtroArea ? filtroArea.value : 'Todos';
        const tipo = filtroTipo ? filtroTipo.value : 'Todos';
        const buscar = searchInput ? searchInput.value : '';

        // Feedback visual intermedio
        tbody.style.opacity = '0.5';

        const queryParams = new URLSearchParams({
            area_id: area,
            tipo_id: tipo,
            buscar: buscar,
            page: numeroPagina
        });

        // Actualizar URL del botón de imprimir para conservar los filtros de búsqueda aplicados
        if (btnImprimirReporte) {
            btnImprimirReporte.href = `/mobiliario/reporte/imprimir?${queryParams.toString()}`;
        }

        fetch(`/mobiliario?${queryParams.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text();
        })
        .then(html => {
            tbody.style.opacity = '1';
            tbody.innerHTML = html;

            // Sincronizar transporte de datos desde el partial inyectado
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
            } else {
                // Manejo de escenario vacío
                if (totalBadge) totalBadge.textContent = '0 Registros';
                if (infoPaginacion) infoPaginacion.textContent = "Mostrando 0 a 0 de 0 registros";
                if (contenedorPaginacion) contenedorPaginacion.innerHTML = '';
            }

            // Volver a enlazar eventos en la nueva tabla
            enlazarConfirmacionesStatus();
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando el módulo de mobiliario con filtros:', err);
        });
    }

    function asignarEventosEnlaces() {
        if (!contenedorPaginacion) return;
        const enlaces = contenedorPaginacion.querySelectorAll('a.page-link');
        
        enlaces.forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                const urlObj = new URL(this.href);
                const paginaDestino = urlObj.searchParams.get('page');
                
                if (paginaDestino) {
                    cargarPagina(paginaDestino);
                }
            });
        });
    }

    // Función debounce para evitar ráfagas de peticiones innecesarias
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // === LISTENERS REACTIVOS AL BUSCADOR Y FILTROS ===
    if (filtroArea) {
        filtroArea.addEventListener('change', function () {
            cargarPagina(1); // Resetear a la página 1 en cambios de filtro
        });
    }

    if (filtroTipo) {
        filtroTipo.addEventListener('change', function () {
            cargarPagina(1); // Resetear a la página 1 en cambios de filtro
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            cargarPagina(1); // Resetear a la página 1 en búsquedas por texto
        }, 300));
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: Confirmación SweetAlert2 al Alternar Status
    // ─────────────────────────────────────────────────────────
    function enlazarConfirmacionesStatus() {
        const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
        toggleStatusLinks.forEach(link => {
            // Eliminar listeners previos clonando el nodo para evitar dobles confirmaciones
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);

            newLink.addEventListener('click', function (e) {
                e.preventDefault();

                const url    = this.getAttribute('data-url');
                const nombre = this.getAttribute('data-nombre');
                const activo = parseInt(this.getAttribute('data-activo'));

                const accion         = activo === 1 ? 'desactivar' : 'activar';
                const iconType       = activo === 1 ? 'warning' : 'question';
                const confirmBtnText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';

                const doRequest = () => {
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
                        title: `¿Desea ${accion} el mobiliario?`,
                        text: `El mobiliario con inventario "${nombre}" cambiará de estado en el sistema.`,
                        icon: iconType,
                        showCancelButton: true,
                        confirmButtonColor: '#2b6cb0',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doRequest();
                        }
                    });
                } else {
                    if (confirm(`¿Está seguro de que desea ${accion} el mobiliario "${nombre}"?`)) {
                        doRequest();
                    }
                }
            });
        });
    }

    // Inicialización de la paginación al renderizar el módulo por primera vez
    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    if (elTransporteInicial && contenedorPaginacion) {
        contenedorPaginacion.innerHTML = document.getElementById('htmlLinksPaginacion').innerHTML;
        asignarEventosEnlaces();
    }

    // Inicializar confirmaciones
    enlazarConfirmacionesStatus();

});
