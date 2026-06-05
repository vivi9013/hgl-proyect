document.addEventListener('DOMContentLoaded', function () {
    // === ELEMENTOS FORMULARIO (IZQUIERDA) ===
    const inputNombre = document.getElementById('nombre');
    const inputVersion = document.getElementById('version'); // <-- Agregado para capturar la versión
    const selectTipo = document.getElementById('tipo'); // <-- Agregado para capturar la categoría
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar = document.getElementById('btnGuardar');

    // === ELEMENTOS FILTRO Y BUSCADOR (DERECHA) ===
    const filtro = document.getElementById('filtroCategoria');
    const searchInput = document.getElementById('global-search');
    const tbody = document.getElementById('tbodyArchivos');
    const totalBadge = document.getElementById('totalArchivos');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');

    // ─────────────────────────────────────────────────────────
    // LÓGICA 1: VERIFICAR DISPONIBILIDAD DE NOMBRE Y VERSIÓN (POR CATEGORÍA)
    // ─────────────────────────────────────────────────────────
    if (inputNombre && inputVersion) {
        function verificarDisponibilidad() {
            const nombre = inputNombre.value.trim();
            const version = inputVersion.value.trim();
            const tipo = selectTipo ? selectTipo.value.trim() : '';

            // Si el nombre, la versión o la categoría están vacíos, reseteamos estilos y permitimos guardar
            if (!nombre || !version || !tipo) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                if(btnGuardar) btnGuardar.disabled = false;
                return;
            }

            loadingSpinner.style.display = 'block';
            feedbackDisponibilidad.innerHTML = '';

            // Se realiza la petición enviando nombre, versión y categoría
            fetch(`/carga-archivos/verificar-nombre?nombre=${encodeURIComponent(nombre)}&version=${encodeURIComponent(version)}&tipo=${encodeURIComponent(tipo)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Error en el servidor');
                return response.json();
            })
            .then(data => {
                loadingSpinner.style.display = 'none';
                if (data.disponible) {
                    feedbackDisponibilidad.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Nombre y versión disponibles</span>';
                    inputNombre.classList.remove('is-invalid');
                    inputNombre.classList.add('is-valid');
                    if(btnGuardar) btnGuardar.disabled = false;
                } else {
                    feedbackDisponibilidad.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> Este nombre y versión ya existen en esta categoría</span>';
                    inputNombre.classList.remove('is-valid');
                    inputNombre.classList.add('is-invalid');
                    if(btnGuardar) btnGuardar.disabled = true;
                }
            })
            .catch(error => {
                loadingSpinner.style.display = 'none';
                console.error('Error verificando disponibilidad:', error);
            });
        }

        const verificarConDebounce = debounce(verificarDisponibilidad, 300);

        // Listeners para el Nombre
        inputNombre.addEventListener('blur', verificarDisponibilidad);
        inputNombre.addEventListener('input', verificarConDebounce);

        // Listeners para la Versión (Reacciona si suben/bajan con las flechas o escriben)
        inputVersion.addEventListener('change', verificarDisponibilidad);
        inputVersion.addEventListener('input', verificarConDebounce);

        // Listener para la Categoría
        if (selectTipo) {
            selectTipo.addEventListener('change', verificarDisponibilidad);
        }
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA 2: REPOSITORIO DE CARGA FILTRADA ASÍNCROMA (AJAX)
    // ─────────────────────────────────────────────────────────
    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        const categoria = filtro ? filtro.value : 'Todos';
        const buscar = searchInput ? searchInput.value : '';

        // Feedback visual intermedio
        tbody.style.opacity = '0.5';

        fetch(`/carga-archivos?categoria=${encodeURIComponent(categoria)}&buscar=${encodeURIComponent(buscar)}&page=${numeroPagina}`, {
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
                // Manejo de escenario vacío (0 registros que coincidan con la búsqueda)
                if (totalBadge) totalBadge.textContent = '0 Registros';
                if (infoPaginacion) infoPaginacion.textContent = "Mostrando 0 a 0 de 0 registros";
                if (contenedorPaginacion) contenedorPaginacion.innerHTML = '';
            }
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando módulo de carga con filtros:', err);
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

    // Función debounce para evitar ráfagas de peticiones innecesarias a la BD mientras se escribe
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // === LISTENERS REACTIVOS AL BUSCADOR ===
    if (filtro) {
        filtro.addEventListener('change', function () {
            cargarPagina(1); // Resetear a la página 1 en cambios de categoría
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            cargarPagina(1); // Resetear a la página 1 en búsquedas por texto
        }, 300));
    }

    // Inicialización de la paginación al renderizar el módulo por primera vez
    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    if (elTransporteInicial && contenedorPaginacion) {
        contenedorPaginacion.innerHTML = document.getElementById('htmlLinksPaginacion').innerHTML;
        asignarEventosEnlaces();
    }
});