/**
 * Lógica Javascript para el módulo de Categoría de Archivos
 */

document.addEventListener('DOMContentLoaded', function () {
    const inputCategoria = document.getElementById('categoria');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar = document.getElementById('btnGuardar');

    // === ELEMENTOS ADICIONALES PARA PAGINACIÓN ===
    const tbody = document.getElementById('tbodyCategorias');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');
    const searchInput = document.getElementById('global-search');
    const totalBadge = document.getElementById('totalCategorias');

    // 1. Mostrar SweetAlert2 si existen los divs de alerta de sesión
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // 2. Verificación de disponibilidad de nombre en tiempo real (AJAX)
    if (inputCategoria && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        let timeoutId;

        inputCategoria.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const nombre = this.value.trim();

            if (!nombre) {
                feedbackDisponibilidad.innerHTML = '';
                inputCategoria.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/categoria-archivos/verificar?categoria=${encodeURIComponent(nombre)}`, {
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
                        feedbackDisponibilidad.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Categoría disponible</span>';
                        inputCategoria.classList.remove('is-invalid');
                        inputCategoria.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> Esta categoría ya existe</span>';
                        inputCategoria.classList.remove('is-valid');
                        inputCategoria.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar categoría:', error);
                });
            }, 300);
        });
    }

    // 3. MOTOR DE PAGINACIÓN ASÍNCRONA (AJAX)
    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        const buscar = searchInput ? searchInput.value : '';

        // Efecto visual de carga suavizado
        tbody.style.opacity = '0.5';

        fetch(`/categoria-archivos?buscar=${encodeURIComponent(buscar)}&page=${numeroPagina}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text();
        })
        .then(html => {
            tbody.style.opacity = '1';
            tbody.innerHTML = html;

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
                
                // REENLAZAR los eventos de cambio de estatus a las nuevas filas inyectadas
                enlazarEventosStatus();
            } else {
                // Manejo de escenario vacío (0 registros que coincidan con la búsqueda)
                if (totalBadge) totalBadge.textContent = '0 Registros';
                if (infoPaginacion) infoPaginacion.textContent = "Mostrando 0 a 0 de 0 registros";
                if (contenedorPaginacion) contenedorPaginacion.innerHTML = '';
            }
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando el módulo de categorías:', err);
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

    // 4. Confirmación de SweetAlert2 al cambiar estatus (Modularizado para re-uso)
    function enlazarEventosStatus() {
        const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
        toggleStatusLinks.forEach(link => {
            // Clonamos el nodo para limpiar listeners previos y evitar ejecuciones dobles
            const nuevoLink = link.cloneNode(true);
            link.parentNode.replaceChild(nuevoLink, link);

            nuevoLink.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                const nombre = this.getAttribute('data-nombre');
                const activo = parseInt(this.getAttribute('data-activo'));

                const accion = (activo === 1) ? 'desactivar' : 'activar';
                const iconType = (activo === 1) ? 'warning' : 'question';
                const confirmBtnText = (activo === 1) ? 'Sí, desactivar' : 'Sí, activar';

                const submitStatusForm = () => {
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
                        title: `¿Desea ${accion} la categoría?`,
                        text: `La categoría "${nombre}" será ${activo === 1 ? 'desactivada' : 'activada'} en el sistema.`,
                        icon: iconType,
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitStatusForm();
                        }
                    });
                } else {
                    if (confirm(`¿Está seguro de que desea ${accion} la categoría "${nombre}"?`)) {
                        submitStatusForm();
                    }
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

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            cargarPagina(1); // Resetear a la página 1 en búsquedas por texto
        }, 300));
    }

    // Inicializar listeners de la primera carga
    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    if (elTransporteInicial && contenedorPaginacion) {
        const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;
        contenedorPaginacion.innerHTML = htmlLinks;
        asignarEventosEnlaces();
    }
    enlazarEventosStatus();
});