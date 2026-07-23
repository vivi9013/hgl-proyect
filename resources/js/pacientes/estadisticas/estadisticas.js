/**
 * Módulo Estadísticas y Reportes RX – Control de Gráficos Interactivos
 * Replica el comportamiento del sistema legacy:
 *  - Tab "Estudios"    → Barras horizontales por región anatómica
 *  - Tab "Técnicos RX" → Gráfico de pastel (pie) con etiquetas externas
 *  - Tab "Por Género"  → Gráfico de pastel (pie) con etiquetas externas
 */

import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', function () {
    // ── Inputs y botones ──
    const fechaInicioInput = document.getElementById('fechai');
    const fechaFinInput    = document.getElementById('fechaf');
    const totalEstudiosInput = document.getElementById('totale');
    const btnImprimir      = document.getElementById('reporte');

    // Instancias globales de gráficos para destruir y redibujar
    let chartEstudios = null;
    let chartTecnicos = null;
    let chartGeneros  = null;

    // ── Paletas de colores ──
    const paletaRegiones = [
        'rgba(100, 149, 237, 0.85)',  // azul acero – Cráneo
        'rgba(100, 149, 237, 0.85)',  // azul acero – Tórax
        'rgba(100, 149, 237, 0.85)',  // azul acero – Abdomen
        'rgba(100, 149, 237, 0.85)',  // azul acero – Columna
        'rgba(100, 149, 237, 0.85)',  // azul acero – Miembro Sup.
        'rgba(100, 149, 237, 0.85)',  // azul acero – Miembro Inf.
        'rgba(100, 149, 237, 0.85)',  // azul acero – Contraste
    ];

    // Paleta multicolor para pastel de técnicos
    const paletaTecnicos = [
        'rgba(100, 185, 110, 0.9)',   // verde
        'rgba(100, 149, 237, 0.9)',   // azul
        'rgba(58,  58,  58,  0.85)',  // gris oscuro
        'rgba(236, 190,  80, 0.9)',   // ámbar
        'rgba(210,  90, 100, 0.9)',   // rojo
        'rgba(130, 100, 210, 0.9)',   // púrpura
        'rgba(70,  190, 200, 0.9)',   // cian
    ];

    // Paleta para pastel de género
    const paletaGenero = [
        'rgba(58,  58,  58,  0.85)',  // gris oscuro – Masculino
        'rgba(100, 149, 237, 0.9)',   // azul celeste – Femenino
        'rgba(130, 100, 210, 0.9)',   // púrpura      – Otro
    ];

    // ── Plugin personalizado: etiquetas en el exterior del pie ──
    const outerLabelPlugin = {
        id: 'outerLabels',
        afterDraw(chart) {
            if (chart.config.type !== 'pie') return;

            const { ctx, data, chartArea } = chart;
            const meta = chart.getDatasetMeta(0);

            ctx.save();
            ctx.font = '11px Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            meta.data.forEach((arc, i) => {
                const { startAngle, endAngle } = arc;
                const midAngle = (startAngle + endAngle) / 2;
                const outerRadius = arc.outerRadius;
                const labelRadius = outerRadius + 40;

                const x = arc.x + Math.cos(midAngle) * labelRadius;
                const y = arc.y + Math.sin(midAngle) * labelRadius;

                const label = data.labels[i] ?? '';
                const value = data.datasets[0].data[i] ?? 0;
                const text  = `${label}: ${value} %`;

                ctx.fillStyle = '#374151';
                ctx.fillText(text, x, y);
            });

            ctx.restore();
        }
    };

    // ── Cargar estadísticas por AJAX ──
    function cargarEstadisticas() {
        const fi = fechaInicioInput.value;
        const ff = fechaFinInput.value;

        if (!fi || !ff) return;

        fetch(`/rx-estadisticas/datos?fi=${fi}&ff=${ff}`)
            .then(res => res.json())
            .then(data => {
                // Actualizar total general
                totalEstudiosInput.value = data.total_estudios;

                if (data.total_estudios === 0) {
                    btnImprimir.setAttribute('disabled', 'disabled');
                } else {
                    btnImprimir.removeAttribute('disabled');
                }

                // ── 1. Barras horizontales – Estudios por Región ──
                renderGraficoRegiones(data.regiones);

                // ── 2. Pie – Técnicos RX ──
                renderGraficoTecnicos(data.tecnicos);

                // ── 3. Pie – Por Género ──
                renderGraficoGeneros(data.generos);
            })
            .catch(err => {
                console.error('Error al cargar estadísticas:', err);
            });
    }

    // ── Gráfico 1: Barras horizontales por región anatómica ──
    function renderGraficoRegiones(regiones) {
        const ctx = document.getElementById('chartRegiones').getContext('2d');

        if (chartEstudios) {
            chartEstudios.destroy();
        }

        const labels = ['Cráneo', 'Tórax', 'Abdomen', 'Columna', 'Media Superior', 'Media Inferior', 'Contraste'];
        const values = [
            regiones.craneo   ?? 0,
            regiones.torax    ?? 0,
            regiones.abdomen  ?? 0,
            regiones.columna  ?? 0,
            regiones.m_sup    ?? 0,
            regiones.m_inf    ?? 0,
            regiones.contraste ?? 0,
        ];

        const totalRegiones = values.reduce((a, b) => a + b, 0);

        if (totalRegiones === 0) {
            drawEmptyState(ctx, 'No hay estudios registrados en este periodo.');
            return;
        }

        chartEstudios = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total de estudios realizados',
                    data: values,
                    backgroundColor: paletaRegiones,
                    borderColor: paletaRegiones.map(c => c.replace('0.85', '1')),
                    borderWidth: 1,
                    borderRadius: 2,
                }]
            },
            options: {
                indexAxis: 'y',   // ← BARRAS HORIZONTALES (igual que el legacy)
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 14,
                            font: { size: 11 },
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` Total de estudios realizados: ${ctx.raw} estudios`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Estudios RX HGL',
                            font: { size: 11 },
                            color: '#6b7280'
                        }
                    },
                    y: {
                        ticks: { font: { size: 12 } }
                    }
                }
            }
        });
    }

    // ── Gráfico 2: Pie – Técnicos RX ──
    function renderGraficoTecnicos(tecnicos) {
        const ctx = document.getElementById('chartTecnicos').getContext('2d');

        if (chartTecnicos) {
            chartTecnicos.destroy();
        }

        if (tecnicos.length === 0) {
            drawEmptyState(ctx, 'Sin registros de técnicos.');
            return;
        }

        // El controlador ya devuelve { name: "Nombre (n)", y: porcentaje }
        const labels = tecnicos.map(t => t.name);
        const values = tecnicos.map(t => t.y);

        chartTecnicos = new Chart(ctx, {
            type: 'pie',
            plugins: [outerLabelPlugin],
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: paletaTecnicos.slice(0, tecnicos.length),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 30, bottom: 30, left: 50, right: 50 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} %`
                        }
                    }
                }
            }
        });
    }

    // ── Gráfico 3: Pie – Por Género ──
    function renderGraficoGeneros(generos) {
        const ctx = document.getElementById('chartGeneros').getContext('2d');

        if (chartGeneros) {
            chartGeneros.destroy();
        }

        if (generos.length === 0) {
            drawEmptyState(ctx, 'Sin registros de pacientes.');
            return;
        }

        const labels = generos.map(g => g.name);
        const values = generos.map(g => g.y);

        chartGeneros = new Chart(ctx, {
            type: 'pie',
            plugins: [outerLabelPlugin],
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: paletaGenero.slice(0, generos.length),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 30, bottom: 30, left: 50, right: 50 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} %`
                        }
                    }
                }
            }
        });
    }

    // ── Helper: dibujar estado vacío en el canvas ──
    function drawEmptyState(ctx, message) {
        const canvas = ctx.canvas;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.font = '13px Arial, sans-serif';
        ctx.fillStyle = '#6b7280';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(message, canvas.width / 2, canvas.height / 2);
    }

    // ── Evento: Imprimir Reporte General ──
    if (btnImprimir) {
        btnImprimir.addEventListener('click', function () {
            const fi = fechaInicioInput.value;
            const ff = fechaFinInput.value;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Imprimir Reporte General?',
                    text: 'Se generará el reporte de estudios RX del periodo seleccionado.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, generar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(`/rx-estadisticas/imprimir?fi=${fi}&ff=${ff}`, '_blank');
                    }
                });
            } else {
                if (confirm('¿Deseas imprimir el reporte general de estudios RX?')) {
                    window.open(`/rx-estadisticas/imprimir?fi=${fi}&ff=${ff}`, '_blank');
                }
            }
        });
    }

    // ── Escuchar cambios de fecha ──
    fechaInicioInput.addEventListener('change', cargarEstadisticas);
    fechaFinInput.addEventListener('change', cargarEstadisticas);

    // ── Carga inicial ──
    cargarEstadisticas();
});
