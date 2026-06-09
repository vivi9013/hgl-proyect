/**
 * Lógica Javascript para la edición de Categorías
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formEditarCategoria');
    if (form) {
        form.addEventListener('submit', function (e) {
            const btn = document.getElementById('btnActualizar');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Actualizando...';
            }
        });
    }
});
