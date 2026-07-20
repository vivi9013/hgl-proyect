document.addEventListener('DOMContentLoaded', function () {
    const bs = window.bootstrap || bootstrap;

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitoGuardar = document.getElementById('alertaExitog');
    if (alertaExitoGuardar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoGuardar.dataset.message || 'El movimiento se ha registrado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    const alertaError = document.getElementById('alertaError');
    if (alertaError && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Error!',
            text: alertaError.dataset.message || 'Hubo un error al procesar el movimiento.',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. REFERENCIAS AL DOM (declaradas al inicio para uso global en el scope)
    // ─────────────────────────────────────────────────────────────────────────
    const cuerpoTabla      = document.getElementById('cuerpoTablaMovimientos');
    const infoPaginacion   = document.getElementById('infoPaginacionMovimientos');
    const paginacionDiv    = document.getElementById('paginacionMovimientos');
    const totalMovimientos = document.getElementById('totalMovimientos');
    const filtroBuscar     = document.getElementById('filtro-buscar');
    const filtroFechaRango = document.getElementById('filtro-fecha-rango');
    const btnImprimir      = document.querySelector('a[href*="imprimir"]');

    const dropdownFiltros = document.getElementById('dropdownFiltros');

    // Inicializar Flatpickr compartido
    if (window.inicializarFlatpickrCompartido) {
        window.inicializarFlatpickrCompartido();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C. HELPER — debounce
    // ─────────────────────────────────────────────────────────────────────────
    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. TABLA — filtros + paginación AJAX
    // ─────────────────────────────────────────────────────────────────────────
    function obtenerFiltros() {
        const fp      = filtroFechaRango?._flatpickr;
        const fechas  = fp ? fp.selectedDates : [];
        const fInicio = fechas[0] ? new Date(fechas[0].getTime() - fechas[0].getTimezoneOffset() * 60000).toISOString().split('T')[0] : '';
        const fFin    = fechas[1] ? new Date(fechas[1].getTime() - fechas[1].getTimezoneOffset() * 60000).toISOString().split('T')[0] : '';
        return {
            buscar:       filtroBuscar?.value.trim() ?? '',
            tipo:         Array.from(document.querySelectorAll('.chk-tipo:checked')).map(el => el.value),
            concepto:     Array.from(document.querySelectorAll('.chk-concepto:checked')).map(el => el.value),
            status:       Array.from(document.querySelectorAll('.chk-status:checked')).map(el => el.value),
            fecha_inicio: fInicio,
            fecha_fin:    fFin,
        };
    }

    function actualizarBtnImprimir() {
        if (!btnImprimir) return;
        const f = obtenerFiltros();
        const params = new URLSearchParams();
        if (f.buscar)      params.set('buscar', f.buscar);
        f.tipo.forEach(v =>    params.append('tipo[]',    v));
        f.concepto.forEach(v => params.append('concepto[]', v));
        f.status.forEach(v =>  params.append('status[]',  v));
        if (f.fecha_inicio) params.set('fecha_inicio', f.fecha_inicio);
        if (f.fecha_fin)    params.set('fecha_fin',    f.fecha_fin);
        const baseUrl = btnImprimir.href.split('?')[0];
        btnImprimir.href = params.toString() ? `${baseUrl}?${params}` : baseUrl;
    }

    function cargarMovimientos(pagina = 1) {
        if (!cuerpoTabla) return;
        const f = obtenerFiltros();
        const params = new URLSearchParams({ page: pagina });
        if (f.buscar)      params.set('buscar', f.buscar);
        f.tipo.forEach(v =>    params.append('tipo[]',    v));
        f.concepto.forEach(v => params.append('concepto[]', v));
        f.status.forEach(v =>  params.append('status[]',  v));
        if (f.fecha_inicio) params.set('fecha_inicio', f.fecha_inicio);
        if (f.fecha_fin)    params.set('fecha_fin',    f.fecha_fin);

        cuerpoTabla.style.opacity    = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/control-insumos/movimientos-insumos?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(datos => {
            cuerpoTabla.style.opacity = '1';
            cuerpoTabla.innerHTML     = datos.html;
            if (totalMovimientos) totalMovimientos.textContent = datos.total;
            if (infoPaginacion)   infoPaginacion.textContent   = datos.info;
            if (paginacionDiv) {
                paginacionDiv.innerHTML = datos.links;
                paginacionDiv.querySelectorAll('a.page-link').forEach(a => {
                    a.addEventListener('click', e => {
                        e.preventDefault();
                        const p = new URL(a.href).searchParams.get('page');
                        if (p) cargarMovimientos(p);
                    });
                });
            }
            actualizarBtnImprimir();
        })
        .catch(() => { cuerpoTabla.style.opacity = '1'; });
    }

    // Listeners de filtros
    if (filtroBuscar) {
        filtroBuscar.addEventListener('input', demorarEjecucion(() => cargarMovimientos(1), 320));
    }

    // Escuchar eventos de filtros reutilizables
    if (dropdownFiltros) {
        dropdownFiltros.addEventListener('filtros:aplicar', () => {
            cargarMovimientos(1);
        });
        dropdownFiltros.addEventListener('filtros:limpiar', () => {
            if (filtroBuscar) filtroBuscar.value = '';
            const fp = filtroFechaRango?._flatpickr;
            if (fp) fp.clear();
            cargarMovimientos(1);
        });
    }

    if (filtroFechaRango) {
        filtroFechaRango.addEventListener('change', () => {
            cargarMovimientos(1);
        });
    }

    // Paginación carga inicial (SSR)
    if (paginacionDiv) {
        paginacionDiv.querySelectorAll('a.page-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const p = new URL(a.href).searchParams.get('page');
                if (p) cargarMovimientos(p);
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. CANCELAR MOVIMIENTO — ejecuta PATCH y recarga tabla
    // ─────────────────────────────────────────────────────────────────────────
    function ejecutarCancelacion(id, detalle) {
        const doCancel = () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(`/control-insumos/movimientos-insumos/${id}/cancelar`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN':     csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json'
                }
            })
            .then(r => r.json())
            .then(datos => {
                if (datos.success) {
                    cargarMovimientos();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ title: '¡Cancelado!', text: datos.message, icon: 'success', timer: 2000, showConfirmButton: false });
                    }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('No se pudo cancelar', datos.message, 'warning');
                }
            })
            .catch(() => {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Error al cancelar el movimiento.', 'error');
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Cancelar movimiento?',
                html: `Se revertirá el stock de <strong>${detalle}</strong>.<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, conservar'
            }).then(res => { if (res.isConfirmed) doCancel(); });
        } else {
            if (confirm(`¿Cancelar ${detalle}?`)) doCancel();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // F. EDITAR MOVIMIENTO — carga datos via GET y abre modal Bootstrap 5
    // ─────────────────────────────────────────────────────────────────────────
    function abrirModalEdicion(id) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        fetch(`/control-insumos/movimientos-insumos/${id}/edit`, {
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN':     csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json'
            }
        })
        .then(r => {
            if (!r.ok) {
                // Leer el body para saber qué devolvió el servidor
                return r.text().then(txt => {
                    throw new Error(`HTTP ${r.status} — ${txt.substring(0, 200)}`);
                });
            }
            return r.json();
        })
        .then(datos => {
            // Llenar campos
            document.getElementById('editar_insumo_nombre').value = datos.insumo_nombre;
            document.getElementById('editar_tipo').value          = datos.tipo;
            document.getElementById('editar_cantidad').value      = datos.cantidad;
            document.getElementById('editar_fecha_movimiento').value = datos.fecha_movimiento;
            document.getElementById('editar_proveedor').value        = datos.proveedor ?? '';

            // Poblar panel info insumo para Salidas
            const elCompatibles = document.getElementById('editar_info_compatibles');
            const elHojas       = document.getElementById('editar_info_hojas');
            const elTiempo      = document.getElementById('editar_info_tiempo');
            const elStock       = document.getElementById('editar_info_stock');
            if (elCompatibles) elCompatibles.textContent = datos.insumo_compatibles || '—';
            if (elHojas)       elHojas.textContent       = datos.insumo_hojas ? `${datos.insumo_hojas} hojas` : '—';
            if (elTiempo)      elTiempo.textContent      = datos.insumo_tiempo || '—';
            if (elStock)       elStock.textContent       = `${datos.insumo_stock} piezas disponibles`;

            // Guardar conceptos en variables del DOM para uso dinámico al cambiar tipo
            const selectTipo = document.getElementById('editar_tipo');
            selectTipo.dataset.conceptosEntrada = JSON.stringify(datos.conceptos_entrada);
            selectTipo.dataset.conceptosSalida  = JSON.stringify(datos.conceptos_salida);
            selectTipo.dataset.conceptoActual   = datos.concepto; // para pre-seleccionar

            // ─── Select de Proveedor ───────────────────────────────────────────
            function inicializarSelectProveedor(valorActual) {
                const sel   = document.getElementById('editar_select_proveedor');
                const input = document.getElementById('editar_proveedor');
                if (!sel || !input) return;

                const opcionExistente = Array.from(sel.options).some(o => o.value === valorActual && o.value !== '' && o.value !== 'Otro');

                if (!valorActual) {
                    sel.value = '';
                    input.value = '';
                    input.classList.add('d-none');
                } else if (opcionExistente) {
                    sel.value = valorActual;
                    input.value = valorActual;
                    input.classList.add('d-none');
                } else {
                    // Valor personalizado → mostrar input libre
                    sel.value = 'Otro';
                    input.value = valorActual;
                    input.classList.remove('d-none');
                }

                sel.onchange = function () {
                    if (this.value === 'Otro') {
                        input.classList.remove('d-none');
                        input.value = '';
                        input.focus();
                    } else {
                        input.classList.add('d-none');
                        input.value = this.value;
                    }
                };
            }

            // Función para actualizar campos según el Tipo (Entrada/Salida)
            function actualizarFormularioEdicion() {
                const tipo            = selectTipo.value;
                const campoProveedor  = document.getElementById('editar_campo_proveedor');
                const panelInfoInsumo = document.getElementById('editar_panel_info_insumo');
                const selectConcepto  = document.getElementById('editar_concepto');

                if (tipo === 'Entrada') {
                    if (campoProveedor)  campoProveedor.style.display  = '';
                    if (panelInfoInsumo) panelInfoInsumo.style.display = 'none';
                } else {
                    if (campoProveedor)  campoProveedor.style.display  = 'none';
                    if (panelInfoInsumo) panelInfoInsumo.style.display = '';
                }

                const conceptos = tipo === 'Entrada'
                    ? JSON.parse(selectTipo.dataset.conceptosEntrada)
                    : JSON.parse(selectTipo.dataset.conceptosSalida);

                selectConcepto.innerHTML = '<option value="">Seleccione...</option>';
                conceptos.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c;
                    opt.textContent = c;
                    if (c === selectTipo.dataset.conceptoActual) opt.selected = true;
                    selectConcepto.appendChild(opt);
                });
            }

            inicializarSelectProveedor(datos.proveedor ?? '');

            // Asignar el listener de cambio una única vez en el select de tipo
            selectTipo.onchange = actualizarFormularioEdicion;

            // Ejecutar visualización inicial
            actualizarFormularioEdicion();

            // Guardar id en el formulario
            document.getElementById('formEditarMovimiento').dataset.id = datos.id_movimiento;

            // Abrir modal de forma segura (Bootstrap 5)
            const modalEl   = document.getElementById('modalEditarMovimiento');
            let   modalInst = bs.Modal.getInstance(modalEl);
            if (!modalInst) modalInst = new bs.Modal(modalEl);
            modalInst.show();
        })
        .catch(err => {
            console.error('[abrirModalEdicion] Error:', err.message);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error al cargar', `Detalle técnico: ${err.message}`, 'error');
            }
        });
    }

    // Submit del formulario de edición (PUT vía fetch)
    const formEditarMovimiento = document.getElementById('formEditarMovimiento');
    if (formEditarMovimiento) {
        formEditarMovimiento.addEventListener('submit', function (e) {
            e.preventDefault();
            const id   = this.dataset.id;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            const payload = {
                tipo:             document.getElementById('editar_tipo').value,
                cantidad:         document.getElementById('editar_cantidad').value,
                concepto:         document.getElementById('editar_concepto').value,
                fecha_movimiento: document.getElementById('editar_fecha_movimiento').value,
                proveedor:        document.getElementById('editar_proveedor').value,
            };

            fetch(`/control-insumos/movimientos-insumos/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN':     csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json',
                    'Content-Type':     'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => {
                if (!r.ok) return r.json().then(err => { throw err; });
                return r.json();
            })
            .then(datos => {
                if (datos.success) {
                    const modalEl   = document.getElementById('modalEditarMovimiento');
                    const modalInst = bs.Modal.getInstance(modalEl);
                    if (modalInst) modalInst.hide();
                    cargarMovimientos();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ title: '¡Actualizado!', text: datos.message, icon: 'success', timer: 2000, showConfirmButton: false });
                    }
                } else {
                    if (typeof Swal !== 'undefined') Swal.fire('Error', datos.message, 'warning');
                }
            })
            .catch(err => {
                const msg = err?.message
                    || (err?.errors ? Object.values(err.errors).flat().join(', ') : '')
                    || 'No se pudo actualizar el movimiento.';
                if (typeof Swal !== 'undefined') Swal.fire('Error de validación', msg, 'error');
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // G. DELEGACIÓN DE EVENTOS EN LA TABLA
    // Un solo listener en el tbody cubre editar y cancelar para cualquier
    // fila, incluso tras recargas AJAX.
    // ─────────────────────────────────────────────────────────────────────────
    if (cuerpoTabla) {
        cuerpoTabla.addEventListener('click', function (e) {
            // Botón Editar
            const btnEditar = e.target.closest('.btn-editar-movimiento');
            if (btnEditar) {
                e.preventDefault();
                const id = btnEditar.dataset.id;
                if (id) abrirModalEdicion(id);
                return;
            }

            // Badge "Activo" en columna Estado → cancelar
            const badgeCancelar = e.target.closest('.badge-cancelar-movimiento');
            if (badgeCancelar) {
                e.preventDefault();
                const id      = badgeCancelar.dataset.id;
                const detalle = badgeCancelar.dataset.detalle || 'este movimiento';
                if (id) ejecutarCancelacion(id, detalle);
                return;
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // H. MODAL SALIDA — carga dinámica de info del insumo + validación stock
    // ─────────────────────────────────────────────────────────────────────────
    const selectInsumo         = document.getElementById('id_insumo_impresora');
    const panelInfoInsumo      = document.getElementById('panelInfoInsumo');
    const infoCompatibles      = document.getElementById('infoCompatibles');
    const infoHojas            = document.getElementById('infoHojas');
    const infoTiempo           = document.getElementById('infoTiempo');
    const infoStock            = document.getElementById('infoStock');
    const inputCantidadSalida  = document.getElementById('cantidad_salida');
    const selectConceptoSalida = document.getElementById('concepto_salida');
    const inputFechaSalida     = document.getElementById('fecha_salida');
    const errorCantidadSalida  = document.getElementById('error_cantidad_salida');
    const formSalida           = document.getElementById('formSalida');
    let stockActual = 0;

    // Helpers para bloquear/desbloquear campos de Salida
    function bloquearCamposSalida() {
        [selectConceptoSalida, inputCantidadSalida, inputFechaSalida].forEach(el => {
            if (!el) return;
            el.disabled = true;
            el.style.opacity = '0.5';
            el.style.pointerEvents = 'none';
        });
        if (inputCantidadSalida) inputCantidadSalida.value = '';
        if (selectConceptoSalida) selectConceptoSalida.value = '';
        if (panelInfoInsumo) panelInfoInsumo.style.display = 'none';
        stockActual = 0;
    }

    function desbloquearCamposSalida() {
        [selectConceptoSalida, inputCantidadSalida, inputFechaSalida].forEach(el => {
            if (!el) return;
            el.disabled = false;
            el.style.opacity = '';
            el.style.pointerEvents = '';
        });
    }

    // Estado inicial: bloqueados
    bloquearCamposSalida();

    function validarCantidadSalida() {
        if (!inputCantidadSalida) return true;
        const cantVal  = parseInt(inputCantidadSalida.value) || 0;
        const idInsumo = selectInsumo ? selectInsumo.value : '';
        if (!idInsumo || inputCantidadSalida.disabled) {
            inputCantidadSalida.classList.remove('is-invalid');
            return true;
        }
        if (cantVal > stockActual) {
            inputCantidadSalida.classList.add('is-invalid');
            if (errorCantidadSalida) {
                errorCantidadSalida.textContent = `La cantidad no puede superar el stock disponible (${stockActual} piezas).`;
            }
            return false;
        }
        inputCantidadSalida.classList.remove('is-invalid');
        return true;
    }

    if (selectInsumo) {
        selectInsumo.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const id  = this.value;
            if (!id) {
                bloquearCamposSalida();
                return;
            }
            desbloquearCamposSalida();
            const hojas       = opt.dataset.hojas       || '';
            const tiempo      = opt.dataset.tiempo      || '';
            const stock       = opt.dataset.stock       || '0';
            const compatibles = (opt.dataset.compatibles || '').trim();
            stockActual = parseInt(stock) || 0;
            if (infoCompatibles) infoCompatibles.textContent = compatibles || '—';
            if (infoHojas)       infoHojas.textContent       = hojas ? `${hojas} hojas` : '—';
            if (infoTiempo)      infoTiempo.textContent      = tiempo || '—';
            if (infoStock)       infoStock.textContent       = `${stock} piezas disponibles`;
            if (panelInfoInsumo) panelInfoInsumo.style.display = 'block';
            if (inputCantidadSalida) { inputCantidadSalida.max = stockActual; validarCantidadSalida(); }
        });
    }

    if (inputCantidadSalida) {
        inputCantidadSalida.addEventListener('input',  validarCantidadSalida);
        inputCantidadSalida.addEventListener('change', validarCantidadSalida);
    }

    if (formSalida) {
        formSalida.addEventListener('submit', function (e) {
            if (!validarCantidadSalida()) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error de Validación',
                        text: `La cantidad solicitada supera el stock disponible (${stockActual} piezas).`,
                        icon: 'warning',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                }
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // I. MODAL ENTRADA — habilitar/deshabilitar campos al seleccionar insumo
    // ─────────────────────────────────────────────────────────────────────────
    const selectInsumoEntrada    = document.getElementById('id_insumo_impresora_entrada');
    const selectConceptoEntrada  = document.getElementById('concepto_entrada');
    const inputCantidadEntrada   = document.getElementById('cantidad_entrada');
    const inputFechaEntrada      = document.getElementById('fecha_entrada');
    const selectProveedorEntrada = document.getElementById('select_proveedor_entrada');
    const inputProveedorEntrada  = document.getElementById('proveedor_entrada');

    function bloquearCamposEntrada() {
        [selectConceptoEntrada, inputCantidadEntrada, inputFechaEntrada, selectProveedorEntrada, inputProveedorEntrada].forEach(el => {
            if (!el) return;
            el.disabled = true;
            el.style.opacity = '0.5';
            el.style.pointerEvents = 'none';
        });
        if (selectConceptoEntrada) selectConceptoEntrada.value = '';
        if (inputCantidadEntrada)  inputCantidadEntrada.value  = '';
        if (selectProveedorEntrada) selectProveedorEntrada.value = '';
        if (inputProveedorEntrada) {
            inputProveedorEntrada.value = '';
            inputProveedorEntrada.classList.add('d-none');
        }
    }

    function desbloquearCamposEntrada() {
        [selectConceptoEntrada, inputCantidadEntrada, inputFechaEntrada, selectProveedorEntrada, inputProveedorEntrada].forEach(el => {
            if (!el) return;
            el.disabled = false;
            el.style.opacity = '';
            el.style.pointerEvents = '';
        });
    }

    // Estado inicial: bloqueados
    bloquearCamposEntrada();

    if (selectInsumoEntrada) {
        selectInsumoEntrada.addEventListener('change', function () {
            if (!this.value) {
                bloquearCamposEntrada();
            } else {
                desbloquearCamposEntrada();
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // J. PROVEEDOR — select + campo libre "Otro" (modal Entrada)
    // ─────────────────────────────────────────────────────────────────────────
    if (selectProveedorEntrada && inputProveedorEntrada) {
        selectProveedorEntrada.addEventListener('change', function () {
            if (this.value === 'Otro') {
                inputProveedorEntrada.classList.remove('d-none');
                inputProveedorEntrada.value = '';
                inputProveedorEntrada.focus();
            } else {
                inputProveedorEntrada.classList.add('d-none');
                inputProveedorEntrada.value = this.value;
            }
        });
    }

});
