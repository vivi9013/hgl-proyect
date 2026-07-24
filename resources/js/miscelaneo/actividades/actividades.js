/**
 * Registro de Actividades - Interacciones Frontend
 * Paginación, buscador con debounce, filtros y visualización de gráficos con Chart.js
 */

import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', function () {
    // ── IDENTIFICAR LA VISTA ACTUAL ──
    const esVistaListado = document.getElementById('tabla-actividades') !== null;
    const esVistaGraficas = document.getElementById('btn-generar-graficas') !== null;

    if (esVistaListado) {
        initListado();
    }

    if (esVistaGraficas) {
        initGraficas();
    }
});

/**
 * ────────────────────────────────────────────────────────
 * 1. LÓGICA DE LA VISTA DE LISTADO (TABLA AJAX)
 * ────────────────────────────────────────────────────────
 */
function initListado() {
    let paginaActual = 1;
    let buscarTerm = '';
    let filtroSeleccionado = '';
    let fechaInicio = '';
    let fechaFin = '';

    const tablaBody = document.getElementById('tabla-actividades-body');
    const totalLabel = document.getElementById('total-registros');
    const paginacionContainer = document.getElementById('paginacion-container');

    const searchInput = document.getElementById('search-input');
    const filtroSelect = document.getElementById('filtro-select');
    const fechaInicioInput = document.getElementById('fecha-inicio');
    const fechaFinInput = document.getElementById('fecha-fin');
    const btnLimpiar = document.getElementById('btn-limpiar');

    // Carga inicial
    cargarActividades(1);

    function cargarActividades(pagina = 1) {
        paginaActual = pagina;
        tablaBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `;

        const queryParams = new URLSearchParams({
            page: paginaActual,
            q: buscarTerm,
            filtro: filtroSeleccionado,
            fi: fechaInicio,
            ff: fechaFin
        });

        fetch(`/actividades?${queryParams.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            return res.json();
        })
        .then(res => {
            totalLabel.textContent = `Mostrando ${res.from || 0} a ${res.to || 0} de ${res.total || 0} registros`;

            if (!res.data || res.data.length === 0) {
                tablaBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fa fa-info-circle me-1"></i> No se encontraron registros de actividades.
                        </td>
                    </tr>
                `;
                paginacionContainer.innerHTML = '';
                return;
            }

            tablaBody.innerHTML = '';
            res.data.forEach(item => {
                const tr = document.createElement('tr');
                
                // Nombre completo del usuario
                let usuarioStr = '—';
                if (item.persona) {
                    const materno = item.persona.ap_materno ? ` ${item.persona.ap_materno}` : '';
                    usuarioStr = `${item.persona.nombre} ${item.persona.ap_paterno}${materno}`;
                }

                tr.innerHTML = `
                    <td class="text-center font-monospace-small fw-bold">${item.id_actividad}</td>
                    <td>${item.descripcion || '—'}</td>
                    <td><span class="badge bg-light text-dark border fw-semibold px-2.5 py-1.5">${item.filtro || '—'}</span></td>
                    <td class="text-center">${item.fecha || '—'}</td>
                    <td class="text-center font-monospace-small">${item.hora || '—'}</td>
                    <td class="fw-semibold text-secondary">${usuarioStr}</td>
                `;
                tablaBody.appendChild(tr);
            });

            // Invocar paginador global
            if (typeof window.renderPaginacion === 'function') {
                window.renderPaginacion(res.links, 'paginacion-container', cargarActividades);
            }
        })
        .catch(err => {
            console.error('Error al cargar actividades:', err);
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        Error al cargar registros. <small class="d-block text-muted mt-1">${err.message}</small>
                    </td>
                </tr>
            `;
            totalLabel.textContent = 'Error al cargar';
        });
    }

    // Buscador con debounce de 300ms
    const debounce = (func, wait) => {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    searchInput.addEventListener('input', debounce(function (e) {
        buscarTerm = e.target.value.trim();
        cargarActividades(1);
    }, 300));

    filtroSelect.addEventListener('change', function (e) {
        filtroSeleccionado = e.target.value;
        cargarActividades(1);
    });

    fechaInicioInput.addEventListener('change', function (e) {
        fechaInicio = e.target.value;
        cargarActividades(1);
    });

    fechaFinInput.addEventListener('change', function (e) {
        fechaFin = e.target.value;
        cargarActividades(1);
    });

    btnLimpiar.addEventListener('click', function () {
        searchInput.value = '';
        filtroSelect.value = '';
        fechaInicioInput.value = '';
        fechaFinInput.value = '';

        buscarTerm = '';
        filtroSeleccionado = '';
        fechaInicio = '';
        fechaFin = '';

        cargarActividades(1);
    });
}

/**
 * ────────────────────────────────────────────────────────
 * 2. LÓGICA DE LA VISTA DE GRÁFICAS (CHART.JS)
 * ────────────────────────────────────────────────────────
 */
function initGraficas() {
    const fiInput = document.getElementById('graficas-fecha-inicio');
    const ffInput = document.getElementById('graficas-fecha-fin');
    const btnGenerar = document.getElementById('btn-generar-graficas');

    let chartBarrasInstancia = null;
    let chartPieInstancia = null;

    // Paletas de colores
    const colors = [
        'rgba(59, 130, 246, 0.85)',  // Azul
        'rgba(16, 185, 129, 0.85)',  // Verde
        'rgba(139, 92, 246, 0.85)',  // Púrpura
        'rgba(245, 158, 11, 0.85)',  // Ámbar
        'rgba(236, 72, 153, 0.85)',  // Rosa
        'rgba(6, 182, 212, 0.85)',   // Cian
        'rgba(107, 114, 128, 0.85)',  // Gris
        'rgba(244, 63, 94, 0.85)'     // Rosa oscuro
    ];
    const borderColors = colors.map(c => c.replace('0.85', '1'));

    // Plugin personalizado para dibujar etiquetas externas en gráfico Pie
    const outerLabelPlugin = {
        id: 'outerLabelsActividades',
        afterDraw(chart) {
            if (chart.config.type !== 'pie') return;

            const { ctx, data } = chart;
            const meta = chart.getDatasetMeta(0);

            ctx.save();
            ctx.font = '11px Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            meta.data.forEach((arc, i) => {
                const { startAngle, endAngle } = arc;
                const midAngle = (startAngle + endAngle) / 2;
                const outerRadius = arc.outerRadius;
                const labelRadius = outerRadius + 30; // Desplazamiento exterior

                const x = arc.x + Math.cos(midAngle) * labelRadius;
                const y = arc.y + Math.sin(midAngle) * labelRadius;

                const label = data.labels[i] ?? '';
                const value = data.datasets[0].data[i] ?? 0;
                const text = `${label}: ${value}`;

                ctx.fillStyle = '#374151';
                ctx.fillText(text, x, y);
            });

            ctx.restore();
        }
    };

    function cargarGraficas() {
        const fi = fiInput.value;
        const ff = ffInput.value;

        if (!fi || !ff) return;

        fetch(`/actividades/graficas/datos?fi=${fi}&ff=${ff}`)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                return res.json();
            })
            .then(data => {
                renderBarras(data);
                renderPie(data);
            })
            .catch(err => {
                console.error('Error al cargar datos de gráficas:', err);
            });
    }

    function renderBarras(datos) {
        const ctx = document.getElementById('chartBarras').getContext('2d');

        if (chartBarrasInstancia) {
            chartBarrasInstancia.destroy();
        }

        if (datos.length === 0) {
            drawEmptyState(ctx, 'No hay inicios de sesión en este periodo.');
            return;
        }

        const labels = datos.map(d => d.label);
        const values = datos.map(d => d.value);

        chartBarrasInstancia = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de inicios de sesión',
                    data: values,
                    backgroundColor: colors.slice(0, datos.length),
                    borderColor: borderColors.slice(0, datos.length),
                    borderWidth: 1.5,
                    borderRadius: 3
                }]
            },
            options: {
                indexAxis: 'y', // Barras horizontales ( Morris legacy style )
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` Cantidad: ${ctx.raw}`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    function renderPie(datos) {
        const ctx = document.getElementById('chartPie').getContext('2d');

        if (chartPieInstancia) {
            chartPieInstancia.destroy();
        }

        if (datos.length === 0) {
            drawEmptyState(ctx, 'No hay inicios de sesión en este periodo.');
            return;
        }

        const labels = datos.map(d => d.label);
        const values = datos.map(d => d.value);

        chartPieInstancia = new Chart(ctx, {
            type: 'pie',
            plugins: [outerLabelPlugin],
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors.slice(0, datos.length),
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 30, bottom: 30, left: 40, right: 40 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw}`
                        }
                    }
                }
            }
        });
    }

    function drawEmptyState(ctx, message) {
        const canvas = ctx.canvas;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.font = '13px sans-serif';
        ctx.fillStyle = '#6b7280';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(message, canvas.width / 2, canvas.height / 2);
    }

    btnGenerar.addEventListener('click', cargarGraficas);

    // Carga inicial
    cargarGraficas();
}
