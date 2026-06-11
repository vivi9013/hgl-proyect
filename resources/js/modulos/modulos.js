/**
 * modulos.js — Lógica del módulo de Gestión de Módulos.
 * Cubre: previsualización en modal, paginación AJAX, toggle de estado,
 * marcar/desmarcar todos en proyectos y perfiles.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS SWEETALERT2 DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────────────────
    // B. PREVISUALIZACIÓN EN TIEMPO REAL (Modal de Alta y Página de Edición)
    // IDs esperados en las vistas:
    //   input#nombre, select#color, input#icono, input#descripcion
    //   span#previewNombre, small#previewDesc, i#previewIcono
    // ─────────────────────────────────────────────────────────────────────────
    const inputNombre    = document.getElementById('nombre');
    const selectColor    = document.getElementById('color');
    const inputIcono     = document.getElementById('icono');
    const inputDesc      = document.getElementById('descripcion');

    const previewIcono   = document.getElementById('previewIcono');
    const previewNombre  = document.getElementById('previewNombre');
    const previewDesc    = document.getElementById('previewDesc');

    function actualizarPreview() {
        if (previewNombre && inputNombre) {
            previewNombre.textContent = inputNombre.value.trim() || 'Nombre del módulo';
        }
        if (previewDesc && inputDesc) {
            previewDesc.textContent = inputDesc.value.trim() || 'Descripción del módulo';
        }
        if (previewIcono && inputIcono) {
            const clases = inputIcono.value.trim().split(/\s+/).filter(Boolean);
            // Conservar clases utilitarias no-fa y agregar las del input
            previewIcono.className = '';
            if (clases.length > 0) {
                clases.forEach(c => previewIcono.classList.add(c));
            } else {
                previewIcono.classList.add('fa', 'fa-cube');
            }
            previewIcono.classList.add('fa-2x', 'text-primary');
        }
    }

    if (inputNombre)  inputNombre.addEventListener('input', actualizarPreview);
    if (inputIcono) {
        inputIcono.addEventListener('keyup', actualizarPreview);
        inputIcono.addEventListener('change', actualizarPreview);
    }
    if (inputDesc)    inputDesc.addEventListener('input', actualizarPreview);
    if (selectColor)  selectColor.addEventListener('change', actualizarPreview);

    // Inicializar preview con valores actuales (útil en página de edición)
    actualizarPreview();

    // ─────────────────────────────────────────────────────────────────────────
    // C. TABLA: PAGINACIÓN ASÍNCRONA Y BÚSQUEDA
    // ─────────────────────────────────────────────────────────────────────────
    const tbody              = document.getElementById('tbodyModulos');
    const infoPaginacion     = document.getElementById('infoPaginacion');
    const contenedorPagina   = document.getElementById('contenedorPaginacion');
    const searchInput        = document.getElementById('global-search');
    const totalBadge         = document.getElementById('totalModulos');

    function cargarPagina(pagina = 1) {
        if (!tbody) return;

        const buscar = searchInput ? searchInput.value.trim() : '';
        tbody.style.opacity = '0.4';
        tbody.style.transition = 'opacity 0.2s';

        fetch(`/modulos?buscar=${encodeURIComponent(buscar)}&page=${pagina}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Error en el servidor');
            return res.json();
        })
        .then(data => {
            tbody.style.opacity = '1';
            tbody.innerHTML = data.html;

            if (totalBadge)        totalBadge.textContent = `${data.total} Registros`;
            if (infoPaginacion)    infoPaginacion.textContent = data.info;
            if (contenedorPagina)  {
                contenedorPagina.innerHTML = data.links;
                asignarEventosPaginacion();
            }

            enlazarToggleStatus();
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('[modulos] Error al paginar:', err);
        });
    }

    function asignarEventosPaginacion() {
        if (!contenedorPagina) return;
        contenedorPagina.querySelectorAll('a.page-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url    = new URL(this.href);
                const pagina = url.searchParams.get('page');
                if (pagina) cargarPagina(pagina);
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. TOGGLE DE ESTADO (botones .btn-toggle-status en la tabla)
    // ─────────────────────────────────────────────────────────────────────────
    function enlazarToggleStatus() {
        document.querySelectorAll('.btn-toggle-status').forEach(btn => {
            // Clonar para eliminar listeners anteriores
            const nuevo = btn.cloneNode(true);
            btn.parentNode.replaceChild(nuevo, btn);

            nuevo.addEventListener('click', function (e) {
                e.preventDefault();
                const id     = this.dataset.id;
                const fila   = this.closest('tr');
                const nombre = fila?.querySelector('.col-nombre-modulo')?.textContent.trim() ?? '';
                const activo = fila?.classList.contains('text-muted') ? 0 : 1;
                const accion = activo === 1 ? 'desactivar' : 'activar';

                const ejecutar = () => {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    fetch(`/modulos/${id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Recargar la página actual de la tabla
                            const paginaActiva = contenedorPagina
                                ?.querySelector('.page-item.active .page-link')
                                ?.textContent?.trim() ?? '1';
                            cargarPagina(paginaActiva);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: '¡Estado actualizado!',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        }
                    })
                    .catch(() => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} módulo?`,
                        text: `"${nombre}" será ${accion}do del sistema.`,
                        icon: activo === 1 ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: `Sí, ${accion}`,
                        cancelButtonText: 'Cancelar'
                    }).then(result => { if (result.isConfirmed) ejecutar(); });
                } else {
                    if (confirm(`¿${accion} el módulo "${nombre}"?`)) ejecutar();
                }
            });
        });
    }

    // Búsqueda con debounce
    function debounce(fn, ms) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => cargarPagina(1), 320));
    }

    // Inicialización: sólo si la tabla existe
    if (tbody) {
        const paginaInicial = contenedorPagina
            ?.querySelector('.page-item.active .page-link')
            ?.textContent?.trim() ?? '1';
        cargarPagina(paginaInicial);
        asignarEventosPaginacion();
    } else {
        // En vistas sin tabla (editar, proyectos, perfiles), enlazar toggles inline
        enlazarToggleStatus();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. VISTA PROYECTOS: marcar / desmarcar todos
    // ─────────────────────────────────────────────────────────────────────────
    const checkAll            = document.getElementById('checkAll');
    const btnMarcarTodos      = document.getElementById('btnMarcarTodos');
    const btnDesmarcarTodos   = document.getElementById('btnDesmarcarTodos');
    const checksProyecto      = () => document.querySelectorAll('.check-proyecto');

    function actualizarFilasProyecto() {
        checksProyecto().forEach(chk => {
            const fila = chk.closest('tr');
            if (!fila) return;
            if (chk.checked) {
                fila.classList.add('table-success');
            } else {
                fila.classList.remove('table-success');
            }
        });
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checksProyecto().forEach(chk => { chk.checked = this.checked; });
            actualizarFilasProyecto();
        });
    }

    checksProyecto().forEach(chk => {
        chk.addEventListener('change', actualizarFilasProyecto);
    });

    if (btnMarcarTodos) {
        btnMarcarTodos.addEventListener('click', () => {
            checksProyecto().forEach(chk => { chk.checked = true; });
            if (checkAll) checkAll.checked = true;
            actualizarFilasProyecto();
        });
    }

    if (btnDesmarcarTodos) {
        btnDesmarcarTodos.addEventListener('click', () => {
            checksProyecto().forEach(chk => { chk.checked = false; });
            if (checkAll) checkAll.checked = false;
            actualizarFilasProyecto();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // F. VISTA PERFILES: marcar / desmarcar todos
    // ─────────────────────────────────────────────────────────────────────────
    const checkAllPerfiles          = document.getElementById('checkAllPerfiles');
    const btnMarcarTodosPerfiles    = document.getElementById('btnMarcarTodosPerfiles');
    const btnDesmarcarTodosPerfiles = document.getElementById('btnDesmarcarTodosPerfiles');
    const checksPerfil              = () => document.querySelectorAll('.check-perfil');

    function actualizarFilasPerfiles() {
        checksPerfil().forEach(chk => {
            const fila = chk.closest('tr');
            if (!fila) return;
            chk.checked ? fila.classList.add('table-success') : fila.classList.remove('table-success');
        });
    }

    if (checkAllPerfiles) {
        checkAllPerfiles.addEventListener('change', function () {
            checksPerfil().forEach(chk => { chk.checked = this.checked; });
            actualizarFilasPerfiles();
        });
    }

    checksPerfil().forEach(chk => {
        chk.addEventListener('change', actualizarFilasPerfiles);
    });

    if (btnMarcarTodosPerfiles) {
        btnMarcarTodosPerfiles.addEventListener('click', () => {
            checksPerfil().forEach(chk => { chk.checked = true; });
            if (checkAllPerfiles) checkAllPerfiles.checked = true;
            actualizarFilasPerfiles();
        });
    }

    if (btnDesmarcarTodosPerfiles) {
        btnDesmarcarTodosPerfiles.addEventListener('click', () => {
            checksPerfil().forEach(chk => { chk.checked = false; });
            if (checkAllPerfiles) checkAllPerfiles.checked = false;
            actualizarFilasPerfiles();
        });
    }

});
