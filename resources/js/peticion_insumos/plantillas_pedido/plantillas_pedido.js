document.addEventListener('DOMContentLoaded', function () {

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ─────────────────────────────────────────────────────────
    // LÓGICA: ABRIR MODAL EDITAR PLANTILLA
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-editar-plantilla');
        if (!btn) return;

        const id          = btn.getAttribute('data-id');
        const nombre      = btn.getAttribute('data-nombre');
        const descripcion = btn.getAttribute('data-descripcion') || '';
        const idArea      = btn.getAttribute('data-area');
        const idSubarea   = btn.getAttribute('data-subarea');
        const idAlmacen   = btn.getAttribute('data-almacen');

        const form          = document.getElementById('formEditarPlantilla');
        const selectArea    = document.getElementById('edit_id_area');
        const selectSubarea = document.getElementById('edit_id_subarea');
        const selectAlmacen = document.getElementById('edit_id_area_almacen');

        if (!form) return;

        // Actualizar action del form con el ID correcto
        form.action = `/peticion-insumos/plantillas-pedido/${id}`;

        // Poblar campos
        document.getElementById('edit_nombre').value      = nombre;
        document.getElementById('edit_descripcion').value = descripcion;

        if (selectAlmacen) {
            selectAlmacen.value = idAlmacen || '';
        }

        // Seleccionar área
        if (selectArea) {
            selectArea.value = idArea;
        }

        // Cargar subáreas y luego seleccionar la correcta
        if (selectSubarea && idArea) {
            selectSubarea.innerHTML = '<option value="">Cargando...</option>';
            fetch(`/peticion-insumos/plantillas-pedido/subareas-por-area?id_area_abastecimiento=${idArea}`, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                selectSubarea.innerHTML = '<option value="">-- Sin subárea --</option>';
                data.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id_subarea_abastecimiento;
                    opt.textContent = sub.nombre;
                    if (String(sub.id_subarea_abastecimiento) === String(idSubarea)) {
                        opt.selected = true;
                    }
                    selectSubarea.appendChild(opt);
                });
            })
            .catch(() => {
                selectSubarea.innerHTML = '<option value="">-- Sin subárea --</option>';
            });
        } else if (selectSubarea) {
            selectSubarea.innerHTML = '<option value="">-- Sin subárea --</option>';
        }

        // Cascade al cambiar área dentro del modal editar
        selectArea?.addEventListener('change', function onEditAreaChange() {
            if (!selectSubarea) return;
            selectSubarea.innerHTML = '<option value="">Cargando...</option>';
            fetch(`/peticion-insumos/plantillas-pedido/subareas-por-area?id_area_abastecimiento=${this.value}`, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                selectSubarea.innerHTML = '<option value="">-- Sin subárea --</option>';
                data.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id_subarea_abastecimiento;
                    opt.textContent = sub.nombre;
                    selectSubarea.appendChild(opt);
                });
            });
        }, { once: true });

        const modalEl = document.getElementById('modalEditarPlantilla');
        if (modalEl && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(modalEl).show();
        }
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: ELIMINAR PLANTILLA COMPLETA (AJAX)
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-eliminar-plantilla');
        if (!btn) return;

        const id     = btn.getAttribute('data-id');
        const nombre = btn.getAttribute('data-nombre');

        const doDelete = () => {
            fetch(`/peticion-insumos/plantillas-pedido/${id}`, {
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
                        Swal.fire('¡Eliminada!', data.mensaje, 'success').then(() => recargarTabla());
                    } else {
                        recargarTabla();
                    }
                }
            })
            .catch(err => console.error('Error al eliminar plantilla:', err));
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar plantilla?',
                text: `La plantilla "${nombre}" y todos sus insumos asignados serán eliminados.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) doDelete(); });
        } else {
            if (confirm(`¿Eliminar la plantilla "${nombre}"?`)) doDelete();
        }
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: ASIGNAR INSUMO A PLANTILLA (AJAX)
    // ─────────────────────────────────────────────────────────
    let idPlantillaActual = null;

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-agregar-insumo');
        if (!btn) return;
        e.preventDefault();

        idPlantillaActual = btn.getAttribute('data-id');
        const nombre = btn.getAttribute('data-nombre') || '';

        const spanNombre = document.getElementById('nombrePlantillaModal');
        if (spanNombre) spanNombre.textContent = nombre;

        const modalEl = document.getElementById('modalAgregarInsumo');
        if (modalEl && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(modalEl).show();
        }
    });

    const formAgregar = document.getElementById('formAgregarInsumo');
    if (formAgregar) {
        formAgregar.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!idPlantillaActual) return;

            const idInsumo  = document.getElementById('id_insumo').value;
            const cantidad  = document.getElementById('cantidad').value;

            fetch(`/peticion-insumos/plantillas-pedido/${idPlantillaActual}/insumo`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id_insumo: idInsumo, cantidad: cantidad })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('modalAgregarInsumo');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    formAgregar.reset();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Asignado!',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonColor: '#2b6cb0'
                        }).then(() => recargarTabla());
                    } else {
                        recargarTabla();
                    }
                }
            })
            .catch(err => console.error('Error al asignar insumo:', err));
        });
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: EDITAR CANTIDAD DE DETALLE (AJAX inline)
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-guardar-detalle');
        if (!btn) return;
        e.preventDefault();

        const idDetalle = btn.getAttribute('data-id');
        const fila      = btn.closest('tr');
        const cantidad  = fila?.querySelector('.input-cantidad')?.value ?? 0;

        fetch(`/peticion-insumos/plantillas-pedido/detalle/${idDetalle}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ cantidad: cantidad })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: data.mensaje,
                        icon: 'success',
                        confirmButtonColor: '#2b6cb0',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => recargarTabla());
                } else {
                    recargarTabla();
                }
            }
        })
        .catch(err => console.error('Error al actualizar detalle:', err));
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: ELIMINAR DETALLE DE PLANTILLA (AJAX)
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-eliminar-detalle');
        if (!btn) return;
        e.preventDefault();

        const idDetalle = btn.getAttribute('data-id');

        const doDelete = () => {
            fetch(`/peticion-insumos/plantillas-pedido/detalle/${idDetalle}`, {
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
                        Swal.fire('¡Eliminado!', data.mensaje, 'success').then(() => recargarTabla());
                    } else {
                        recargarTabla();
                    }
                }
            })
            .catch(err => console.error('Error al eliminar detalle:', err));
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Quitar insumo de la plantilla?',
                text: 'El insumo dejará de pertenecer a esta plantilla de pedido.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) doDelete(); });
        } else {
            if (confirm('¿Está seguro de quitar este insumo?')) doDelete();
        }
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: COMBO EN CASCADA EN MODAL CREAR PLANTILLA
    // ─────────────────────────────────────────────────────────
    const modalAreaSelect = document.getElementById('modal_id_area_abastecimiento');
    const modalSubareaSelect = document.getElementById('modal_id_subarea_abastecimiento');

    if (modalAreaSelect && modalSubareaSelect) {
        modalAreaSelect.addEventListener('change', function () {
            const areaId = this.value;
            modalSubareaSelect.innerHTML = '<option value="">-- Sin subárea específica --</option>';

            if (!areaId) return;

            fetch(`/peticion-insumos/plantillas-pedido/subareas-por-area?id_area_abastecimiento=${areaId}`, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                data.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id_subarea_abastecimiento;
                    opt.textContent = sub.nombre;
                    modalSubareaSelect.appendChild(opt);
                });
            })
            .catch(err => console.error('Error al cargar subáreas en modal:', err));
        });
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: FILTROS EN TIEMPO REAL (ÁREA, SUBÁREA, BUSCAR, ESTATUS)
    // ─────────────────────────────────────────────────────────
    const contenedor   = document.getElementById('contenedor-tabla-plantillas');
    const selectArea    = document.getElementById('filter-area');
    const selectSubarea = document.getElementById('filter-subarea');
    const inputBuscar   = document.getElementById('buscar-plantilla');
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

        controladorActual?.abort();
        controladorActual = new AbortController();
        const idPeticion = ++peticionActual;

        contenedor.style.opacity = '0.5';
        contenedor.style.transition = 'opacity 0.15s';

        fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            signal: controladorActual.signal
        })
            .then(r => r.json())
            .then(data => {
                if (idPeticion !== peticionActual) return;
                contenedor.innerHTML = data.html;
                contenedor.style.opacity = '1';
                const nuevaUrl = params.toString() ? `?${params.toString()}` : location.pathname;
                history.replaceState(null, '', nuevaUrl);
                vincularPaginacionExterna();
                inicializarPaginacionInsumos(contenedor);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                console.error('Error al recargar tabla de plantillas:', err);
                if (idPeticion === peticionActual) contenedor.style.opacity = '1';
            });
    }

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

        fetch(`/peticion-insumos/plantillas-pedido/subareas-por-area?id_area_abastecimiento=${idArea}`, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => {
                if (!r.ok) {
                    return r.text().then(txt => {
                        console.error(`[cargarSubareas] HTTP ${r.status}:`, txt.substring(0, 300));
                        throw new Error(`HTTP ${r.status}`);
                    });
                }
                return r.json();
            })
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
            .catch((err) => {
                console.error('[cargarSubareas] Fetch falló:', err);
                selectSubarea.innerHTML = '<option value="">-- Error al cargar --</option>';
                selectSubarea.disabled = false;
            });
    };

    selectArea?.addEventListener('change', function () {
        if (this.value) {
            cargarSubareas(this.value);
        } else if (selectSubarea) {
            selectSubarea.innerHTML = '<option value="">-- Todas las Subáreas --</option>';
            selectSubarea.disabled = false;
        }
        recargarTabla();
    });

    selectSubarea?.addEventListener('change', () => recargarTabla());

    inputBuscar?.addEventListener('input', () => {
        clearTimeout(debounceBuscar);
        debounceBuscar = setTimeout(() => recargarTabla(), 350);
    });

    checksStatus.forEach(chk => chk.addEventListener('change', () => recargarTabla()));

    vincularPaginacionExterna();

    // ─────────────────────────────────────────────────────────
    // LÓGICA: PAGINACIÓN INTERNA DE INSUMOS (POR TARJETA)
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

            function calcularRangoPaginas(pagina) {
                const rango = new Set([1, totalPaginas, pagina, pagina - 1, pagina + 1]);
                return [...rango].filter(p => p >= 1 && p <= totalPaginas).sort((a, b) => a - b);
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
    // LÓGICA: ALTERNAR ESTADO VIA AJAX
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
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonColor: '#2b6cb0'
                        }).then(() => recargarTabla());
                    } else {
                        recargarTabla();
                    }
                }
            })
            .catch(err => console.error('Error:', err));
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `¿Desea ${accion} la plantilla?`,
                text: `La plantilla "${nombre}" cambiará de estado.`,
                icon: iconType,
                showCancelButton: true,
                confirmButtonColor: '#2b6cb0',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) doRequest(); });
        } else {
            if (confirm(`¿Desea ${accion} la plantilla "${nombre}"?`)) doRequest();
        }
    });
});
