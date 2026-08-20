/**
 * TOMAR SERVICIOS — Módulo de Soporte Técnico (Técnicos / Administradores)
 * Vanilla JS + AJAX desacoplado para el estándar HGL
 */

const Swal = window.Swal || {
    fire: (opts) => alert(opts.text || opts.title || '')
};

let paginaActual = 1;
let debounceTimer = null;
let buscarTerm = '';
let currentAreaFiltro = '';
let currentEstadoFiltro = '';
let currentAreaEquipos = [];

function abrirModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const m = bootstrap.Modal.getOrCreateInstance(el);
    m.show();
}

function cerrarModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    bootstrap.Modal.getInstance(el)?.hide();
}

// ─── 1. BANDEJA DE PENDIENTES ────────────────────────────────────────────────
function initPendientes() {
    cargarPendientes(1);

    const buscador = document.querySelector('[data-rol="buscar-pendientes"]');
    if (buscador) {
        buscador.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                buscarTerm = this.value.trim();
                cargarPendientes(1);
            }, 300);
        });
    }

    const selectArea = document.getElementById('filtro-area-pendientes');
    if (selectArea) {
        selectArea.addEventListener('change', function () {
            currentAreaFiltro = this.value;
            cargarPendientes(1);
        });
    }

    const formTomar = document.getElementById('form-tomar-servicio');
    if (formTomar) {
        formTomar.addEventListener('submit', function (e) {
            e.preventDefault();
            ejecutarTomarServicio(this);
        });
    }
}

function cargarPendientes(pagina) {
    paginaActual = pagina;
    const url = `/tomar-servicios?buscar=${encodeURIComponent(buscarTerm)}&id_area=${encodeURIComponent(currentAreaFiltro)}&page=${pagina}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(res => {
        const contenedor = document.getElementById('contenedor-tabla-pendientes');
        if (contenedor) contenedor.innerHTML = res.html;

        const paginador = document.getElementById('paginador-pendientes');
        if (paginador && res.links) {
            paginador.innerHTML = res.links;
            paginador.querySelectorAll('a.page-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const urlParams = new URLSearchParams(this.getAttribute('href').split('?')[1]);
                    const p = urlParams.get('page');
                    if (p) cargarPendientes(p);
                });
            });
        }

        const info = document.getElementById('info-registros-pendientes');
        if (info) info.textContent = res.info ?? '';

        bindPendientesActions();
    })
    .catch(() => {
        const contenedor = document.getElementById('contenedor-tabla-pendientes');
        if (contenedor) contenedor.innerHTML = '<p class="text-center text-danger py-4">Error al cargar las solicitudes pendientes.</p>';
    });
}

function bindPendientesActions() {
    document.querySelectorAll('[data-accion="abrir-tomar"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const folio = this.dataset.folio || id;
            const solicitante = this.dataset.solicitante;
            const area = this.dataset.area;
            const desc = this.dataset.descripcion;

            document.getElementById('tomar-id-servicio').value = id;
            document.getElementById('tomar-label-folio').textContent = '#' + folio;
            document.getElementById('tomar-label-solicitante').textContent = solicitante;
            document.getElementById('tomar-label-area').textContent = area;
            document.getElementById('tomar-label-desc').textContent = desc;

            abrirModal('modal-tomar-servicio');
        });
    });
}

function ejecutarTomarServicio(form) {
    const id = document.getElementById('tomar-id-servicio').value;
    const data = new FormData(form);
    const btn = form.querySelector('[type="submit"]');

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Asignando...';
    }

    fetch(`/tomar-servicios/${id}/tomar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            cerrarModal('modal-tomar-servicio');
            Swal.fire({
                icon: 'success',
                title: '¡Servicio Asignado!',
                text: res.message,
                confirmButtonColor: '#000',
            }).then(() => {
                window.location.href = '/tomar-servicios/mis-servicios';
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.error ?? 'No se pudo tomar el servicio.', confirmButtonColor: '#000' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#000' }))
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check me-1"></i> Confirmar y Tomar Servicio';
        }
    });
}

// ─── 2. MIS SERVICIOS EN PROCESO ─────────────────────────────────────────────
function initEnProceso() {
    cargarEnProceso(1);

    const buscador = document.querySelector('[data-rol="buscar-proceso"]');
    if (buscador) {
        buscador.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                buscarTerm = this.value.trim();
                cargarEnProceso(1);
            }, 300);
        });
    }

    // Toggle botón de inventario
    const btnToggleInv = document.getElementById('btn-toggle-inventario-panel');
    if (btnToggleInv) {
        btnToggleInv.addEventListener('click', function () {
            const panel = document.getElementById('panel-buscar-inventario');
            const txt = document.getElementById('btn-toggle-inventario-text');
            if (panel) {
                const isHidden = panel.classList.contains('d-none');
                if (isHidden) {
                    panel.classList.remove('d-none');
                    if (txt) txt.textContent = 'Ocultar Inventario';
                    const inpSearch = document.getElementById('input-buscar-equipo-inline');
                    if (inpSearch) inpSearch.focus();
                } else {
                    panel.classList.add('d-none');
                    if (txt) txt.textContent = 'Seleccionar del Inventario';
                }
            }
        });
    }

    // Input de búsqueda en vivo de inventario
    const inputSearchInv = document.getElementById('input-buscar-equipo-inline');
    if (inputSearchInv) {
        inputSearchInv.addEventListener('input', function () {
            renderTablaInventario(this.value.trim());
        });
    }

    // Botón para quitar equipo seleccionado
    const btnQuitarEq = document.getElementById('btn-quitar-equipo');
    if (btnQuitarEq) {
        btnQuitarEq.addEventListener('click', function () {
            deseleccionarEquipo();
        });
    }

    const formConcluir = document.getElementById('form-concluir-servicio');
    if (formConcluir) {
        formConcluir.addEventListener('submit', function (e) {
            e.preventDefault();
            ejecutarConcluirServicio(this);
        });
    }

    const formAjustar = document.getElementById('form-ajustar-fechas');
    if (formAjustar) {
        formAjustar.addEventListener('submit', function (e) {
            e.preventDefault();
            ejecutarAjustarFechas(this);
        });
    }
}

function cargarEnProceso(pagina) {
    paginaActual = pagina;
    const url = `/tomar-servicios/mis-servicios?buscar=${encodeURIComponent(buscarTerm)}&page=${pagina}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(res => {
        const contenedor = document.getElementById('contenedor-tabla-proceso');
        if (contenedor) contenedor.innerHTML = res.html;

        const paginador = document.getElementById('paginador-proceso');
        if (paginador && res.links) {
            paginador.innerHTML = res.links;
            paginador.querySelectorAll('a.page-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const urlParams = new URLSearchParams(this.getAttribute('href').split('?')[1]);
                    const p = urlParams.get('page');
                    if (p) cargarEnProceso(p);
                });
            });
        }

        const info = document.getElementById('info-registros-proceso');
        if (info) info.textContent = res.info ?? '';

        bindEnProcesoActions();
    })
    .catch(() => {
        const contenedor = document.getElementById('contenedor-tabla-proceso');
        if (contenedor) contenedor.innerHTML = '<p class="text-center text-danger py-4">Error al cargar los servicios en proceso.</p>';
    });
}

function bindEnProcesoActions() {
    // Botón Concluir
    document.querySelectorAll('[data-accion="abrir-concluir"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const idArea = this.dataset.idArea;
            const folio = this.dataset.folio || id;
            const solicitante = this.dataset.solicitante;
            const desc = this.dataset.descripcion;

            document.getElementById('concluir-id-servicio').value = id;
            document.getElementById('concluir-label-folio').textContent = '#' + folio;
            document.getElementById('concluir-label-solicitante').textContent = solicitante;
            document.getElementById('concluir-label-desc').textContent = desc;

            // Reset selector de equipo
            deseleccionarEquipo();

            // Cargar inventario del área por AJAX
            cargarMobiliarioArea(idArea);

            abrirModal('modal-concluir-servicio');
        });
    });

    // Botón Reasignar
    document.querySelectorAll('[data-accion="reasignar"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            reasignarServicio(id);
        });
    });

    // Botón Ajustar Fechas
    document.querySelectorAll('[data-accion="abrir-ajustar-fechas"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            document.getElementById('ajustar-id-servicio').value = id;
            document.getElementById('ajustar-label-folio').textContent = '#' + id;
            document.getElementById('ajustar-fecha-pet').value = this.dataset.fechaPet || '';
            document.getElementById('ajustar-hora-pet').value = this.dataset.horaPet || '';
            document.getElementById('ajustar-fecha-tom').value = this.dataset.fechaTom || '';
            document.getElementById('ajustar-hora-tom').value = this.dataset.horaTom || '';
            document.getElementById('ajustar-motivo').value = '';

            abrirModal('modal-ajustar-fechas');
        });
    });
}

function cargarMobiliarioArea(idArea) {
    const tbody = document.getElementById('tbody-inventario-modal');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin me-1"></i> Cargando inventario del área...</td></tr>';
    }

    currentAreaEquipos = [];

    fetch(`/tomar-servicios/mobiliario-area/${idArea}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(items => {
        currentAreaEquipos = items;
        renderTablaInventario('');
    })
    .catch(() => {
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-danger">Error al cargar inventario del área.</td></tr>';
        }
    });
}

function renderTablaInventario(filtro) {
    const tbody = document.getElementById('tbody-inventario-modal');
    if (!tbody) return;

    const f = filtro.toLowerCase();
    const items = currentAreaEquipos.filter(it => {
        if (!f) return true;
        return (it.inventario && it.inventario.toLowerCase().includes(f)) ||
               (it.tipo && it.tipo.toLowerCase().includes(f)) ||
               (it.descripcion && it.descripcion.toLowerCase().includes(f)) ||
               (it.marca && it.marca.toLowerCase().includes(f)) ||
               (it.modelo && it.modelo.toLowerCase().includes(f)) ||
               (it.responsable && it.responsable.toLowerCase().includes(f));
    });

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted">No se encontraron equipos en esta área con el filtro indicado.</td></tr>';
        return;
    }

    let html = '';
    items.forEach(it => {
        html += `
        <tr class="item-inventario-row" data-id="${it.id}">
            <td class="ps-2 fw-bold text-dark"><span class="badge bg-light text-dark border">${it.inventario}</span></td>
            <td>
                <span class="fw-semibold text-primary d-block">${it.tipo}</span>
                <small class="text-muted text-truncate d-inline-block" style="max-width:200px;">${it.descripcion}</small>
            </td>
            <td>
                <small class="d-block fw-semibold text-dark">${it.marca} ${it.modelo}</small>
                <small class="text-muted" style="font-size:0.75rem;">S/N: ${it.serie}</small>
            </td>
            <td>
                <small class="text-muted d-block text-truncate" style="max-width:140px;">${it.responsable}</small>
            </td>
            <td class="text-center pe-2">
                <button type="button" class="btn btn-xs btn-outline-success btn-seleccionar-equipo"
                        data-id="${it.id}"
                        data-inv="${it.inventario}"
                        data-tipo="${it.tipo}"
                        data-desc="${it.descripcion}"
                        data-marca="${it.marca}"
                        data-modelo="${it.modelo}"
                        data-resp="${it.responsable}">
                    <i class="fa fa-check"></i> Elegir
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;

    // Vincular clic en seleccionar
    tbody.querySelectorAll('.btn-seleccionar-equipo').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            seleccionarEquipo({
                id: this.dataset.id,
                inventario: this.dataset.inv,
                tipo: this.dataset.tipo,
                descripcion: this.dataset.desc,
                marca: this.dataset.marca,
                modelo: this.dataset.modelo,
                responsable: this.dataset.resp,
            });
        });
    });
}

function seleccionarEquipo(item) {
    document.getElementById('concluir-input-id-mobiliario').value = item.id;

    const badge = document.getElementById('badge-equipo-inv');
    const txtDesc = document.getElementById('txt-equipo-desc');
    const txtDet = document.getElementById('txt-equipo-detalles');
    const btnQuitar = document.getElementById('btn-quitar-equipo');
    const panel = document.getElementById('panel-buscar-inventario');
    const txtToggle = document.getElementById('btn-toggle-inventario-text');

    if (badge) {
        badge.className = 'badge bg-success px-2 py-1';
        badge.textContent = 'Inv: ' + item.inventario;
    }
    if (txtDesc) {
        txtDesc.textContent = `${item.tipo} - ${item.descripcion}`;
    }
    if (txtDet) {
        txtDet.textContent = `${item.marca} ${item.modelo} | Resp: ${item.responsable}`;
    }
    if (btnQuitar) {
        btnQuitar.classList.remove('d-none');
    }

    // Ocultar panel después de elegir
    if (panel) {
        panel.classList.add('d-none');
    }
    if (txtToggle) {
        txtToggle.textContent = 'Cambiar Equipo';
    }
}

function deseleccionarEquipo() {
    document.getElementById('concluir-input-id-mobiliario').value = '';

    const badge = document.getElementById('badge-equipo-inv');
    const txtDesc = document.getElementById('txt-equipo-desc');
    const txtDet = document.getElementById('txt-equipo-detalles');
    const btnQuitar = document.getElementById('btn-quitar-equipo');
    const panel = document.getElementById('panel-buscar-inventario');
    const txtToggle = document.getElementById('btn-toggle-inventario-text');

    if (badge) {
        badge.className = 'badge bg-secondary px-2 py-1';
        badge.textContent = 'Sin equipo';
    }
    if (txtDesc) {
        txtDesc.textContent = 'Servicio general / Sin equipo específico';
    }
    if (txtDet) {
        txtDet.textContent = 'Soporte en sitio, red o software general';
    }
    if (btnQuitar) {
        btnQuitar.classList.add('d-none');
    }
    if (panel) {
        panel.classList.add('d-none');
    }
    if (txtToggle) {
        txtToggle.textContent = 'Seleccionar del Inventario';
    }
}

function ejecutarConcluirServicio(form) {
    const id = document.getElementById('concluir-id-servicio').value;
    const accion = form.querySelector('[name="accion_realizada"]')?.value.trim();

    if (!accion || accion.length < 10) {
        Swal.fire({
            icon: 'warning',
            title: 'Detalle insuficiente',
            text: 'Debes detallar la acción o solución realizada con al menos 10 caracteres.',
            confirmButtonColor: '#000'
        });
        return;
    }

    const data = new FormData(form);
    const btn = form.querySelector('[type="submit"]');

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Guardando conclusión...';
    }

    fetch(`/tomar-servicios/${id}/concluir`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            cerrarModal('modal-concluir-servicio');
            Swal.fire({
                icon: 'success',
                title: '¡Servicio Concluido!',
                text: res.message,
                confirmButtonColor: '#000',
            }).then(() => {
                window.location.href = '/tomar-servicios/por-liberar';
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.error ?? 'No se pudo concluir el servicio.', confirmButtonColor: '#000' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#000' }))
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check-circle me-1"></i> Concluir Servicio';
        }
    });
}

function reasignarServicio(id) {
    Swal.fire({
        title: 'Reasignar / Devolver Servicio #' + id,
        text: '¿Deseas devolver esta solicitud a la bandeja de pendientes para que otro técnico pueda atenderla?',
        input: 'text',
        inputPlaceholder: 'Motivo de la reasignación (opcional)',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, reasignar',
        cancelButtonText: 'Cancelar',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`/tomar-servicios/${id}/reasignar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ motivo: result.value || 'Reasignación por el técnico' }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Devuelto a Pendientes',
                    text: res.message,
                    confirmButtonColor: '#000',
                }).then(() => cargarEnProceso(paginaActual));
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.error ?? 'No se pudo reasignar.', confirmButtonColor: '#000' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#000' }));
    });
}

function ejecutarAjustarFechas(form) {
    const id = document.getElementById('ajustar-id-servicio').value;
    const motivo = form.querySelector('[name="motivo_modificado"]')?.value.trim();

    if (!motivo || motivo.length < 5) {
        Swal.fire({
            icon: 'warning',
            title: 'Motivo requerido',
            text: 'Debes justificar el cambio de fecha/hora con al menos 5 caracteres.',
            confirmButtonColor: '#000'
        });
        return;
    }

    const data = new FormData(form);
    const btn = form.querySelector('[type="submit"]');

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Guardando...';
    }

    fetch(`/tomar-servicios/${id}/ajustar-fechas`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            cerrarModal('modal-ajustar-fechas');
            Swal.fire({
                icon: 'success',
                title: 'Fechas Actualizadas',
                text: res.message,
                confirmButtonColor: '#000',
            }).then(() => cargarEnProceso(paginaActual));
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.error ?? 'No se pudo actualizar.', confirmButtonColor: '#000' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#000' }))
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save me-1"></i> Guardar Cambios';
        }
    });
}

// ─── 3. SERVICIOS POR LIBERAR ────────────────────────────────────────────────
function initPorLiberar() {
    cargarPorLiberar(1);

    const buscador = document.querySelector('[data-rol="buscar-por-liberar"]');
    if (buscador) {
        buscador.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                buscarTerm = this.value.trim();
                cargarPorLiberar(1);
            }, 300);
        });
    }
}

function cargarPorLiberar(pagina) {
    paginaActual = pagina;
    const url = `/tomar-servicios/por-liberar?buscar=${encodeURIComponent(buscarTerm)}&page=${pagina}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(res => {
        const contenedor = document.getElementById('contenedor-tabla-por-liberar');
        if (contenedor) contenedor.innerHTML = res.html;

        const paginador = document.getElementById('paginador-por-liberar');
        if (paginador && res.links) {
            paginador.innerHTML = res.links;
            paginador.querySelectorAll('a.page-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const urlParams = new URLSearchParams(this.getAttribute('href').split('?')[1]);
                    const p = urlParams.get('page');
                    if (p) cargarPorLiberar(p);
                });
            });
        }

        const info = document.getElementById('info-registros-por-liberar');
        if (info) info.textContent = res.info ?? '';

        bindPorLiberarActions();
    })
    .catch(() => {
        const contenedor = document.getElementById('contenedor-tabla-por-liberar');
        if (contenedor) contenedor.innerHTML = '<p class="text-center text-danger py-4">Error al cargar los servicios por liberar.</p>';
    });
}

function bindPorLiberarActions() {
    document.querySelectorAll('[data-accion="liberar-soporte"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            liberarPorSoporte(id);
        });
    });
}

function liberarPorSoporte(id) {
    Swal.fire({
        title: 'Liberar Servicio #' + id,
        text: '¿Confirmas la liberación y cierre de este servicio por parte de Soporte Técnico?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, liberar servicio',
        cancelButtonText: 'Cancelar',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`/tomar-servicios/${id}/liberar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ _method: 'POST' }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Servicio Liberado!',
                    text: res.message,
                    confirmButtonColor: '#000',
                }).then(() => cargarPorLiberar(paginaActual));
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.error ?? 'No se pudo liberar.', confirmButtonColor: '#000' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#000' }));
    });
}

// ─── 4. HISTORIAL GENERAL ────────────────────────────────────────────────────
function initHistorial() {
    cargarHistorial(1);

    const buscador = document.querySelector('[data-rol="buscar-historial"]');
    if (buscador) {
        buscador.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                buscarTerm = this.value.trim();
                cargarHistorial(1);
            }, 300);
        });
    }

    const selectArea = document.getElementById('filtro-area-historial');
    if (selectArea) {
        selectArea.addEventListener('change', function () {
            currentAreaFiltro = this.value;
            cargarHistorial(1);
        });
    }

    const selectEstado = document.getElementById('filtro-estado-historial');
    if (selectEstado) {
        selectEstado.addEventListener('change', function () {
            currentEstadoFiltro = this.value;
            cargarHistorial(1);
        });
    }
}

function cargarHistorial(pagina) {
    paginaActual = pagina;
    const url = `/tomar-servicios/historial?buscar=${encodeURIComponent(buscarTerm)}&id_area=${encodeURIComponent(currentAreaFiltro)}&estado=${encodeURIComponent(currentEstadoFiltro)}&page=${pagina}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(res => {
        const contenedor = document.getElementById('contenedor-tabla-historial');
        if (contenedor) contenedor.innerHTML = res.html;

        const paginador = document.getElementById('paginador-historial');
        if (paginador && res.links) {
            paginador.innerHTML = res.links;
            paginador.querySelectorAll('a.page-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const urlParams = new URLSearchParams(this.getAttribute('href').split('?')[1]);
                    const p = urlParams.get('page');
                    if (p) cargarHistorial(p);
                });
            });
        }

        const info = document.getElementById('info-registros-historial');
        if (info) info.textContent = res.info ?? '';
    })
    .catch(() => {
        const contenedor = document.getElementById('contenedor-tabla-historial');
        if (contenedor) contenedor.innerHTML = '<p class="text-center text-danger py-4">Error al cargar el historial.</p>';
    });
}

// ─── INICIALIZACIÓN AUTOMÁTICA ────────────────────────────────────────────────
function initModuloTomar() {
    if (document.getElementById('modulo-tomar-pendientes')) {
        initPendientes();
    }
    if (document.getElementById('modulo-tomar-en-proceso')) {
        initEnProceso();
    }
    if (document.getElementById('modulo-tomar-por-liberar')) {
        initPorLiberar();
    }
    if (document.getElementById('modulo-tomar-historial')) {
        initHistorial();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModuloTomar);
} else {
    initModuloTomar();
}
