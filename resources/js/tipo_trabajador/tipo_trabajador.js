document.addEventListener('DOMContentLoaded', function () {

    // === ELEMENTOS DEL FORMULARIO DE ALTA ===
    const inputTipo     = document.getElementById('tipo');
    const tipoFeedback  = document.getElementById('tipoFeedback');
    const btnGuardar    = document.getElementById('btnGuardar');

    // ─────────────────────────────────────────────────────────
    // LÓGICA: VALIDACIÓN EN TIEMPO REAL DEL NOMBRE (AJAX)
    // ─────────────────────────────────────────────────────────
    let verificarTimeout = null;

    if (inputTipo && tipoFeedback) {
        inputTipo.addEventListener('input', function () {
            const valor = this.value.trim();

            // Limpiar feedback si el campo está vacío
            if (!valor) {
                tipoFeedback.classList.add('d-none');
                tipoFeedback.textContent = '';
                if (btnGuardar) btnGuardar.disabled = false;
                return;
            }

            clearTimeout(verificarTimeout);
            verificarTimeout = setTimeout(() => {
                fetch(`/tipo-trabajador/verificar?tipo=${encodeURIComponent(valor)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    tipoFeedback.classList.remove('d-none', 'text-success', 'text-danger');
                    if (data.disponible) {
                        tipoFeedback.classList.add('text-success');
                        tipoFeedback.textContent = '✔ El nombre está disponible.';
                        if (btnGuardar) btnGuardar.disabled = false;
                    } else {
                        tipoFeedback.classList.add('text-danger');
                        tipoFeedback.textContent = '✘ Este tipo de trabajador ya existe.';
                        if (btnGuardar) btnGuardar.disabled = true;
                    }
                })
                .catch(() => {
                    tipoFeedback.classList.add('d-none');
                });
            }, 400);
        });
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: CONFIRMACIÓN SweetAlert2 AL ALTERNAR STATUS (DELEGADO)
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Error al actualizar el estado');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonColor: '#2b6cb0'
                        }).then(() => {
                            // Indicar al contenedor de la tabla interactiva que recargue sus datos
                            const container = document.querySelector('[data-tabla-interactiva]');
                            if (container) {
                                container.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        alert(data.mensaje);
                        location.reload();
                    }
                }
            })
            .catch(err => {
                console.error('Error:', err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                } else {
                    alert('No se pudo actualizar el estado.');
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title:             `¿Desea ${accion} el tipo de trabajador?`,
                text:              `El tipo "${nombre}" cambiará de estado en el sistema.`,
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
            if (confirm(`¿Está seguro de que desea ${accion} el tipo "${nombre}"?`)) {
                doRequest();
            }
        }
    });

    // Abrir modal automáticamente si hay errores de validación (tras rebote del formulario)
    const pageErrors = document.getElementById('hasFormErrors');
    if (pageErrors && pageErrors.dataset.errors === '1') {
        const modalEl = document.getElementById('modalRegistrarTipo');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INICIALIZACIÓN DE CHART.JS (GRÁFICAS ANALÍTICAS)
    // ─────────────────────────────────────────────────────────────────────────
    const canvasDonut = document.getElementById('donutEstadoChart');
    const canvasBarra = document.getElementById('barTrabajadorChart');

    if (canvasDonut && typeof Chart !== 'undefined') {
        const centerLabel = document.getElementById('donutCenterLabel');
        const centerValue = document.getElementById('donutCenterValue');

        const datosEstado = JSON.parse(canvasDonut.dataset.json || '{}');
        const labelsEstado = Object.keys(datosEstado);
        const valuesEstado = Object.values(datosEstado);
        const totalEstado  = valuesEstado.reduce((a, b) => a + b, 0);

        if (centerValue) centerValue.textContent = totalEstado;

        const coloresEstado = ['#10b981', '#ef4444']; // Verde activos, Rojo inactivos

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

    if (canvasBarra && typeof Chart !== 'undefined') {
        const datosTrab = JSON.parse(canvasBarra.dataset.json || '{}');
        const labelsTrab = Object.keys(datosTrab);
        const valuesTrab = Object.values(datosTrab);

        new Chart(canvasBarra.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsTrab,
                datasets: [{
                    label: 'Cantidad de Trabajadores',
                    data: valuesTrab,
                    backgroundColor: '#3b82f6',
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

});
