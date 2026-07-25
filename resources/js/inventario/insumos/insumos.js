import { initPanelClaves } from '../shared/panel-claves.js';

/**
 * Lógica JavaScript para el catálogo de Insumos
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {

    const inputClave             = document.getElementById('clave');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner         = document.getElementById('loadingSpinner');
    const btnGuardar             = document.getElementById('btnGuardar');
    const insumoIdField          = document.getElementById('insumo_id'); // Presente solo en la vista de edición

    // ── 0. Filtrado en Tiempo Real (buscador local) ─────────────────────────
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

            const rows = document.querySelectorAll('#tablaInsumos tbody tr');
            let matchCount = 0;
            rows.forEach(row => {
                // Ignorar la fila de mensaje
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
                            <i class="fa fa-search fa-2x mb-2 d-block text-dark"></i>
                            No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en la base de datos.
                        </td>
                    `;
                    document.querySelector('#tablaInsumos tbody').appendChild(noRecordsRow);
                } else {
                    noRecordsRow.style.display = '';
                    noRecordsRow.querySelector('td').innerHTML = `
                        <i class="fa fa-search fa-2x mb-2 d-block text-dark"></i>
                        No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en la base de datos.
                    `;
                }
            } else if (noRecordsRow) {
                noRecordsRow.style.display = 'none';
            }
        });

        initPanelClaves({
            panelId: 'panelClaves',
            inputBuscarId: 'inputBuscar',
            inputHiddenId: null,
            sugerenciasId: null,
            endpoint: '/insumos',
            columnaExtra: 'tipo',
            onSelect: (insumo) => {
                if (inputBuscar) {
                    inputBuscar.value = insumo.clave;
                }
                if (formBuscar) {
                    formBuscar.submit();
                }
            }
        });
    }

    // ── 1. Alertas SweetAlert2 con sesión flash de Laravel ──────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito  = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitog.getAttribute('data-message') || 'El insumo se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.getAttribute('data-message') || 'El insumo se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Aceptar'
        });
    }

    // ── 2. Verificación AJAX de disponibilidad de clave (debounce 300 ms) ────
    if (inputClave && feedbackDisponibilidad && btnGuardar) {
        let timeoutId;

        inputClave.addEventListener('input', function () {
            clearTimeout(timeoutId);
            const clave = this.value.trim();

            if (!clave) {
                feedbackDisponibilidad.innerHTML = '';
                inputClave.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            timeoutId = setTimeout(() => {
                if (loadingSpinner) loadingSpinner.style.display = 'block';
                feedbackDisponibilidad.innerHTML = '';

                let url = `/insumos/verificar?clave=${encodeURIComponent(clave)}`;
                if (insumoIdField && insumoIdField.value) {
                    url += `&id=${encodeURIComponent(insumoIdField.value)}`;
                }

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error en la llamada al servidor');
                    return response.json();
                })
                .then(data => {
                    if (loadingSpinner) loadingSpinner.style.display = 'none';

                    if (data.disponible) {
                        feedbackDisponibilidad.innerHTML =
                            '<span class="text-success-custom"><i class="fa fa-check-circle me-1"></i> Clave disponible</span>';
                        inputClave.classList.remove('is-invalid');
                        inputClave.classList.add('is-valid');
                        btnGuardar.disabled = false;
                    } else {
                        feedbackDisponibilidad.innerHTML =
                            '<span class="text-danger-custom"><i class="fa fa-times-circle me-1"></i> Esta clave ya se encuentra registrada</span>';
                        inputClave.classList.remove('is-valid');
                        inputClave.classList.add('is-invalid');
                        btnGuardar.disabled = true;
                    }
                })
                .catch(error => {
                    if (loadingSpinner) loadingSpinner.style.display = 'none';
                    console.error('Error al verificar la clave:', error);
                });
            }, 300);
        });
    }

    // ── 3. Confirmación SweetAlert2 + envío GET para cambiar status ───────────
    const toggleStatusLinks = document.querySelectorAll('.btn-toggle-status');
    toggleStatusLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const url    = this.getAttribute('data-url');
            const clave  = this.getAttribute('data-clave');
            const activo = parseInt(this.getAttribute('data-activo'));

            const accion         = activo === 1 ? 'desactivar' : 'activar';
            const iconType       = activo === 1 ? 'warning' : 'question';
            const confirmBtnText = activo === 1 ? 'Sí, desactivar' : 'Sí, activar';

            const doRequest = () => {
                window.location.href = url;
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `¿Desea ${accion} el insumo?`,
                    text: `El insumo con clave "${clave}" será ${activo === 1 ? 'desactivado' : 'activado'} en el sistema.`,
                    icon: iconType,
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#d33',
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        doRequest();
                    }
                });
            } else {
                if (confirm(`¿Está seguro de que desea ${accion} el insumo con clave "${clave}"?`)) {
                    doRequest();
                }
            }
        });
    });

});
