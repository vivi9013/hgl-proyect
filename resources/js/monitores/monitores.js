/**
 * monitores.js — Lógica para el módulo de Monitores.
 * Cubre: alertas de sesión, paginación y búsqueda AJAX, alternado de estado,
 * prellenado de campos del monitor vía AJAX, y gráficas con Chart.js.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. PAGINACIÓN ASÍNCRONA Y BÚSQUEDA REACTIVA
    // ─────────────────────────────────────────────────────────────────────────
    const filtroArea = document.getElementById('filtroCategoria');
    const entradaBusqueda = document.getElementById('global-search');
    const tbody = document.getElementById('tbodyArchivos');
    const totalBadge = document.getElementById('totalArchivos');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');

    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        const area = filtroArea ? filtroArea.value : 'Todos';
        const buscar = entradaBusqueda ? entradaBusqueda.value : '';

        // Efecto visual de carga
        tbody.style.opacity = '0.5';

        fetch(`/monitores?area_id=${encodeURIComponent(area)}&buscar=${encodeURIComponent(buscar)}&page=${numeroPagina}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.json();
        })
        .then(data => {
            tbody.style.opacity = '1';
            tbody.innerHTML = data.html;

            if (totalBadge) totalBadge.textContent = `${data.total} ${data.total === 1 ? 'Registro' : 'Registros'}`;
            if (infoPaginacion) infoPaginacion.textContent = data.info;
            
            if (contenedorPaginacion) {
                contenedorPaginacion.innerHTML = data.links;
                asignarEventosPaginacion();
            }

            enlazarConfirmacionesStatus();
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando el módulo de monitores:', err);
        });
    }

    function asignarEventosPaginacion() {
        if (!contenedorPaginacion) return;
        const enlaces = contenedorPaginacion.querySelectorAll('a.page-link');
        
        enlaces.forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                const urlObj = new URL(this.href);
                const paginaDestino = urlObj.searchParams.get('page');
                
                if (paginaDestino) {
                    cargarPagina(paginaDestino);
                }
            });
        });
    }

    // Función debounce para ráfagas de teclado
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    if (filtroArea) {
        filtroArea.addEventListener('change', function () {
            cargarPagina(1);
        });
    }

    if (entradaBusqueda) {
        entradaBusqueda.addEventListener('input', debounce(function () {
            cargarPagina(1);
        }, 300));
    }

    // Inicializar eventos de paginación si existen inicialmente
    if (tbody) {
        // Enlazar los enlaces estáticos inyectados por PHP inicialmente
        const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
        if (elTransporteInicial && contenedorPaginacion) {
            const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;
            contenedorPaginacion.innerHTML = htmlLinks;
            asignarEventosPaginacion();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. ALTERNAR ESTADO (ACTIVO/INACTIVO) CON CONFIRMACIÓN
    // ─────────────────────────────────────────────────────────────────────────
    function enlazarConfirmacionesStatus() {
        const toggleLinks = document.querySelectorAll('.btn-toggle-status');
        toggleLinks.forEach(link => {
            // Clonar nodo para evitar duplicidad de listeners
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);

            newLink.addEventListener('click', function (e) {
                e.preventDefault();

                const url = this.getAttribute('data-url');
                const nombre = this.getAttribute('data-nombre');
                const activo = parseInt(this.getAttribute('data-activo'));

                const accion = activo === 1 ? 'desactivar' : 'activar';
                const iconType = activo === 1 ? 'warning' : 'question';
                const confirmBtnText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';

                const ejecutarAccion = () => {
                    const tokenCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': tokenCsrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Volver a cargar la página activa para actualizar la lista
                            const paginaActiva = contenedorPaginacion
                                ?.querySelector('.page-item.active .page-link')
                                ?.textContent?.trim() ?? '1';
                            cargarPagina(paginaActiva);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: '¡Estado actualizado!',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(err => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', err.message || 'No se pudo actualizar el estado.', 'error');
                        } else {
                            alert('Error: ' + err.message);
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿Desea ${accion} el monitor?`,
                        text: `El monitor con inventario "${nombre}" cambiará de estado en el sistema.`,
                        icon: iconType,
                        showCancelButton: true,
                        confirmButtonColor: '#2b6cb0',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            ejecutarAccion();
                        }
                    });
                } else {
                    if (confirm(`¿Está seguro de que desea ${accion} el monitor "${nombre}"?`)) {
                        ejecutarAccion();
                    }
                }
            });
        });
    }

    // Inicializar confirmaciones
    enlazarConfirmacionesStatus();

    // ─────────────────────────────────────────────────────────────────────────
    // C. AUTOCOMPLETADO / PRELLENADO DE MOBILIARIO
    // ─────────────────────────────────────────────────────────────────────────
    const selectInventario = document.getElementById('inventario');
    const inputMarca = document.getElementById('marca');
    const inputModelo = document.getElementById('modelo');
    const inputSerie = document.getElementById('serie');
    const inputDescripcion = document.getElementById('descripcion');

    if (selectInventario) {
        selectInventario.addEventListener('change', function () {
            const inventarioValue = this.value;
            if (!inventarioValue) return;

            // Feedback visual mientras carga
            if (inputModelo) inputModelo.placeholder = 'Cargando datos...';
            if (inputSerie) inputSerie.placeholder = 'Cargando datos...';
            if (inputDescripcion) inputDescripcion.placeholder = 'Cargando datos...';

            fetch(`/monitores/mobiliario-info/${encodeURIComponent(inventarioValue)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Error al obtener la info del mobiliario');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    if (inputMarca) inputMarca.value = data.marca;
                    if (inputModelo) inputModelo.value = data.modelo;
                    if (inputSerie) inputSerie.value = data.serie;
                    if (inputDescripcion) inputDescripcion.value = data.descripcion;
                }
            })
            .catch(err => {
                console.error('Error al autocompletar monitor:', err);
            })
            .finally(() => {
                if (inputModelo) inputModelo.placeholder = 'ghfghcfghfghf';
                if (inputSerie) inputSerie.placeholder = 'ghvfghchgcfg';
                if (inputDescripcion) inputDescripcion.placeholder = 'yhdfgxdfgdsfg';
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. INICIALIZACIÓN DE CHART.JS (ESTADÍSTICAS)
    // ─────────────────────────────────────────────────────────────────────────
    const canvasPastel = document.getElementById('pastelChart');
    const canvasBarra = document.getElementById('barChart');

    if (canvasPastel && canvasBarra && typeof Chart !== 'undefined') {
        const centerLabel = document.getElementById('chartCenterLabel');
        const centerValue = document.getElementById('chartCenterValue');

        // Leer datos desde atributos HTML
        const datosMarca = JSON.parse(canvasPastel.dataset.json || '{}');
        const labelsMarca = Object.keys(datosMarca);
        const valuesMarca = Object.values(datosMarca);
        const totalMonitores = valuesMarca.reduce((a, b) => a + b, 0);

        const datosTipo = JSON.parse(canvasBarra.dataset.json || '{}');
        const labelsTipo = Object.keys(datosTipo);
        const valuesTipo = Object.values(datosTipo);

        // Inicializar texto del centro del pastel
        if (centerLabel) centerLabel.textContent = 'Total Monitores';
        if (centerValue) centerValue.textContent = totalMonitores;

        // Colores premium y sobrios (escala de grises, negro y azul)
        const coloresPastel = [
            '#1f2937', // Gris muy oscuro
            '#3b82f6', // Azul primario
            '#4b5563', // Slate 600
            '#6b7280', // Slate 500
            '#9ca3af', // Slate 400
            '#d1d5db', // Slate 300
        ];

        // 1. PASTEL CHART (DONUT) - Por Marca
        new Chart(canvasPastel.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labelsMarca,
                datasets: [{
                    data: valuesMarca,
                    backgroundColor: coloresPastel,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const valor = context.raw || 0;
                                const porcentaje = ((valor / totalMonitores) * 100).toFixed(1);
                                return ` ${context.label}: ${valor} (${porcentaje}%)`;
                            }
                        }
                    }
                },
                cutout: '65%',
                onHover: (event, chartElements) => {
                    if (chartElements.length > 0) {
                        const index = chartElements[0].index;
                        const label = labelsMarca[index];
                        const val = valuesMarca[index];
                        const percent = ((val / totalMonitores) * 100).toFixed(1);
                        if (centerLabel) centerLabel.textContent = label;
                        if (centerValue) centerValue.textContent = `${val} (${percent}%)`;
                    } else {
                        if (centerLabel) centerLabel.textContent = 'Total Monitores';
                        if (centerValue) centerValue.textContent = totalMonitores;
                    }
                }
            }
        });

        // 2. BAR CHART - Por Tipo
        new Chart(canvasBarra.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsTipo,
                datasets: [{
                    label: 'Cantidad',
                    data: valuesTipo,
                    backgroundColor: '#1f2937',
                    borderWidth: 0,
                    borderRadius: 6,
                    barPercentage: 0.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#4b5563'
                        },
                        grid: {
                            color: '#f3f4f6'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#4b5563',
                            font: { weight: 'bold' }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }

});
