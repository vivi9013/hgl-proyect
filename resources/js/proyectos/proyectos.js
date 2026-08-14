/**
 * proyectos.js — Lógica del catálogo de Proyectos.
 * Cubre: alertas SweetAlert2, paginación y búsqueda AJAX, alternar estado,
 * seleccionar todos los módulos en la vista de asignación.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS SWEETALERT2 DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitoGuardar = document.getElementById('alertaExitog');
    const alertaExitoActualizar = document.getElementById('alertaExito');

    if (alertaExitoGuardar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoGuardar.dataset.message || 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExitoActualizar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoActualizar.dataset.message || 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // Abrir modal de alta automáticamente si hay errores de validación
    const modalAltaEl = document.getElementById('modalAltaProyecto');
    if (modalAltaEl && modalAltaEl.dataset.autoOpen === 'true') {
        const modalInstancia = new bootstrap.Modal(modalAltaEl);
        modalInstancia.show();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C. ALTERNAR ESTADO (delegación de eventos en document)
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-alternar-estado');
        if (!boton) return;

        e.preventDefault();

        const idRegistro      = boton.dataset.id;
        const nombreProyecto  = boton.dataset.nombre || '';
        const estaActivo      = parseInt(boton.dataset.activo || '0');
        const accionTexto     = estaActivo === 1 ? 'desactivar' : 'activar';

        const ejecutarAccion = () => {
            const tokenCsrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(`/proyectos/${idRegistro}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN'     : tokenCsrf,
                    'X-Requested-With' : 'XMLHttpRequest',
                    'Accept'           : 'application/json'
                }
            })
            .then(respuesta => respuesta.json())
            .then(datos => {
                if (datos.success) {
                    document.querySelector('[data-tabla-interactiva]')
                        ?.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title              : '¡Estado actualizado!',
                            text               : datos.message,
                            icon               : 'success',
                            timer              : 1500,
                            showConfirmButton  : false
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
                title            : `¿${accionTexto.charAt(0).toUpperCase() + accionTexto.slice(1)} proyecto?`,
                text             : `"${nombreProyecto}" será ${accionTexto}do del sistema.`,
                icon             : estaActivo === 1 ? 'warning' : 'question',
                showCancelButton : true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor : '#d33',
                confirmButtonText : `Sí, ${accionTexto}`,
                cancelButtonText  : 'Cancelar'
            }).then(resultado => { if (resultado.isConfirmed) ejecutarAccion(); });
        } else {
            if (confirm(`¿${accionTexto} el proyecto "${nombreProyecto}"?`)) ejecutarAccion();
        }
    });

    // ─────────────────────────────────────────────────────────────────────────
    // C.2 FORMULARIO DE TOGGLE ESTADO EN VISTA DE EDICIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const formToggleEstado = document.getElementById('formToggleEstado');
    if (formToggleEstado) {
        formToggleEstado.addEventListener('submit', function (e) {
            e.preventDefault();

            const url = this.getAttribute('action');
            const tokenCsrf = this.querySelector('input[name="_token"]')?.value ?? '';
            const nombreProyecto = this.dataset.nombre || '';
            const estaActivo = parseInt(this.dataset.activo || '0');
            const accionTexto = estaActivo === 1 ? 'desactivar' : 'activar';

            const ejecutarAccionEdit = () => {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': tokenCsrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(formToggleEstado)
                })
                .then(respuesta => {
                    if (!respuesta.ok) throw new Error('Error en el servidor');
                    return respuesta.json();
                })
                .then(datos => {
                    if (datos.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '¡Estado actualizado!',
                                text: datos.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            window.location.reload();
                        }
                    }
                })
                .catch(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'No se pudo actualizar el estado del proyecto.', 'error');
                    } else {
                        alert('No se pudo actualizar el estado del proyecto.');
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `¿${accionTexto.charAt(0).toUpperCase() + accionTexto.slice(1)} proyecto?`,
                    text: `"${nombreProyecto}" será ${accionTexto}do del sistema.`,
                    icon: estaActivo === 1 ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Sí, ${accionTexto}`,
                    cancelButtonText: 'Cancelar'
                }).then(resultado => {
                    if (resultado.isConfirmed) {
                        ejecutarAccionEdit();
                    }
                });
            } else {
                if (confirm(`¿${accionTexto} el proyecto "${nombreProyecto}"?`)) {
                    ejecutarAccionEdit();
                }
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. ASOCIACIÓN DE MÓDULOS: SELECCIONAR / DESELECCIONAR TODOS
    // ─────────────────────────────────────────────────────────────────────────
    const seleccionarTodosModulos = document.getElementById('seleccionarTodosModulos');
    const btnSeleccionarTodosModulos = document.getElementById('btnSeleccionarTodosModulos');
    const btnDeseleccionarTodosModulos = document.getElementById('btnDeseleccionarTodosModulos');
    const casillasModulo = () => document.querySelectorAll('.casilla-modulo');

    function actualizarFilasModulo() {
        casillasModulo().forEach(casilla => {
            const fila = casilla.closest('tr');
            if (!fila) return;
            if (casilla.checked) {
                fila.classList.add('table-success');
            } else {
                fila.classList.remove('table-success');
            }
        });
    }

    if (seleccionarTodosModulos) {
        seleccionarTodosModulos.addEventListener('change', function () {
            casillasModulo().forEach(casilla => { casilla.checked = this.checked; });
            actualizarFilasModulo();
        });
    }

    casillasModulo().forEach(casilla => {
        casilla.addEventListener('change', actualizarFilasModulo);
    });

    if (btnSeleccionarTodosModulos) {
        btnSeleccionarTodosModulos.addEventListener('click', () => {
            casillasModulo().forEach(casilla => { casilla.checked = true; });
            if (seleccionarTodosModulos) seleccionarTodosModulos.checked = true;
            actualizarFilasModulo();
        });
    }

    if (btnDeseleccionarTodosModulos) {
        btnDeseleccionarTodosModulos.addEventListener('click', () => {
            casillasModulo().forEach(casilla => { casilla.checked = false; });
            if (seleccionarTodosModulos) seleccionarTodosModulos.checked = false;
            actualizarFilasModulo();
        });
    }

});