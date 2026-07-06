/**
 * impresoras.js — Lógica del catálogo de Impresoras.
 * Cubre: alertas SweetAlert2, paginación y búsqueda AJAX, alternar estado,
 * validación de IP duplicada y gráficos con Chart.js.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS SWEETALERT2 DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitoGuardar = document.getElementById('alertaExitog');
    const alertaExitoActualizar = document.getElementById('alertaExito');

    if (alertaExitoGuardar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoGuardar.dataset.message || 'La impresora se ha registrado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExitoActualizar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoActualizar.dataset.message || 'La impresora se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // Abrir modal de alta automáticamente si hay errores de validación de Laravel
    const modalAltaEl = document.getElementById('modalAltaImpresora');
    if (modalAltaEl && modalAltaEl.dataset.autoOpen === 'true') {
        const modalInstancia = new bootstrap.Modal(modalAltaEl);
        modalInstancia.show();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. TABLA: PAGINACIÓN ASÍNCRONA Y BÚSQUEDA
    // ─────────────────────────────────────────────────────────────────────────
    const cuerpoTabla = document.getElementById('cuerpoTablaImpresoras');
    const infoPaginacionElemento = document.getElementById('infoPaginacion');
    const contenedorPaginas = document.getElementById('contenedorPaginacion');
    const entradaBusqueda = document.getElementById('busqueda-global');
    const etiquetaTotal = document.getElementById('totalImpresoras');

    function cargarPagina(numeroPagina = 1) {
        if (!cuerpoTabla) return;

        const textoBusqueda = entradaBusqueda ? entradaBusqueda.value.trim() : '';
        cuerpoTabla.style.opacity = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/control-insumos/impresoras?buscar=${encodeURIComponent(textoBusqueda)}&page=${numeroPagina}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(respuesta => {
            if (!respuesta.ok) throw new Error('Error en el servidor');
            return respuesta.json();
        })
        .then(datos => {
            cuerpoTabla.style.opacity = '1';
            cuerpoTabla.innerHTML = datos.html;

            if (etiquetaTotal)            etiquetaTotal.textContent = `${datos.total} Registros`;
            if (infoPaginacionElemento)    infoPaginacionElemento.textContent = datos.info;
            if (contenedorPaginas)  {
                contenedorPaginas.innerHTML = datos.links;
                asignarEventosPaginacion();
            }

            enlazarAlternarEstado();
        })
        .catch(error => {
            cuerpoTabla.style.opacity = '1';
            console.error('[impresoras] Error al paginar:', error);
        });
    }

    function asignarEventosPaginacion() {
        if (!contenedorPaginas) return;
        contenedorPaginas.querySelectorAll('a.page-link').forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                const pagina = url.searchParams.get('page');
                if (pagina) cargarPagina(pagina);
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C. ALTERNAR ESTADO (botones .btn-alternar-estado en la tabla)
    // ─────────────────────────────────────────────────────────────────────────
    function enlazarAlternarEstado() {
        document.querySelectorAll('.btn-toggle-status').forEach(boton => {
            const clon = boton.cloneNode(true);
            boton.parentNode.replaceChild(clon, boton);

            clon.addEventListener('click', function (e) {
                e.preventDefault();
                const idRegistro = this.dataset.id;
                const marcaModelo = this.dataset.marcaModelo || '';
                const estaActivo = parseInt(this.dataset.activo || '0');
                const accionTexto = estaActivo === 1 ? 'desactivar' : 'activar';

                const ejecutarAccion = () => {
                    const tokenCsrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    fetch(`/control-insumos/impresoras/${idRegistro}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': tokenCsrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(respuesta => respuesta.json())
                    .then(datos => {
                        if (datos.success) {
                            const paginaActiva = contenedorPaginas
                                ?.querySelector('.page-item.active .page-link')
                                ?.textContent?.trim() ?? '1';
                            cargarPagina(paginaActiva);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: '¡Estado actualizado!',
                                    text: datos.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        }
                    })
                    .catch(() => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿${accionTexto.charAt(0).toUpperCase() + accionTexto.slice(1)} impresora?`,
                        text: `"${marcaModelo}" será ${accionTexto}da del sistema.`,
                        icon: estaActivo === 1 ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: `Sí, ${accionTexto}`,
                        cancelButtonText: 'Cancelar'
                    }).then(resultado => { if (resultado.isConfirmed) ejecutarAccion(); });
                } else {
                    if (confirm(`¿${accionTexto} la impresora "${marcaModelo}"?`)) ejecutarAccion();
                }
            });
        });
    }

    // Retardo para la entrada de búsqueda
    function demorarEjecucion(funcion, milisegundos) {
        let temporizador;
        return function (...argumentos) {
            clearTimeout(temporizador);
            temporizador = setTimeout(() => funcion.apply(this, argumentos), milisegundos);
        };
    }

    if (entradaBusqueda) {
        entradaBusqueda.addEventListener('input', demorarEjecucion(() => cargarPagina(1), 320));
    }

    // Inicialización de la tabla
    if (cuerpoTabla) {
        const paginaInicial = contenedorPaginas
            ?.querySelector('.page-item.active .page-link')
            ?.textContent?.trim() ?? '1';
        cargarPagina(paginaInicial);
        asignarEventosPaginacion();
    } else {
        // Enlazar de forma estática si no hay cuerpo AJAX (ej. en edición)
        enlazarAlternarEstado();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. TOGGLE ESTADO FORM EN VISTA DE EDICIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const formToggleEstado = document.getElementById('formToggleEstado');
    if (formToggleEstado) {
        formToggleEstado.addEventListener('submit', function (e) {
            e.preventDefault();

            const url = this.getAttribute('action');
            const tokenCsrf = this.querySelector('input[name="_token"]')?.value ?? '';
            const marcaModelo = this.dataset.marcaModelo || '';
            const estaActivo = parseInt(this.dataset.activo || '0');
            const accionTexto = estaActivo === 1 ? 'desactivar' : 'activar';

            const ejecutarAccionEdit = () => {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': tokenCsrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(formToggleEstado)
                })
                .then(respuesta => {
                    if (!respuesta.ok) throw new Error('Error en el servidor');
                    return respuesta.json();
                })
                .then(datos => {
                    if (datos.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '¡Estado actualizado!',
                                text: datos.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            window.location.reload();
                        }
                    }
                })
                .catch(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'No se pudo actualizar el estado de la impresora.', 'error');
                    } else {
                        alert('No se pudo actualizar el estado de la impresora.');
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `¿${accionTexto.charAt(0).toUpperCase() + accionTexto.slice(1)} impresora?`,
                    text: `"${marcaModelo}" será ${accionTexto}da en el sistema.`,
                    icon: estaActivo === 1 ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Sí, ${accionTexto}`,
                    cancelButtonText: 'Cancelar'
                }).then(resultado => {
                    if (resultado.isConfirmed) {
                        ejecutarAccionEdit();
                    }
                });
            } else {
                if (confirm(`¿${accionTexto} la impresora "${marcaModelo}"?`)) {
                    ejecutarAccionEdit();
                }
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. VALIDACIÓN DE IP DUPLICADA EN TIEMPO REAL
    // ─────────────────────────────────────────────────────────────────────────
    const inputIp = document.getElementById('ip');
    const feedbackIp = document.getElementById('feedbackIp');
    const btnGuardarImpresora = document.getElementById('btnGuardarImpresora');

    if (inputIp && feedbackIp && btnGuardarImpresora) {
        let timeoutIp;

        inputIp.addEventListener('input', function () {
            clearTimeout(timeoutIp);
            const ipValue = this.value.trim();
            const excluirId = this.dataset.excluir || '';

            if (ipValue === '') {
                feedbackIp.innerHTML = '';
                inputIp.classList.remove('is-invalid', 'is-valid');
                btnGuardarImpresora.disabled = false;
                return;
            }

            // Expresión regular simple para validar formato IP v4
            const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            if (!ipRegex.test(ipValue)) {
                feedbackIp.innerHTML = '<span class="text-danger small"><i class="fa fa-times-circle"></i> Formato de IP inválido (Ej: 192.168.1.50)</span>';
                inputIp.classList.remove('is-valid');
                inputIp.classList.add('is-invalid');
                btnGuardarImpresora.disabled = true;
                return;
            }

            timeoutIp = setTimeout(() => {
                feedbackIp.innerHTML = '<span class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Verificando disponibilidad...</span>';

                fetch(`/control-insumos/impresoras/verificar-ip?ip=${encodeURIComponent(ipValue)}&excluir=${excluirId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.disponible) {
                        feedbackIp.innerHTML = '<span class="text-success small"><i class="fa fa-check-circle"></i> Dirección IP disponible</span>';
                        inputIp.classList.remove('is-invalid');
                        inputIp.classList.add('is-valid');
                        btnGuardarImpresora.disabled = false;
                    } else {
                        feedbackIp.innerHTML = '<span class="text-danger small"><i class="fa fa-times-circle"></i> Esta IP ya está asignada a otra impresora</span>';
                        inputIp.classList.remove('is-valid');
                        inputIp.classList.add('is-invalid');
                        btnGuardarImpresora.disabled = true;
                    }
                })
                .catch(err => {
                    console.error('Error al verificar IP:', err);
                    feedbackIp.innerHTML = '';
                });
            }, 300);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // F. INICIALIZACIÓN DE CHART.JS EN LA SECCIÓN DE GRÁFICAS
    // ─────────────────────────────────────────────────────────────────────────
    const canvasPastel = document.getElementById('pastelChart');
    const canvasBarra = document.getElementById('barChart');

    if (canvasPastel && canvasBarra && typeof Chart !== 'undefined') {
        const centerLabel = document.getElementById('chartCenterLabel');
        const centerValue = document.getElementById('chartCenterValue');

        // Leer datos desde atributos HTML
        const datosTecnologia = JSON.parse(canvasPastel.dataset.json || '{}');
        const labelsTecnologia = Object.keys(datosTecnologia);
        const valuesTecnologia = Object.values(datosTecnologia);
        const totalTecnologia = valuesTecnologia.reduce((a, b) => a + b, 0);

        const datosTipo = JSON.parse(canvasBarra.dataset.json || '{}');
        const labelsTipo = Object.keys(datosTipo);
        const valuesTipo = Object.values(datosTipo);

        // Inicializar texto del centro del pastel
        if (centerLabel) centerLabel.textContent = 'Total Impresoras';
        if (centerValue) centerValue.textContent = totalTecnologia;

        // Paleta de colores en tonos de grises y azul oscuro (premium y sobrio)
        const coloresPastel = [
            '#1f2937', // Gris muy oscuro (slate 800)
            '#374151', // Slate 700
            '#4b5563', // Slate 600
            '#6b7280', // Slate 500
            '#9ca3af', // Slate 400
            '#d1d5db', // Slate 300
        ];

        // 1. PASTEL CHART (DONUT) - Por Tecnología
        new Chart(canvasPastel.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labelsTecnologia,
                datasets: [{
                    data: valuesTecnologia,
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
                                const porcentaje = ((valor / totalTecnologia) * 100).toFixed(1);
                                return ` ${context.label}: ${valor} (${porcentaje}%)`;
                            }
                        }
                    }
                },
                cutout: '65%',
                onHover: (event, chartElements) => {
                    if (chartElements.length > 0) {
                        const index = chartElements[0].index;
                        const label = labelsTecnologia[index];
                        const val = valuesTecnologia[index];
                        const percent = ((val / totalTecnologia) * 100).toFixed(1);
                        if (centerLabel) centerLabel.textContent = label;
                        if (centerValue) centerValue.textContent = `${val} (${percent}%)`;
                    } else {
                        if (centerLabel) centerLabel.textContent = 'Total Impresoras';
                        if (centerValue) centerValue.textContent = totalTecnologia;
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
                            font: { weight: 'bold' }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }

});
