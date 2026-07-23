document.addEventListener('DOMContentLoaded', function () {

    // === ELEMENTOS PRINCIPALES ===
    const searchInput          = document.getElementById('global-search');
    const tbody                = document.getElementById('tbodyArchivos');
    const totalBadge           = document.getElementById('totalArchivos');
    const infoPaginacion       = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');

    // === ELEMENTOS DEL FORMULARIO DE ALTA ===
    const inputNombre          = document.getElementById('nombre');
    const nombreFeedback       = document.getElementById('nombreFeedback');
    const inputAbreviatura     = document.getElementById('abreviatura');
    const abreviaturaFeedback  = document.getElementById('abreviaturaFeedback');
    const btnGuardar           = document.getElementById('btnGuardar');

    // Estado local de disponibilidad
    let nombreValido      = true;
    let abreviaturaValido = true;

    function actualizarBotonGuardar() {
        if (btnGuardar) {
            btnGuardar.disabled = !(nombreValido && abreviaturaValido);
        }
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: PAGINACIÓN Y BÚSQUEDA ASÍNCRONA (AJAX)
    // ─────────────────────────────────────────────────────────
    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        const buscar = searchInput ? searchInput.value : '';

        // Feedback visual intermedio
        tbody.style.opacity = '0.5';

        const queryParams = new URLSearchParams({
            buscar: buscar,
            page:   numeroPagina
        });

        fetch(`/sedes?${queryParams.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text();
        })
        .then(html => {
            tbody.style.opacity = '1';
            tbody.innerHTML = html;

            // Sincronizar datos de paginación desde el partial inyectado
            const elTransporte = document.getElementById('datosPaginacionTransporte');

            if (elTransporte) {
                const totalGlobal = parseInt(elTransporte.getAttribute('data-total'));
                const textoInfo   = elTransporte.getAttribute('data-info');
                const htmlLinks   = document.getElementById('htmlLinksPaginacion').innerHTML;

                if (totalBadge)     totalBadge.textContent = `${totalGlobal} ${totalGlobal === 1 ? 'Registro' : 'Registros'}`;
                if (infoPaginacion) infoPaginacion.textContent = textoInfo;

                if (contenedorPaginacion) {
                    contenedorPaginacion.innerHTML = htmlLinks;
                    asignarEventosEnlaces();
                }
            } else {
                if (totalBadge)     totalBadge.textContent = '0 Registros';
                if (infoPaginacion) infoPaginacion.textContent = 'Mostrando 0 a 0 de 0 registros';
                if (contenedorPaginacion) contenedorPaginacion.innerHTML = '';
            }

            // Volver a enlazar confirmaciones de status en la nueva tabla
            enlazarConfirmacionesStatus();
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error al cargar el módulo de sedes:', err);
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

    // Función debounce para evitar ráfagas de peticiones
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Listener de búsqueda con debounce de 300ms
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            cargarPagina(1);
        }, 300));
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: VALIDACIÓN EN TIEMPO REAL (AJAX)
    // ─────────────────────────────────────────────────────────
    let verificarTimeout = null;

    function verificarDisponibilidad() {
        const nombre      = inputNombre ? inputNombre.value.trim() : '';
        const abreviatura = inputAbreviatura ? inputAbreviatura.value.trim() : '';

        // Si están vacíos, reiniciar feedbacks visuales
        if (!nombre) {
            if (nombreFeedback) { nombreFeedback.classList.add('d-none'); nombreFeedback.textContent = ''; }
            nombreValido = true;
        }
        if (!abreviatura) {
            if (abreviaturaFeedback) { abreviaturaFeedback.classList.add('d-none'); abreviaturaFeedback.textContent = ''; }
            abreviaturaValido = true;
        }

        if (!nombre && !abreviatura) {
            actualizarBotonGuardar();
            return;
        }

        clearTimeout(verificarTimeout);
        verificarTimeout = setTimeout(() => {
            const params = new URLSearchParams();
            if (nombre) params.set('nombre', nombre);
            if (abreviatura) params.set('abreviatura', abreviatura);

            fetch(`/sedes/verificar?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (nombre) {
                    if (nombreFeedback) {
                        nombreFeedback.classList.remove('d-none', 'text-success', 'text-danger');
                        if (data.nombre_disponible) {
                            nombreFeedback.classList.add('text-success');
                            nombreFeedback.textContent = '✔ El nombre de la sede está disponible.';
                            nombreValido = true;
                        } else {
                            nombreFeedback.classList.add('text-danger');
                            nombreFeedback.textContent = '✘ Esta sede ya se encuentra registrada.';
                            nombreValido = false;
                        }
                    }
                }

                if (abreviatura) {
                    if (abreviaturaFeedback) {
                        abreviaturaFeedback.classList.remove('d-none', 'text-success', 'text-danger');
                        if (data.abreviatura_disponible) {
                            abreviaturaFeedback.classList.add('text-success');
                            abreviaturaFeedback.textContent = '✔ La abreviatura está disponible.';
                            abreviaturaValido = true;
                        } else {
                            abreviaturaFeedback.classList.add('text-danger');
                            abreviaturaFeedback.textContent = '✘ Esta abreviatura ya se encuentra registrada.';
                            abreviaturaValido = false;
                        }
                    }
                }

                actualizarBotonGuardar();
            })
            .catch(() => {
                // Silenciosamente ignorar errores de red
            });
        }, 400);
    }

    if (inputNombre) {
        inputNombre.addEventListener('input', verificarDisponibilidad);
    }
    if (inputAbreviatura) {
        inputAbreviatura.addEventListener('input', verificarDisponibilidad);
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: CONFIRMACIÓN SweetAlert2 AL ALTERNAR STATUS
    // ─────────────────────────────────────────────────────────
    function enlazarConfirmacionesStatus() {
        const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
        toggleStatusLinks.forEach(link => {
            // Evitar dobles confirmaciones clonando el nodo
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

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const csrfInput = document.createElement('input');
                    csrfInput.type  = 'hidden';
                    csrfInput.name  = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type  = 'hidden';
                    methodInput.name  = '_method';
                    methodInput.value = 'PATCH';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title:             `¿Desea ${accion} la sede?`,
                        text:              `La sede "${nombre}" cambiará de estado en el sistema.`,
                        icon:              iconType,
                        showCancelButton:  true,
                        confirmButtonColor: '#2b6cb0',
                        cancelButtonColor:  '#d33',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText:  'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doRequest();
                        }
                    });
                } else {
                    if (confirm(`¿Está seguro de que desea ${accion} la sede "${nombre}"?`)) {
                        doRequest();
                    }
                }
            });
        });
    }

    // ─────────────────────────────────────────────────────────
    // INICIALIZACIÓN
    // ─────────────────────────────────────────────────────────
    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    if (elTransporteInicial && contenedorPaginacion) {
        contenedorPaginacion.innerHTML = document.getElementById('htmlLinksPaginacion').innerHTML;
        asignarEventosEnlaces();
    }

    // Inicializar confirmaciones de status en carga inicial
    enlazarConfirmacionesStatus();

    // Abrir modal automáticamente si hay errores de validación (tras rebote del formulario)
    const pageErrors = document.getElementById('hasFormErrors');
    if (pageErrors && pageErrors.dataset.errors === '1') {
        const modalEl = document.getElementById('modalRegistrarSede');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INICIALIZACIÓN DE CHART.JS (GRÁFICAS ANALÍTICAS)
    // ─────────────────────────────────────────────────────────────────────────
    const canvasDonut    = document.getElementById('donutEstadoChart');
    const canvasBarTrab  = document.getElementById('barTrabajadoresChart');

    // Helper para crear gráficos de barras reutilizable
    function crearBarChart(canvas, label, color) {
        if (!canvas || typeof Chart === 'undefined') return;
        const datos  = JSON.parse(canvas.dataset.json || '{}');
        const labels = Object.keys(datos);
        const values = Object.values(datos);

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    backgroundColor: color,
                    borderWidth: 0,
                    borderRadius: 6,
                    barPercentage: 0.55
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
                        ticks: { stepSize: 1, color: '#4b5563' },
                        grid: { color: '#f3f4f6' }
                    },
                    x: {
                        ticks: { color: '#4b5563', font: { weight: 'bold', size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Donut Chart
    if (canvasDonut && typeof Chart !== 'undefined') {
        const centerLabel = document.getElementById('donutCenterLabel');
        const centerValue = document.getElementById('donutCenterValue');

        const datosEstado  = JSON.parse(canvasDonut.dataset.json || '{}');
        const labelsEstado = Object.keys(datosEstado);
        const valuesEstado = Object.values(datosEstado);
        const totalEstado  = valuesEstado.reduce((a, b) => a + b, 0);

        if (centerValue) centerValue.textContent = totalEstado;

        const coloresEstado = ['#10b981', '#ef4444'];

        new Chart(canvasDonut.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labelsEstado,
                datasets: [{
                    data: valuesEstado,
                    backgroundColor: coloresEstado,
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
                        labels: { boxWidth: 12, font: { size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const valor = context.raw || 0;
                                const porcentaje = totalEstado > 0 ? ((valor / totalEstado) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${valor} (${porcentaje}%)`;
                            }
                        }
                    }
                },
                cutout: '65%',
                onHover: (event, chartElements) => {
                    if (chartElements.length > 0) {
                        const index = chartElements[0].index;
                        const label = labelsEstado[index];
                        const val   = valuesEstado[index];
                        const pct   = totalEstado > 0 ? ((val / totalEstado) * 100).toFixed(1) : 0;
                        if (centerLabel) centerLabel.textContent = label;
                        if (centerValue) centerValue.textContent = `${val} (${pct}%)`;
                    } else {
                        if (centerLabel) centerLabel.textContent = 'Total';
                        if (centerValue) centerValue.textContent = totalEstado;
                    }
                }
            }
        });
    }

    // Bar Charts
    crearBarChart(canvasBarTrab, 'Cantidad de Personas', '#0d6efd');

});
