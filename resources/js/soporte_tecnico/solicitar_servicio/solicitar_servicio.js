/**
 * SOLICITAR SERVICIO — Módulo de Soporte Técnico
 * Vanilla JS + AJAX desacoplado para el estándar HGL
 */

const Swal = window.Swal || {
    fire: (opts) => alert(opts.text || opts.title || '')
};

// ─── Estado del módulo ────────────────────────────────────────────────────────
let paginaActualSeguimiento = 1;
let paginaActualHistorial   = 1;
let buscarSeguimiento       = '';
let buscarHistorial         = '';
let debounceTimer           = null;

// ─── Bootstrap modal helper ───────────────────────────────────────────────────
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

// ─── VISTA INDEX: generar solicitud ──────────────────────────────────────────
function initIndex() {
    document.querySelectorAll('.btn-generar-solicitud').forEach(btn => {
        btn.addEventListener('click', function () {
            const idArea     = this.dataset.idArea;
            const nombreArea = this.dataset.nombreArea;

            const inputIdArea = document.getElementById('modal-id-area');
            const labelArea   = document.getElementById('modal-nombre-area');
            const form        = document.getElementById('form-solicitud');

            if (inputIdArea) inputIdArea.value = idArea;
            if (labelArea) labelArea.textContent = nombreArea;
            if (form) form.reset();

            abrirModal('modal-nueva-solicitud');
            setTimeout(() => {
                const desc = document.getElementById('input-descripcion');
                if (desc) desc.focus();
            }, 400);
        });
    });

    const formSolicitud = document.getElementById('form-solicitud');
    if (formSolicitud) {
        formSolicitud.addEventListener('submit', function (e) {
            e.preventDefault();
            guardarSolicitud(this);
        });
    }
}

function guardarSolicitud(form) {
    const desc = form.querySelector('[name="descripcion"]')?.value.trim();
    if (!desc || desc.length < 10) {
        Swal.fire({
            icon: 'warning',
            title: 'Descripción muy corta',
            text: 'Por favor detalla el problema con al menos 10 caracteres.',
            confirmButtonColor: '#000'
        });
        return;
    }

    const data    = new FormData(form);
    const btnSend = form.querySelector('[type="submit"]');

    if (btnSend) {
        btnSend.disabled = true;
        btnSend.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Enviando...';
    }

    const storeUrl = (window.routes && window.routes.store) ? window.routes.store : '/solicitar-servicio/guardar';

    fetch(storeUrl, {
        method : 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            cerrarModal('modal-nueva-solicitud');
            Swal.fire({
                icon : 'success',
                title: '¡Solicitud generada!',
                text : res.message,
                confirmButtonColor: '#000',
            }).then(() => {
                window.location.href = (window.routes && window.routes.seguimiento) ? window.routes.seguimiento : '/solicitar-servicio/seguimiento';
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.error ?? (res.message ?? 'No se pudo generar la solicitud.'), confirmButtonColor: '#000' });
        }
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Revisa tu conexión e intenta de nuevo.', confirmButtonColor: '#000' }))
    .finally(() => {
        if (btnSend) {
            btnSend.disabled    = false;
            btnSend.innerHTML   = '<i class="fa fa-paper-plane me-1"></i> Generar Solicitud';
        }
    });
}

// ─── VISTA SEGUIMIENTO ────────────────────────────────────────────────────────
function initSeguimiento() {
    cargarSeguimiento(1);

    const buscador = document.querySelector('[data-rol="buscar"]');
    if (buscador) {
        buscador.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                buscarSeguimiento       = this.value.trim();
                paginaActualSeguimiento = 1;
                cargarSeguimiento(1);
            }, 320);
        });
    }
}

function cargarSeguimiento(pagina) {
    paginaActualSeguimiento = pagina;
    const baseUrl = (window.routes && window.routes.seguimiento) ? window.routes.seguimiento : '/solicitar-servicio/seguimiento';
    const url     = `${baseUrl}?buscar=${encodeURIComponent(buscarSeguimiento)}&page=${pagina}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(res => {
        const contenedor = document.getElementById('contenedor-tabla');
        if (contenedor) contenedor.innerHTML = res.html;

        const paginador = document.getElementById('paginador-seguimiento');
        if (paginador && res.links) {
            paginador.innerHTML = res.links;
            paginador.querySelectorAll('a.page-link').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const urlParams = new URLSearchParams(this.getAttribute('href').split('?')[1]);
                    const p = urlParams.get('page');
                    if (p) cargarSeguimiento(p);
                });
            });
        }

        const info = document.getElementById('info-registros');
        if (info) info.textContent = res.info ?? '';

        bindSeguimientoActions();
    })
    .catch(() => {
        const contenedor = document.getElementById('contenedor-tabla');
        if (contenedor) {
            contenedor.innerHTML = '<p class="text-center text-danger py-3">Error al cargar los datos.</p>';
        }
    });
}

function bindSeguimientoActions() {
    document.querySelectorAll('[data-accion="ver-detalle"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            verDetalle(id);
        });
    });

    document.querySelectorAll('[data-accion="liberar"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            liberarServicio(id);
        });
    });

    document.querySelectorAll('[data-accion="cancelar"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            cancelarServicio(id);
        });
    });
}

// ─── VISTA HISTORIAL ──────────────────────────────────────────────────────────
function initHistorial() {
    cargarHistorial(1);

    const buscador = document.querySelector('[data-rol="buscar"]');
    if (buscador) {
        buscador.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                buscarHistorial       = this.value.trim();
                paginaActualHistorial = 1;
                cargarHistorial(1);
            }, 320);
        });
    }
}

function cargarHistorial(pagina) {
    paginaActualHistorial = pagina;
    const baseUrl = (window.routes && window.routes.historial) ? window.routes.historial : '/solicitar-servicio/historial';
    const url     = `${baseUrl}?buscar=${encodeURIComponent(buscarHistorial)}&page=${pagina}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(res => {
        const contenedor = document.getElementById('contenedor-tabla');
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

        const info = document.getElementById('info-registros');
        if (info) info.textContent = res.info ?? '';

        bindHistorialActions();
    })
    .catch(() => {
        const contenedor = document.getElementById('contenedor-tabla');
        if (contenedor) {
            contenedor.innerHTML = '<p class="text-center text-danger py-3">Error al cargar el historial.</p>';
        }
    });
}

function bindHistorialActions() {
    document.querySelectorAll('[data-accion="ver-detalle"]').forEach(btn => {
        btn.addEventListener('click', function () {
            verDetalle(this.dataset.id);
        });
    });
}

// ─── DETALLE VÍA AJAX ─────────────────────────────────────────────────────────
function verDetalle(id) {
    const url = `/solicitar-servicio/${id}/detalles`;

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        setTexto('modal-det-folio',       '#' + d.id);
        setTexto('modal-det-area',        d.area);
        setTexto('modal-det-descripcion', d.descripcion);
        setTexto('modal-det-fecha-pet',   d.fecha_peticion + ' ' + d.hora_peticion);
        setTexto('modal-det-estatus',     d.estatus_final);

        setTexto('modal-det-servidor',    d.nombre_servidor);
        setTexto('modal-det-ext',         'Ext: ' + (d.ext_servidor || '—'));
        setTexto('modal-det-fecha-tom',   d.fecha_tomado !== '—' ? d.fecha_tomado + ' ' + d.hora_tomado : 'Aún no tomado');

        setTexto('modal-det-equipo',      d.inventario ? (d.inventario + ' (' + (d.descripcion_mobiliario || 'Sin detalles') + ')') : 'Sin equipo específico');
        setTexto('modal-det-fecha-ter',   d.fecha_termino !== '—' ? d.fecha_termino + ' ' + d.hora_termino : 'Pendiente');
        setTexto('modal-det-tipo',        d.tipo_servicio || 'Soporte General');
        setTexto('modal-det-accion',      d.accion_realizada || 'En espera de resolución técnica.');

        actualizarIndicador('ind-pendiente', d.pendiente);
        actualizarIndicador('ind-proceso',   d.proceso);
        actualizarIndicador('ind-terminado', d.terminado);
        actualizarIndicador('ind-liberado',  d.liberado);

        const btnPrint = document.getElementById('modal-btn-imprimir-hoja');
        if (btnPrint) {
            btnPrint.href = `/tomar-servicios/hoja-servicio/${d.id}`;
        }

        abrirModal('modal-detalle-servicio');
    })
    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el detalle del servicio.', confirmButtonColor: '#000' }));
}

function setTexto(id, texto) {
    const el = document.getElementById(id);
    if (el) el.textContent = texto ?? '—';
}

function actualizarIndicador(id, activo) {
    const el = document.getElementById(id);
    if (!el) return;
    el.className = activo ? 'fw-bold text-success' : 'fw-bold text-muted';
    el.textContent = activo ? '✓' : '○';
}

// ─── LIBERAR SERVICIO ─────────────────────────────────────────────────────────
function liberarServicio(id) {
    Swal.fire({
        title            : 'Liberar Servicio',
        text             : '¿Confirmas que el servicio fue resuelto satisfactoriamente y deseas liberarlo?',
        icon             : 'question',
        showCancelButton : true,
        confirmButtonColor: '#198754',
        cancelButtonColor : '#6c757d',
        confirmButtonText : 'Sí, liberar de conformidad',
        cancelButtonText  : 'Cancelar',
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`/solicitar-servicio/${id}/liberar`, {
            method : 'POST',
            headers: {
                'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type'    : 'application/json',
            },
            body: JSON.stringify({ _method: 'POST' }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon : 'success',
                    title: '¡Servicio Liberado!',
                    text : res.message,
                    confirmButtonColor: '#000',
                }).then(() => cargarSeguimiento(paginaActualSeguimiento));
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.error ?? 'No se pudo liberar el servicio.', confirmButtonColor: '#000' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#000' }));
    });
}

// ─── CANCELAR SERVICIO ────────────────────────────────────────────────────────
function cancelarServicio(id) {
    Swal.fire({
        title             : 'Cancelar Solicitud #' + id,
        text              : 'Ingresa el motivo de cancelación de esta solicitud:',
        input             : 'text',
        inputPlaceholder  : 'Ej. Se solucionó internamente / Ya no se requiere',
        icon              : 'warning',
        showCancelButton  : true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor : '#6c757d',
        confirmButtonText : 'Sí, cancelar solicitud',
        cancelButtonText  : 'Volver',
        inputValidator    : (value) => {
            if (!value || value.trim().length < 4) {
                return 'Debes escribir un motivo de al menos 4 caracteres.';
            }
        }
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`/solicitar-servicio/${id}/cancelar`, {
            method : 'POST',
            headers: {
                'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type'    : 'application/json',
            },
            body: JSON.stringify({ motivo: result.value }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon : 'success',
                    title: 'Cancelada',
                    text : res.message,
                    confirmButtonColor: '#000',
                }).then(() => cargarSeguimiento(paginaActualSeguimiento));
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.error ?? 'No se pudo cancelar la solicitud.', confirmButtonColor: '#000' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#000' }));
    });
}

// ─── GRÁFICAS Chart.js ────────────────────────────────────────────────────────
function initGraficas() {
    if (typeof Chart === 'undefined') return;

    const ctxEstados = document.getElementById('chart-estados');
    if (ctxEstados) {
        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Liberados', 'Cancelados'],
                datasets: [{
                    data: [
                        parseInt(ctxEstados.dataset.activos   ?? 0),
                        parseInt(ctxEstados.dataset.liberados ?? 0),
                        parseInt(ctxEstados.dataset.cancelados ?? 0),
                    ],
                    backgroundColor: ['#ffc107', '#0d6efd', '#dc3545'],
                    borderColor    : '#fff',
                    borderWidth    : 2,
                }],
            },
            options: {
                responsive: true,
                cutout    : '65%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed} servicios`
                        }
                    }
                }
            }
        });
    }

    const ctxArea = document.getElementById('chart-por-area');
    if (ctxArea) {
        const labels = JSON.parse(ctxArea.dataset.labels ?? '[]');
        const values = JSON.parse(ctxArea.dataset.values ?? '[]');
        new Chart(ctxArea, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label          : 'Servicios solicitados',
                    data           : values,
                    backgroundColor: '#000',
                    borderRadius   : 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales : {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    const ctxMes = document.getElementById('chart-por-mes');
    if (ctxMes) {
        const labels = JSON.parse(ctxMes.dataset.labels ?? '[]');
        const values = JSON.parse(ctxMes.dataset.values ?? '[]');
        new Chart(ctxMes, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label          : 'Servicios por mes',
                    data           : values,
                    fill           : true,
                    tension        : 0.4,
                    borderColor    : '#000',
                    backgroundColor: 'rgba(0,0,0,0.08)',
                    pointBackgroundColor: '#000',
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales : {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }
}

// ─── INICIALIZACIÓN AUTOMÁTICA SEGÚN VISTA ACTIVA ─────────────────────────────
function initModulo() {
    if (document.getElementById('modulo-solicitar-servicio')) {
        initIndex();
    }
    if (document.getElementById('modulo-seguimiento-servicio')) {
        initSeguimiento();
    }
    if (document.getElementById('modulo-historial-servicio')) {
        initHistorial();
    }
    if (document.getElementById('modulo-graficas-servicio')) {
        initGraficas();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModulo);
} else {
    initModulo();
}
