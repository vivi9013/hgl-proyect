/**
 * personas.js — Lógica AJAX para el catálogo de Personas
 * Incluye: paginación asíncrona, búsqueda reactiva, toggle status,
 * toggle estudiante, carga dinámica de municipios y SweetAlert2.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Referencias DOM ──────────────────────────────────────────────────────
    const tbody               = document.getElementById('tbodyPersonas');
    const infoPaginacion      = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');
    const searchInput         = document.getElementById('global-search');
    const totalBadge          = document.getElementById('totalPersonas');

    // Alertas de sesión (SweetAlert2)
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito  = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitog.dataset.message || 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.dataset.message || 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // ── Carga dinámica de Estados en el modal de alta ─────────────────────────
    const estadoSel    = document.getElementById('estado_sel');
    const municipioSel = document.getElementById('municipio_sel');

    if (estadoSel) {
        // Cargar estados al abrir el modal
        fetch('/personas/municipios?estado=', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).catch(() => {}); // Silenciar si la ruta no devuelve estados

        // Cargar estados desde la BD al abrir modal
        const modalEl = document.getElementById('modalAltaPersona');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function () {
                if (estadoSel.options.length <= 1) {
                    cargarEstados(estadoSel, municipioSel);
                }
            });
        }

        estadoSel.addEventListener('change', function () {
            cargarMunicipios(this.value, municipioSel);
        });
    }

    function cargarEstados(selectEstado, selectMunicipio) {
        // Los estados se cargan desde el endpoint de municipios pasando 'estado' vacío
        // Alternativa: usar un endpoint dedicado. Por ahora, hacemos una petición AJAX
        // al listado de personas para obtener los estados únicos.
        // En su lugar, usamos el truco de llamar a la misma URL con un parámetro especial.
        fetch('/personas/municipios?estado=__estados__', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            selectEstado.innerHTML = '<option value="">-- Seleccionar --</option>';
            Object.keys(data).forEach(key => {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = key;
                selectEstado.appendChild(opt);
            });
        })
        .catch(() => {});
    }

    function cargarMunicipios(estado, selectMunicipio) {
        if (!estado || !selectMunicipio) return;
        selectMunicipio.innerHTML = '<option value="">Cargando...</option>';

        fetch(`/personas/municipios?estado=${encodeURIComponent(estado)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            selectMunicipio.innerHTML = '<option value="">-- Seleccionar --</option>';
            Object.entries(data).forEach(([key, val]) => {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val;
                selectMunicipio.appendChild(opt);
            });
        })
        .catch(() => {
            selectMunicipio.innerHTML = '<option value="">Error al cargar</option>';
        });
    }

    // ── Motor de paginación AJAX ─────────────────────────────────────────────
    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        const buscar = searchInput ? searchInput.value : '';
        tbody.style.opacity = '0.5';

        fetch(`/personas?buscar=${encodeURIComponent(buscar)}&page=${numeroPagina}`, {
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
            tbody.style.opacity = '1';
            tbody.innerHTML = data.html;

            if (totalBadge)          totalBadge.textContent = `${data.total} ${data.total === 1 ? 'Registro' : 'Registros'}`;
            if (infoPaginacion)       infoPaginacion.textContent = data.info;
            if (contenedorPaginacion) {
                contenedorPaginacion.innerHTML = data.links;
                asignarEventosEnlaces();
            }

            enlazarEventosAcciones();
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando personas:', err);
        });
    }

    function asignarEventosEnlaces() {
        if (!contenedorPaginacion) return;
        contenedorPaginacion.querySelectorAll('a.page-link').forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                const urlObj = new URL(this.href);
                const pg = urlObj.searchParams.get('page');
                if (pg) cargarPagina(pg);
            });
        });
    }

    // ── Acciones en tabla: toggle status y toggle estudiante ─────────────────
    function enlazarEventosAcciones() {

        // ── Toggle Status ──
        document.querySelectorAll('.btn-toggle-status').forEach(btn => {
            const nuevo = btn.cloneNode(true);
            btn.parentNode.replaceChild(nuevo, btn);

            nuevo.addEventListener('click', function () {
                const id  = this.getAttribute('data-id');
                const row = this.closest('tr');
                const nombre = row.querySelector('td:nth-child(3)')?.textContent.trim() ?? '';
                const esInactivo = row.classList.contains('text-muted');
                const accion = esInactivo ? 'activar' : 'desactivar';

                const runFetch = () => {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    fetch(`/personas/${id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ title: 'Actualizado', text: data.message, icon: 'success', timer: 1400, showConfirmButton: false });
                            }
                            const pgActiva = contenedorPaginacion?.querySelector('.page-item.active .page-link');
                            cargarPagina(pgActiva ? parseInt(pgActiva.textContent) : 1);
                        }
                    })
                    .catch(err => console.error(err));
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} persona?`,
                        text: `"${nombre}" será ${accion}da en el sistema.`,
                        icon: esInactivo ? 'question' : 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: `Sí, ${accion}`,
                        cancelButtonText: 'Cancelar'
                    }).then(result => { if (result.isConfirmed) runFetch(); });
                } else {
                    if (confirm(`¿${accion} a "${nombre}"?`)) runFetch();
                }
            });
        });

        // ── Toggle Estudiante ──
        document.querySelectorAll('.btn-toggle-estudiante').forEach(btn => {
            const nuevo = btn.cloneNode(true);
            btn.parentNode.replaceChild(nuevo, btn);

            nuevo.addEventListener('click', function () {
                const id  = this.getAttribute('data-id');
                const row = this.closest('tr');
                const nombre = row.querySelector('td:nth-child(3)')?.textContent.trim() ?? '';
                const icono  = this.querySelector('i');
                const esEstudiante = icono && icono.style.opacity !== '0.35';
                const accion = esEstudiante ? 'quitar el rol de estudiante a' : 'asignar el rol de estudiante a';

                const runFetch = () => {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    fetch(`/personas/${id}/estudiante`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ title: 'Actualizado', text: data.message, icon: 'success', timer: 1400, showConfirmButton: false });
                            }
                            const pgActiva = contenedorPaginacion?.querySelector('.page-item.active .page-link');
                            cargarPagina(pgActiva ? parseInt(pgActiva.textContent) : 1);
                        }
                    })
                    .catch(err => console.error(err));
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Cambiar rol de estudiante?',
                        text: `Se va a ${accion} "${nombre}".`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, cambiar',
                        cancelButtonText: 'Cancelar'
                    }).then(result => { if (result.isConfirmed) runFetch(); });
                } else {
                    if (confirm(`¿${accion} "${nombre}"?`)) runFetch();
                }
            });
        });
    }

    // ── Debounce de búsqueda ─────────────────────────────────────────────────
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => cargarPagina(1), 350));
    }

    // ── Carga inicial ────────────────────────────────────────────────────────
    const activeLink = contenedorPaginacion?.querySelector('.page-item.active .page-link');
    cargarPagina(activeLink ? parseInt(activeLink.textContent) : 1);
});
