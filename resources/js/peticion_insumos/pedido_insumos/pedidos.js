document.addEventListener('DOMContentLoaded', function () {

    // ── Referencias DOM ──
    const containerTabla   = document.getElementById('contenedor-tabla-pedidos');
    const inputBuscar      = document.getElementById('inputBuscarPedido');
    const selectStatus     = document.getElementById('selectFiltroStatus');
    const inputFechaInicio = document.getElementById('inputFechaInicio');
    const inputFechaFin    = document.getElementById('inputFechaFin');
    const btnResetFiltros  = document.getElementById('btnResetFiltros');

    // Modal Crear
    const selectArea       = document.getElementById('select_area_abastecimiento');
    const selectSubarea    = document.getElementById('select_subarea_abastecimiento');
    const selectAlmacen    = document.getElementById('select_area_almacen');
    const selectPlantilla  = document.getElementById('select_plantilla_pedido');
    const btnCargarPlantilla = document.getElementById('btnCargarPlantilla');
    
    const inputBuscarInsumo = document.getElementById('inputBuscarInsumo');
    const inputCantidadInsumo = document.getElementById('inputCantidadInsumo');
    const dropdownResult    = document.getElementById('dropdownInsumosResult');
    const btnAgregarInsumo  = document.getElementById('btnAgregarInsumoLista');
    const tbodyPedido       = document.getElementById('tbodyPedidoInsumos');
    const alertError        = document.getElementById('alertCrearPedidoError');

    const btnGuardarBorrador = document.getElementById('btnGuardarBorrador');
    const btnEnviarPedido    = document.getElementById('btnEnviarPedido');

    // Modal Detalle
    const modalDetalleEl      = document.getElementById('modalDetallePedido');
    const modalDetalle        = modalDetalleEl ? new bootstrap.Modal(modalDetalleEl) : null;
    const lblIdPedidoDetalle  = document.getElementById('lblIdPedidoDetalle');
    const lblAreaDetalle      = document.getElementById('lblAreaDetalle');
    const lblSubareaAlmacen   = document.getElementById('lblSubareaAlmacenDetalle');
    const lblFechaUsuario     = document.getElementById('lblFechaUsuarioDetalle');
    const lblStatusDetalle    = document.getElementById('lblStatusDetalle');
    const tbodyDetalles       = document.getElementById('tbodyDetallesConsultar');
    const btnImprimirModal    = document.getElementById('btnImprimirModalDetalle');

    // Estado local de la lista de insumos en el modal de creación
    let listaInsumos = [];
    let insumoSeleccionadoTemp = null;
    let debounceTimerTabla  = null;
    let debounceTimerInsumo = null;

    // ── Utilidad: Escapado HTML para prevenir XSS ──
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // ── 1. Cargar Tabla Vía AJAX ──
    function cargarTabla(page = 1) {
        if (!containerTabla) return;

        const params = new URLSearchParams({
            page: page,
            buscar: inputBuscar ? inputBuscar.value.trim() : '',
            status: selectStatus ? selectStatus.value : '',
            fecha_inicio: inputFechaInicio ? inputFechaInicio.value : '',
            fecha_fin: inputFechaFin ? inputFechaFin.value : ''
        });

        fetch(`/peticion-insumos/pedidos?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            containerTabla.innerHTML = data.html ?? data;
            actualizarPaginador();
        })
        .catch(err => console.error('Error al cargar la tabla:', err));
    }

    function actualizarPaginador() {
        const pageData = document.getElementById('ajax-pagination-data');
        if (!pageData) return;

        const current = parseInt(pageData.dataset.currentPage);
        const last    = parseInt(pageData.dataset.lastPage);
        const total   = parseInt(pageData.dataset.total);
        const from    = parseInt(pageData.dataset.from);
        const to      = parseInt(pageData.dataset.to);

        document.getElementById('pag-desde').textContent = from;
        document.getElementById('pag-hasta').textContent = to;
        document.getElementById('pag-total').textContent = total;

        if (window.renderPaginacion) {
            window.renderPaginacion('paginador-pedidos', current, last, (targetPage) => {
                cargarTabla(targetPage);
            });
        }
    }

    // Inicializar paginador al cargar la página
    actualizarPaginador();

    // Eventos de Filtros Reactivos
    if (inputBuscar) {
        inputBuscar.addEventListener('input', () => {
            clearTimeout(debounceTimerTabla);
            debounceTimerTabla = setTimeout(() => cargarTabla(1), 300);
        });
    }

    if (selectStatus) selectStatus.addEventListener('change', () => cargarTabla(1));
    if (inputFechaInicio) inputFechaInicio.addEventListener('change', () => cargarTabla(1));
    if (inputFechaFin) inputFechaFin.addEventListener('change', () => cargarTabla(1));

    if (btnResetFiltros) {
        btnResetFiltros.addEventListener('click', () => {
            if (inputBuscar) inputBuscar.value = '';
            if (selectStatus) selectStatus.value = '';
            if (inputFechaInicio) inputFechaInicio.value = '';
            if (inputFechaFin) inputFechaFin.value = '';
            cargarTabla(1);
        });
    }

    // ── 2. Cascadas de Combos (Área -> Subárea) ──
    if (selectArea) {
        selectArea.addEventListener('change', function () {
            const idArea = this.value;
            selectSubarea.innerHTML = '<option value="">Cargando subáreas...</option>';

            fetch(`/peticion-insumos/pedidos/subareas?id_area_abastecimiento=${idArea}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">-- Todas / General --</option>';
                data.forEach(sub => {
                    options += `<option value="${sub.id_subarea_abastecimiento}">${sub.nombre}</option>`;
                });
                selectSubarea.innerHTML = options;
            })
            .catch(() => {
                selectSubarea.innerHTML = '<option value="">-- Todas / General --</option>';
            });
        });
    }

    // ── 2b. Cambio de Almacén → Refrescar stock/fondo_fijo de insumos ya agregados ──
    if (selectAlmacen) {
        selectAlmacen.addEventListener('change', function () {
            const idAlmacen = this.value;
            if (!idAlmacen || listaInsumos.length === 0) return;

            const peticiones = listaInsumos.map((item) =>
                fetch(`/peticion-insumos/pedidos/autocompletar-insumo?term=${encodeURIComponent(item.clave)}&id_area_almacen=${idAlmacen}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    // Buscar el insumo exacto por id_insumo en los resultados
                    const encontrado = data.find(d => d.id_insumo === item.id_insumo);
                    if (encontrado) {
                        item.existencia = encontrado.existencia;
                        item.fondo_fijo = encontrado.fondo_fijo;
                    }
                })
                .catch(() => { /* ignorar fallos individuales */ })
            );

            Promise.all(peticiones).then(() => renderTablaModal());
        });
    }

    // ── 3. Autocompletado Reactivo de Insumos ──
    if (inputBuscarInsumo) {
        inputBuscarInsumo.addEventListener('input', function () {
            const term = this.value.trim();
            const idAlmacen = selectAlmacen ? selectAlmacen.value : null;

            clearTimeout(debounceTimerInsumo);

            if (term.length < 2) {
                dropdownResult.style.display = 'none';
                dropdownResult.innerHTML = '';
                insumoSeleccionadoTemp = null;
                return;
            }

            debounceTimerInsumo = setTimeout(() => {
                fetch(`/peticion-insumos/pedidos/autocompletar-insumo?term=${encodeURIComponent(term)}&id_area_almacen=${idAlmacen}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    // Si el usuario ya cambió el texto mientras la petición estaba en curso, ignorar esta respuesta obsoleta
                    if (inputBuscarInsumo.value.trim() !== term) {
                        return;
                    }
                    if (data.length === 0) {
                        dropdownResult.innerHTML = '<div class="dropdown-item disabled text-muted">No se encontraron insumos</div>';
                    } else {
                        let itemsHtml = '';
                        data.forEach(item => {
                            itemsHtml += `
                                <div class="dropdown-item item-insumo-option" 
                                     data-id="${escapeHtml(item.id_insumo)}" 
                                     data-clave="${escapeHtml(item.clave)}" 
                                     data-desc="${escapeHtml(item.descripcion)}"
                                     data-existencia="${escapeHtml(item.existencia)}"
                                     data-fondo="${escapeHtml(item.fondo_fijo)}">
                                    <div class="fw-bold text-dark">[${escapeHtml(item.clave)}] ${escapeHtml(item.descripcion)}</div>
                                    <div class="small text-muted">Stock: ${escapeHtml(item.existencia)} | Fondo Fijo: ${escapeHtml(item.fondo_fijo)}</div>
                                </div>
                            `;
                        });
                        dropdownResult.innerHTML = itemsHtml;
                    }
                    dropdownResult.style.display = 'block';
                });
            }, 250);
        });
    }

    // Selección de opción del autocomplete
    if (dropdownResult) {
        dropdownResult.addEventListener('click', function (e) {
            const option = e.target.closest('.item-insumo-option');
            if (option) {
                insumoSeleccionadoTemp = {
                    id_insumo: parseInt(option.dataset.id),
                    clave: option.dataset.clave,
                    descripcion: option.dataset.desc,
                    existencia: parseInt(option.dataset.existencia),
                    fondo_fijo: parseInt(option.dataset.fondo)
                };
                inputBuscarInsumo.value = `[${insumoSeleccionadoTemp.clave}] ${insumoSeleccionadoTemp.descripcion}`;
                dropdownResult.style.display = 'none';
            }
        });
    }

    // Ocultar dropdown al hacer click fuera
    document.addEventListener('click', function (e) {
        if (dropdownResult && !e.target.closest('#inputBuscarInsumo') && !e.target.closest('#dropdownInsumosResult')) {
            dropdownResult.style.display = 'none';
        }
    });

    // ── 4. Agregar Insumo a la Lista ──
    if (btnAgregarInsumo) {
        btnAgregarInsumo.addEventListener('click', function () {
            if (!insumoSeleccionadoTemp) {
                mostrarAlertaError('Por favor seleccione un insumo válido de la lista desplegable.');
                return;
            }

            const cantidad = parseInt(inputCantidadInsumo.value) || 1;
            if (cantidad < 1) {
                mostrarAlertaError('La cantidad solicitada debe ser mayor a 0.');
                return;
            }

            // Verificar si ya existe en la lista local
            const indexExistente = listaInsumos.findIndex(i => i.id_insumo === insumoSeleccionadoTemp.id_insumo);
            if (indexExistente !== -1) {
                listaInsumos[indexExistente].cantidad += cantidad;
            } else {
                listaInsumos.push({
                    id_insumo: insumoSeleccionadoTemp.id_insumo,
                    clave: insumoSeleccionadoTemp.clave,
                    descripcion: insumoSeleccionadoTemp.descripcion,
                    existencia: insumoSeleccionadoTemp.existencia,
                    fondo_fijo: insumoSeleccionadoTemp.fondo_fijo,
                    cantidad: cantidad
                });
            }

            // Aviso no bloqueante si la cantidad supera el fondo fijo
            const fondoFijoRef = insumoSeleccionadoTemp ? insumoSeleccionadoTemp.fondo_fijo : 0;
            const cantidadFinal = indexExistente !== -1 ? listaInsumos[indexExistente].cantidad : cantidad;
            if (fondoFijoRef > 0 && cantidadFinal > fondoFijoRef) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cantidad excede el Fondo Fijo',
                    text: `La cantidad solicitada (${cantidadFinal}) supera el fondo fijo establecido (${fondoFijoRef}). Puede continuar, pero verifique con el área de almacén.`,
                    confirmButtonColor: '#000000',
                    timer: 4000,
                    timerProgressBar: true
                });
            }

            // Reset campos
            inputBuscarInsumo.value = '';
            inputCantidadInsumo.value = 1;
            insumoSeleccionadoTemp = null;
            ocultarAlertaError();

            renderTablaModal();
        });
    }

    // ── 5. Cargar desde Plantilla ──
    if (btnCargarPlantilla) {
        btnCargarPlantilla.addEventListener('click', function () {
            const idPlantilla = selectPlantilla ? selectPlantilla.value : '';
            if (!idPlantilla) {
                mostrarAlertaError('Seleccione una plantilla de pedido.');
                return;
            }

            fetch(`/peticion-insumos/pedidos/plantilla/${idPlantilla}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.id_area_abastecimiento && selectArea) {
                    selectArea.value = data.id_area_abastecimiento;
                    selectArea.dispatchEvent(new Event('change'));
                }
                if (data.id_area_almacen && selectAlmacen) {
                    selectAlmacen.value = data.id_area_almacen;
                }

                if (data.insumos && data.insumos.length > 0) {
                    listaInsumos = data.insumos.map(item => ({
                        id_insumo: item.id_insumo,
                        clave: item.clave,
                        descripcion: item.descripcion,
                        existencia: item.existencia,
                        fondo_fijo: item.fondo_fijo,
                        cantidad: item.cantidad
                    }));
                    renderTablaModal();
                    ocultarAlertaError();
                    Swal.fire({
                        icon: 'success',
                        title: 'Plantilla Cargada',
                        text: `Se importaron ${listaInsumos.length} insumos de la plantilla.`,
                        timer: 1800,
                        showConfirmButton: false
                    });
                }
            })
            .catch(err => {
                mostrarAlertaError('Error al obtener los insumos de la plantilla.');
            });
        });
    }

    // Renderizar la tabla dentro del modal de creación
    function renderTablaModal() {
        if (!tbodyPedido) return;

        if (listaInsumos.length === 0) {
            tbodyPedido.innerHTML = `
                <tr id="trEmptyPedido">
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-basket fs-3 d-block mb-1 text-secondary"></i>
                        No hay insumos agregados a esta solicitud de pedido.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        listaInsumos.forEach((item, index) => {
            html += `
                <tr>
                    <td class="font-monospace fw-bold">[${escapeHtml(item.clave)}]</td>
                    <td>${escapeHtml(item.descripcion)}</td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${escapeHtml(item.existencia)}</span></td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${escapeHtml(item.fondo_fijo)}</span></td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center input-item-cant mx-auto" 
                               style="width: 80px;" data-index="${index}" min="1" value="${escapeHtml(item.cantidad)}">
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remover-item" data-index="${index}" title="Quitar insumo">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbodyPedido.innerHTML = html;
    }

    // Event Delegation para modificar cantidad o remover insumo
    if (tbodyPedido) {
        tbodyPedido.addEventListener('input', function (e) {
            if (e.target.classList.contains('input-item-cant')) {
                const idx = parseInt(e.target.dataset.index);
                // Bug 8: forzar mínimo 1, rechazar negativos y cero
                let val = parseInt(e.target.value);
                if (isNaN(val) || val < 1) {
                    val = 1;
                    e.target.value = 1;
                }
                if (listaInsumos[idx]) {
                    listaInsumos[idx].cantidad = val;
                    // Bug 5: aviso no bloqueante si supera fondo_fijo en edición inline
                    const ff = listaInsumos[idx].fondo_fijo;
                    if (ff > 0 && val > ff) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cantidad excede el Fondo Fijo',
                            text: `La cantidad (${val}) supera el fondo fijo (${ff}). Puede continuar.`,
                            confirmButtonColor: '#000000',
                            timer: 3500,
                            timerProgressBar: true
                        });
                    }
                }
            }
        });

        tbodyPedido.addEventListener('click', function (e) {
            const btnRemove = e.target.closest('.btn-remover-item');
            if (btnRemove) {
                const idx = parseInt(btnRemove.dataset.index);
                listaInsumos.splice(idx, 1);
                renderTablaModal();
            }
        });
    }

    function mostrarAlertaError(msg) {
        if (alertError) {
            alertError.textContent = msg;
            alertError.classList.remove('d-none');
        }
    }

    function ocultarAlertaError() {
        if (alertError) {
            alertError.classList.add('d-none');
        }
    }

    // ── 6. Guardar Pedido (Borrador o Enviado a CENDIS) ──
    function procesarGuardado(statusDestino) {
        const idArea = selectArea ? selectArea.value : '';
        const idAlmacen = selectAlmacen ? selectAlmacen.value : '';

        if (!idArea) {
            mostrarAlertaError('Debe seleccionar un área de abastecimiento solicitante.');
            return;
        }

        if (!idAlmacen) {
            mostrarAlertaError('Debe seleccionar un área de almacén.');
            return;
        }

        if (listaInsumos.length === 0) {
            mostrarAlertaError('Debe agregar al menos un insumo a la lista del pedido.');
            return;
        }

        const payload = {
            id_area_abastecimiento: idArea,
            id_subarea_abastecimiento: selectSubarea ? selectSubarea.value : null,
            id_area_almacen: idAlmacen,
            status: statusDestino,
            insumos: listaInsumos.map(i => ({
                id_insumo: i.id_insumo,
                cve_insumo: i.clave,
                cantidad: i.cantidad
            }))
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Deshabilitar el botón de envío para prevenir doble clic
        const esEnvio = statusDestino === 'terminado';
        if (esEnvio && btnEnviarPedido) {
            btnEnviarPedido.disabled = true;
            btnEnviarPedido.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Enviando...';
        }

        fetch('/peticion-insumos/pedidos/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal
                const modalEl = document.getElementById('modalCrearPedido');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                // Resetear estado
                listaInsumos = [];
                renderTablaModal();
                ocultarAlertaError();

                Swal.fire({
                    icon: 'success',
                    title: 'Pedido Registrado',
                    text: data.message,
                    confirmButtonColor: '#000000'
                });

                cargarTabla(1);
            } else {
                mostrarAlertaError(data.message || 'Ocurrió un error al guardar el pedido.');
            }
        })
        .catch(err => {
            mostrarAlertaError('Error de red al guardar el pedido.');
        })
        .finally(() => {
            // Restaurar el botón de envío en cualquier caso
            if (esEnvio && btnEnviarPedido) {
                btnEnviarPedido.disabled = false;
                btnEnviarPedido.innerHTML = '<i class="bi bi-send"></i> Enviar Pedido';
            }
        });
    }

    if (btnGuardarBorrador) {
        btnGuardarBorrador.addEventListener('click', () => procesarGuardado('borrador'));
    }

    if (btnEnviarPedido) {
        btnEnviarPedido.addEventListener('click', () => {
            Swal.fire({
                title: '¿Finalizar y Enviar a CENDIS?',
                text: 'El pedido pasará al inventario central de CENDIS para su surtimiento.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Enviar Pedido',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarGuardado('terminado');
                }
            });
        });
    }

    // ── 7. Consultar Detalle de Pedido (Ver Detalle) ──
    document.addEventListener('click', function (e) {
        const btnVer = e.target.closest('.btn-ver-detalle');
        if (btnVer && modalDetalle) {
            const id = btnVer.dataset.id;
            
            lblIdPedidoDetalle.textContent = `#${id}`;
            lblAreaDetalle.textContent = 'Cargando...';
            lblSubareaAlmacen.textContent = '-';
            lblFechaUsuario.textContent = '-';
            lblStatusDetalle.innerHTML = '-';
            tbodyDetalles.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted">Cargando detalles...</td></tr>';
            
            if (btnImprimirModal) btnImprimirModal.href = `/peticion-insumos/pedidos/imprimir/${id}`;

            modalDetalle.show();

            fetch(`/peticion-insumos/pedidos/detalle/${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(p => {
                lblAreaDetalle.textContent = p.area_abastecimiento ? p.area_abastecimiento.nombre : 'N/A';
                lblSubareaAlmacen.textContent = `${p.subarea_abastecimiento ? p.subarea_abastecimiento.nombre : 'Sin subárea'} | Almacén: ${p.area_almacen ? p.area_almacen.nombre : 'General'}`;
                
                const solicitante = p.usuario && p.usuario.persona ? `${p.usuario.persona.nombre} ${p.usuario.persona.ap_paterno}` : 'Sistema';
                lblFechaUsuario.textContent = `${p.fecha_registro} ${p.hora_registro || ''} (por: ${solicitante})`;

                let badgeHtml = `<span class="badge bg-dark">${p.status}</span>`;
                if (p.status === 'terminado') badgeHtml = `<span class="badge bg-warning text-dark">Enviado a CENDIS</span>`;
                if (p.status === 'Aceptado') badgeHtml = `<span class="badge bg-success">Surtido y Aceptado</span>`;
                if (p.status === 'cancelado') badgeHtml = `<span class="badge bg-danger">Cancelado</span>`;
                if (p.status === 'borrador') badgeHtml = `<span class="badge bg-secondary">Borrador</span>`;
                lblStatusDetalle.innerHTML = badgeHtml;

                if (p.detalles && p.detalles.length > 0) {
                    let rows = '';
                    p.detalles.forEach(d => {
                        rows += `
                            <tr>
                                <td class="font-monospace fw-bold">[${d.cve_insumo || (d.insumo ? d.insumo.clave : 'N/A')}]</td>
                                <td>${d.insumo ? d.insumo.descripcion : 'N/A'}</td>
                                <td class="text-center fw-bold">${d.cantidad}</td>
                                <td class="text-center text-success fw-bold">${d.surtido}</td>
                                <td class="text-center text-danger fw-bold">${d.faltante}</td>
                            </tr>
                        `;
                    });
                    tbodyDetalles.innerHTML = rows;
                } else {
                    tbodyDetalles.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted">No contiene insumos registrados.</td></tr>';
                }
            })
            .catch(err => {
                tbodyDetalles.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-danger">Error al obtener el detalle.</td></tr>';
            });
        }

        // ── 8. Cancelar Pedido ──
        const btnCancel = e.target.closest('.btn-cancelar-pedido');
        if (btnCancel) {
            const id = btnCancel.dataset.id;

            Swal.fire({
                title: `¿Cancelar Pedido #${id}?`,
                text: 'Esta acción cancelará la solicitud enviada a CENDIS.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Cancelar Pedido',
                cancelButtonText: 'Volver'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch(`/peticion-insumos/pedidos/cancelar/${id}`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pedido Cancelado',
                                text: data.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                            cargarTabla(1);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    });
                }
            });
        }
    });

});
