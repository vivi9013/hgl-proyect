/**
 * Lógica JavaScript para Insumos por Área
 * Inventario de Medicamentos y Material de Curación – HGL
 */

window.llenarLista = function() {
    fetch('/insumos-area/buscar-insumos', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(insumos => {
        let tbody = '';
        insumos.forEach((insumo, index) => {
            tbody += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="text-center font-weight-bold">${insumo.clave}</td>
                    <td>${insumo.descripcion}</td>
                    <td class="text-center"><span class="badge bg-secondary">${insumo.tipo}</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-success" 
                                onclick="seleccionarIdInsumo('${insumo.id_insumo}', '${insumo.clave}', '${insumo.descripcion.replace(/'/g, "\\'")}', '${insumo.tipo}')">
                            <i class="fa fa-check-square"></i> Seleccionar
                        </button>
                    </td>
                </tr>
            `;
        });

        const htmlTable = `
            <div class="p-3">
                <table id="tablaInsumosModal" class="table table-striped table-bordered table-hover w-100">
                    <thead>
                        <tr class="table-dark">
                            <th class="text-center">#</th>
                            <th class="text-center">Clave</th>
                            <th>Descripción</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tbody}
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('listaInsumosContenido').innerHTML = htmlTable;
        
        // Inicializar DataTable en el modal si existe jquery / datatables
        if ($.fn.DataTable) {
            $('#tablaInsumosModal').DataTable({
                "language": {
                    "url": "/plugins/datatables/langauge/Spanish.json"
                },
                "pageLength": 10,
                "lengthMenu": [5, 10, 25]
            });
        }
        
        const modal = new bootstrap.Modal(document.getElementById('modalInsumos'));
        modal.show();
    })
    .catch(error => {
        console.error('Error al cargar insumos:', error);
        if (typeof alertify !== 'undefined') {
            alertify.error('Error al cargar la lista de insumos.');
        }
    });
};

window.seleccionarIdInsumo = function(id, clave, descripcion, tipo) {
    document.getElementById('id_insumo').value = id;
    document.getElementById('cve_insumo').value = clave;
    document.getElementById('descripcion_insumo').value = descripcion;
    document.getElementById('tipo').value = tipo;

    // Quitar estados anteriores de error/éxito
    document.getElementById('cve_insumo').classList.remove('is-invalid');
    document.getElementById('cve_insumo').classList.add('is-valid');

    // Cerrar modal
    const modalEl = document.getElementById('modalInsumos');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }

    // Habilitar campos
    document.getElementById('fondo_fijo_insumo').disabled = false;
    document.getElementById('stock_inicial_insumo').disabled = false;
    document.getElementById('fondo_fijo_insumo').value = '';
    document.getElementById('fondo_fijo_insumo').focus();

    validarEspaciosVacios();
};

window.seleccionarIdInsumoClave = function(e) {
    if (e.key === 'Enter' || e.keyCode === 13 || e.key === 'Tab' || e.keyCode === 9) {
        e.preventDefault();
        const clave = document.getElementById('cve_insumo').value.trim();

        if (clave === "") {
            if (typeof alertify !== 'undefined') {
                alertify.error("Introduzca una clave");
            }
            return;
        }

        fetch(`/insumos-area/verificar-clave?clave=${encodeURIComponent(clave)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.encontrado) {
                document.getElementById('id_insumo').value = data.id_insumo;
                document.getElementById('descripcion_insumo').value = data.descripcion;
                document.getElementById('tipo').value = data.tipo;

                document.getElementById('cve_insumo').classList.remove('is-invalid');
                document.getElementById('cve_insumo').classList.add('is-valid');

                document.getElementById('fondo_fijo_insumo').disabled = false;
                document.getElementById('stock_inicial_insumo').disabled = false;
                document.getElementById('fondo_fijo_insumo').focus();
            } else {
                if (typeof alertify !== 'undefined') {
                    alertify.error("No se encuentra el insumo o no está activo");
                }
                document.getElementById('id_insumo').value = "";
                document.getElementById('cve_insumo').value = "";
                document.getElementById('descripcion_insumo').value = "";
                document.getElementById('tipo').value = "";
                document.getElementById('cve_insumo').classList.add('is-invalid');

                document.getElementById('fondo_fijo_insumo').disabled = true;
                document.getElementById('stock_inicial_insumo').disabled = true;
            }
            validarEspaciosVacios();
        })
        .catch(error => {
            console.error('Error al consultar clave:', error);
        });
    }
};

window.validarClaveInput = function() {
    const areaId = document.getElementById('area_almacen_select').value;
    const cveInput = document.getElementById('cve_insumo');

    if (areaId === "" || areaId == 0) {
        cveInput.value = '';
        cveInput.disabled = true;
        document.getElementById('fondo_fijo_insumo').value = '';
        document.getElementById('fondo_fijo_insumo').disabled = true;
        document.getElementById('stock_inicial_insumo').value = '0';
        document.getElementById('stock_inicial_insumo').disabled = true;
        document.getElementById('descripcion_insumo').value = '';
        document.getElementById('tipo').value = '';
    } else {
        cveInput.disabled = false;
        cveInput.focus();
    }
    validarEspaciosVacios();
};

window.validarCveEmpty = function() {
    const clave = document.getElementById('cve_insumo').value.trim();
    if (clave === '') {
        document.getElementById('id_insumo').value = "";
        document.getElementById('descripcion_insumo').value = "";
        document.getElementById('tipo').value = "";
        document.getElementById('fondo_fijo_insumo').value = '';
        document.getElementById('fondo_fijo_insumo').disabled = true;
        document.getElementById('stock_inicial_insumo').value = '0';
        document.getElementById('stock_inicial_insumo').disabled = true;
    }
    validarEspaciosVacios();
};

window.validarEspaciosVacios = function() {
    const idInsumo = document.getElementById('id_insumo').value;
    const cveInsumo = document.getElementById('cve_insumo').value;
    const stock = document.getElementById('stock_inicial_insumo').value;
    const fondoFijo = document.getElementById('fondo_fijo_insumo').value;
    const area = document.getElementById('area_almacen_select').value;
    const btnGuardar = document.getElementById('btnGuardarInfo');

    if (!btnGuardar) return;

    if (cveInsumo === '' || stock === '' || fondoFijo === '' || area === '' || area == 0 || fondoFijo <= 0 || idInsumo === '') {
        btnGuardar.disabled = true;
    } else {
        btnGuardar.disabled = false;
    }
};

// Guardar Stock Inline (AJAX PATCH)
window.guardarStockInicial = function(id, fondoFijo, event) {
    if (event.key === 'Enter' || event.keyCode === 13) {
        event.preventDefault();
        const input = document.getElementById(`stock_inicial_insumo${id}`);
        const stockVal = parseInt(input.value);

        if (isNaN(stockVal) || stockVal < 0) {
            if (typeof alertify !== 'undefined') {
                alertify.error("Cantidad de stock errónea.");
            }
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
                if (typeof alertify !== 'undefined') {
                    alertify.success("Stock actualizado correctamente.");
                }
                actualizarVisualPorcentaje(id, data.porcentaje, data.stock);
                input.blur();
            }
        })
        .catch(error => {
            console.error('Error al guardar stock:', error);
            if (typeof alertify !== 'undefined') {
                alertify.error("Error al actualizar el stock.");
            }
        });
    }
};

window.guardarStockInicial2 = function(id, fondoFijo) {
    const input = document.getElementById(`stock_inicial_insumo${id}`);
    const stockVal = parseInt(input.value);

    if (isNaN(stockVal) || stockVal < 0) {
        if (typeof alertify !== 'undefined') {
            alertify.error("Cantidad de stock errónea.");
        }
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
        }
    })
    .catch(error => {
        console.error('Error al guardar stock blur:', error);
    });
};

// Guardar Fondo Fijo Inline (AJAX PATCH)
window.guardarFondoFijo = function(id, event) {
    if (event.key === 'Enter' || event.keyCode === 13) {
        event.preventDefault();
        const input = document.getElementById(`fondo_fijo${id}`);
        const ffVal = parseInt(input.value);

        if (isNaN(ffVal) || ffVal <= 0) {
            if (typeof alertify !== 'undefined') {
                alertify.error("Fondo fijo erróneo (debe ser mayor a 0).");
            }
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
                if (typeof alertify !== 'undefined') {
                    alertify.success("Fondo Fijo actualizado correctamente.");
                }
                actualizarVisualPorcentaje(id, data.porcentaje, data.stock);
                input.blur();
            }
        })
        .catch(error => {
            console.error('Error al guardar fondo fijo:', error);
            if (typeof alertify !== 'undefined') {
                alertify.error("Error al actualizar el fondo fijo.");
            }
        });
    }
};

// Función para actualizar termómetros visuales en la tabla
window.actualizarVisualPorcentaje = function(id, porcentaje, stock) {
    const pctTd = document.getElementById(`porcentaje_fondof${id}`);
    const icono = document.getElementById(`icono${id}`);
    const stockInput = document.getElementById(`stock_inicial_insumo${id}`);

    if (pctTd) {
        pctTd.innerText = `${porcentaje.toFixed(1)} %`;
    }

    if (icono && stockInput) {
        // Remover clases de color anteriores
        stockInput.classList.remove('stock-muy-bajo', 'stock-bajo', 'stock-regular', 'stock-suficiente', 'stock-excedido');

        if (porcentaje < 25) {
            icono.className = "fa fa-thermometer-empty fa-2x thermometer-icon";
            icono.style.color = "#d63031";
            stockInput.classList.add('stock-muy-bajo');
        } else if (porcentaje >= 25 && porcentaje < 50) {
            icono.className = "fa fa-thermometer-quarter fa-2x thermometer-icon";
            icono.style.color = "#e67e22";
            stockInput.classList.add('stock-bajo');
        } else if (porcentaje >= 50 && porcentaje < 75) {
            icono.className = "fa fa-thermometer-half fa-2x thermometer-icon";
            icono.style.color = "#f1c40f";
            stockInput.classList.add('stock-regular');
        } else if (porcentaje >= 75 && porcentaje <= 100) {
            icono.className = "fa fa-thermometer-three-quarters fa-2x thermometer-icon";
            icono.style.color = "#27ae60";
            stockInput.classList.add('stock-suficiente');
        } else {
            icono.className = "fa fa-thermometer-full fa-2x thermometer-icon";
            icono.style.color = "#2980b9";
            stockInput.classList.add('stock-excedido');
        }
    }
};

// Lógica de reportes interactivos por AJAX
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
    let queryParams = `id_area_almacen=${areaId}`;
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
            let tbody = '';
            data.insumos.forEach((ia, index) => {
                let colorClass = '';
                let badgeClass = '';
                if (ia.porcentaje < 25) {
                    colorClass = 'stock-muy-bajo';
                    badgeClass = 'stock-muy-bajo-badge';
                } else if (ia.porcentaje >= 25 && ia.porcentaje < 50) {
                    colorClass = 'stock-bajo';
                    badgeClass = 'stock-bajo-badge';
                } else if (ia.porcentaje >= 50 && ia.porcentaje < 75) {
                    colorClass = 'stock-regular';
                    badgeClass = 'stock-regular-badge';
                } else if (ia.porcentaje >= 75 && ia.porcentaje <= 100) {
                    colorClass = 'stock-suficiente';
                    badgeClass = 'stock-suficiente-badge';
                } else {
                    colorClass = 'stock-excedido';
                    badgeClass = 'stock-excedido-badge';
                }

                tbody += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center font-weight-bold">${ia.clave}</td>
                        <td>${ia.descripcion}</td>
                        <td class="text-center"><span class="badge bg-secondary">${ia.tipo}</span></td>
                        <td class="text-center">${ia.area}</td>
                        <td class="text-center fw-bold ${colorClass}">${ia.stock}</td>
                        <td class="text-center">${ia.fondo_fijo}</td>
                        <td class="text-center"><span class="badge ${badgeClass} badge-porcentaje">${ia.porcentaje} %</span></td>
                    </tr>
                `;
            });

            if (data.insumos.length === 0) {
                tbody = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-info-circle fa-2x mb-2 d-block"></i>
                            No se encontraron insumos que coincidan con los niveles de stock seleccionados.
                        </td>
                    </tr>
                `;
                document.getElementById('btnImprimirReporte').disabled = true;
            } else {
                document.getElementById('btnImprimirReporte').disabled = false;
            }

            document.getElementById('tablaReporteCuerpo').innerHTML = tbody;
            document.getElementById('total_insumos').innerText = `Total en stock: ${data.total_stock}`;
        }
    })
    .catch(error => {
        if (spinner) spinner.style.display = 'none';
        console.error('Error al cargar reporte:', error);
    });
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

    let queryParams = `id_area_almacen=${areaId}`;
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
    const selectArea = document.getElementById('area_almacen_select');
    if (selectArea) {
        selectArea.addEventListener('change', function() {
            validarClaveInput();
        });
    }

    const cveInput = document.getElementById('cve_insumo');
    if (cveInput) {
        cveInput.addEventListener('keypress', function(e) {
            seleccionarIdInsumoClave(e);
        });
        cveInput.addEventListener('keyup', function() {
            validarCveEmpty();
        });
        cveInput.addEventListener('dblclick', function() {
            llenarLista();
        });
    }

    const ffInput = document.getElementById('fondo_fijo_insumo');
    if (ffInput) {
        ffInput.addEventListener('keyup', function() {
            validarEspaciosVacios();
        });
        ffInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const stockInput = document.getElementById('stock_inicial_insumo');
                if (stockInput) {
                    stockInput.disabled = false;
                    stockInput.focus();
                }
            }
        });
    }

    const stockInput = document.getElementById('stock_inicial_insumo');
    if (stockInput) {
        stockInput.addEventListener('keyup', function() {
            validarEspaciosVacios();
        });
        stockInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const btnSave = document.getElementById('btnGuardarInfo');
                if (btnSave && !btnSave.disabled) {
                    btnSave.focus();
                }
            }
        });
    }

    // Inicializar tooltips o datatables si existen
    if (typeof $ !== 'undefined' && $.fn.DataTable && document.getElementById('tablaInsumosAreaPrincipal')) {
        $('#tablaInsumosAreaPrincipal').DataTable({
            "language": {
                "url": "/plugins/datatables/langauge/Spanish.json"
            }
        });
    }
});
