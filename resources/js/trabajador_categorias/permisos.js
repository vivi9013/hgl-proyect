/**
 * Lógica Javascript para el módulo de Permisos de Archivos con Persistencia entre Paginación
 */

document.addEventListener('DOMContentLoaded', function () {
    // === SELECTORES GLOBAL DE ESTADOS (MEMORIA) ===
    const categoriasSeleccionadas = new Set();
    
    // El formulario y contenedor de inputs
    const formAsignar = document.getElementById('formAsignarCategorias');
    const inputsDestinoOcultos = document.getElementById('inputsDestinoOcultos');

    // Nodos de la tabla asíncrona
    const tbody = document.getElementById('tbodyAsignacion');
    const tbodyTrabajadores = document.getElementById('tbodyTrabajadores');
    const totalTrabajadoresBadge = document.getElementById('totalTrabajadores');
    const searchInput = document.getElementById('global-search');
    const contador = document.getElementById('contadorSeleccionados');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');
    
    const btnMarcarTodos = document.getElementById('btnMarcarTodos');
    const btnDesmarcarTodos = document.getElementById('btnDesmarcarTodos');

    // SweetAlert2 Inicializadores estándar del sistema
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({ title: '¡Operación Satisfactoria!', text: alertaExitog.getAttribute('data-mensaje') || 'El registro se ha guardado correctamente.', icon: 'success', confirmButtonColor: '#3085d6', confirmButtonText: 'Aceptar' });
    }
    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({ title: '¡Operación Satisfactoria!', text: alertaExito.getAttribute('data-mensaje') || 'El registro se ha actualizado correctamente.', icon: 'success', confirmButtonColor: '#3085d6', confirmButtonText: 'Aceptar' });
    }

    // ─────────────────────────────────────────────────────────
    // GESTIÓN DE MEMORIA VIRTUAL DE CHECKBOXES
    // ─────────────────────────────────────────────────────────
    function inicializarMemoriaDesdeDOM() {
        if (!tbody) return;
        // Captura el estado cargado inicialmente por Laravel
        const checkboxesActuales = tbody.querySelectorAll('.chk-permiso');
        checkboxesActuales.forEach(chk => {
            if (chk.checked) {
                categoriasSeleccionadas.add(chk.value);
            }
        });
        actualizarContadorInterfaz();
    }

    function actualizarContadorInterfaz() {
        if (contador) {
            contador.textContent = categoriasSeleccionadas.size;
        }
    }

    function sincronizarCheckboxesVisuales() {
        if (!tbody) return;
        const checkboxesActuales = tbody.querySelectorAll('.chk-permiso');
        
        checkboxesActuales.forEach(chk => {
            if (categoriasSeleccionadas.has(chk.value)) {
                chk.checked = true;
                const row = chk.closest('.fila-categoria');
                if (row) row.classList.add('table-success-soft');
            } else {
                chk.checked = false;
                const row = chk.closest('.fila-categoria');
                if (row) row.classList.remove('table-success-soft');
            }
        });
    }

    function enlazarEventosCheckboxes() {
        if (!tbody) return;
        const checkboxesActuales = tbody.querySelectorAll('.chk-permiso');
        
        checkboxesActuales.forEach(chk => {
            chk.addEventListener('change', function () {
                const row = this.closest('.fila-categoria');
                if (this.checked) {
                    categoriasSeleccionadas.add(this.value);
                    if (row) row.classList.add('table-success-soft');
                } else {
                    categoriasSeleccionadas.delete(this.value);
                    if (row) row.classList.remove('table-success-soft');
                }
                actualizarContadorInterfaz();
            });
        });
    }

    // ─────────────────────────────────────────────────────────
    // ACCIONES EN MASA (MARCAR / DESMARCAR POR PÁGINA)
    // ─────────────────────────────────────────────────────────
    if (btnMarcarTodos) {
        btnMarcarTodos.addEventListener('click', function (e) {
            e.preventDefault();
            if (!tbody) return;
            tbody.querySelectorAll('.chk-permiso').forEach(chk => {
                chk.checked = true;
                categoriasSeleccionadas.add(chk.value);
                const row = chk.closest('.fila-categoria');
                if (row) row.classList.add('table-success-soft');
            });
            actualizarContadorInterfaz();
        });
    }

    if (btnDesmarcarTodos) {
        btnDesmarcarTodos.addEventListener('click', function (e) {
            e.preventDefault();
            if (!tbody) return;
            tbody.querySelectorAll('.chk-permiso').forEach(chk => {
                chk.checked = false;
                categoriasSeleccionadas.delete(chk.value);
                const row = chk.closest('.fila-categoria');
                if (row) row.classList.remove('table-success-soft');
            });
            actualizarContadorInterfaz();
        });
    }

    // ─────────────────────────────────────────────────────────
    // MOTOR DE PAGINACIÓN ASÍNCRONA (AJAX)
    // ─────────────────────────────────────────────────────────
    function cargarPaginaAsignacion(numeroPagina = 1) {
        if (!tbody) return;
        tbody.style.opacity = '0.5';

        // Captura la URI actual del formulario de asignación
        const urlAsignar = window.location.pathname;
        const buscar = searchInput ? searchInput.value : '';

        fetch(`${urlAsignar}?buscar=${encodeURIComponent(buscar)}&page=${numeroPagina}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error recuperando la matriz.');
            return response.text();
        })
        .then(html => {
            tbody.style.opacity = '1';
            tbody.innerHTML = html;

            const elTransporte = document.getElementById('datosPaginacionTransporte');
            if (elTransporte) {
                const textoInfo = elTransporte.getAttribute('data-info');
                const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;

                if (infoPaginacion) infoPaginacion.textContent = textoInfo;
                if (contenedorPaginacion) {
                    contenedorPaginacion.innerHTML = htmlLinks;
                    asignarEventosEnlaces();
                }

                // Sincronizar UI con la memoria virtual y volver a registrar listeners
                sincronizarCheckboxesVisuales();
                enlazarEventosCheckboxes();
            }
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando matriz de asignación:', err);
        });
    }

    function cargarPaginaTrabajadores(numeroPagina = 1) {
        if (!tbodyTrabajadores) return;
        tbodyTrabajadores.style.opacity = '0.5';

        const buscar = searchInput ? searchInput.value : '';

        fetch(`/permisos-archivo?buscar=${encodeURIComponent(buscar)}&page=${numeroPagina}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error recuperando la lista de trabajadores.');
            return response.text();
        })
        .then(html => {
            tbodyTrabajadores.style.opacity = '1';
            tbodyTrabajadores.innerHTML = html;

            const elTransporte = document.getElementById('datosPaginacionTransporte');
            if (elTransporte) {
                const totalGlobal = parseInt(elTransporte.getAttribute('data-total'));
                const textoInfo = elTransporte.getAttribute('data-info');
                const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;

                if (totalTrabajadoresBadge) {
                    totalTrabajadoresBadge.textContent = `${totalGlobal} ${totalGlobal === 1 ? 'Registro' : 'Registros'}`;
                }
                if (infoPaginacion) infoPaginacion.textContent = textoInfo;
                if (contenedorPaginacion) {
                    contenedorPaginacion.innerHTML = htmlLinks;
                    asignarEventosEnlaces();
                }
            } else {
                if (totalTrabajadoresBadge) totalTrabajadoresBadge.textContent = '0 Registros';
                if (infoPaginacion) infoPaginacion.textContent = "Mostrando 0 a 0 de 0 trabajadores";
                if (contenedorPaginacion) contenedorPaginacion.innerHTML = '';
            }
        })
        .catch(err => {
            tbodyTrabajadores.style.opacity = '1';
            console.error('Error paginando lista de trabajadores:', err);
        });
    }

    function asignarEventosEnlaces() {
        if (!contenedorPaginacion) return;
        contenedorPaginacion.querySelectorAll('a.page-link').forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                const urlObj = new URL(this.href);
                const paginaDestino = urlObj.searchParams.get('page');
                if (paginaDestino) {
                    if (tbodyTrabajadores) {
                        cargarPaginaTrabajadores(paginaDestino);
                    } else if (tbody) {
                        cargarPaginaAsignacion(paginaDestino);
                    }
                }
            });
        });
    }

    // ─────────────────────────────────────────────────────────
    // PREPARACIÓN DE ENVÍO DE DATOS POST (INYECCIÓN DE CHECKS)
    // ─────────────────────────────────────────────────────────
    if (formAsignar) {
        formAsignar.addEventListener('submit', function (e) {
            // Limpiar inyecciones anteriores por seguridad
            if (inputsDestinoOcultos) inputsDestinoOcultos.innerHTML = '';

            // Generar un input hidden por cada ID almacenado en la memoria virtual
            categoriasSeleccionadas.forEach(idCategoria => {
                const inputHidden = document.createElement('input');
                inputHidden.type = 'hidden';
                inputHidden.name = 'categorias[]';
                inputHidden.value = idCategoria;
                if (inputsDestinoOcultos) {
                    inputsDestinoOcultos.appendChild(inputHidden);
                }
            });
        });
    }

    // Función debounce para evitar ráfagas de peticiones
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            if (tbodyTrabajadores) {
                cargarPaginaTrabajadores(1);
            } else if (tbody) {
                cargarPaginaAsignacion(1);
            }
        }, 300));
    }

    // Inicialización al cargar la página
    inicializarMemoriaDesdeDOM();
    enlazarEventosCheckboxes();

    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    if (elTransporteInicial && contenedorPaginacion) {
        contenedorPaginacion.innerHTML = document.getElementById('htmlLinksPaginacion').innerHTML;
        asignarEventosEnlaces();
    }
});