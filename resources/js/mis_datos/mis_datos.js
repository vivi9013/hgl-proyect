/**
 * Lógica Javascript para el módulo Mis Datos Personales
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Auto-conversión de RFC y CURP a mayúsculas
    const rfcInput = document.getElementById('rfc');
    const curpInput = document.getElementById('curp');

    if (rfcInput) {
        rfcInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }

    if (curpInput) {
        curpInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }

    // 2. Validación de Bootstrap 5 en el cliente antes de enviar
    const form = document.getElementById('formMisDatos');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }

    // 3. Auto-ocultación de alertas de éxito tras 5 segundos
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        setTimeout(function () {
            const closeButton = successAlert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            } else {
                successAlert.style.transition = 'opacity 0.5s ease';
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }
        }, 5000);
    }
});
