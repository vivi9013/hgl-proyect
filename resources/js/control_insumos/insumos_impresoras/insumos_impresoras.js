/**
 * insumos_impresoras.js — Lógica del módulo Catálogo de Insumos de Impresoras.
 * Cubre: alertas SweetAlert2, alternar estado (delegación).
 * La paginación/búsqueda/filtros son responsabilidad de tabla-interactiva.js.
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
    // B. BADGE DE TOTAL — actualizado vía evento de tabla-interactiva.js
    // ─────────────────────────────────────────────────────────────────────────
    const etiquetaTotal = document.getElementById('totalInsumos');

    document.querySelector('[data-tabla-interactiva]')
        ?.addEventListener('tabla-interactiva:actualizado', e => {
            if (etiquetaTotal && e.detail.total !== null) {
                etiquetaTotal.textContent = `${e.detail.total} Registros`;
            }
        });

    // ─────────────────────────────────────────────────────────────────────────
    // C. ALTERNAR ESTADO (AJAX) — delegación en document
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-toggle-status');
        if (!boton) return;

        e.preventDefault();

        const id         = boton.dataset.id;
        const nombre     = boton.dataset.nombre || '';
        const estaActivo = parseInt(boton.dataset.activo || '0');
        const accion     = estaActivo === 1 ? 'desactivar' : 'activar';

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
                    // Refrescar tabla desde la página 1 (mismo comportamiento aceptado en tipo_trabajador.js)
                    const cont = document.querySelector('[data-tabla-interactiva]');
                    if (cont) cont.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));

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
