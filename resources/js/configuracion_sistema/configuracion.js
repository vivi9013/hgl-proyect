/**
 * Lógica Javascript — Módulo Configuración General del Sistema
 */
document.addEventListener('DOMContentLoaded', function () {

    // 1. SweetAlert2 — Alertas por sección
    const alertas = [
        { id: 'alerta-exito_institucion', icon: 'success' },
        { id: 'alerta-exito_seguridad',   icon: 'success' },
        { id: 'alerta-exito_encabezado',  icon: 'success' },
    ];

    alertas.forEach(({ id, icon }) => {
        const el = document.getElementById(id);
        if (el && typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Operación Satisfactoria!',
                text: el.getAttribute('data-message'),
                icon,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar',
                timer: 3000,
                timerProgressBar: true,
            });
        }
    });

    // 2. Loading state en los tres botones de submit
    const botones = [
        { formId: null, btnId: 'btnInstitucion', label: 'Guardando...' },
        { formId: null, btnId: 'btnSeguridad',   label: 'Guardando...' },
        { formId: null, btnId: 'btnEncabezado',  label: 'Subiendo...'  },
    ];

    botones.forEach(({ btnId, label }) => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.closest('form').addEventListener('submit', function () {
                btn.disabled = true;
                btn.innerHTML = `<i class="fa fa-spinner fa-spin me-2"></i>${label}`;
            });
        }
    });

    // 3. Toggle mostrar/ocultar contraseña inicial
    const btnToggle   = document.getElementById('btnToggleContra');
    const inputContra = document.getElementById('contra');
    const iconoContra = document.getElementById('iconoContra');

    if (btnToggle && inputContra) {
        btnToggle.addEventListener('click', function () {
            const esTexto = inputContra.type === 'text';
            inputContra.type     = esTexto ? 'password' : 'text';
            iconoContra.className = esTexto ? 'fa fa-eye' : 'fa fa-eye-slash';
        });
    }

    // 4. Preview de imagen antes de subir
    const inputEncabezado = document.getElementById('encabezado');
    const preview         = document.getElementById('previewNuevoEncabezado');

    if (inputEncabezado && preview) {
        inputEncabezado.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                preview.style.display = 'none';
                return;
            }

            // Validación client-side de tipo
            if (!file.type.includes('jpeg')) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Formato inválido', 'Solo se permiten imágenes en formato JPG.', 'warning');
                }
                this.value = '';
                preview.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src          = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }
});