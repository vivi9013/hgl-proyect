import { initPanelClaves } from '../shared/panel-claves.js';
import { clasificarStock } from '../../shared/stock-niveles.js';

/**
 * Lógica JavaScript para Insumos por Área
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

    // --- 1. REFERENCIAS DE ELEMENTOS (MODAL DE ASIGNACIÓN) ---
    const selectArea = document.getElementById('area_almacen_select');
    const inputBuscarInsumo = document.getElementById('buscarInsumo');
    const inputIdInsumo = document.getElementById('id_insumo');
    const sugerenciasDiv = document.getElementById('sugerenciasInsumo');
    const inputFondoFijo = document.getElementById('fondo_fijo_insumo');
    const inputStockInicial = document.getElementById('stock_inicial_insumo');
    const inputDescInsumo = document.getElementById('descripcion_insumo');
    const inputTipoInsumo = document.getElementById('tipo');
    const btnGuardarInfo = document.getElementById('btnGuardarInfo');
    const formAsignar = document.getElementById('formAsignarInsumo');

    // Panel de claves flotante (dentro del modal)
    const panelClaves = document.getElementById('panelClaves');
    const filasClaves = document.getElementById('filasClaves');
    const panelClavesLoading = document.getElementById('panelClavesLoading');
    const panelClavesVacio = document.getElementById('panelClavesVacio');
    const filtroPanelClaves = document.getElementById('filtroPanelClaves');
    const cerrarPanelClaves = document.getElementById('cerrarPanelClaves');

    let timeoutBusqueda = null;
    let clavesCache = [];

    // --- 2. ALERTAS CON SWEETALERT2 ---
    const mostrarAlertaSesion = (idElemento) => {
        const el = document.getElementById(idElemento);
        if (el && typeof Swal !== 'undefined') {
            const msg = el.getAttribute('data-message') || 'Operación realizada correctamente.';
            Swal.fire({
                title: '¡Operación Satisfactoria!',
                text: msg,
                icon: 'success',
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Aceptar'
            });
        }
    };

    mostrarAlertaSesion('alertaExitog');
    mostrarAlertaSesion('alertaExito');

    const mostrarToast = (icon, title) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                icon: icon,
                title: title
            });
        }
    };

    // --- 3. AUTOCOMPLETADO DE INSUMOS ---
    const resetearFormInsumo = () => {
        if (inputIdInsumo) inputIdInsumo.value = '';
        if (inputDescInsumo) inputDescInsumo.value = '';
        if (inputTipoInsumo) inputTipoInsumo.value = '';
        validarFormAsignar();
    };

    const buscarInsumos = () => {
        const query = (inputBuscarInsumo?.value || '').trim();
        clearTimeout(timeoutBusqueda);

        if (query.length < 2) {
            if (sugerenciasDiv) {
                sugerenciasDiv.style.display = 'none';
                sugerenciasDiv.innerHTML = '';
            }
            return;
        }

        timeoutBusqueda = setTimeout(() => {
            fetch(`/insumos-area/buscar-insumos?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Error al consultar');
                return res.json();
            })
            .then(data => {
                if ((inputBuscarInsumo?.value || '').trim() !== query) {
                    return;
                }
                if (sugerenciasDiv) {
                    sugerenciasDiv.innerHTML = '';
                    if (!data || data.length === 0) {
                        sugerenciasDiv.style.display = 'none';
                        return;
                    }

                    data.forEach(insumo => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action py-2';
                        btn.innerHTML = `
                            <span class="badge bg-secondary me-2">${insumo.clave}</span>
                            <span>${insumo.descripcion}</span>
                            <span class="badge bg-light text-dark border float-end">${insumo.tipo}</span>
                        `;
                        btn.addEventListener('click', () => {
                            seleccionarInsumo(insumo.id_insumo, insumo.clave, insumo.descripcion, insumo.tipo);
                        });
                        sugerenciasDiv.appendChild(btn);
                    });
                    sugerenciasDiv.style.display = 'block';
                }
            })
            .catch(err => {
                console.error(err);
            });
        }, 300);
    };

    const seleccionarInsumo = (id, clave, descripcion, tipo) => {
        if (inputIdInsumo) inputIdInsumo.value = id;
        if (inputBuscarInsumo) inputBuscarInsumo.value = `[${clave}] ${descripcion}`;
        if (inputDescInsumo) inputDescInsumo.value = descripcion;
        if (inputTipoInsumo) inputTipoInsumo.value = tipo;

        if (sugerenciasDiv) {
            sugerenciasDiv.style.display = 'none';
            sugerenciasDiv.innerHTML = '';
        }

        if (panelClaves) panelClaves.style.display = 'none';

        if (inputFondoFijo) inputFondoFijo.focus();
        validarFormAsignar();
    };

    if (inputBuscarInsumo) {
        inputBuscarInsumo.addEventListener('input', () => {
            resetearFormInsumo();
            buscarInsumos();
        });

        // Cerrar sugerencias al dar clic fuera
        document.addEventListener('click', (e) => {
            if (!inputBuscarInsumo.contains(e.target) && sugerenciasDiv && !sugerenciasDiv.contains(e.target)) {
                sugerenciasDiv.style.display = 'none';
            }
        });
    }

    // --- 4. PANEL DE ACCESO RÁPIDO A CLAVES ---
    initPanelClaves({
        panelId: 'panelClaves',
        inputBuscarId: 'buscarInsumo',
        inputHiddenId: 'id_insumo',
        sugerenciasId: 'sugerenciasInsumo',
        areaInputId: 'area_almacen_select',
        endpoint: '/insumos-area/buscar-insumos',
        columnaExtra: 'tipo',
        onSelect: (insumo) => {
            seleccionarInsumo(insumo.id_insumo, insumo.clave, insumo.descripcion, insumo.tipo);
        }
    });

    // --- 5. VALIDACIÓN DEL FORMULARIO DE ASIGNACIÓN ---
    const validarFormAsignar = () => {
        const area = selectArea?.value || '';
        const idInsumo = inputIdInsumo?.value || '';
        const ff = parseInt(inputFondoFijo?.value) || 0;
        const stock = parseInt(inputStockInicial?.value);

        const disabled = !area || !idInsumo || ff <= 0 || isNaN(stock) || stock < 0;
        if (btnGuardarInfo) btnGuardarInfo.disabled = disabled;
    };

    [selectArea, inputFondoFijo, inputStockInicial].forEach(el => {
        if (el) {
            el.addEventListener('change', validarFormAsignar);
            el.addEventListener('input', validarFormAsignar);
        }
    });

    let esReabiertoPorError = document.querySelector('#modalAsignarInsumo .is-invalid') !== null;

    const modalAsignar = document.getElementById('modalAsignarInsumo');
    if (modalAsignar) {
        modalAsignar.addEventListener('hidden.bs.modal', () => {
            if (panelClaves) panelClaves.style.display = 'none';
            clavesCache = [];
            if (esReabiertoPorError) {
                esReabiertoPorError = false;
                return;
            }
            if (formAsignar) {
                formAsignar.reset();
                if (inputIdInsumo) inputIdInsumo.value = '';
                if (inputDescInsumo) inputDescInsumo.value = '';
                if (inputTipoInsumo) inputTipoInsumo.value = '';
            }
            validarFormAsignar();
        });
    }

    // --- 6. EDICIÓN INLINE DE STOCK Y FONDO FIJO (EVENT LISTENERS EN TABLA) ---
    const actualizarVisualPorcentaje = (id, porcentaje, stock) => {
        const pctTd = document.getElementById(`porcentaje_fondof${id}`);
        const icono = document.getElementById(`icono${id}`);
        const stockInput = document.getElementById(`stock_inicial_insumo${id}`);

        if (pctTd) {
            pctTd.innerText = `${porcentaje.toFixed(1)} %`;
        }

        if (icono && stockInput) {
            const fondoFijo = stockInput.getAttribute('data-fondo') || 0;
            const meta = clasificarStock(stock, fondoFijo);

            stockInput.classList.remove('stock-muy-bajo', 'stock-bajo', 'stock-regular', 'stock-suficiente', 'stock-excedido');
            icono.className = meta.iconoClass;
            icono.style.color = meta.color;
            stockInput.classList.add(meta.stockClass);
        }
    };

    const enviarStockAjax = (id, stockVal, inputEl) => {
        if (isNaN(stockVal) || stockVal < 0) {
            mostrarToast('error', 'Cantidad de stock errónea.');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/insumos-area/${id}/stock`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ stock: stockVal })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                actualizarVisualPorcentaje(id, data.porcentaje, data.stock);
                inputEl.setAttribute('data-fondo', data.fondo_fijo);
                mostrarToast('success', 'Stock actualizado correctamente.');
            }
        })
        .catch(error => {
            console.error('Error al guardar stock:', error);
            mostrarToast('error', 'Error al actualizar el stock.');
        });
    };

    const enviarFondoFijoAjax = (id, ffVal, inputEl) => {
        if (isNaN(ffVal) || ffVal <= 0) {
            mostrarToast('error', 'Fondo fijo erróneo (debe ser mayor a 0).');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/insumos-area/${id}/fondo-fijo`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ fondo_fijo: ffVal })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                actualizarVisualPorcentaje(id, data.porcentaje, data.stock);
                const stockInput = document.getElementById(`stock_inicial_insumo${id}`);
                if (stockInput) {
                    stockInput.setAttribute('data-fondo', data.fondo_fijo);
                }
                mostrarToast('success', 'Fondo Fijo actualizado correctamente.');
            }
        })
        .catch(error => {
            console.error('Error al guardar fondo fijo:', error);
            mostrarToast('error', 'Error al actualizar el fondo fijo.');
        });
    };

    // Vincular event listeners a los inputs editables de la tabla
    document.querySelectorAll('.input-inline-stock').forEach(input => {
        input.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const val = parseInt(this.value);
                enviarStockAjax(id, val, this);
                this.blur();
            }
        });
        input.addEventListener('blur', function () {
            const id = this.getAttribute('data-id');
            const val = parseInt(this.value);
            enviarStockAjax(id, val, this);
        });
    });

    document.querySelectorAll('.input-inline-fondo').forEach(input => {
        input.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const val = parseInt(this.value);
                enviarFondoFijoAjax(id, val, this);
                this.blur();
            }
        });
        input.addEventListener('blur', function () {
            const id = this.getAttribute('data-id');
            const val = parseInt(this.value);
            enviarFondoFijoAjax(id, val, this);
        });
    });
});

// --- 7. PANEL DE REPORTES (DEFINICIONES EN WINDOWS PARA REPORTES.BLADE.PHP) ---
window.llenarListaReporte = function() {
    const areaId = document.getElementById('area_almacen_reporte').value;
    if (!areaId) return;

    const niveles = [];
    if (document.getElementById('chkMuyBajo') && document.getElementById('chkMuyBajo').checked) niveles.push('muy_bajo');
    if (document.getElementById('chkBajo') && document.getElementById('chkBajo').checked) niveles.push('bajo');
    if (document.getElementById('chkRegular') && document.getElementById('chkRegular').checked) niveles.push('regular');
    if (document.getElementById('chkSuficiente') && document.getElementById('chkSuficiente').checked) niveles.push('suficiente');
    if (document.getElementById('chkExcedido') && document.getElementById('chkExcedido').checked) niveles.push('excedido');

    // Construir los parámetros
    let queryParams = `area_id=${areaId}`;
    niveles.forEach(nivel => {
        queryParams += `&niveles[]=${nivel}`;
    });

    const spinner = document.getElementById('loadingSpinnerReporte');
    if (spinner) spinner.style.display = 'block';

    fetch(`/insumos-area/reportes/datos?${queryParams}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (spinner) spinner.style.display = 'none';

        if (data.ok) {
            window.reporteInsumosCache = data.insumos || [];
            window.reportePaginaActual = 1;
            window.reportePorPagina = 10;

            const btnImp = document.getElementById('btnImprimirReporte');
            if (btnImp) btnImp.disabled = window.reporteInsumosCache.length === 0;

            document.getElementById('total_insumos').innerText = `Total en stock: ${data.total_stock}`;
            renderizarPaginaReporte();
        }
    })
    .catch(error => {
        if (spinner) spinner.style.display = 'none';
        console.error('Error al cargar reporte:', error);
    });
};

window.renderizarPaginaReporte = function() {
    const lista = window.reporteInsumosCache || [];
    const total = lista.length;
    const porPagina = window.reportePorPagina || 10;
    const paginaActual = window.reportePaginaActual || 1;
    const totalPaginas = Math.ceil(total / porPagina) || 1;

    const inicio = (paginaActual - 1) * porPagina;
    const fin = Math.min(inicio + porPagina, total);
    const paginados = lista.slice(inicio, fin);

    let tbody = '';
    if (total === 0) {
        tbody = `
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="fa fa-info-circle fa-2x mb-2 d-block"></i>
                    No se encontraron insumos que coincidan con los niveles de stock seleccionados.
                </td>
            </tr>
        `;
    } else {
        paginados.forEach((ia, idx) => {
            const meta = clasificarStock(ia.stock, ia.fondo_fijo);
            const colorClass = meta.stockClass;
            const badgeClass = `${meta.stockClass}-badge`;

            tbody += `
                <tr>
                    <td class="text-center fw-bold text-muted">${inicio + idx + 1}</td>
                    <td class="text-center fw-bold" style="white-space: nowrap;">${ia.clave}</td>
                    <td style="word-break: break-word; min-width: 140px;">${ia.descripcion}</td>
                    <td class="text-center" style="white-space: nowrap;"><span class="badge bg-secondary" style="font-size: 0.75rem;">${ia.tipo}</span></td>
                    <td class="text-center" style="white-space: nowrap;"><span class="badge bg-light text-dark border">${ia.area}</span></td>
                    <td class="text-center fw-bold ${colorClass}">${ia.stock}</td>
                    <td class="text-center">${ia.fondo_fijo}</td>
                    <td class="text-center" style="white-space: nowrap;"><span class="badge ${badgeClass} badge-porcentaje">${ia.porcentaje} %</span></td>
                </tr>
            `;
        });
    }

    document.getElementById('tablaReporteCuerpo').innerHTML = tbody;

    // Renderizar Controles de Paginación Compactos
    const paginadorEl = document.getElementById('paginadorReporte');
    if (!paginadorEl) return;

    if (total === 0) {
        paginadorEl.style.setAttribute('style', 'display: none !important;');
        return;
    }

    paginadorEl.removeAttribute('style');
    paginadorEl.className = 'd-flex justify-content-between align-items-center border-top pt-3 mt-3 flex-wrap gap-2';

    // Generar únicamente las páginas cercanas a la actual
    let botonesPaginas = '';
    const maxVisibles = 5;
    let pagInicio = Math.max(1, paginaActual - 2);
    let pagFin = Math.min(totalPaginas, pagInicio + maxVisibles - 1);

    if (pagFin - pagInicio + 1 < maxVisibles) {
        pagInicio = Math.max(1, pagFin - maxVisibles + 1);
    }

    if (pagInicio > 1) {
        botonesPaginas += `<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="cambiarPaginaReporte(1)">1</button>`;
        if (pagInicio > 2) {
            botonesPaginas += `<span class="px-1 text-muted">…</span>`;
        }
    }

    for (let p = pagInicio; p <= pagFin; p++) {
        if (p === paginaActual) {
            botonesPaginas += `<button type="button" class="btn btn-sm btn-primary active me-1">${p}</button>`;
        } else {
            botonesPaginas += `<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="cambiarPaginaReporte(${p})">${p}</button>`;
        }
    }

    if (pagFin < totalPaginas) {
        if (pagFin < totalPaginas - 1) {
            botonesPaginas += `<span class="px-1 text-muted">…</span>`;
        }
        botonesPaginas += `<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="cambiarPaginaReporte(${totalPaginas})">${totalPaginas}</button>`;
    }

    paginadorEl.innerHTML = `
        <div class="text-muted small">
            Mostrando ${inicio + 1} a ${fin} de ${total} insumos (Página ${paginaActual} de ${totalPaginas})
        </div>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" ${paginaActual === 1 ? 'disabled' : ''} onclick="cambiarPaginaReporte(${paginaActual - 1})">
                <i class="fa fa-chevron-left me-1"></i> Anterior
            </button>
            <div class="d-inline-flex align-items-center px-1">
                ${botonesPaginas}
            </div>
            <button type="button" class="btn btn-outline-secondary" ${paginaActual === totalPaginas ? 'disabled' : ''} onclick="cambiarPaginaReporte(${paginaActual + 1})">
                Siguiente <i class="fa fa-chevron-right ms-1"></i>
            </button>
        </div>
    `;
};

window.cambiarPaginaReporte = function(nuevaPagina) {
    const totalPaginas = Math.ceil((window.reporteInsumosCache || []).length / (window.reportePorPagina || 10));
    if (nuevaPagina < 1 || nuevaPagina > totalPaginas) return;
    window.reportePaginaActual = nuevaPagina;
    renderizarPaginaReporte();
};

window.imprimirReporte = function() {
    const areaId = document.getElementById('area_almacen_reporte').value;
    if (!areaId) return;

    const niveles = [];
    if (document.getElementById('chkMuyBajo') && document.getElementById('chkMuyBajo').checked) niveles.push('muy_bajo');
    if (document.getElementById('chkBajo') && document.getElementById('chkBajo').checked) niveles.push('bajo');
    if (document.getElementById('chkRegular') && document.getElementById('chkRegular').checked) niveles.push('regular');
    if (document.getElementById('chkSuficiente') && document.getElementById('chkSuficiente').checked) niveles.push('suficiente');
    if (document.getElementById('chkExcedido') && document.getElementById('chkExcedido').checked) niveles.push('excedido');

    let queryParams = `area_id=${areaId}`;
    niveles.forEach(nivel => {
        queryParams += `&niveles[]=${nivel}`;
    });

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Deseas imprimir el reporte?',
            text: "Se abrirá la versión lista para impresión en una pestaña nueva.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, abrir impresión',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(`/insumos-area/reportes/imprimir?${queryParams}`, '_blank');
            }
        });
    } else {
        if (confirm("¿Deseas imprimir el reporte?")) {
            window.open(`/insumos-area/reportes/imprimir?${queryParams}`, '_blank');
        }
    }
};

document.addEventListener('DOMContentLoaded', function () {
    const filtroAreaEl = document.getElementById('filtro_area');
    const formBuscarFiltrosEl = document.getElementById('formBuscarFiltros');
    if (filtroAreaEl && formBuscarFiltrosEl) {
        filtroAreaEl.addEventListener('change', function () {
            formBuscarFiltrosEl.submit();
        });
    }
});
