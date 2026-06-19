/**
 * personas_edit.js — Lógica de la vista de edición de Persona
 * Maneja la carga dinámica de municipios al cambiar el estado seleccionado.
 */

document.addEventListener('DOMContentLoaded', function () {

    const estadoEdit    = document.getElementById('estado_edit');
    const municipioEdit = document.getElementById('municipio_edit');

    if (estadoEdit && municipioEdit) {
        estadoEdit.addEventListener('change', function () {
            const estado = this.value;
            if (!estado) return;

            const valorActualMunicipio = municipioEdit.value;
            municipioEdit.innerHTML = '<option value="">Cargando...</option>';

            fetch(`/personas/municipios?estado=${encodeURIComponent(estado)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                municipioEdit.innerHTML = '<option value="">-- Seleccionar --</option>';
                Object.entries(data).forEach(([key, val]) => {
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = val;
                    // Preservar selección si el municipio sigue disponible
                    if (val === valorActualMunicipio) opt.selected = true;
                    municipioEdit.appendChild(opt);
                });
            })
            .catch(() => {
                municipioEdit.innerHTML = '<option value="">Error al cargar municipios</option>';
            });
        });
    }
});
