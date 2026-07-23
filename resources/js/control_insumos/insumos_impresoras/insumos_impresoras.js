/**
 * insumos_impresoras.js — Lógica del módulo Catálogo de Insumos de Impresoras.
 * Cubre: alertas SweetAlert2, paginación y búsqueda AJAX, alternar estado.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS SWEETALERT2 DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitoGuardar    = document.getElementById('alertaExitog');
    const alertaExitoActualizar = document.getElementById('alertaExito');

    if (alertaExitoGuardar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoGuardar.dataset.message || 'El insumo se ha registrado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExitoActualizar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoActualizar.dataset.message || 'El insumo se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // Abrir modal automáticamente si hay errores de validación
    const modalAltaEl = document.getElementById('modalAltaInsumo');
    if (modalAltaEl && modalAltaEl.dataset.autoOpen === 'true') {
        new bootstrap.Modal(modalAltaEl).show();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. TABLA: PAGINACIÓN ASÍNCRONA Y BÚSQUEDA
    // ─────────────────────────────────────────────────────────────────────────
    const cuerpoTabla           = document.getElementById('cuerpoTablaInsumos');
    const infoPaginacion        = document.getElementById('infoPaginacion');
    const contenedorPaginacion  = document.getElementById('contenedorPaginacion');
    const etiquetaTotal         = document.getElementById('totalInsumos');
    const filtroBuscar          = document.getElementById('filtro-buscar');
    const filtroFechaRango      = document.getElementById('filtro-fecha-rango');
    const dropdownFiltros       = document.getElementById('dropdownFiltros');

    // Inicializar Flatpickr compartido
    if (window.inicializarFlatpickrCompartido) {
        window.inicializarFlatpickrCompartido();
    }

    function obtenerFiltros() {
        const fp      = filtroFechaRango?._flatpickr;
        const fechas  = fp ? fp.selectedDates : [];
        const fInicio = fechas[0] ? new Date(fechas[0].getTime() - fechas[0].getTimezoneOffset() * 60000).toISOString().split('T')[0] : '';
        const fFin    = fechas[1] ? new Date(fechas[1].getTime() - fechas[1].getTimezoneOffset() * 60000).toISOString().split('T')[0] : '';
        return {
            buscar:       filtroBuscar?.value.trim() ?? '',
            familia:      Array.from(document.querySelectorAll('.chk-familia:checked')).map(el => el.value),
            status:       Array.from(document.querySelectorAll('.chk-status:checked')).map(el => el.value),
            fecha_inicio: fInicio,
            fecha_fin:    fFin,
        };
    }

    function cargarPagina(pagina = 1) {
        if (!cuerpoTabla) return;

        const f = obtenerFiltros();
        const params = new URLSearchParams({ page: pagina });
        if (f.buscar)       params.set('buscar', f.buscar);
        f.familia.forEach(v => params.append('familia[]', v));
        f.status.forEach(v =>  params.append('status[]',  v));
        if (f.fecha_inicio) params.set('fecha_inicio', f.fecha_inicio);
        if (f.fecha_fin)    params.set('fecha_fin',    f.fecha_fin);

        cuerpoTabla.style.opacity    = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/control-insumos/insumos-impresoras?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) throw new Error('Error en el servidor'); return r.json(); })
        .then(datos => {
            cuerpoTabla.style.opacity = '1';
            cuerpoTabla.innerHTML     = datos.html;

            if (etiquetaTotal)          etiquetaTotal.textContent = `${datos.total} Registros`;
            if (infoPaginacion)         infoPaginacion.textContent = datos.info;
            if (contenedorPaginacion) {
                contenedorPaginacion.innerHTML = datos.links;
                asignarEventosPaginacion();
            }
            enlazarAlternarEstado();
        })
        .catch(err => {
            cuerpoTabla.style.opacity = '1';
            console.error('[insumos_impresoras] Error al paginar:', err);
        });
    }

    function asignarEventosPaginacion() {
        if (!contenedorPaginacion) return;
        contenedorPaginacion.querySelectorAll('a.page-link').forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                const pagina = new URL(this.href).searchParams.get('page');
                if (pagina) cargarPagina(pagina);
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C. ALTERNAR ESTADO (AJAX)
    // ─────────────────────────────────────────────────────────────────────────
    function enlazarAlternarEstado() {
        document.querySelectorAll('.btn-toggle-status').forEach(boton => {
            const clon = boton.cloneNode(true);
            boton.parentNode.replaceChild(clon, boton);

            clon.addEventListener('click', function (e) {
                e.preventDefault();
                const id          = this.dataset.id;
                const nombre      = this.dataset.nombre || '';
                const estaActivo  = parseInt(this.dataset.activo || '0');
                const accion      = estaActivo === 1 ? 'desactivar' : 'activar';

                const ejecutar = () => {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    fetch(`/control-insumos/insumos-impresoras/${id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(datos => {
                        if (datos.success) {
                            const paginaActiva = contenedorPaginacion
                                ?.querySelector('.page-item.active .page-link')
                                ?.textContent?.trim() ?? '1';
                            cargarPagina(paginaActiva);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ title: '¡Estado actualizado!', text: datos.message,
                                    icon: 'success', timer: 1500, showConfirmButton: false });
                            }
                        }
                    })
                    .catch(() => {
                        if (typeof Swal !== 'undefined') Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} insumo?`,
                        text: `"${nombre}" será ${accion}do del catálogo.`,
                        icon: estaActivo === 1 ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: `Sí, ${accion}`,
                        cancelButtonText: 'Cancelar'
                    }).then(res => { if (res.isConfirmed) ejecutar(); });
                } else {
                    if (confirm(`¿${accion} el insumo "${nombre}"?`)) ejecutar();
                }
            });
        });
    }

    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    if (filtroBuscar) {
        filtroBuscar.addEventListener('input', demorarEjecucion(() => cargarPagina(1), 320));
    }

    // Escuchar eventos de filtros reutilizables
    if (dropdownFiltros) {
        dropdownFiltros.addEventListener('filtros:aplicar', () => {
            cargarPagina(1);
        });
        dropdownFiltros.addEventListener('filtros:limpiar', () => {
            if (filtroBuscar) filtroBuscar.value = '';
            const fp = filtroFechaRango?._flatpickr;
            if (fp) fp.clear();
            cargarPagina(1);
        });
    }

    if (filtroFechaRango) {
        filtroFechaRango.addEventListener('change', () => {
            cargarPagina(1);
        });
    }

    if (cuerpoTabla) {
        const paginaInicial = contenedorPaginacion
            ?.querySelector('.page-item.active .page-link')
            ?.textContent?.trim() ?? '1';
        cargarPagina(paginaInicial);
        asignarEventosPaginacion();
    } else {
        enlazarAlternarEstado();
    }

});
