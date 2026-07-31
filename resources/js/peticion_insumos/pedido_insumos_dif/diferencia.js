document.addEventListener('DOMContentLoaded', function () {

    // ── Referencias DOM ──
    const selectArea        = document.getElementById('select_area_abastecimiento_dif');
    const selectSubarea     = document.getElementById('select_subarea_abastecimiento_dif');
    const selectAlmacen     = document.getElementById('select_area_almacen_dif');
    const checkSoloFaltantes= document.getElementById('checkSoloFaltantes');
    const btnCalcular       = document.getElementById('btnCalcularDiferencias');

    const tbodyDiferencias  = document.getElementById('tbodyInsumosDiferencia');
    const checkSelectAll    = document.getElementById('checkSelectAllDiferencia');
    const badgeTotalItems   = document.getElementById('badgeTotalItems');
    const alertError        = document.getElementById('alertDiferenciaError');

    const btnBorrador       = document.getElementById('btnGuardarBorradorDif');
    const btnEnviar         = document.getElementById('btnGenerarPedidoDif');

    const containerHistorial= document.getElementById('contenedor-tabla-historial');

    // Estado local de los insumos calculados
    let listadoInsumos = [];

    // ── 1. Cascadas de Combos (Área -> Subárea) ──
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

    // ── 2. Calcular Diferencias por AJAX ──
    function obtenerDiferencias() {
        const idArea    = selectArea ? selectArea.value : '';
        const idSubarea = selectSubarea ? selectSubarea.value : '';
        const idAlmacen = selectAlmacen ? selectAlmacen.value : '';
        const soloFalt  = checkSoloFaltantes ? checkSoloFaltantes.checked : true;

        if (!idArea) {
            mostrarAlertaError('Debe seleccionar un área de abastecimiento solicitante.');
            return;
        }

        ocultarAlertaError();
        tbodyDiferencias.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-dark me-2"></div>Calculando faltantes...</td></tr>';

        const params = new URLSearchParams({
            id_area_abastecimiento: idArea,
            id_subarea_abastecimiento: idSubarea,
            id_area_almacen: idAlmacen,
            solo_faltantes: soloFalt
        });

        fetch(`/peticion-insumos/pedidos-diferencia/calcular?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                listadoInsumos = data.insumos || [];
                renderTablaDiferencias();
            } else {
                mostrarAlertaError(data.message || 'Error al calcular diferencias.');
            }
        })
        .catch(err => {
            mostrarAlertaError('Error de red al consultar el catálogo de diferencias.');
        });
    }

    if (btnCalcular) {
        btnCalcular.addEventListener('click', obtenerDiferencias);
    }

    // ── 3. Renderizar Tabla de Diferencias ──
    function renderTablaDiferencias() {
        if (!tbodyDiferencias) return;

        if (listadoInsumos.length === 0) {
            tbodyDiferencias.innerHTML = `
                <tr id="trEmptyDiferencia">
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle fs-3 d-block mb-1 text-success"></i>
                        No se encontraron insumos con faltante para los filtros seleccionados.
                    </td>
                </tr>
            `;
            actualizarContadorItems();
            return;
        }

        let html = '';
        listadoInsumos.forEach((item, index) => {
            const isChecked = item.seleccionado ? 'checked' : '';
            html += `
                <tr>
                    <td class="ps-3 text-center">
                        <input type="checkbox" class="form-check-input check-insumo-dif" data-index="${index}" ${isChecked}>
                    </td>
                    <td class="font-monospace fw-bold">[${item.clave}]</td>
                    <td>${item.descripcion}</td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${item.stock}</span></td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${item.fondo_fijo}</span></td>
                    <td class="text-center">
                        <span class="badge badge-deficit px-2 py-1">${item.diferencia}</span>
                    </td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center input-cant-dif mx-auto" 
                               style="width: 90px;" data-index="${index}" min="1" value="${item.cantidad_pedir}">
                    </td>
                </tr>
            `;
        });

        tbodyDiferencias.innerHTML = html;
        actualizarContadorItems();
    }

    function actualizarContadorItems() {
        const seleccionados = listadoInsumos.filter(i => i.seleccionado);
        if (badgeTotalItems) {
            badgeTotalItems.textContent = `${seleccionados.length} / ${listadoInsumos.length} seleccionados`;
        }
    }

    // ── Checkbox Seleccionar Todos ──
    if (checkSelectAll) {
        checkSelectAll.addEventListener('change', function () {
            const isChecked = this.checked;
            listadoInsumos.forEach(item => item.seleccionado = isChecked);
            
            document.querySelectorAll('.check-insumo-dif').forEach(chk => chk.checked = isChecked);
            actualizarContadorItems();
        });
    }

    // Event Delegation para checkboxes e inputs de cantidad
    if (tbodyDiferencias) {
        tbodyDiferencias.addEventListener('change', function (e) {
            if (e.target.classList.contains('check-insumo-dif')) {
                const idx = parseInt(e.target.dataset.index);
                if (listadoInsumos[idx]) {
                    listadoInsumos[idx].seleccionado = e.target.checked;
                }
                actualizarContadorItems();
            }
        });

        tbodyDiferencias.addEventListener('input', function (e) {
            if (e.target.classList.contains('input-cant-dif')) {
                const idx = parseInt(e.target.dataset.index);
                const val = parseInt(e.target.value) || 1;
                if (listadoInsumos[idx]) {
                    listadoInsumos[idx].cantidad_pedir = val;
                }
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

    // ── 4. Guardar Pedido por Diferencia ──
    function procesarGuardarPedido(statusDestino) {
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

        const insumosSeleccionados = listadoInsumos.filter(i => i.seleccionado);

        if (insumosSeleccionados.length === 0) {
            mostrarAlertaError('Debe seleccionar al menos un insumo de la lista para generar el pedido.');
            return;
        }

        const payload = {
            id_area_abastecimiento: idArea,
            id_subarea_abastecimiento: selectSubarea ? selectSubarea.value : null,
            id_area_almacen: idAlmacen,
            status: statusDestino,
            insumos: insumosSeleccionados.map(i => ({
                id_insumo: i.id_insumo,
                cve_insumo: i.clave,
                stock: i.stock,
                fondo_fijo: i.fondo_fijo,
                cantidad: i.cantidad_pedir
            }))
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/peticion-insumos/pedidos-diferencia/guardar', {
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
                listadoInsumos = [];
                renderTablaDiferencias();
                ocultarAlertaError();

                Swal.fire({
                    icon: 'success',
                    title: 'Pedido Generado',
                    text: data.message,
                    confirmButtonColor: '#000000'
                });

                cargarHistorial(1);
            } else {
                mostrarAlertaError(data.message || 'Ocurrió un error al generar el pedido.');
            }
        })
        .catch(err => {
            mostrarAlertaError('Error de red al procesar el pedido por diferencia.');
        });
    }

    if (btnBorrador) {
        btnBorrador.addEventListener('click', () => procesarGuardarPedido('borrador'));
    }

    if (btnEnviar) {
        btnEnviar.addEventListener('click', () => {
            const count = listadoInsumos.filter(i => i.seleccionado).length;
            if (count === 0) {
                mostrarAlertaError('Debe marcar al menos un insumo para generar el pedido.');
                return;
            }

            Swal.fire({
                title: '¿Generar y Enviar a CENDIS?',
                text: `Se creará una solicitud de pedido por diferencia con ${count} insumos seleccionados.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Generar y Enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarGuardarPedido('terminado');
                }
            });
        });
    }

    // ── 5. Historial AJAX y Paginación ──
    function cargarHistorial(page = 1) {
        if (!containerHistorial) return;

        fetch(`/peticion-insumos/pedidos-diferencia?page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            containerHistorial.innerHTML = data.html ?? data;
            actualizarPaginadorHistorial();
        });
    }

    function actualizarPaginadorHistorial() {
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
            window.renderPaginacion('paginador-historial', current, last, (targetPage) => {
                cargarHistorial(targetPage);
            });
        }
    }

    actualizarPaginadorHistorial();

});
