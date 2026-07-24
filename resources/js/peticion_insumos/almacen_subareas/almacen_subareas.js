document.addEventListener('DOMContentLoaded', function () {

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ─────────────────────────────────────────────────────────
    // LÓGICA: ASIGNAR INSUMO A SUBÁREA (MODAL)
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnAdd = e.target.closest('.btn-agregar-insumo');
        if (!btnAdd) return;

        e.preventDefault();
        const idAlmacen = btnAdd.getAttribute('data-id');
        const subareaNombre = btnAdd.getAttribute('data-subarea') || '';

        const formAdd = document.getElementById('formAgregarInsumo');
        const spanNombre = document.getElementById('nombreSubareaModal');

        if (formAdd) {
            formAdd.action = `/peticion-insumos/almacen-subareas/${idAlmacen}/insumo`;
        }
        if (spanNombre) {
            spanNombre.textContent = subareaNombre;
        }

        const modalEl = document.getElementById('modalAgregarInsumo');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: EDITAR DETALLE (STOCK Y FONDO FIJO VIA AJAX)
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-editar-detalle');
        if (!btnEdit) return;

        e.preventDefault();
        const idDetalle = btnEdit.getAttribute('data-id');
        const cantidad = btnEdit.getAttribute('data-cantidad');
        const fondo = btnEdit.getAttribute('data-fondo');

        document.getElementById('edit_detalle_id').value = idDetalle;
        document.getElementById('edit_cantidad').value = cantidad;
        document.getElementById('edit_fondo_fijo').value = fondo;

        const modalEl = document.getElementById('modalEditarDetalle');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    const formEditarDetalle = document.getElementById('formEditarDetalle');
    if (formEditarDetalle) {
        formEditarDetalle.addEventListener('submit', function (e) {
            e.preventDefault();
            const idDetalle = document.getElementById('edit_detalle_id').value;
            const cantidad = document.getElementById('edit_cantidad').value;
            const fondo = document.getElementById('edit_fondo_fijo').value;

            fetch(`/peticion-insumos/almacen-subareas/detalle/${idDetalle}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cantidad: cantidad, fondo_fijo: fondo })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('modalEditarDetalle');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonColor: '#2b6cb0'
                        }).then(() => {
                            recargarTabla();
                        });
                    } else {
                        alert(data.mensaje);
                        recargarTabla();
                    }
                }
            })
            .catch(err => {
                console.error('Error al actualizar detalle:', err);
            });
        });
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: ELIMINAR DETALLE DE SUBÁREA VIA AJAX
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnDelete = e.target.closest('.btn-eliminar-detalle');
        if (!btnDelete) return;

        e.preventDefault();
        const idDetalle = btnDelete.getAttribute('data-id');

        const doDelete = () => {
            fetch(`/peticion-insumos/almacen-subareas/detalle/${idDetalle}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('¡Eliminado!', data.mensaje, 'success').then(() => {
                            recargarTabla();
                        });
                    } else {
                        alert(data.mensaje);
                        recargarTabla();
                    }
                }
            })
            .catch(err => {
                console.error('Error al eliminar detalle:', err);
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Quitar insumo de la subárea?',
                text: 'El insumo dejará de pertenecer al inventario de esta subárea.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) doDelete();
            });
        } else {
            if (confirm('¿Está seguro de quitar este insumo?')) doDelete();
        }
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: FILTROS EN TIEMPO REAL (ÁREA, SUBÁREA, BUSCAR, ESTATUS)
    // ─────────────────────────────────────────────────────────
    const contenedor   = document.getElementById('contenedor-tabla-almacenes');
    const selectArea    = document.getElementById('filter-area');
    const selectSubarea = document.getElementById('filter-subarea');
    const inputBuscar   = document.getElementById('buscar-almacen');
    const checksStatus  = document.querySelectorAll('.filter-status-checkbox');

    let debounceBuscar;
    let peticionActual = 0;       // secuencia para descartar respuestas obsoletas
    let controladorActual = null; // AbortController de la petición en curso

    function recolectarParametros(extra = {}) {
        const params = new URLSearchParams();
        if (inputBuscar?.value.trim()) params.set('buscar', inputBuscar.value.trim());
        if (selectArea?.value)         params.set('id_area_abastecimiento', selectArea.value);
        if (selectSubarea?.value)      params.set('id_subarea_abastecimiento', selectSubarea.value);

        const estatusMarcados = [...checksStatus].filter(c => c.checked).map(c => c.value);
        estatusMarcados.forEach(v => params.append('status[]', v));

        Object.entries(extra).forEach(([k, v]) => params.set(k, v));
        return params;
    }

    function recargarTabla(extra = {}) {
        if (!contenedor) return;
        const params = recolectarParametros(extra);
        const url = `${contenedor.dataset.endpoint}?${params.toString()}`;

        // Cancela la petición anterior si sigue en vuelo (ya quedó obsoleta)
        controladorActual?.abort();
        controladorActual = new AbortController();

        // Marca esta petición como "la más reciente"; solo ella podrá pintar resultados
        const idPeticion = ++peticionActual;

        contenedor.style.opacity = '0.5';
        contenedor.style.transition = 'opacity 0.15s';

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            signal: controladorActual.signal
        })
            .then(r => r.json())
            .then(data => {
                // Si mientras esperábamos ya se disparó una petición más nueva, ignorar esta respuesta
                if (idPeticion !== peticionActual) return;

                contenedor.innerHTML = data.html;
                contenedor.style.opacity = '1';
                const nuevaUrl = params.toString() ? `?${params.toString()}` : location.pathname;
                history.replaceState(null, '', nuevaUrl);
                vincularPaginacionExterna();
                inicializarPaginacionInsumos(contenedor);
            })
            .catch(err => {
                if (err.name === 'AbortError') return; // cancelación intencional, no es un error real
                console.error('Error al recargar tabla de almacenes:', err);
                if (idPeticion === peticionActual) contenedor.style.opacity = '1';
            });
    }

    // Re-vincula los links de paginación externa (Laravel) tras cada recarga AJAX
    function vincularPaginacionExterna() {
        if (!contenedor) return;
        contenedor.querySelectorAll('.pagination a.page-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const page = new URL(a.href).searchParams.get('page');
                recargarTabla(page ? { page } : {});
            });
        });
    }

    const cargarSubareas = (idArea) => {
        if (!selectSubarea) return;
        selectSubarea.innerHTML = '<option value="">Cargando...</option>';
        selectSubarea.disabled = true;

        fetch(`/peticion-insumos/almacen-subareas/subareas-por-area?id_area_abastecimiento=${idArea}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                selectSubarea.innerHTML = '<option value="">-- Todas las Subáreas --</option>';
                data.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id_subarea_abastecimiento;
                    opt.textContent = sub.siglas ? `[${sub.siglas}] ${sub.nombre}` : sub.nombre;
                    selectSubarea.appendChild(opt);
                });
                selectSubarea.disabled = false;
            })
            .catch(() => {
                selectSubarea.innerHTML = '<option value="">-- Error al cargar --</option>';
                selectSubarea.disabled = false;
            });
    };

    // Área: recarga subáreas Y dispara filtro en tiempo real
    selectArea?.addEventListener('change', function () {
        if (this.value) {
            cargarSubareas(this.value);
        } else if (selectSubarea) {
            selectSubarea.innerHTML = '<option value="">-- Todas las Subáreas --</option>';
            selectSubarea.disabled = false;
        }
        recargarTabla();
    });

    // Subárea: dispara filtro en tiempo real
    selectSubarea?.addEventListener('change', () => recargarTabla());

    // Buscar: tiempo real con debounce (no requiere botón)
    inputBuscar?.addEventListener('input', () => {
        clearTimeout(debounceBuscar);
        debounceBuscar = setTimeout(() => recargarTabla(), 350);
    });

    // Estatus (Activo/Inactivo): tiempo real
    checksStatus.forEach(chk => chk.addEventListener('change', () => recargarTabla()));

    vincularPaginacionExterna();

    // ─────────────────────────────────────────────────────────
    // LÓGICA: PAGINACIÓN INTERNA DE INSUMOS (10 POR PÁGINA, POR TARJETA)
    // ─────────────────────────────────────────────────────────
    function inicializarPaginacionInsumos(scope = document) {
        scope.querySelectorAll('.tabla-insumos-paginada').forEach(cont => {
            const perPage = parseInt(cont.dataset.perPage, 10) || 10;
            const filas   = [...cont.querySelectorAll('.fila-insumo')];
            const info    = cont.querySelector('.texto-info-paginacion');
            const nav     = cont.querySelector('.controles-paginacion');
            const total   = filas.length;
            const totalPaginas = Math.max(1, Math.ceil(total / perPage));

            if (total <= perPage) {
                if (info) info.textContent = `Mostrando ${total} de ${total} insumos`;
                if (nav) nav.innerHTML = '';
                return;
            }

            // Calcula qué números de página mostrar: siempre 1 y la última,
            // + 1 antes/después de la página actual, y "…" donde se salte un rango.
            function calcularRangoPaginas(pagina) {
                const rango = new Set([1, totalPaginas, pagina, pagina - 1, pagina + 1]);
                return [...rango]
                    .filter(p => p >= 1 && p <= totalPaginas)
                    .sort((a, b) => a - b);
            }

            function crearItem(etiquetaHtml, { activo = false, deshabilitado = false, onClick = null } = {}) {
                const li = document.createElement('li');
                li.className = `page-item ${activo ? 'active' : ''} ${deshabilitado ? 'disabled' : ''}`;
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.innerHTML = etiquetaHtml;
                if (!deshabilitado && onClick) {
                    a.addEventListener('click', e => { e.preventDefault(); onClick(); });
                }
                li.appendChild(a);
                return li;
            }

            function render(pagina) {
                filas.forEach((fila, i) => {
                    fila.style.display =
                        (i >= (pagina - 1) * perPage && i < pagina * perPage) ? '' : 'none';
                });
                const desde = (pagina - 1) * perPage + 1;
                const hasta = Math.min(pagina * perPage, total);
                if (info) info.textContent = `Mostrando ${desde} a ${hasta} de ${total} insumos`;

                if (!nav) return;
                nav.innerHTML = '';

                nav.appendChild(crearItem('&laquo;', {
                    deshabilitado: pagina === 1,
                    onClick: () => render(pagina - 1)
                }));

                const paginasVisibles = calcularRangoPaginas(pagina);
                let anterior = 0;
                paginasVisibles.forEach(p => {
                    if (p - anterior > 1) {
                        nav.appendChild(crearItem('…', { deshabilitado: true }));
                    }
                    nav.appendChild(crearItem(String(p), {
                        activo: p === pagina,
                        onClick: () => render(p)
                    }));
                    anterior = p;
                });

                nav.appendChild(crearItem('&raquo;', {
                    deshabilitado: pagina === totalPaginas,
                    onClick: () => render(pagina + 1)
                }));
            }

            render(1);
        });
    }

    inicializarPaginacionInsumos();

    // ─────────────────────────────────────────────────────────
    // LÓGICA: CONFIRMACIÓN SweetAlert2 AL ALTERNAR STATUS
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.btn-toggle-status');
        if (!toggleBtn) return;

        e.preventDefault();
        const url    = toggleBtn.getAttribute('data-url');
        const nombre = toggleBtn.getAttribute('data-nombre');
        const activo = parseInt(toggleBtn.getAttribute('data-activo'));

        const accion         = activo === 1 ? 'desactivar' : 'activar';
        const iconType       = activo === 1 ? 'warning' : 'question';
        const confirmBtnText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';

        const doRequest = () => {
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonColor: '#2b6cb0'
                        }).then(() => {
                            recargarTabla();
                        });
                    } else {
                        alert(data.mensaje);
                        recargarTabla();
                    }
                }
            })
            .catch(err => {
                console.error('Error:', err);
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title:             `¿Desea ${accion} el almacén de la subárea?`,
                text:              `El almacén "${nombre}" cambiará de estado.`,
                icon:              iconType,
                showCancelButton:  true,
                confirmButtonColor: '#2b6cb0',
                cancelButtonColor:  '#d33',
                confirmButtonText: confirmBtnText,
                cancelButtonText:  'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) doRequest();
            });
        } else {
            if (confirm(`¿Desea ${accion} el almacén "${nombre}"?`)) doRequest();
        }
    });

    // Reabrir modal en caso de rebote con errores
    const pageErrors = document.getElementById('hasFormErrors');
    if (pageErrors && pageErrors.dataset.errors === '1') {
        const modalEl = document.getElementById('modalRegistrarAlmacen');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

});

