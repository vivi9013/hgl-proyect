import { initPanelClaves } from '../shared/panel-claves.js';

/**
 * Lógica JavaScript para el módulo de Devoluciones de Insumos
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── 1.5. Validación Frontend del Modal "Nueva Devolución" ─────────────────
    const formNuevaDevolucion = document.getElementById('formNuevaDevolucion');
    if (formNuevaDevolucion) {
        formNuevaDevolucion.addEventListener('submit', function (e) {
            let isValid = true;
            const areaAlmacen = document.getElementById('id_area_almacen');
            const motivo = document.getElementById('id_motivo');

            if (areaAlmacen) {
                areaAlmacen.classList.remove('is-invalid');
                if (!areaAlmacen.value) {
                    areaAlmacen.classList.add('is-invalid');
                    isValid = false;
                }
            }

            if (motivo) {
                motivo.classList.remove('is-invalid');
                if (!motivo.value) {
                    motivo.classList.add('is-invalid');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        const areaAlmacen = document.getElementById('id_area_almacen');
        const motivo = document.getElementById('id_motivo');
        if (areaAlmacen) {
            areaAlmacen.addEventListener('change', function () {
                if (areaAlmacen.value) areaAlmacen.classList.remove('is-invalid');
            });
        }
        if (motivo) {
            motivo.addEventListener('change', function () {
                if (motivo.value) motivo.classList.remove('is-invalid');
            });
        }
    }

    // ── 1. Alertas SweetAlert2 con sesión de Laravel ──────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');
    const alertaError = document.getElementById('alertaError');

    if (alertaExitog && typeof Swal !== 'undefined') {
        const msg = alertaExitog.getAttribute('data-message') || 'Operación realizada con éxito.';
        Swal.fire({
            title: '¡Operación Exitosa!',
            text: msg,
            icon: 'success',
            confirmButtonColor: '#1d4ed8',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        const msg = alertaExito.getAttribute('data-message') || 'Operación realizada con éxito.';
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: msg,
            icon: 'success',
            confirmButtonColor: '#1d4ed8',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaError && typeof Swal !== 'undefined') {
        const msg = alertaError.getAttribute('data-message') || 'Ocurrió un error inesperado.';
        Swal.fire({
            title: 'Aviso',
            text: msg,
            icon: 'warning',
            confirmButtonColor: '#1d4ed8',
            confirmButtonText: 'Aceptar'
        });
    }

    // ── 2. Autocompletado del Buscador de Insumos (Detalle de Devolución) ──────
    const inputBuscarInsumo = document.getElementById('buscarInsumoDetalle');
    const inputIdInsumo = document.getElementById('id_insumo_detalle');
    const sugerenciasDiv = document.getElementById('sugerenciasDetalle');

    let timeoutBusqueda = null;

    if (inputBuscarInsumo && sugerenciasDiv) {
        inputBuscarInsumo.addEventListener('input', function () {
            const termino = inputBuscarInsumo.value.trim();
            clearTimeout(timeoutBusqueda);

            if (termino.length < 2) {
                sugerenciasDiv.style.display = 'none';
                sugerenciasDiv.innerHTML = '';
                inputIdInsumo.value = '';
                return;
            }

            timeoutBusqueda = setTimeout(() => {
                const url = `/devoluciones/buscar-insumos?q=${encodeURIComponent(termino)}`;

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Error de servidor');
                    return res.json();
                })
                .then(data => {
                    sugerenciasDiv.innerHTML = '';

                    if (!data || data.length === 0) {
                        sugerenciasDiv.style.display = 'none';
                        inputIdInsumo.value = '';
                        return;
                    }

                    data.forEach(insumo => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                        item.innerHTML = `
                            <div>
                                <span class="badge bg-primary text-white me-2" style="font-family: monospace;">${insumo.clave}</span>
                                <span>${insumo.descripcion}</span>
                            </div>
                            <span class="badge bg-secondary text-white small">${insumo.tipo || 'Insumo'}</span>
                        `;
                        item.addEventListener('click', () => {
                            inputBuscarInsumo.value = `[${insumo.clave}] ${insumo.descripcion}`;
                            inputIdInsumo.value = insumo.id_insumo;
                            sugerenciasDiv.style.display = 'none';
                            sugerenciasDiv.innerHTML = '';
                        });
                        sugerenciasDiv.appendChild(item);
                    });

                    sugerenciasDiv.style.display = 'block';
                })
                .catch(err => {
                    console.error('Error al buscar insumos:', err);
                    sugerenciasDiv.style.display = 'none';
                });
            }, 300);
        });

        // Cerrar sugerencias al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!inputBuscarInsumo.contains(e.target) && !sugerenciasDiv.contains(e.target)) {
                sugerenciasDiv.style.display = 'none';
            }
        });

        // Inicializar panel de claves para devoluciones
        initPanelClaves({
            panelId: 'panelClavesDetalle',
            inputBuscarId: 'buscarInsumoDetalle',
            inputHiddenId: 'id_insumo_detalle',
            sugerenciasId: 'sugerenciasDetalle',
            endpoint: '/devoluciones/buscar-insumos',
            columnaExtra: 'none'
        });
    }

    // ── 3. Validación de Insumo antes de enviar formulario de detalle ─────────
    const formAgregarInsumo = document.getElementById('formAgregarInsumo');
    if (formAgregarInsumo) {
        formAgregarInsumo.addEventListener('submit', function (e) {
            const idInsumo = inputIdInsumo ? inputIdInsumo.value : '';
            const cantidad = document.getElementById('cantidad_detalle') ? parseInt(document.getElementById('cantidad_detalle').value) : 0;

            if (!idInsumo) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Insumo requerido',
                        text: 'Debe seleccionar un insumo de la lista de sugerencias.',
                        icon: 'warning',
                        confirmButtonColor: '#1d4ed8',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    alert('Debe seleccionar un insumo de la lista de sugerencias.');
                }
                return;
            }

            if (isNaN(cantidad) || cantidad <= 0) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Cantidad inválida',
                        text: 'La cantidad debe ser mayor a cero.',
                        icon: 'warning',
                        confirmButtonColor: '#1d4ed8',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    alert('La cantidad debe ser mayor a cero.');
                }
                return;
            }
        });
    }

    // ── 4. Confirmación SweetAlert2 para eliminar insumo de la devolución ─────
    const btnEliminarDetalle = document.querySelectorAll('.btn-eliminar-detalle');
    btnEliminarDetalle.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('data-url');
            const insumo = this.getAttribute('data-insumo');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Quitar insumo?',
                    html: `Se removerá <strong>"${insumo}"</strong> de esta devolución.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, quitar insumo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar petición DELETE mediante fetch
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ _method: 'DELETE' })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.ok) {
                                Swal.fire({
                                    title: '¡Removido!',
                                    text: data.mensaje,
                                    icon: 'success',
                                    confirmButtonColor: '#1d4ed8'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.mensaje || 'No se pudo quitar el insumo.', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
                        });
                    }
                });
            } else {
                if (confirm(`¿Está seguro de que desea quitar "${insumo}" de la devolución?`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        });
    });

    // ── 5. Confirmación SweetAlert2 para cancelar/reactivar devolución ─────────
    const btnCancelar = document.querySelectorAll('.btn-cancelar-devolucion');
    btnCancelar.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const folio = this.getAttribute('data-folio') || 'devolución';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Cancelar devolución?',
                    html: `Se marcará la devolución <strong>"${folio}"</strong> como Cancelada.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, cancelar devolución',
                    cancelButtonText: 'No, conservar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                if (confirm(`¿Está seguro de que desea cancelar la devolución "${folio}"?`)) {
                    window.location.href = url;
                }
            }
        });
    });

    const btnReactivar = document.querySelectorAll('.btn-reactivar-devolucion');
    btnReactivar.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const folio = this.getAttribute('data-folio') || 'devolución';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Reactivar devolución?',
                    html: `Se cambiará el estado de la devolución <strong>"${folio}"</strong> a Pendiente (En proceso).`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, reactivar',
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                if (confirm(`¿Está seguro de que desea reactivar la devolución "${folio}"?`)) {
                    window.location.href = url;
                }
            }
        });
    });

    // ── 6. Confirmación SweetAlert2 para finalizar devolución ──────────────────
    const btnFinalizar = document.getElementById('btnFinalizar');
    const formFinalizar = document.getElementById('formFinalizarHidden');

    if (btnFinalizar && formFinalizar) {
        btnFinalizar.addEventListener('click', function (e) {
            e.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Finalizar Devolución?',
                    text: 'Una vez finalizada, el stock del almacén se incrementará y no podrá agregar más insumos.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, finalizar devolución',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formFinalizar.submit();
                    }
                });
            } else {
                if (confirm('¿Está seguro de finalizar esta devolución? Esto incrementará el stock del almacén.')) {
                    formFinalizar.submit();
                }
            }
        });
    }

    // ── 7. Auto-envío de filtros de rango de fechas al cambiar ────────────────
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const formBuscar = document.getElementById('formBuscar');

    const fechaInicioTerm = document.getElementById('fecha_inicio_term');
    const fechaFinTerm = document.getElementById('fecha_fin_term');
    const formBuscarTerminadas = document.getElementById('formBuscarTerminadas');

    const autoSubmitFecha = (inicioInput, finInput, form) => {
        if (!inicioInput || !finInput || !form) return;

        const valInicio = inicioInput.value;
        const valFin = finInput.value;

        if (valInicio && valFin && valInicio > valFin) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Rango de fechas inválido',
                    text: 'La fecha de inicio no puede ser posterior a la fecha de fin.',
                    icon: 'warning',
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'Aceptar'
                });
            } else {
                alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
            }
            inicioInput.value = '';
            finInput.value = '';
            return;
        }

        form.submit();
    };

    if (fechaInicio && fechaFin && formBuscar) {
        fechaInicio.addEventListener('change', () => autoSubmitFecha(fechaInicio, fechaFin, formBuscar));
        fechaFin.addEventListener('change', () => autoSubmitFecha(fechaInicio, fechaFin, formBuscar));
    }

    if (fechaInicioTerm && fechaFinTerm && formBuscarTerminadas) {
        fechaInicioTerm.addEventListener('change', () => autoSubmitFecha(fechaInicioTerm, fechaFinTerm, formBuscarTerminadas));
        fechaFinTerm.addEventListener('change', () => autoSubmitFecha(fechaInicioTerm, fechaFinTerm, formBuscarTerminadas));
    }
});
