/**
 * tabla-ajax.js — Módulo JS compartido para tablas con paginación y filtros AJAX.
 *
 * Centraliza la lógica repetida en los módulos de Petición de Insumos:
 *   - fetch con header X-Requested-With
 *   - debounce de búsqueda (300ms)
 *   - interceptación de paginación
 *   - filtros por checkbox de estatus
 *   - toggle de estatus con SweetAlert2
 *   - lectura del token CSRF
 *   - history.replaceState para URL bookmarkeable
 *
 * USO:
 *   import { initTablaAjax } from '@/shared/tabla-ajax.js';
 *
 *   initTablaAjax({
 *     contenedorId    : 'contenedor-tabla',          // ID del div que contiene el HTML de la tabla
 *     formId          : 'formFiltros',               // ID del <form> cuyos inputs se serialicen
 *     inputBuscarId   : 'buscar',                    // ID del input de búsqueda (para debounce)
 *     statusSelector  : '.filtro-status',            // Selector de checkboxes de estatus
 *     toggleSelector  : '.btn-toggle-status',        // Selector del badge/botón de toggle
 *     statusUrlFn     : (id) => `/ruta/${id}/status`,// Función que retorna la URL del endpoint PATCH
 *     entityLabel     : 'el registro',               // Texto que aparece en el SweetAlert de toggle
 *     debounceMs      : 300,                         // (opcional) milisegundos de debounce
 *   });
 *
 * Los módulos que tengan lógica extra (gráficas, autocompletado, etc.)
 * la añaden localmente — esta función SOLO cubre la tabla AJAX.
 */

/**
 * @param {Object} opts
 */
export function initTablaAjax(opts = {}) {
    const {
        contenedorId   = 'contenedor-tabla',
        formId         = 'formFiltros',
        inputBuscarId  = 'buscar',
        statusSelector = '.filtro-status',
        toggleSelector = '.btn-toggle-status',
        statusUrlFn    = null,
        entityLabel    = 'el registro',
        debounceMs     = 300,
    } = opts;

    const contenedor  = document.getElementById(contenedorId);
    const form        = document.getElementById(formId);
    const inputBuscar = document.getElementById(inputBuscarId);
    let   timerDebounce = null;

    if (!contenedor) return; // Página sin tabla AJAX — no hacer nada

    // ── Helpers internos ────────────────────────────────────────────────────

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    }

    /**
     * Construye la URL de fetch combinando la URL base con los parámetros
     * del formulario de filtros, añadiendo el paginador externo si se pasa.
     */
    function buildUrl(baseUrl = null) {
        const target = baseUrl || window.location.href.split('?')[0];
        const params = form
            ? new URLSearchParams(new FormData(form)).toString()
            : '';
        return params ? `${target}?${params}` : target;
    }

    /**
     * Recarga el contenido de la tabla vía AJAX.
     * @param {string|null} url  URL de paginación (null = misma página + filtros)
     */
    function cargarTabla(url = null) {
        const fetchUrl = buildUrl(url);

        contenedor.style.opacity    = '0.5';
        contenedor.style.transition = 'opacity 0.15s';

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept'          : 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                contenedor.innerHTML = data.html;
            }
            // Actualizar URL del navegador para que sea bookmarkeable
            try {
                const search = new URL(fetchUrl).search;
                history.replaceState(null, '', search || window.location.pathname);
            } catch (_) { /* URL relativa — sin replaceState */ }
        })
        .catch(err => console.error('[tabla-ajax] Error al cargar la tabla:', err))
        .finally(() => {
            contenedor.style.opacity = '1';
        });
    }

    // ── Búsqueda reactiva con debounce ──────────────────────────────────────
    if (inputBuscar) {
        inputBuscar.addEventListener('input', () => {
            clearTimeout(timerDebounce);
            timerDebounce = setTimeout(() => cargarTabla(), debounceMs);
        });
    }

    // ── Paginación AJAX (delegación en el contenedor) ───────────────────────
    contenedor.addEventListener('click', e => {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            cargarTabla(link.href);
        }
    });

    // ── Checkboxes de filtro de estatus ─────────────────────────────────────
    document.querySelectorAll(statusSelector).forEach(chk => {
        chk.addEventListener('change', () => cargarTabla());
    });

    // ── Toggle de estatus con SweetAlert2 (delegación) ──────────────────────
    if (typeof statusUrlFn === 'function') {
        contenedor.addEventListener('click', e => {
            const badge = e.target.closest(toggleSelector);
            if (!badge) return;

            const id          = badge.dataset.id;
            const statusActual = badge.dataset.status;
            const accion      = statusActual === '1' ? 'desactivar' : 'activar';
            const nuevoEstado = statusActual === '1' ? 'Inactivo' : 'Activo';

            Swal.fire({
                title            : `¿Deseas ${accion} ${entityLabel}?`,
                text             : `El estatus cambiará a ${nuevoEstado}.`,
                icon             : 'warning',
                showCancelButton : true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor : '#d33',
                confirmButtonText : 'Sí, cambiar',
                cancelButtonText  : 'Cancelar',
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(statusUrlFn(id), {
                    method : 'PATCH',
                    headers: {
                        'Content-Type'    : 'application/json',
                        'X-CSRF-TOKEN'    : csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon             : 'success',
                            title            : '¡Actualizado!',
                            text             : data.mensaje,
                            timer            : 1500,
                            showConfirmButton : false,
                        });
                        cargarTabla();
                    }
                })
                .catch(() => Swal.fire('Error', 'No se pudo actualizar el estatus.', 'error'));
            });
        });
    }

    // Exponer cargarTabla por si el módulo consumidor necesita recargar
    // desde lógica externa (ej. tras guardar un modal)
    return { cargarTabla };
}
