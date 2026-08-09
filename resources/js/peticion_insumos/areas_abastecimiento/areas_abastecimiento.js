import { initTablaAjax } from '@/shared/tabla-ajax.js';

document.addEventListener('DOMContentLoaded', function () {

    // ── Tabla AJAX (búsqueda, paginación, filtros, toggle de estatus) ────────
    initTablaAjax({
        contenedorId: 'contenedor-tabla',
        formId: 'formFiltros',
        inputBuscarId: 'buscar',
        statusSelector: '.filtro-status',
        toggleSelector: '.btn-toggle-status',
        statusUrlFn: (id) => `/peticion-insumos/areas-abastecimiento/${id}/status`,
        entityLabel: 'esta área',
    });

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
                    inputNombreModal.classList.toggle('is-invalid', !data.valido);
                    if (feedbackNombre) feedbackNombre.textContent = data.valido ? '' : data.mensaje;
                });
        });
    }

    // ── Gráficas con Chart.js ────────────────────────────────────────────────
    const canvasEstatus = document.getElementById('chartEstatusArea');
    const canvasTopSubareas = document.getElementById('chartTopSubareas');

    if (canvasEstatus && window.dataGrafica) {
        new Chart(canvasEstatus, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [
                        window.dataGrafica.estatus.activos,
                        window.dataGrafica.estatus.inactivos
                    ],
                    backgroundColor: ['#198754', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
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
