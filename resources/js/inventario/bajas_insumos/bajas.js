/**
 * Lógica JavaScript para el módulo de Bajas de Insumos
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Refs de elementos del formulario ──────────────────────────────────────
    const selectArea         = document.getElementById('id_area_almacen');
    const inputBuscarInsumo  = document.getElementById('buscarInsumo');
    const inputIdInsumo      = document.getElementById('id_insumo');
    const sugerenciasDiv     = document.getElementById('sugerenciasInsumo');
    const infoStock          = document.getElementById('infoStock');
    const stockDisponible    = document.getElementById('stockDisponible');
    const inputCantidad      = document.getElementById('cantidad');
    const btnGuardar         = document.getElementById('btnGuardar');

    // ── 1. Alertas SweetAlert2 con sesión de Laravel ──────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito  = document.getElementById('alertaExito');
    const alertaError  = document.getElementById('alertaError');

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
    let stockMaximo     = 0;

    /**
     * Resetea el campo de insumo seleccionado.
     */
    const resetearInsumo = () => {
        inputIdInsumo.value = '';
        infoStock.style.display = 'none';
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
        const idArea  = selectArea?.value || '';

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
                    item.innerHTML = `
                        <span class="clave-badge">${insumo.clave}</span>
                        ${insumo.descripcion}
                        ${idArea ? `<span class="stock-info">Stock: ${insumo.stock}</span>` : ''}
                    `;
                    item.addEventListener('click', () => {
                        inputBuscarInsumo.value = `[${insumo.clave}] ${insumo.descripcion}`;
                        inputIdInsumo.value     = insumo.id_insumo;
                        sugerenciasDiv.style.display = 'none';
                        sugerenciasDiv.innerHTML = '';

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

    // ── 5. Confirmación SweetAlert2 para cancelar bajas ─────────────────────
    const cancelarLinks = document.querySelectorAll('.btn-cancelar-baja');
    cancelarLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const url      = this.getAttribute('data-url');
            const insumo   = this.getAttribute('data-insumo');
            const cantidad = this.getAttribute('data-cantidad');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Cancelar esta baja?',
                    html: `Se cancelará la baja de <strong>${cantidad}</strong> unidad(es) de <strong>"${insumo}"</strong>.<br>El stock será restaurado automáticamente.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, cancelar baja',
                    cancelButtonText: 'No, mantener'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                if (confirm(`¿Cancelar la baja de "${insumo}"? El stock será restaurado.`)) {
                    window.location.href = url;
                }
            }
        });
    });

    // ── 6. Buscador: enviar al presionar Enter ─────────────────────────────
    const inputBuscar = document.getElementById('inputBuscar');
    if (inputBuscar) {
        inputBuscar.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('formBuscar')?.submit();
            }
        });
    }

});
