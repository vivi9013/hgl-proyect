document.addEventListener('DOMContentLoaded', function () {
    const contenedorTabla = document.getElementById('contenedor-tabla');
    const inputBuscar = document.getElementById('buscar');
    const formFiltros = document.getElementById('formFiltros');
    let timerBusqueda = null;

    // ── Función para Recargar la Tabla AJAX ─────────────────────────────────
    function cargarTabla(url = null) {
        if (!contenedorTabla) return;
        const baseUrl = url ? url.split('?')[0] : window.location.pathname;
        const params = new URLSearchParams(new FormData(formFiltros)).toString();
        const fetchUrl = params ? `${baseUrl}?${params}` : baseUrl;

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
                const newUrl = params ? `${window.location.pathname}?${params}` : window.location.pathname;
                window.history.replaceState(null, '', newUrl);
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

    // ── Evento de Paginación ────────────────────────────────────────────────
    if (contenedorTabla) {
        contenedorTabla.addEventListener('click', function (e) {
            const linkPaginacion = e.target.closest('.pagination a');
            if (linkPaginacion) {
                e.preventDefault();
                cargarTabla(linkPaginacion.href);
            }
        });
    }

    // ── Checkbox Filtro de Estatus ──────────────────────────────────────────
    const checkboxesStatus = document.querySelectorAll('.filtro-status');
    checkboxesStatus.forEach(chk => {
        chk.addEventListener('change', () => cargarTabla());
    });

    // ── Delegación de eventos para cambiar estatus con SweetAlert2 ─────────
    if (contenedorTabla) {
        contenedorTabla.addEventListener('click', function (e) {
            const badge = e.target.closest('.btn-toggle-status');
            if (!badge) return;

            const id = badge.dataset.id;
            const statusActual = badge.dataset.status;
            const accion = statusActual === '1' ? 'desactivar' : 'activar';

            Swal.fire({
                title: `¿Deseas ${accion} esta área?`,
                text: `El estatus del área cambiará a ${statusActual === '1' ? 'Inactivo' : 'Activo'}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(`/peticion-insumos/areas-abastecimiento/${id}/status`, {
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

    // ── Validación en tiempo real del Nombre en Modal ────────────────────────
    const inputNombreModal = document.getElementById('modal_nombre');
    const feedbackNombre = document.getElementById('feedback_modal_nombre');

    if (inputNombreModal) {
        inputNombreModal.addEventListener('blur', function () {
            const val = this.value.trim();
            if (!val) return;

            fetch(`/peticion-insumos/areas-abastecimiento/verificar?nombre=${encodeURIComponent(val)}`)
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
        });
    }

    // ── Inicialización de Gráficas con Chart.js ──────────────────────────────
    const canvasEstatus = document.getElementById('chartEstatusArea');
    const canvasTopSubareas = document.getElementById('chartTopSubareas');

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

    if (canvasTopSubareas && window.dataGrafica) {
        new Chart(canvasTopSubareas, {
            type: 'bar',
            data: {
                labels: window.dataGrafica.topSubareas.labels,
                datasets: [{
                    label: 'Subáreas Asignadas',
                    data: window.dataGrafica.topSubareas.val,
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
