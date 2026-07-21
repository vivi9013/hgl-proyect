/**
 * Lógica Javascript para el módulo de Usuarios (JSON AJAX Render)
 */

document.addEventListener('DOMContentLoaded', function () {
    const inputNombre = document.getElementById('username');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar = document.getElementById('btnGuardar');

    // === ELEMENTOS DE PAGINACIÓN ===
    const tbody = document.getElementById('cuerpoTablaUsuarios');
    const infoPaginacion = document.getElementById('infoPaginacionUsuarios');
    const contenedorPaginacion = document.getElementById('paginacionUsuarios');
    const searchInput = document.getElementById('filtro-buscar');

    // 1. Mostrar SweetAlert2 si existen los divs de alerta de sesión
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitog.getAttribute('data-message'),
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.getAttribute('data-message'),
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // 2. Verificación de disponibilidad de nombre de usuario en tiempo real (AJAX)
    if (inputNombre && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        let timeoutId;

        inputNombre.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const username = this.value.trim();

            if (!username) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/usuarios/verificar?username=${encodeURIComponent(username)}`, {
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
                        feedbackDisponibilidad.innerHTML = '<span class="text-success-custom"><i class="fa fa-check-circle"></i> Nombre de usuario disponible</span>';
                        inputNombre.classList.remove('is-invalid');
                        inputNombre.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML = '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> Este usuario ya está en uso</span>';
                        inputNombre.classList.remove('is-valid');
                        inputNombre.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar usuario:', error);
                });
            }, 300);
        });
    }

    // 3. MOTOR DE PAGINACIÓN ASÍNCRONA (AJAX)
    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        const buscar = searchInput ? searchInput.value : '';

        tbody.style.opacity = '0.5';

        fetch(`/usuarios?buscar=${encodeURIComponent(buscar)}&page=${numeroPagina}`, {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.json();
        })
        .then(data => {
            tbody.style.opacity = '1';
            tbody.innerHTML = data.html;

            if (infoPaginacion) {
                infoPaginacion.textContent = data.info;
            }
            if (contenedorPaginacion) {
                contenedorPaginacion.innerHTML = data.links;
                asignarEventosEnlaces();
            }

            // Re-enlazar listeners de acciones en las nuevas filas
            enlazarEventosAcciones();
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando el módulo:', err);
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

    // 4. Configurar listeners de status y de restablecer contraseña
    function enlazarEventosAcciones() {
        // Status Toggle
        const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
        toggleStatusLinks.forEach(link => {
            const nuevoLink = link.cloneNode(true);
            link.parentNode.replaceChild(nuevoLink, link);

            nuevoLink.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const url = `/usuarios/${id}/status`;
                
                const row = this.closest('tr');
                const nombreUsuario = row.querySelector('td:nth-child(3)').textContent.trim();
                const esInactivo = row.classList.contains('text-muted');
                const activo = esInactivo ? 0 : 1;

                const accion = (activo === 1) ? 'desactivar' : 'activar';
                const confirmBtnText = (activo === 1) ? 'Sí, desactivar' : 'Sí, activar';
                const iconType = (activo === 1) ? 'warning' : 'question';

                const runFetch = () => {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Error al actualizar el estado');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: '¡Operación Satisfactoria!',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                            
                            // Recargar página actual
                            const elPaginaActiva = contenedorPaginacion?.querySelector('.page-item.active .page-link');
                            const paginaActiva = elPaginaActiva ? parseInt(elPaginaActiva.textContent) : 1;
                            cargarPagina(paginaActiva);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿Desea ${accion} el usuario?`,
                        text: `El usuario "${nombreUsuario}" será ${activo === 1 ? 'desactivado' : 'activado'} en el sistema.`,
                        icon: iconType,
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            runFetch();
                        }
                    });
                } else {
                    if (confirm(`¿Está seguro de que desea ${accion} el usuario "${nombreUsuario}"?`)) {
                        runFetch();
                    }
                }
            });
        });

        // Restablecer Password (Reiniciar)
        const btnRestablecerList = document.querySelectorAll('.btn-restablecer-password');
        btnRestablecerList.forEach(link => {
            const nuevoLink = link.cloneNode(true);
            link.parentNode.replaceChild(nuevoLink, link);

            nuevoLink.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const url = `/usuarios/${id}/restablecer`;

                const row = this.closest('tr');
                const nombreUsuario = row.querySelector('td:nth-child(3)').textContent.trim();

                const runFetch = () => {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Error al restablecer la contraseña');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: '¡Operación Satisfactoria!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                            } else {
                                alert(data.message);
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo restablecer la contraseña.', 'error');
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Desea reiniciar la contraseña del usuario?',
                        text: `La contraseña del usuario "${nombreUsuario}" será restablecida al valor predeterminado configurado en el sistema y se le solicitará cambiarla en su próximo inicio de sesión.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, reiniciar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            runFetch();
                        }
                    });
                } else {
                    if (confirm(`¿Está seguro de reiniciar la contraseña del usuario "${nombreUsuario}"?`)) {
                        runFetch();
                    }
                }
            });
        });
    }

    // Debounce de búsqueda
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            cargarPagina(1);
        }, 300));
    }

    // Carga inicial: la tabla y la paginación ya vienen renderizadas por el servidor (SSR),
    // solo se enlazan los eventos de acciones y de los links de paginación existentes.
    enlazarEventosAcciones();
    asignarEventosEnlaces();
});