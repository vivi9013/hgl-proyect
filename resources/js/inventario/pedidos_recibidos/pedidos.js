/**
 * JavaScript para el módulo de Pedidos Recibidos
 * HGL – Sistema de Gestión de Inventario
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Alertas de sesión con SweetAlert2 ─────────────────────────────────
    const exitogEl = document.getElementById('alertaExitog');
    const errorEl  = document.getElementById('alertaError');

    if (exitogEl && typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: exitogEl.dataset.message || 'Operación realizada con éxito.',
            confirmButtonColor: '#16a34a',
            timer: 4000,
            timerProgressBar: true
        });
    }
    if (errorEl && typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorEl.dataset.message || 'Ocurrió un error.',
            confirmButtonColor: '#dc2626'
        });
    }

    // ── Lógica de la tabla de detalles del pedido ─────────────────────────
    const tablaDetalles = document.getElementById('tablaDetallesPedido');
    const btnLiberar    = document.getElementById('btnLiberarPedido');
    const btnCancelar   = document.getElementById('btnCancelarPedido');
    const formLiberar   = document.getElementById('formLiberarPedido');
    const formCancelar  = document.getElementById('formCancelarPedido');

    if (!tablaDetalles) return; // Salir si no estamos en la vista de detalle

    const META_CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Función: recalcular si el botón Liberar debe activarse ─────────────
    function recalcularBotonLiberar() {
        if (!btnLiberar) return;
        const alguno = [...tablaDetalles.querySelectorAll('.check-habilitar')]
            .some(ch => ch.checked);
        btnLiberar.disabled = !alguno;
    }

    // ── Switch de habilitación: habilita/deshabilita el input de surtido ───
    tablaDetalles.addEventListener('change', function (e) {
        if (!e.target.classList.contains('check-habilitar')) return;

        const fila         = e.target.closest('tr');
        const inputSurtido = fila.querySelector('.input-surtido');
        const cantidad     = parseInt(fila.dataset.cantidad) || 0;
        const stock        = parseInt(fila.dataset.stock) || 0;

        if (e.target.checked) {
            inputSurtido.disabled = false;
            inputSurtido.max = Math.min(cantidad, stock);
            if (parseInt(inputSurtido.value) === 0) {
                inputSurtido.value = Math.min(cantidad, stock);
                actualizarFaltante(fila, inputSurtido.value);
            }
        } else {
            inputSurtido.disabled = true;
            inputSurtido.value = 0;
            actualizarFaltante(fila, 0);
        }

        guardarSurtido(fila, inputSurtido.value);
        recalcularBotonLiberar();
    });

    // ── Cambio manual de cantidad surtida ──────────────────────────────────
    tablaDetalles.addEventListener('input', function (e) {
        if (!e.target.classList.contains('input-surtido')) return;

        const fila     = e.target.closest('tr');
        const cantidad = parseInt(fila.dataset.cantidad) || 0;
        const stock    = parseInt(fila.dataset.stock) || 0;
        let surtido    = parseInt(e.target.value) || 0;

        // Clamp
        if (surtido < 0) surtido = 0;
        if (surtido > Math.min(cantidad, stock)) surtido = Math.min(cantidad, stock);
        e.target.value = surtido;

        actualizarFaltante(fila, surtido);
    });

    // Guardar al perder foco
    tablaDetalles.addEventListener('blur', function (e) {
        if (!e.target.classList.contains('input-surtido')) return;
        const fila = e.target.closest('tr');
        guardarSurtido(fila, e.target.value);
    }, true);

    // ── Actualizar celda de faltante ───────────────────────────────────────
    function actualizarFaltante(fila, surtido) {
        const cantidad  = parseInt(fila.dataset.cantidad) || 0;
        const faltante  = Math.max(0, cantidad - parseInt(surtido));
        const celdaFalt = fila.querySelector('.text-faltante');
        if (celdaFalt) celdaFalt.textContent = faltante;
    }

    // ── Petición AJAX para guardar surtido ────────────────────────────────
    function guardarSurtido(fila, surtido) {
        const url = fila.dataset.url;
        if (!url) return;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': META_CSRF,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ surtido: parseInt(surtido) || 0 }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                actualizarFaltante(fila, data.surtido);
                recalcularBotonLiberar();
            } else if (data.error && typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Advertencia', text: data.error, confirmButtonColor: '#000' });
            }
        })
        .catch(() => {});
    }

    // ── Botón Liberar Pedido ───────────────────────────────────────────────
    if (btnLiberar && formLiberar) {
        btnLiberar.addEventListener('click', function () {
            if (typeof Swal === 'undefined') { formLiberar.submit(); return; }
            Swal.fire({
                title: '¿Liberar Pedido?',
                html: 'Se registrará el surtimiento y se descontará el stock.<br><strong>Esta acción no se puede deshacer fácilmente.</strong>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-check-circle"></i> Sí, Liberar',
                cancelButtonText: 'Cancelar',
            }).then(result => {
                if (result.isConfirmed) formLiberar.submit();
            });
        });
    }

    // ── Botón Cancelar Pedido ──────────────────────────────────────────────
    if (btnCancelar && formCancelar) {
        btnCancelar.addEventListener('click', function () {
            if (typeof Swal === 'undefined') { formCancelar.submit(); return; }
            Swal.fire({
                title: '¿Cancelar Pedido?',
                text: 'Si el pedido ya fue surtido, el stock será restaurado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-ban"></i> Sí, Cancelar',
                cancelButtonText: 'No, Regresar',
            }).then(result => {
                if (result.isConfirmed) formCancelar.submit();
            });
        });
    }

    // Inicializar estado del botón Liberar
    recalcularBotonLiberar();
});
