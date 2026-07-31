import { initPanelClaves } from '../shared/panel-claves.js';

/**
 * Lógica JavaScript para el módulo de Entrada de Insumos al Cendis
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

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
            confirmButtonColor: '#2b6cb0',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        const msg = alertaExito.getAttribute('data-message') || 'Operación realizada con éxito.';
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: msg,
            icon: 'success',
            confirmButtonColor: '#2b6cb0',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaError && typeof Swal !== 'undefined') {
        const msg = alertaError.getAttribute('data-message') || 'Ocurrió un error inesperado.';
        Swal.fire({
            title: 'Aviso',
            text: msg,
            icon: 'warning',
            confirmButtonColor: '#2b6cb0',
            confirmButtonText: 'Aceptar'
        });
    }

    // ── 2. Autocompletado del Buscador de Insumos (Detalle de Entrada) ──────────
    const inputBuscarInsumo = document.getElementById('buscarInsumoDetalle');
    const inputIdInsumo = document.getElementById('id_insumo_detalle');
    const inputDescripcion = document.getElementById('descripcion_insumo');
    const inputStock = document.getElementById('stock_insumo');
    const inputSolicitado = document.getElementById('solicitado_detalle');
    const inputCantidad = document.getElementById('cantidad_detalle');
    const inputFaltante = document.getElementById('faltante_detalle');
    const sugerenciasDiv = document.getElementById('sugerenciasDetalle');
    const areaAlmacenId = document.getElementById('id_area_almacen_active')?.value;

    let timeoutBusqueda = null;

    if (inputBuscarInsumo && sugerenciasDiv) {
        inputBuscarInsumo.addEventListener('input', function () {
            const termino = inputBuscarInsumo.value.trim();
            clearTimeout(timeoutBusqueda);

            if (termino.length < 2) {
                sugerenciasDiv.style.display = 'none';
                sugerenciasDiv.innerHTML = '';
                if (inputIdInsumo) inputIdInsumo.value = '';
                return;
            }

            timeoutBusqueda = setTimeout(() => {
                const url = `/entradas-cendis/buscar-insumos?q=${encodeURIComponent(termino)}`;

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
                        if (inputIdInsumo) inputIdInsumo.value = '';
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
                            inputBuscarInsumo.value = insumo.clave;
                            if (inputIdInsumo) inputIdInsumo.value = insumo.id_insumo;
                            if (inputDescripcion) inputDescripcion.value = insumo.descripcion;
                            
                            sugerenciasDiv.style.display = 'none';
                            sugerenciasDiv.innerHTML = '';

                            // Consultar stock en el área de almacén activa
                            if (areaAlmacenId && insumo.id_insumo) {
                                fetch(`/entradas-cendis/consultar-stock?id_insumo=${insumo.id_insumo}&id_area_almacen=${areaAlmacenId}`)
                                .then(res => res.json())
                                .then(stockData => {
                                    if (inputStock) inputStock.value = stockData.stock || 0;
                                });
                            }

                            // Habilitar campos
                            if (inputSolicitado) inputSolicitado.removeAttribute('disabled');
                            if (inputCantidad) inputCantidad.removeAttribute('disabled');
                            if (inputSolicitado) inputSolicitado.focus();
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

        // Inicializar panel de claves para CENDIS
        initPanelClaves({
            panelId: 'panelClavesDetalle',
            inputBuscarId: 'buscarInsumoDetalle',
            inputHiddenId: 'id_insumo_detalle',
            sugerenciasId: 'sugerenciasDetalle',
            areaInputId: 'id_area_almacen_active',
            endpoint: '/entradas-cendis/buscar-insumos',
            columnaExtra: 'stock',
            onSelect: (insumo) => {
                if (inputBuscarInsumo) inputBuscarInsumo.value = insumo.clave;
                if (inputDescripcion) inputDescripcion.value = insumo.descripcion;
                
                // Consultar stock en el área de almacén activa
                if (areaAlmacenId && insumo.id_insumo) {
                    fetch(`/entradas-cendis/consultar-stock?id_insumo=${insumo.id_insumo}&id_area_almacen=${areaAlmacenId}`)
                    .then(res => res.json())
                    .then(stockData => {
                        if (inputStock) inputStock.value = stockData.stock || 0;
                    });
                }

                // Habilitar campos
                if (inputSolicitado) inputSolicitado.removeAttribute('disabled');
                if (inputCantidad) inputCantidad.removeAttribute('disabled');
                if (inputSolicitado) inputSolicitado.focus();
            }
        });
    }

    // ── 3. Cálculo de Faltante en tiempo real ──────────────────────────────────
    const difCantidad = () => {
        if (!inputSolicitado || !inputCantidad || !inputFaltante) return;
        const solicitado = parseInt(inputSolicitado.value) || 0;
        const entregado = parseInt(inputCantidad.value) || 0;
        const faltante = solicitado - entregado;
        inputFaltante.value = faltante >= 0 ? faltante : 0;
    };

    if (inputSolicitado && inputCantidad) {
        inputSolicitado.addEventListener('input', difCantidad);
        inputCantidad.addEventListener('input', difCantidad);
    }

    // ── 4. Validación de Insumo antes de enviar formulario de detalle ─────────
    const formAgregarInsumo = document.getElementById('formAgregarInsumo');
    if (formAgregarInsumo) {
        formAgregarInsumo.addEventListener('submit', function (e) {
            const idInsumo = inputIdInsumo ? inputIdInsumo.value : '';
            const solicitado = inputSolicitado ? parseInt(inputSolicitado.value) : 0;
            const cantidad = inputCantidad ? parseInt(inputCantidad.value) : 0;

            if (!idInsumo) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Insumo requerido',
                        text: 'Debe seleccionar un insumo válido.',
                        icon: 'warning',
                        confirmButtonColor: '#2b6cb0',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    alert('Debe seleccionar un insumo válido.');
                }
                return;
            }

            if (isNaN(solicitado) || solicitado < 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Cantidad solicitada inválida',
                    text: 'La cantidad solicitada debe ser mayor o igual a cero.',
                    icon: 'warning',
                    confirmButtonColor: '#2b6cb0',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            if (isNaN(cantidad) || cantidad < 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Cantidad entregada inválida',
                    text: 'La cantidad entregada debe ser mayor o igual a cero.',
                    icon: 'warning',
                    confirmButtonColor: '#2b6cb0',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
        });
    }

    // ── 5. Confirmación SweetAlert2 para eliminar insumo de la entrada ──────────
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
                    html: `Se removerá <strong>"${insumo}"</strong> de esta entrada.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, quitar insumo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
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
                                    confirmButtonColor: '#2b6cb0'
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
                if (confirm(`¿Está seguro de que desea quitar "${insumo}" de la entrada?`)) {
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

    // ── 6. Confirmación SweetAlert2 para cancelar/reactivar entrada ──────────────
    const btnCancelar = document.querySelectorAll('.btn-cancelar-entrada');
    btnCancelar.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const folio = this.getAttribute('data-folio') || 'entrada';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Cancelar entrada?',
                    html: `Se marcará la entrada <strong>"${folio}"</strong> como Cancelada.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, cancelar entrada',
                    cancelButtonText: 'No, conservar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                if (confirm(`¿Está seguro de que desea cancelar la entrada "${folio}"?`)) {
                    window.location.href = url;
                }
            }
        });
    });

    const btnReactivar = document.querySelectorAll('.btn-reactivar-entrada');
    btnReactivar.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const folio = this.getAttribute('data-folio') || 'entrada';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Reactivar entrada?',
                    html: `Se reactivará la entrada <strong>"${folio}"</strong> y volverá a estar En proceso.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2b6cb0',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, reactivar',
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                if (confirm(`¿Está seguro de que desea reactivar la entrada "${folio}"?`)) {
                    window.location.href = url;
                }
            }
        });
    });

    // ── 7. Confirmación SweetAlert2 para finalizar entrada ────────────────────────
    const btnFinalizar = document.getElementById('btnFinalizar');
    const formFinalizar = document.getElementById('formFinalizarHidden');

    if (btnFinalizar && formFinalizar) {
        btnFinalizar.addEventListener('click', function (e) {
            e.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Finalizar Entrada?',
                    text: 'Una vez finalizada, el stock del almacén se incrementará y no podrá agregar más insumos.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2f855a',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, finalizar entrada',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formFinalizar.submit();
                    }
                });
            } else {
                if (confirm('¿Está seguro de finalizar esta entrada? Esto incrementará el stock del almacén.')) {
                    formFinalizar.submit();
                }
            }
        });
    }

    // ── 8. Actualizar Cantidad en la tabla en tiempo real (instantáneo + AJAX) ──
    const inputsCantidadTabla = document.querySelectorAll('.cantidad-tabla-input');

    const recalcularTotalesTabla = () => {
        const tabla = document.getElementById('tablaDetalles');
        if (!tabla) return;

        let totalSol = 0;
        let totalCant = 0;
        let totalFal = 0;

        const filas = tabla.querySelectorAll('tbody tr');
        filas.forEach(tr => {
            if (tr.id === 'filaVacia') return;
            const solCol = tr.querySelector('.solicitado-col');
            const cantInput = tr.querySelector('.cantidad-tabla-input');
            const falCol = tr.querySelector('.faltante-col');

            const sol = parseInt(solCol?.textContent) || 0;
            const cant = cantInput ? (parseInt(cantInput.value) || 0) : (parseInt(tr.children[4]?.textContent) || 0);
            const fal = parseInt(falCol?.textContent) || 0;

            totalSol += sol;
            totalCant += cant;
            totalFal += fal;
        });

        const elTotalSol = document.getElementById('totalSolicitadoTabla');
        const elTotalCant = document.getElementById('totalCantidadTabla');
        const elTotalFal = document.getElementById('totalFaltanteTabla');

        if (elTotalSol) elTotalSol.textContent = totalSol;
        if (elTotalCant) elTotalCant.textContent = totalCant;
        if (elTotalFal) {
            elTotalFal.textContent = totalFal;
            if (totalFal > 0) {
                elTotalFal.classList.remove('text-success');
                elTotalFal.classList.add('text-danger');
            } else {
                elTotalFal.classList.remove('text-danger');
                elTotalFal.classList.add('text-success');
            }
        }
    };

    inputsCantidadTabla.forEach(input => {
        const actualizarFilaInstantanea = () => {
            const val = parseInt(input.value) || 0;
            const fila = input.closest('tr');
            if (!fila) return;

            const colSolicitado = fila.querySelector('.solicitado-col');
            const colFaltante = fila.querySelector('.faltante-col');

            const solicitado = parseInt(colSolicitado?.textContent) || 0;
            const faltante = Math.max(0, solicitado - val);

            if (colFaltante) {
                colFaltante.textContent = faltante;
                if (faltante > 0) {
                    colFaltante.classList.remove('text-success');
                    colFaltante.classList.add('text-danger');
                } else {
                    colFaltante.classList.remove('text-danger');
                    colFaltante.classList.add('text-success');
                }
            }

            recalcularTotalesTabla();
        };

        const updateValServer = () => {
            const url = input.getAttribute('data-url');
            const prevVal = parseInt(input.getAttribute('data-prev')) || 0;
            const val = parseInt(input.value) || 0;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (val < 0) {
                Swal.fire('Cantidad inválida', 'La cantidad debe ser mayor o igual a cero.', 'warning');
                input.value = prevVal;
                actualizarFilaInstantanea();
                return;
            }

            if (val === prevVal) return;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PUT',
                    cantidad: val
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Error HTTP: ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.ok) {
                    input.setAttribute('data-prev', val);
                    const fila = input.closest('tr');
                    const colSolicitado = fila.querySelector('.solicitado-col');
                    const colFaltante = fila.querySelector('.faltante-col');

                    if (colSolicitado && data.solicitado !== undefined) {
                        colSolicitado.textContent = data.solicitado;
                    }
                    if (colFaltante && data.faltante !== undefined) {
                        colFaltante.textContent = data.faltante;
                        if (data.faltante > 0) {
                            colFaltante.classList.remove('text-success');
                            colFaltante.classList.add('text-danger');
                        } else {
                            colFaltante.classList.remove('text-danger');
                            colFaltante.classList.add('text-success');
                        }
                    }
                    recalcularTotalesTabla();
                } else {
                    Swal.fire('Error', data.mensaje || 'No se pudo actualizar.', 'error');
                    input.value = prevVal;
                    actualizarFilaInstantanea();
                }
            })
            .catch(err => {
                console.error('Error al actualizar cantidad:', err);
                Swal.fire('Error', 'Ocurrió un error al actualizar la cantidad.', 'error');
                input.value = prevVal;
                actualizarFilaInstantanea();
            });
        };

        // Escuchar el evento 'input' para actualización 100% instantánea (0ms)
        input.addEventListener('input', actualizarFilaInstantanea);

        // Escuchar el evento 'change' (blur) para guardar en servidor de fondo
        input.addEventListener('change', updateValServer);

        input.addEventListener('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                updateValServer();
                input.blur();
            }
        });
    });

    // ── 9. Auto-envío de filtros al cambiar ─────────────────────────────────────
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const formBuscar = document.getElementById('formBuscar');

    const autoSubmitFecha = (inicioInput, finInput, form) => {
        if (!inicioInput || !finInput || !form) return;

        const valInicio = inicioInput.value;
        const valFin = finInput.value;

        if (valInicio && valFin && valInicio > valFin) {
            Swal.fire({
                title: 'Rango de fechas inválido',
                text: 'La fecha de inicio no puede ser posterior a la fecha de fin.',
                icon: 'warning',
                confirmButtonColor: '#2b6cb0',
                confirmButtonText: 'Aceptar'
            });
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

    // ── 10. Paginación de la tabla de insumos del detalle ───────────────────────
    const tablaDetalles = document.getElementById('tablaDetalles');
    const pagContainer = document.getElementById('paginacionTablaDetallesContainer');
    const ulPaginacion = document.getElementById('ulPaginacionTablaDetalles');
    const selectLimit = document.getElementById('selectLimitTablaDetalles');
    const infoInicio = document.getElementById('pagInicioTablaDetalles');
    const infoFin = document.getElementById('pagFinTablaDetalles');
    const infoTotal = document.getElementById('pagTotalTablaDetalles');

    if (tablaDetalles && pagContainer && ulPaginacion) {
        let itemsPorPagina = 10;
        let paginaActual = 1;

        const obtenerFilas = () => {
            const tbody = tablaDetalles.querySelector('tbody');
            if (!tbody) return [];
            return Array.from(tbody.querySelectorAll('tr')).filter(tr => tr.id !== 'filaVacia');
        };

        const renderizarPaginacion = () => {
            const filas = obtenerFilas();
            const totalFilas = filas.length;

            if (totalFilas === 0) {
                pagContainer.style.display = 'none';
                return;
            }

            pagContainer.style.display = 'flex';

            const limitVal = selectLimit ? selectLimit.value : '10';
            const limit = limitVal === 'all' ? totalFilas : parseInt(limitVal, 10) || 10;
            itemsPorPagina = limit;

            const totalPaginas = Math.ceil(totalFilas / itemsPorPagina);

            if (paginaActual > totalPaginas) paginaActual = totalPaginas;
            if (paginaActual < 1) paginaActual = 1;

            const inicio = (paginaActual - 1) * itemsPorPagina;
            const fin = Math.min(inicio + itemsPorPagina, totalFilas);

            filas.forEach((tr, index) => {
                if (index >= inicio && index < fin) {
                    tr.style.display = '';
                } else {
                    tr.style.display = 'none';
                }
            });

            if (infoInicio) infoInicio.textContent = totalFilas > 0 ? inicio + 1 : 0;
            if (infoFin) infoFin.textContent = fin;
            if (infoTotal) infoTotal.textContent = totalFilas;

            ulPaginacion.innerHTML = '';

            if (totalPaginas <= 1) {
                return;
            }

            // Botón Anterior
            const liPrev = document.createElement('li');
            liPrev.className = `page-item ${paginaActual === 1 ? 'disabled' : ''}`;
            liPrev.innerHTML = `<a class="page-link" href="#" aria-label="Anterior">&laquo;</a>`;
            liPrev.addEventListener('click', (e) => {
                e.preventDefault();
                if (paginaActual > 1) {
                    paginaActual--;
                    renderizarPaginacion();
                }
            });
            ulPaginacion.appendChild(liPrev);

            // Rango de páginas a mostrar
            let startPage = Math.max(1, paginaActual - 2);
            let endPage = Math.min(totalPaginas, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            for (let i = startPage; i <= endPage; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                li.addEventListener('click', (e) => {
                    e.preventDefault();
                    paginaActual = i;
                    renderizarPaginacion();
                });
                ulPaginacion.appendChild(li);
            }

            // Botón Siguiente
            const liNext = document.createElement('li');
            liNext.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
            liNext.innerHTML = `<a class="page-link" href="#" aria-label="Siguiente">&raquo;</a>`;
            liNext.addEventListener('click', (e) => {
                e.preventDefault();
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    renderizarPaginacion();
                }
            });
            ulPaginacion.appendChild(liNext);
        };

        if (selectLimit) {
            selectLimit.addEventListener('change', () => {
                paginaActual = 1;
                renderizarPaginacion();
            });
        }

        renderizarPaginacion();
    }
});

