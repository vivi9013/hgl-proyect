import { initTablaAjax } from '@/shared/tabla-ajax.js';

document.addEventListener('DOMContentLoaded', function () {

    // ── Tabla AJAX (búsqueda, paginación, filtros, toggle de estatus) ────────
    initTablaAjax({
        contenedorId  : 'contenedor-tabla',
        formId        : 'formFiltros',
        inputBuscarId : 'buscar',
        statusSelector: '.filtro-status',
        toggleSelector: '.btn-toggle-status',
        statusUrlFn   : (id) => `/peticion-insumos/subareas-abastecimiento/${id}/status`,
        entityLabel   : 'esta subárea',
    });

    // ── Select de Área — recarga tabla al cambiar ────────────────────────────
    // (Nota: initTablaAjax serializa todo el formFiltros; el select de área
    //  ya está dentro del form, por lo que el change dispara el filtro via
    //  el propio form. Si el select está fuera del form, añadir aquí:)
    const selectAreaFiltro = document.getElementById('id_area_abastecimiento_filtro');
    if (selectAreaFiltro && !selectAreaFiltro.closest('#formFiltros')) {
        // Solo si el select está FUERA del form (situación legacy)
        selectAreaFiltro.addEventListener('change', () => {
            document.getElementById('formFiltros')?.dispatchEvent(new Event('submit'));
        });
    }

    // ── Validación en tiempo real en Modal de Alta ───────────────────────────
    const selectAreaModal  = document.getElementById('modal_id_area');
    const inputNombreModal = document.getElementById('modal_nombre');
    const feedbackNombre   = document.getElementById('feedback_modal_nombre');

    function validarDuplicadoModal() {
        if (!inputNombreModal || !selectAreaModal) return;
        const nombre = inputNombreModal.value.trim();
        const areaId = selectAreaModal.value;
        if (!nombre || !areaId) return;

        fetch(`/peticion-insumos/subareas-abastecimiento/verificar?nombre=${encodeURIComponent(nombre)}&id_area_abastecimiento=${areaId}`)
            .then(res => res.json())
            .then(data => {
                inputNombreModal.classList.toggle('is-invalid', !data.valido);
                if (feedbackNombre) feedbackNombre.textContent = data.valido ? '' : data.mensaje;
            });
    }

    if (inputNombreModal) inputNombreModal.addEventListener('blur', validarDuplicadoModal);
    if (selectAreaModal)  selectAreaModal.addEventListener('change', validarDuplicadoModal);

    // ── Gráficas con Chart.js ────────────────────────────────────────────────
    const canvasEstatus  = document.getElementById('chartEstatusSubarea');
    const canvasPorArea  = document.getElementById('chartSubareasPorArea');

    if (canvasEstatus && window.dataGrafica) {
        new Chart(canvasEstatus, {
            type: 'doughnut',
            data: {
                labels  : ['Activos', 'Inactivos'],
                datasets: [{ data: [window.dataGrafica.estatus.activos, window.dataGrafica.estatus.inactivos], backgroundColor: ['#198754', '#dc3545'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    if (canvasPorArea && window.dataGrafica) {
        new Chart(canvasPorArea, {
            type: 'bar',
            data: {
                labels  : window.dataGrafica.porArea.labels,
                datasets: [{ label: 'Subáreas Registradas', data: window.dataGrafica.porArea.val, backgroundColor: '#0d6efd' }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
        });
    }
});
