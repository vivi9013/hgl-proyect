document.addEventListener('DOMContentLoaded', function () {

    // === ELEMENTOS DEL FORMULARIO DE ALTA ===
    const inputNumEmpleado    = document.getElementById('num_empleado');
    const selectPersona       = document.getElementById('id_persona');
    const numEmpleadoFeedback = document.getElementById('numEmpleadoFeedback');
    const personaFeedback     = document.getElementById('personaFeedback');
    const btnGuardar          = document.getElementById('btnGuardar');

    // === ELEMENTOS DEL FORMULARIO DE EDICIÓN ===
    const editInputNumEmp     = document.getElementById('edit_num_empleado');
    const editSelectPersona   = document.getElementById('edit_id_persona');
    const editNumEmpFeedback  = document.getElementById('editNumEmpleadoFeedback');
    const editPersonaFeedback = document.getElementById('editPersonaFeedback');
    const btnActualizar       = document.getElementById('btnActualizar');

    // ─────────────────────────────────────────────────────────
    // LÓGICA: VERIFICACIÓN DE DUPLICADOS EN TIEMPO REAL (ALTA)
    // ─────────────────────────────────────────────────────────
    let checkTimeout = null;

    const verificarDuplicadosAlta = () => {
        const numEmp = inputNumEmpleado ? inputNumEmpleado.value.trim() : '';
        const personaId = selectPersona ? selectPersona.value : '';

        if (!numEmp && !personaId) {
            if (numEmpleadoFeedback) numEmpleadoFeedback.classList.add('d-none');
            if (personaFeedback) personaFeedback.classList.add('d-none');
            if (btnGuardar) btnGuardar.disabled = false;
            return;
        }

        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(() => {
            fetch(`/trabajadores/verificar?num_empleado=${encodeURIComponent(numEmp)}&id_persona=${encodeURIComponent(personaId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                let hasError = false;

                if (numEmpleadoFeedback) {
                    numEmpleadoFeedback.classList.remove('d-none', 'text-success', 'text-danger');
                    if (data.existe_num) {
                        numEmpleadoFeedback.classList.add('text-danger');
                        numEmpleadoFeedback.textContent = '✘ Este número de empleado ya está en uso.';
                        hasError = true;
                    } else if (numEmp) {
                        numEmpleadoFeedback.classList.add('text-success');
                        numEmpleadoFeedback.textContent = '✔ Número de empleado disponible.';
                    }
                }

                if (personaFeedback) {
                    personaFeedback.classList.remove('d-none', 'text-success', 'text-danger');
                    if (data.existe_persona) {
                        personaFeedback.classList.add('text-danger');
                        personaFeedback.textContent = '✘ Esta persona ya tiene un expediente asignado.';
                        hasError = true;
                    } else if (personaId) {
                        personaFeedback.classList.add('text-success');
                        personaFeedback.textContent = '✔ Persona disponible.';
                    }
                }

                if (btnGuardar) btnGuardar.disabled = hasError;
            })
            .catch(() => {});
        }, 400);
    };

    if (inputNumEmpleado) inputNumEmpleado.addEventListener('input', verificarDuplicadosAlta);
    if (selectPersona) selectPersona.addEventListener('change', verificarDuplicadosAlta);

    // ─────────────────────────────────────────────────────────
    // LÓGICA: EDICIÓN DE TRABAJADOR VÍA MODAL Y AJAX
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-editar-trabajador');
        if (!btnEdit) return;

        e.preventDefault();
        const trabajadorId = btnEdit.getAttribute('data-id');

        fetch(`/trabajadores/${trabajadorId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.trabajador) {
                const t = data.trabajador;
                const formEdit = document.getElementById('formEditarTrabajador');

                if (formEdit) {
                    formEdit.action = `/trabajadores/${t.id}`;
                    document.getElementById('edit_trabajador_id').value = t.id;
                    if (editInputNumEmp) editInputNumEmp.value = t.num_empleado || '';
                    if (editSelectPersona) editSelectPersona.value = t.id_persona || '';
                    if (document.getElementById('edit_id_sede')) document.getElementById('edit_id_sede').value = t.id_sede || '';
                    if (document.getElementById('edit_id_departamento')) document.getElementById('edit_id_departamento').value = t.id_departamento || '';
                    if (document.getElementById('edit_id_puesto')) document.getElementById('edit_id_puesto').value = t.id_puesto || '';
                    if (document.getElementById('edit_id_tipo_trabajador')) document.getElementById('edit_id_tipo_trabajador').value = t.id_tipo_trabajador || '';
                    if (document.getElementById('edit_fecha_ingreso')) document.getElementById('edit_fecha_ingreso').value = t.fecha_ingreso || '';

                    const modalEl = document.getElementById('modalEditarTrabajador');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }
            }
        })
        .catch(err => {
            console.error('Error al cargar datos del trabajador:', err);
        });
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: FILTROS ADICIONALES (DEPARTAMENTO, PUESTO, TIPO, SEDE)
    // ─────────────────────────────────────────────────────────
    const filtroSelects = document.querySelectorAll('.filtro-select');
    filtroSelects.forEach(select => {
        select.addEventListener('change', function () {
            const container = document.querySelector('[data-tabla-interactiva]');
            if (container) {
                container.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));
            }
        });
    });

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
                title:             `¿Desea ${accion} al trabajador?`,
                text:              `El expediente "${nombre}" cambiará de estado en el sistema.`,
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
            if (confirm(`¿Está seguro de que desea ${accion} al trabajador "${nombre}"?`)) {
                doRequest();
            }
        }
    });

    // Reabrir modal en caso de rebote con errores
    const pageErrors = document.getElementById('hasFormErrors');
    if (pageErrors && pageErrors.dataset.errors === '1') {
        const modalEl = document.getElementById('modalRegistrarTrabajador');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    const editPageErrors = document.getElementById('hasEditFormErrors');
    if (editPageErrors && editPageErrors.dataset.id) {
        const modalEl = document.getElementById('modalEditarTrabajador');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INICIALIZACIÓN DE CHART.JS (GRÁFICAS ANALÍTICAS)
    // ─────────────────────────────────────────────────────────────────────────
    const canvasDonutEstado = document.getElementById('donutEstadoChart');
    const canvasBarDepto     = document.getElementById('barDeptoChart');
    const canvasBarPuesto    = document.getElementById('barPuestoChart');
    const canvasBarTipo      = document.getElementById('barTipoChart');

    if (canvasDonutEstado && typeof Chart !== 'undefined') {
        const rawData = JSON.parse(canvasDonutEstado.dataset.json || '[]');
        const labels  = rawData.map(i => i.label);
        const values  = rawData.map(i => i.total);

        new Chart(canvasDonutEstado.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '65%'
            }
        });
    }

    const crearBarChart = (canvas, colorHex) => {
        if (!canvas || typeof Chart === 'undefined') return;
        const rawData = JSON.parse(canvas.dataset.json || '[]');
        const labels  = rawData.map(i => i.label);
        const values  = rawData.map(i => i.total);

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad de Trabajadores',
                    data: values,
                    backgroundColor: colorHex,
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { ticks: { font: { size: 10 } } }
                }
            }
        });
    };

    crearBarChart(canvasBarDepto, '#10b981');
    crearBarChart(canvasBarPuesto, '#06b6d4');
    crearBarChart(canvasBarTipo, '#f59e0b');

});
