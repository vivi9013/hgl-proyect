/**
 * Lógica JavaScript para el módulo de Áreas de Almacén
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

    const inputNombre              = document.getElementById('nombre');
    const feedbackDisponibilidad   = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner           = document.getElementById('loadingSpinner');
    const btnGuardar               = document.getElementById('btnGuardar');

    // ── 0. Filtrado en Tiempo Real (basado en el buscador de Panel de Control) ─────
    const inputBuscar = document.getElementById('inputBuscar');
    const formBuscar  = document.getElementById('formBuscar');
    if (inputBuscar && formBuscar) {
        inputBuscar.addEventListener('input', function () {
            const query = inputBuscar.value.toLowerCase().trim();

            // Filtrado local de filas de la tabla en tiempo real (como en el Panel de Control)
            const rows = document.querySelectorAll('#tablaAreas tbody tr');
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
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2 d-block"></i>
                            No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en el servidor.
                        </td>
                    `;
                    document.querySelector('#tablaAreas tbody').appendChild(noRecordsRow);
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
    const alertaExito  = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'El área de almacén se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'El área de almacén se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // ── 2. Verificación AJAX de disponibilidad de nombre (debounce 300 ms) ────
    if (inputNombre && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        let timeoutId;

        inputNombre.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const nombre = this.value.trim();

            if (!nombre) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                fetch(`/areas-almacen/verificar?nombre=${encodeURIComponent(nombre)}`, {
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
                            '<span class="text-success-custom"><i class="fa fa-check-circle"></i> Nombre disponible</span>';
                        inputNombre.classList.remove('is-invalid');
                        inputNombre.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML =
                            '<span class="text-danger-custom"><i class="fa fa-times-circle"></i> Esta área ya se encuentra registrada</span>';
                        inputNombre.classList.remove('is-valid');
                        inputNombre.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    loadingSpinner.style.display = 'none';
                    console.error('Error al verificar área:', error);
                });
            }, 300);
        });
    }

    // ── 3. Confirmación SweetAlert2 + envío PATCH para cambiar status ──────────
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
                    title: `¿Desea ${accion} el área?`,
                    text: `El área "${nombre}" será ${activo === 1 ? 'desactivada' : 'activada'} en el sistema.`,
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
                if (confirm(`¿Está seguro de que desea ${accion} el área "${nombre}"?`)) {
                    doRequest();
                }
            }
        });
    });

});
