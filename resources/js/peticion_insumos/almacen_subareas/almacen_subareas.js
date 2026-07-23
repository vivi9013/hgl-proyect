document.addEventListener('DOMContentLoaded', function () {

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ─────────────────────────────────────────────────────────
    // LÓGICA: ASIGNAR INSUMO A SUBÁREA (MODAL)
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnAdd = e.target.closest('.btn-agregar-insumo');
        if (!btnAdd) return;

        e.preventDefault();
        const idAlmacen = btnAdd.getAttribute('data-id');
        const subareaNombre = btnAdd.getAttribute('data-subarea') || '';

        const formAdd = document.getElementById('formAgregarInsumo');
        const spanNombre = document.getElementById('nombreSubareaModal');

        if (formAdd) {
            formAdd.action = `/peticion-insumos/almacen-subareas/${idAlmacen}/insumo`;
        }
        if (spanNombre) {
            spanNombre.textContent = subareaNombre;
        }

        const modalEl = document.getElementById('modalAgregarInsumo');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: EDITAR DETALLE (STOCK Y FONDO FIJO VIA AJAX)
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-editar-detalle');
        if (!btnEdit) return;

        e.preventDefault();
        const idDetalle = btnEdit.getAttribute('data-id');
        const cantidad = btnEdit.getAttribute('data-cantidad');
        const fondo = btnEdit.getAttribute('data-fondo');

        document.getElementById('edit_detalle_id').value = idDetalle;
        document.getElementById('edit_cantidad').value = cantidad;
        document.getElementById('edit_fondo_fijo').value = fondo;

        const modalEl = document.getElementById('modalEditarDetalle');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    const formEditarDetalle = document.getElementById('formEditarDetalle');
    if (formEditarDetalle) {
        formEditarDetalle.addEventListener('submit', function (e) {
            e.preventDefault();
            const idDetalle = document.getElementById('edit_detalle_id').value;
            const cantidad = document.getElementById('edit_cantidad').value;
            const fondo = document.getElementById('edit_fondo_fijo').value;

            fetch(`/peticion-insumos/almacen-subareas/detalle/${idDetalle}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cantidad: cantidad, fondo_fijo: fondo })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('modalEditarDetalle');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonColor: '#2b6cb0'
                        }).then(() => {
                            recargarTabla();
                        });
                    } else {
                        alert(data.mensaje);
                        recargarTabla();
                    }
                }
            })
            .catch(err => {
                console.error('Error al actualizar detalle:', err);
            });
        });
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA: ELIMINAR DETALLE DE SUBÁREA VIA AJAX
    // ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btnDelete = e.target.closest('.btn-eliminar-detalle');
        if (!btnDelete) return;

        e.preventDefault();
        const idDetalle = btnDelete.getAttribute('data-id');

        const doDelete = () => {
            fetch(`/peticion-insumos/almacen-subareas/detalle/${idDetalle}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('¡Eliminado!', data.mensaje, 'success').then(() => {
                            recargarTabla();
                        });
                    } else {
                        alert(data.mensaje);
                        recargarTabla();
                    }
                }
            })
            .catch(err => {
                console.error('Error al eliminar detalle:', err);
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Quitar insumo de la subárea?',
                text: 'El insumo dejará de pertenecer al inventario de esta subárea.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) doDelete();
            });
        } else {
            if (confirm('¿Está seguro de quitar este insumo?')) doDelete();
        }
    });

    // ─────────────────────────────────────────────────────────
    // LÓGICA: CASCADA ÁREA → SUBÁREA (AJAX)
    // ─────────────────────────────────────────────────────────
    const selectArea    = document.getElementById('filter-area');
    const selectSubarea = document.getElementById('filter-subarea');

    const cargarSubareas = (idArea) => {
        if (!selectSubarea) return;

        // Limpiar y deshabilitar mientras carga
        selectSubarea.innerHTML = '<option value="">Cargando...</option>';
        selectSubarea.disabled = true;

        const url = `/peticion-insumos/almacen-subareas/subareas-por-area?id_area_abastecimiento=${idArea}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            selectSubarea.innerHTML = '<option value="">-- Todas las Subáreas --</option>';
            data.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.id_subarea_abastecimiento;
                opt.textContent = sub.siglas ? `[${sub.siglas}] ${sub.nombre}` : sub.nombre;
                selectSubarea.appendChild(opt);
            });
            selectSubarea.disabled = false;
        })
        .catch(() => {
            selectSubarea.innerHTML = '<option value="">-- Error al cargar --</option>';
            selectSubarea.disabled = false;
        });
    };

    if (selectArea) {
        selectArea.addEventListener('change', function () {
            const idArea = this.value;
            if (idArea) {
                cargarSubareas(idArea);
            } else {
                // Sin área seleccionada: resetear subárea
                if (selectSubarea) {
                    selectSubarea.innerHTML = '<option value="">-- Todas las Subáreas --</option>';
                    selectSubarea.disabled = false;
                }
            }
        });
    }

    // Al cambiar subárea, disparar búsqueda automáticamente
    if (selectSubarea) {
        selectSubarea.addEventListener('change', function () {
            if (this.value) {
                recargarTabla();
            }
        });
    }

    const recargarTabla = () => {
        const container = document.querySelector('[data-tabla-interactiva]');
        if (container) {
            container.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));
        } else {
            location.reload();
        }
    };

    // ─────────────────────────────────────────────────────────
    // LÓGICA: CONFIRMACIÓN SweetAlert2 AL ALTERNAR STATUS
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
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonColor: '#2b6cb0'
                        }).then(() => {
                            recargarTabla();
                        });
                    } else {
                        alert(data.mensaje);
                        recargarTabla();
                    }
                }
            })
            .catch(err => {
                console.error('Error:', err);
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title:             `¿Desea ${accion} el almacén de la subárea?`,
                text:              `El almacén "${nombre}" cambiará de estado.`,
                icon:              iconType,
                showCancelButton:  true,
                confirmButtonColor: '#2b6cb0',
                cancelButtonColor:  '#d33',
                confirmButtonText: confirmBtnText,
                cancelButtonText:  'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) doRequest();
            });
        } else {
            if (confirm(`¿Desea ${accion} el almacén "${nombre}"?`)) doRequest();
        }
    });

    // Reabrir modal en caso de rebote con errores
    const pageErrors = document.getElementById('hasFormErrors');
    if (pageErrors && pageErrors.dataset.errors === '1') {
        const modalEl = document.getElementById('modalRegistrarAlmacen');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INICIALIZACIÓN DE CHART.JS (GRÁFICAS ANALÍTICAS)
    // ─────────────────────────────────────────────────────────────────────────
    const canvasDonutEstado = document.getElementById('donutEstadoChart');
    const canvasBarSubarea   = document.getElementById('barSubareaChart');
    const canvasBarBajos     = document.getElementById('barInsumosBajosChart');

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
                plugins: { legend: { position: 'bottom' } },
                cutout: '65%'
            }
        });
    }

    if (canvasBarSubarea && typeof Chart !== 'undefined') {
        const rawData = JSON.parse(canvasBarSubarea.dataset.json || '[]');
        const labels  = rawData.map(i => i.label);
        const values  = rawData.map(i => i.total);

        new Chart(canvasBarSubarea.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Insumos Registrados',
                    data: values,
                    backgroundColor: '#10b981',
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
    }

    if (canvasBarBajos && typeof Chart !== 'undefined') {
        const rawData = JSON.parse(canvasBarBajos.dataset.json || '[]');
        const labels = rawData.map(i => i.label);
        const stockValues = rawData.map(i => i.cantidad);
        const fondoValues = rawData.map(i => i.fondo_fijo);

        new Chart(canvasBarBajos.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Stock Actual',
                        data: stockValues,
                        backgroundColor: '#ef4444',
                        borderRadius: 6
                    },
                    {
                        label: 'Fondo Fijo (Meta)',
                        data: fondoValues,
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true },
                    x: { ticks: { font: { size: 10 } } }
                }
            }
        });
    }

});
