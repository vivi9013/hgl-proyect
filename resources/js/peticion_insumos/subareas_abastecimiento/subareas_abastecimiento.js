document.addEventListener('DOMContentLoaded', function () {
    const contenedorTabla = document.getElementById('contenedor-tabla');
    const inputBuscar = document.getElementById('buscar');
    const selectAreaFiltro = document.getElementById('id_area_abastecimiento_filtro');
    const formFiltros = document.getElementById('formFiltros');
    let timerBusqueda = null;

    // ── Recargar Tabla AJAX ──────────────────────────────────────────────────
    function cargarTabla(url = null) {
        if (!contenedorTabla) return;
        const targetUrl = url || window.location.href;
        const params = new URLSearchParams(new FormData(formFiltros)).toString();
        const fetchUrl = targetUrl.includes('?') 
            ? `${targetUrl}&${params}` 
            : `${targetUrl}?${params}`;

        contenedorTabla.style.opacity = '0.5';

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                contenedorTabla.innerHTML = data.html;
            }
        })
        .catch(err => console.error('Error al cargar la tabla:', err))
        .finally(() => {
            contenedorTabla.style.opacity = '1';
        });
    }

    // ── Búsqueda reactiva con debounce ──────────────────────────────────────
    if (inputBuscar) {
        inputBuscar.addEventListener('input', function () {
            clearTimeout(timerBusqueda);
            timerBusqueda = setTimeout(() => {
                cargarTabla();
            }, 300);
        });
    }

    if (selectAreaFiltro) {
        selectAreaFiltro.addEventListener('change', () => cargarTabla());
    }

    // ── Paginación ──────────────────────────────────────────────────────────
    if (contenedorTabla) {
        contenedorTabla.addEventListener('click', function (e) {
            const linkPaginacion = e.target.closest('.pagination a');
            if (linkPaginacion) {
                e.preventDefault();
                cargarTabla(linkPaginacion.href);
            }
        });
    }

    // ── Checkboxes Filtro Estatus ───────────────────────────────────────────
    const checkboxesStatus = document.querySelectorAll('.filtro-status');
    checkboxesStatus.forEach(chk => {
        chk.addEventListener('change', () => cargarTabla());
    });

    // ── Cambiar Estatus con SweetAlert2 ─────────────────────────────────────
    if (contenedorTabla) {
        contenedorTabla.addEventListener('click', function (e) {
            const badge = e.target.closest('.btn-toggle-status');
            if (!badge) return;

            const id = badge.dataset.id;
            const statusActual = badge.dataset.status;
            const accion = statusActual === '1' ? 'desactivar' : 'activar';

            Swal.fire({
                title: `¿Deseas ${accion} esta subárea?`,
                text: `El estatus de la subárea cambiará a ${statusActual === '1' ? 'Inactivo' : 'Activo'}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(`/peticion-insumos/subareas-abastecimiento/${id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: data.mensaje,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            cargarTabla();
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'No se pudo actualizar el estatus.', 'error');
                    });
                }
            });
        });
    }

    // ── Validación en tiempo real en Modal ──────────────────────────────────
    const selectAreaModal = document.getElementById('modal_id_area');
    const inputNombreModal = document.getElementById('modal_nombre');
    const feedbackNombre = document.getElementById('feedback_modal_nombre');

    function validarDuplicadoModal() {
        if (!inputNombreModal || !selectAreaModal) return;
        const nombre = inputNombreModal.value.trim();
        const areaId = selectAreaModal.value;

        if (!nombre || !areaId) return;

        fetch(`/peticion-insumos/subareas-abastecimiento/verificar?nombre=${encodeURIComponent(nombre)}&id_area_abastecimiento=${areaId}`)
            .then(res => res.json())
            .then(data => {
                if (!data.valido) {
                    inputNombreModal.classList.add('is-invalid');
                    if (feedbackNombre) feedbackNombre.textContent = data.mensaje;
                } else {
                    inputNombreModal.classList.remove('is-invalid');
                    if (feedbackNombre) feedbackNombre.textContent = '';
                }
            });
    }

    if (inputNombreModal) inputNombreModal.addEventListener('blur', validarDuplicadoModal);
    if (selectAreaModal) selectAreaModal.addEventListener('change', validarDuplicadoModal);

    // ── Inicialización de Gráficas con Chart.js ──────────────────────────────
    const canvasEstatus = document.getElementById('chartEstatusSubarea');
    const canvasPorArea = document.getElementById('chartSubareasPorArea');

    if (canvasEstatus && window.dataGrafica) {
        new Chart(canvasEstatus, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [window.dataGrafica.estatus.activos, window.dataGrafica.estatus.inactivos],
                    backgroundColor: ['#198754', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    if (canvasPorArea && window.dataGrafica) {
        new Chart(canvasPorArea, {
            type: 'bar',
            data: {
                labels: window.dataGrafica.porArea.labels,
                datasets: [{
                    label: 'Subáreas Registradas',
                    data: window.dataGrafica.porArea.val,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
