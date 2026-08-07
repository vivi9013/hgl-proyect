import { initPanelClaves } from '../shared/panel-claves.js';

/**
 * Lógica JavaScript para el módulo de Bajas de Insumos
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Refs de elementos del formulario ──────────────────────────────────────
    const selectArea = document.getElementById('id_area_almacen');
    const inputBuscarInsumo = document.getElementById('buscarInsumo');
    const inputIdInsumo = document.getElementById('id_insumo');
    const sugerenciasDiv = document.getElementById('sugerenciasInsumo');
    const infoStock = document.getElementById('infoStock');
    const stockDisponible = document.getElementById('stockDisponible');
    const inputCantidad = document.getElementById('cantidad');
    const btnGuardar = document.getElementById('btnGuardar');

    const infoAreaAsignada = document.getElementById('infoAreaAsignada');
    const nombreAreaAsignada = document.getElementById('nombreAreaAsignada');

    // ── 1. Alertas SweetAlert2 con sesión de Laravel ──────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');
    const alertaError = document.getElementById('alertaError');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Baja Registrada!',
            text: 'La baja de insumo se ha registrado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'La baja de insumo ha sido cancelada y el stock restaurado.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaError && typeof Swal !== 'undefined') {
        const msg = alertaError.getAttribute('data-message') || 'Ocurrió un error inesperado.';
        Swal.fire({
            title: 'Aviso',
            text: msg,
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // ── 2. Buscador de insumos con autocompletado (AJAX) ────────────────────
    let timeoutBusqueda = null;
    let stockMaximo = 0;

    /**
     * Resetea el campo de insumo seleccionado.
     */
    const resetearInsumo = () => {
        inputIdInsumo.value = '';
        infoStock.style.display = 'none';
        if (infoAreaAsignada) infoAreaAsignada.style.display = 'none';
        if (nombreAreaAsignada) nombreAreaAsignada.textContent = '—';
        stockMaximo = 0;
        if (inputCantidad) {
            inputCantidad.removeAttribute('max');
        }
    };

    /**
     * Realiza la búsqueda de insumos mediante fetch.
     */
    const buscarInsumos = () => {
        const termino = (inputBuscarInsumo?.value || '').trim();
        const idArea = selectArea?.value || '';

        clearTimeout(timeoutBusqueda);

        if (termino.length < 2) {
            sugerenciasDiv.style.display = 'none';
            sugerenciasDiv.innerHTML = '';
            return;
        }

        timeoutBusqueda = setTimeout(() => {
            let url = `/bajas-insumos/buscar-insumos?q=${encodeURIComponent(termino)}`;
            if (idArea) url += `&id_area_almacen=${encodeURIComponent(idArea)}`;

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
                        return;
                    }

                    data.forEach(insumo => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';

                        let badgeColorClass = 'bg-secondary';
                        if (insumo.tipo === 'Medicamento') {
                            badgeColorClass = 'bg-primary';
                        } else if (insumo.tipo === 'Material de curación') {
                            badgeColorClass = 'bg-success';
                        }

                        item.innerHTML = `
                        ${insumo.tipo ? `<span class="badge ${badgeColorClass} me-1" style="font-size: 0.7rem;">${insumo.tipo}</span>` : ''}
                        <span class="clave-badge">${insumo.clave}</span>
                        ${insumo.descripcion}
                        ${idArea ? `<span class="stock-info">Stock: ${insumo.stock}</span>` : ''}
                    `;
                        item.addEventListener('click', () => {
                            inputBuscarInsumo.value = `[${insumo.clave}] ${insumo.descripcion}`;
                            inputIdInsumo.value = insumo.id_insumo;
                            sugerenciasDiv.style.display = 'none';
                            sugerenciasDiv.innerHTML = '';

                            // Mostrar área asignada
                            if (infoAreaAsignada && nombreAreaAsignada) {
                                nombreAreaAsignada.textContent = insumo.area_asignada || 'Sin Área Asignada';
                                infoAreaAsignada.style.display = 'inline-block';
                            }

                            // Mostrar stock
                            if (idArea) {
                                stockMaximo = parseInt(insumo.stock) || 0;
                                stockDisponible.textContent = stockMaximo;
                                infoStock.style.display = 'inline-block';
                                if (inputCantidad) {
                                    inputCantidad.setAttribute('max', stockMaximo);
                                }
                            }
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
    };

    if (inputBuscarInsumo) {
        inputBuscarInsumo.addEventListener('input', () => {
            resetearInsumo();
            buscarInsumos();
        });

        // Cerrar sugerencias al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!inputBuscarInsumo.contains(e.target) && !sugerenciasDiv.contains(e.target)) {
                sugerenciasDiv.style.display = 'none';
            }
        });
    }

    // ── 3. Al cambiar área → resetear insumo y re-buscar si ya hay texto ─────
    if (selectArea) {
        selectArea.addEventListener('change', () => {
            resetearInsumo();
            if (inputBuscarInsumo && inputBuscarInsumo.value.trim().length >= 2) {
                buscarInsumos();
            }
        });
    }

    // ── 4. Validar cantidad contra stock antes de enviar ─────────────────────
    const formBaja = document.getElementById('formBaja');
    if (formBaja) {
        formBaja.addEventListener('submit', function (e) {
            const idInsumo = inputIdInsumo?.value;
            const cantidad = parseInt(inputCantidad?.value || 0);

            if (!idInsumo) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Insumo requerido',
                        text: 'Debe seleccionar un insumo de la lista de sugerencias.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    alert('Debe seleccionar un insumo de la lista de sugerencias.');
                }
                return;
            }

            if (stockMaximo > 0 && cantidad > stockMaximo) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Stock insuficiente',
                        text: `La cantidad ingresada (${cantidad}) excede el stock disponible (${stockMaximo} piezas).`,
                        icon: 'error',
                        confirmButtonText: 'Corregir'
                    });
                } else {
                    alert(`La cantidad excede el stock disponible (${stockMaximo}).`);
                }
            }
        });
    }

    // ── 5. Confirmación SweetAlert2 para alternar estado de bajas ───────────
    const toggleStatusLinks = document.querySelectorAll('.btn-toggle-baja-status');
    toggleStatusLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const url = this.getAttribute('data-url');
            const insumo = this.getAttribute('data-insumo');
            const cantidad = this.getAttribute('data-cantidad');
            const accion = this.getAttribute('data-accion'); // 'cancelar' o 'activar'

            let title, html, icon, confirmColor, confirmText;

            if (accion === 'cancelar') {
                title = '¿Cancelar esta baja?';
                html = `Se cancelará la baja de <strong>${cantidad}</strong> unidad(es) de <strong>"${insumo}"</strong>.<br>El stock será restaurado automáticamente.`;
                icon = 'warning';
                confirmColor = '#d33';
                confirmText = 'Sí, cancelar baja';
            } else {
                title = '¿Reactivar esta baja?';
                html = `Se reactivará la baja de <strong>${cantidad}</strong> unidad(es) de <strong>"${insumo}"</strong>.<br>El stock será descontado del almacén.`;
                icon = 'question';
                confirmColor = '#3085d6';
                confirmText = 'Sí, reactivar baja';
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    html: html,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                const desc = accion === 'cancelar' ? 'cancelar' : 'reactivar';
                if (confirm(`¿Está seguro de que desea ${desc} la baja de "${insumo}"?`)) {
                    window.location.href = url;
                }
            }
        });
    });

    // ── 6. Buscador: filtrado en tiempo real (basado en el buscador de Panel de Control) ─────
    const inputBuscar = document.getElementById('inputBuscar');
    const formBuscar  = document.getElementById('formBuscar');
    if (inputBuscar && formBuscar) {
        inputBuscar.addEventListener('input', function () {
            const query = inputBuscar.value.toLowerCase().trim();

            // Columnas a evaluar (índices y etiquetas): 1: Insumo, 2: Clave, 3: Área, 4: Motivo
            const columnasMap = [
                { index: 1, label: 'Insumo' },
                { index: 2, label: 'Clave' },
                { index: 3, label: 'Área' },
                { index: 4, label: 'Motivo' }
            ];

            const rows = document.querySelectorAll('#tablaAreas tbody tr');
            let matchCount = 0;
            rows.forEach(row => {
                // Ignorar filas de mensaje o no resultados
                if (row.cells.length === 1 && row.cells[0].classList.contains('text-center')) {
                    return;
                }
                if (row.id === 'noLocalResultsRow') {
                    return;
                }

                // Remover badge de match previo de la columna Insumo (cell 1)
                const celdaInsumo = row.cells[1];
                if (celdaInsumo) {
                    const badgeViejo = celdaInsumo.querySelector('[data-match-badge]');
                    if (badgeViejo) {
                        badgeViejo.remove();
                    }
                }

                if (query === '') {
                    row.classList.remove('d-none');
                    return;
                }

                let primeraCoincidenciaCol = null;

                for (const col of columnasMap) {
                    const cell = row.cells[col.index];
                    if (cell && cell.textContent.toLowerCase().includes(query)) {
                        primeraCoincidenciaCol = col;
                        break;
                    }
                }

                if (primeraCoincidenciaCol) {
                    row.classList.remove('d-none');
                    matchCount++;

                    // Insertar badge indicador si la coincidencia ocurrió
                    if (celdaInsumo && primeraCoincidenciaCol.index !== 1) {
                        const badgeMatch = document.createElement('span');
                        badgeMatch.className = 'badge bg-info text-dark ms-1 shadow-sm';
                        badgeMatch.setAttribute('data-match-badge', 'true');
                        badgeMatch.style.fontSize = '0.65rem';
                        badgeMatch.textContent = `en ${primeraCoincidenciaCol.label}`;
                        celdaInsumo.appendChild(badgeMatch);
                    }
                } else {
                    row.classList.add('d-none');
                }
            });

            // Mostrar u ocultar fila de no resultados locales
            let noRecordsRow = document.getElementById('noLocalResultsRow');
            if (query !== '' && matchCount === 0) {
                if (!noRecordsRow) {
                    noRecordsRow = document.createElement('tr');
                    noRecordsRow.id = 'noLocalResultsRow';
                    noRecordsRow.innerHTML = `
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2 d-block"></i>
                            No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en el servidor.
                        </td>
                    `;
                    document.querySelector('#tablaAreas tbody').appendChild(noRecordsRow);
                } else {
                    noRecordsRow.style.display = '';
                    noRecordsRow.querySelector('td').innerHTML = `
                        <i class="fa fa-search fa-2x mb-2 d-block"></i>
                        No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en el servidor.
                    `;
                }
            } else if (noRecordsRow) {
                noRecordsRow.style.display = 'none';
            }
        });
    }

    // ── 7. Fechas: auto-enviar el formulario al cambiar cualquier fecha ─────
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');

    const autoSubmitFecha = (e) => {
        const valInicio = fechaInicio?.value;
        const valFin = fechaFin?.value;

        if (valInicio && valFin && valInicio > valFin) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Rango de fechas inválido',
                    text: 'La fecha de inicio no puede ser posterior a la fecha de fin.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                });
            } else {
                alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
            }

            // Restablecer el campo que causó la inconsistencia
            if (e && e.target) {
                e.target.value = '';
            }
            return;
        }

        if (formBuscar) formBuscar.submit();
    };

    if (fechaInicio) fechaInicio.addEventListener('change', (e) => autoSubmitFecha(e));
    if (fechaFin) fechaFin.addEventListener('change', (e) => autoSubmitFecha(e));

    const filtroArea = document.getElementById('filtro_area');
    if (filtroArea && formBuscar) {
        filtroArea.addEventListener('change', () => formBuscar.submit());
    }

    // ── 8. Panel de claves (doble clic en buscarInsumo) ────────────────────
    initPanelClaves({
        panelId: 'panelClaves',
        inputBuscarId: 'buscarInsumo',
        inputHiddenId: 'id_insumo',
        sugerenciasId: 'sugerenciasInsumo',
        areaInputId: 'id_area_almacen',
        endpoint: '/bajas-insumos/buscar-insumos',
        columnaExtra: 'stock',
        onSelect: (insumo) => {
            const idArea = selectArea?.value || '';
            if (infoAreaAsignada && nombreAreaAsignada) {
                nombreAreaAsignada.textContent = insumo.area_asignada || 'Sin Área Asignada';
                infoAreaAsignada.style.display = 'inline-block';
            }
            if (insumo.stock !== undefined && idArea) {
                stockMaximo = parseInt(insumo.stock) || 0;
                if (stockDisponible) stockDisponible.textContent = stockMaximo;
                if (infoStock) infoStock.style.display = 'inline-block';
                if (inputCantidad) inputCantidad.setAttribute('max', stockMaximo);
            }
        }
    });

    // Cerrar panel al cerrar el modal + limpiar formulario
    let esReabiertoPorErrorBaja = document.querySelector('#modalAltaBaja .is-invalid') !== null;

    const modalAltaBaja = document.getElementById('modalAltaBaja');
    if (modalAltaBaja) {
        modalAltaBaja.addEventListener('hidden.bs.modal', () => {
            // Cerrar panel de claves
            const panelClaves = document.getElementById('panelClaves');
            if (panelClaves) panelClaves.style.display = 'none';

            if (esReabiertoPorErrorBaja) {
                esReabiertoPorErrorBaja = false;
                return;
            }

            // Limpiar todos los campos del formulario
            if (selectArea) selectArea.value = '';
            if (inputBuscarInsumo) inputBuscarInsumo.value = '';
            if (inputIdInsumo) inputIdInsumo.value = '';
            if (inputCantidad) { inputCantidad.value = ''; inputCantidad.removeAttribute('max'); }
            const motivoEl = document.getElementById('motivo');
            if (motivoEl) motivoEl.value = '';
            const doctorNombreEl = document.getElementById('doctor_nombre');
            if (doctorNombreEl) doctorNombreEl.value = '';
            const doctorEspecialidadEl = document.getElementById('doctor_especialidad');
            if (doctorEspecialidadEl) doctorEspecialidadEl.value = '';

            // Ocultar info de stock y sugerencias
            if (infoStock) infoStock.style.display = 'none';
            if (sugerenciasDiv) sugerenciasDiv.style.display = 'none';
            stockMaximo = 0;

            // Quitar clases de validación si las hay
            const formBajaEl = document.getElementById('formBaja');
            if (formBajaEl) {
                formBajaEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            }
        });
    }

});
