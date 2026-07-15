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
    const entradaBusqueda       = document.getElementById('busqueda-global');
    const etiquetaTotal         = document.getElementById('totalInsumos');

    function cargarPagina(pagina = 1) {
        if (!cuerpoTabla) return;

        const buscar = entradaBusqueda ? entradaBusqueda.value.trim() : '';
        cuerpoTabla.style.opacity    = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/control-insumos/insumos-impresoras?buscar=${encodeURIComponent(buscar)}&page=${pagina}`, {
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

    if (entradaBusqueda) {
        entradaBusqueda.addEventListener('input', demorarEjecucion(() => cargarPagina(1), 320));
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
