document.addEventListener('DOMContentLoaded', function () {
    const filtro = document.getElementById('filtroCategoria');
    const searchInput = document.getElementById('global-search');
    const tbody = document.getElementById('tbodyArchivos');
    const totalBadge = document.getElementById('totalArchivos');

    // Carga los archivos aplicando categoría y término de búsqueda actual
    function cargarArchivos(pagina = 1) {
        const categoria = filtro ? filtro.value : 'Todos';
        const buscar = searchInput ? searchInput.value : '';

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted mb-0">Cargando formatos...</p>
                </td>
            </tr>
        `;

        fetch(`/buscador-archivos/filtrar?categoria=${encodeURIComponent(categoria)}&buscar=${encodeURIComponent(buscar)}&page=${pagina}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Respuesta no satisfactoria del servidor');
            }
            return response.text();
        })
        .then(html => {
            tbody.innerHTML = html;

            // --- Lógica de Paginación Dinámica ---
            const elTransporte = document.getElementById('datosPaginacionTransporte');
            const infoPaginacion = document.getElementById('infoPaginacion');
            const contenedorPaginacion = document.getElementById('contenedorPaginacion');

            if (elTransporte) {
                // 1. Obtener los metadatos desde la fila oculta de la tabla
                const totalGlobal = parseInt(elTransporte.getAttribute('data-total'));
                const textoInfo = elTransporte.getAttribute('data-info');
                const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;

                // 2. Actualizar el Badge principal con el total real de la BD
                totalBadge.textContent = `${totalGlobal} ${totalGlobal === 1 ? 'Registro' : 'Registros'}`;
                
                // 3. Actualizar textos de control de Bootstrap e inyectar botones
                if (infoPaginacion) infoPaginacion.textContent = textoInfo;
                if (contenedorPaginacion) {
                    contenedorPaginacion.innerHTML = htmlLinks;
                    // 4. Interceptar clicks de los nuevos botones para que no recarguen página
                    asignarEventosPaginacion();
                }
            } else {
                // Caso en el que entra al @empty (0 registros)
                totalBadge.textContent = '0 Registros';
                if (infoPaginacion) infoPaginacion.textContent = "Mostrando 0 a 0 de 0 registros";
                if (contenedorPaginacion) contenedorPaginacion.innerHTML = '';
            }
        })
        .catch(err => {
            console.error('Error al cargar formatos:', err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-danger">
                        <span class="fa-stack fa-lg mb-2">
                            <i class="fa fa-folder-open-o fa-stack-1x text-secondary"></i>
                            <i class="fa fa-exclamation-triangle fa-stack-2x text-danger"></i>
                        </span>
                        <p class="fw-bold mb-0">Error al cargar los formatos. Por favor, intente nuevamente.</p>
                    </td>
                </tr>
            `;
            totalBadge.textContent = 'Error';
            if (document.getElementById('contenedorPaginacion')) {
                document.getElementById('contenedorPaginacion').innerHTML = '';
            }
        });
    }

    // Nueva función para capturar los clicks de la paginación generada por Laravel
    function asignarEventosPaginacion() {
        const enlaces = document.querySelectorAll('#contenedorPaginacion a.page-link');
        
        enlaces.forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault(); // Detener la recarga de página normal del enlace
                
                // Extraer el número de página de la URL generada por Laravel (?page=X)
                const urlObj = new URL(this.href);
                const paginaDestino = urlObj.searchParams.get('page');
                
                if (paginaDestino) {
                    cargarArchivos(paginaDestino);
                }
            });
        });
    }

    // Función debounce para retrasar la ejecución de búsquedas mientras el usuario escribe
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Listeners
    if (filtro) {
        filtro.addEventListener('change', function () {
            // Al cambiar de categoría, siempre reiniciamos a la página 1
            cargarArchivos(1);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            // Al buscar un término, siempre reiniciamos a la página 1
            cargarArchivos(1);
        }, 300));
    }

    // Inicialización de la paginación al renderizar el módulo por primera vez (SSR)
    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');
    if (elTransporteInicial && contenedorPaginacion) {
        contenedorPaginacion.innerHTML = document.getElementById('htmlLinksPaginacion').innerHTML;
        asignarEventosPaginacion();
    }
});