/**
 * Lógica JavaScript para el módulo de Motivos de Devoluciones
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

    const inputDescripcion         = document.getElementById('descripcion');
    const selectModificar          = document.getElementById('modificar');
    const feedbackDisponibilidad   = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner           = document.getElementById('loadingSpinner');
    const btnGuardar               = document.getElementById('btnGuardar');

    // ── 0. Filtrado en Tiempo Real ─────────────────────────────────────────────
    const inputBuscar = document.getElementById('inputBuscar');
    const formBuscar  = document.getElementById('formBuscar');
    if (inputBuscar && formBuscar) {
        const resetearSiVacio = () => {
            if (inputBuscar.value.trim() === '' && window.location.search.includes('buscar=')) {
                window.location.href = window.location.pathname;
            }
        };

        inputBuscar.addEventListener('search', resetearSiVacio);

        inputBuscar.addEventListener('input', function () {
            const query = inputBuscar.value.toLowerCase().trim();

            if (query === '' && window.location.search.includes('buscar=')) {
                window.location.href = window.location.pathname;
                return;
            }

            // Filtrado local de filas de la tabla en tiempo real
            const rows = document.querySelectorAll('#tablaMotivos tbody tr');
            let matchCount = 0;
            rows.forEach(row => {
                // Ignorar filas de mensaje
                if (row.cells.length === 1 && row.cells[0].classList.contains('text-center')) {
                    return;
                }
                if (row.id === 'noLocalResultsRow') {
                    return;
                }

                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.classList.remove('d-none');
                    matchCount++;
                } else {
                    row.classList.add('d-none');
                }
            });

            // Mostrar u ocultar fila de no resultados locales
            let noRecordsRow = document.getElementById('noLocalResultsRow');
            if (query !== '' && matchCount === 0) {
                if (!noRecordsRow) {
                    noRecordsRow = document.createElement('tr');
                    noRecordsRow.id = 'noLocalResultsRow';
                    noRecordsRow.innerHTML = `
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2 d-block"></i>
                            No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en el servidor.
                        </td>
                    `;
                    document.querySelector('#tablaMotivos tbody').appendChild(noRecordsRow);
                } else {
                    noRecordsRow.style.display = '';
                    noRecordsRow.querySelector('td').innerHTML = `
                        <i class="fa fa-search fa-2x mb-2 d-block"></i>
                        No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en el servidor.
                    `;
                }
            } else if (noRecordsRow) {
                noRecordsRow.style.display = 'none';
            }
        });
    }

    // ── 1. Alertas SweetAlert2 con sesión de Laravel ──────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');

    if (alertaExitog && typeof Swal !== 'undefined') {
        const msg = alertaExitog.getAttribute('data-message') || 'Operación realizada correctamente.';
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: msg,
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }


    // ── 2. Verificación AJAX de disponibilidad de descripción (debounce 300 ms) ─
    if (inputDescripcion && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        let timeoutId;

        const realizarVerificacion = () => {
            clearTimeout(timeoutId);
            const descripcion = inputDescripcion.value.trim();

            if (!descripcion) {
                feedbackDisponibilidad.innerHTML = '';
                inputDescripcion.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/motivos/verificar?descripcion=${encodeURIComponent(descripcion)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error de servidor');
                    return response.json();
                })
                .then(data => {
                    loadingSpinner.style.display = 'none';

                    if (data.disponible) {
                        feedbackDisponibilidad.innerHTML =
                            '<span class="text-success-custom"><i class="fa fa-check-circle"></i> Descripción disponible</span>';
                        inputDescripcion.classList.remove('is-invalid');
                        inputDescripcion.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML =
                            '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> Este motivo ya se encuentra registrado</span>';
                        inputDescripcion.classList.remove('is-valid');
                        inputDescripcion.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar motivo:', error);
                });
            }, 300);
        };

        inputDescripcion.addEventListener('input', realizarVerificacion);
    }

    // ── 3. Confirmación SweetAlert2 + envío GET para cambiar status ─────────────
    const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
    toggleStatusLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const url    = this.getAttribute('data-url');
            const nombre = this.getAttribute('data-nombre');
            const activo = parseInt(this.getAttribute('data-activo'));

            const accion         = activo === 1 ? 'desactivar' : 'activar';
            const iconType       = activo === 1 ? 'warning' : 'question';
            const confirmBtnText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';

            const doRequest = () => {
                window.location.href = url;
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `¿Desea ${accion} el motivo?`,
                    text: `El motivo "${nombre}" será ${activo === 1 ? 'desactivado' : 'activado'} en el sistema.`,
                    icon: iconType,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        doRequest();
                    }
                });
            } else {
                if (confirm(`¿Está seguro de que desea ${accion} el motivo "${nombre}"?`)) {
                    doRequest();
                }
            }
        });
    });

});
