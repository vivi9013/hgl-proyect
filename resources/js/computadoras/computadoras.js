document.addEventListener('DOMContentLoaded', function () {

    // === ELEMENTOS FILTRO Y BUSCADOR (DERECHA) ===
    const filtro = document.getElementById('filtroCategoria');
    const searchInput = document.getElementById('global-search');
    const tbody = document.getElementById('tbodyArchivos');
    const totalBadge = document.getElementById('totalArchivos');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');

    // ─────────────────────────────────────────────────────────
    // LÓGICA: REPOSITORIO DE CARGA FILTRADA ASÍNCROMA (AJAX)
    // ─────────────────────────────────────────────────────────
    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        const categoria = filtro ? filtro.value : 'Todos';
        const buscar = searchInput ? searchInput.value : '';

        // Feedback visual intermedio
        tbody.style.opacity = '0.5';

        fetch(`/computadoras?area_id=${encodeURIComponent(categoria)}&buscar=${encodeURIComponent(buscar)}&page=${numeroPagina}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text();
        })
        .then(html => {
            tbody.style.opacity = '1';
            tbody.innerHTML = html;

            // Sincronizar transporte de datos desde el partial inyectado
            const elTransporte = document.getElementById('datosPaginacionTransporte');
            
            if (elTransporte) {
                const totalGlobal = parseInt(elTransporte.getAttribute('data-total'));
                const textoInfo = elTransporte.getAttribute('data-info');
                const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;

                if (totalBadge) totalBadge.textContent = `${totalGlobal} ${totalGlobal === 1 ? 'Registro' : 'Registros'}`;
                if (infoPaginacion) infoPaginacion.textContent = textoInfo;
                
                if (contenedorPaginacion) {
                    contenedorPaginacion.innerHTML = htmlLinks;
                    asignarEventosEnlaces();
                }
            } else {
                // Manejo de escenario vacío
                if (totalBadge) totalBadge.textContent = '0 Registros';
                if (infoPaginacion) infoPaginacion.textContent = "Mostrando 0 a 0 de 0 registros";
                if (contenedorPaginacion) contenedorPaginacion.innerHTML = '';
            }

            // Volver a enlazar eventos en la nueva tabla
            enlazarConfirmacionesStatus();
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando módulo de computadoras con filtros:', err);
        });
    }

    function asignarEventosEnlaces() {
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

    // Función debounce para evitar ráfagas de peticiones innecesarias
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // === LISTENERS REACTIVOS AL BUSCADOR Y FILTRO ===
    if (filtro) {
        filtro.addEventListener('change', function () {
            cargarPagina(1); // Resetear a la página 1 en cambios de área
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            cargarPagina(1); // Resetear a la página 1 en búsquedas por texto
        }, 300));
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: Confirmación SweetAlert2 al Alternar Status
    // ─────────────────────────────────────────────────────────
    function enlazarConfirmacionesStatus() {
        const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
        toggleStatusLinks.forEach(link => {
            // Eliminar listeners previos clonando el nodo para evitar dobles confirmaciones
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);

            newLink.addEventListener('click', function (e) {
                e.preventDefault();

                const url    = this.getAttribute('data-url');
                const nombre = this.getAttribute('data-nombre');
                const activo = parseInt(this.getAttribute('data-activo'));

                const accion         = activo === 1 ? 'desactivar' : 'activar';
                const iconType       = activo === 1 ? 'warning' : 'question';
                const confirmBtnText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';

                const doRequest = () => {
                    const form = document.createElement('form');
                    form.action = url;
                    form.method = 'POST';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PATCH';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿Desea ${accion} el equipo?`,
                        text: `La computadora con inventario "${nombre}" cambiará de estado en el sistema.`,
                        icon: iconType,
                        showCancelButton: true,
                        confirmButtonColor: '#2b6cb0',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doRequest();
                        }
                    });
                } else {
                    if (confirm(`¿Está seguro de que desea ${accion} el equipo "${nombre}"?`)) {
                        doRequest();
                    }
                }
            });
        });
    }

    // Inicialización de la paginación al renderizar el módulo por primera vez
    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    if (elTransporteInicial && contenedorPaginacion) {
        contenedorPaginacion.innerHTML = document.getElementById('htmlLinksPaginacion').innerHTML;
        asignarEventosEnlaces();
    }

    // Inicializar confirmaciones
    enlazarConfirmacionesStatus();

    // ─────────────────────────────────────────────────────────────────────────
    // D. INICIALIZACIÓN DE CHART.JS (ESTADÍSTICAS)
    // ─────────────────────────────────────────────────────────────────────────
    const canvasPastel = document.getElementById('pastelChart');
    const canvasBarra = document.getElementById('barChart');
    const canvasArea = document.getElementById('areaChart');

    if (canvasPastel && canvasBarra && canvasArea && typeof Chart !== 'undefined') {
        const centerLabel = document.getElementById('chartCenterLabel');
        const centerValue = document.getElementById('chartCenterValue');

        // Leer datos desde atributos HTML
        const datosSO = JSON.parse(canvasPastel.dataset.json || '{}');
        const labelsSO = Object.keys(datosSO);
        const valuesSO = Object.values(datosSO);
        const totalSO = valuesSO.reduce((a, b) => a + b, 0);

        const datosMarca = JSON.parse(canvasBarra.dataset.json || '{}');
        const labelsMarca = Object.keys(datosMarca);
        const valuesMarca = Object.values(datosMarca);

        const datosArea = JSON.parse(canvasArea.dataset.json || '{}');
        const labelsArea = Object.keys(datosArea);
        const valuesArea = Object.values(datosArea);

        // Inicializar texto del centro del pastel
        if (centerLabel) centerLabel.textContent = 'Total Equipos';
        if (centerValue) centerValue.textContent = totalSO;

        // Colores premium y sobrios
        const coloresPremium = [
            '#1f2937', // Gris muy oscuro
            '#3b82f6', // Azul primario
            '#10b981', // Verde esmeralda
            '#f59e0b', // Amarillo
            '#ef4444', // Rojo
            '#6366f1', // Indigo
            '#8b5cf6', // Violeta
            '#ec4899', // Rosa
            '#14b8a6', // Turquesa
            '#f97316', // Naranja
        ];

        // 1. PASTEL CHART (DONUT) - Sistemas Operativos
        new Chart(canvasPastel.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labelsSO,
                datasets: [{
                    data: valuesSO,
                    backgroundColor: coloresPremium,
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
                                const porcentaje = ((valor / totalSO) * 100).toFixed(1);
                                return ` ${context.label}: ${valor} (${porcentaje}%)`;
                            }
                        }
                    }
                },
                cutout: '65%',
                onHover: (event, chartElements) => {
                    if (chartElements.length > 0) {
                        const index = chartElements[0].index;
                        const label = labelsSO[index];
                        const val = valuesSO[index];
                        const percent = ((val / totalSO) * 100).toFixed(1);
                        if (centerLabel) centerLabel.textContent = label;
                        if (centerValue) centerValue.textContent = `${val} (${percent}%)`;
                    } else {
                        if (centerLabel) centerLabel.textContent = 'Total Equipos';
                        if (centerValue) centerValue.textContent = totalSO;
                    }
                }
            }
        });

        // 2. BAR CHART - Distribución por Marca
        new Chart(canvasBarra.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsMarca,
                datasets: [{
                    label: 'Cantidad de Equipos',
                    data: valuesMarca,
                    backgroundColor: '#3b82f6',
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

        // 3. AREA/BAR CHART - Distribución por Área
        new Chart(canvasArea.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsArea,
                datasets: [{
                    label: 'Cantidad de Equipos',
                    data: valuesArea,
                    backgroundColor: '#1f2937',
                    borderWidth: 0,
                    borderRadius: 4,
                    barPercentage: 0.6
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
                            font: { size: 10 }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }

});
