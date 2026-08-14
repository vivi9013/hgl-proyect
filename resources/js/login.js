$(document).ready(function () {
    // Forzar que el check empiece desmarcado siempre
    $('#olvido').prop('checked', false);

    // Listener del checkbox "Olvidé mi contraseña"
    $('#olvido').on('change', function () {
        const activo = $(this).is(':checked');
        $('#password').prop('required', !activo).prop('disabled', activo).val(activo ? '' : $('#password').val());
        $('#btnLoginSubmit').text(activo ? 'Solicitar Recuperación' : 'Ingresar');
    });

    $('form').submit(function (evento) {
        evento.preventDefault();

        const formulario  = $(this);
        const botonEnviar = formulario.find('button[type="submit"]');

        // Si el checkbox de recuperación está marcado, interceptar el flujo
        if ($('#olvido').is(':checked')) {
            solicitarRecuperacion(formulario, botonEnviar);
            return;
        }

        // Deshabilitar botón para evitar doble envío y errores de CSRF por peticiones concurrentes
        botonEnviar.prop('disabled', true).html('Ingresando');

        const loginUrl           = formulario.attr('action');
        const urlActualizarPass  = formulario.data('update-url');
        const dashboardUrl       = formulario.data('dashboard-url');
        const urlCerrarSesion    = formulario.data('logout-url');
        const urlCambiarContra   = formulario.data('url-cambiar-contra');

        const data = {
            _token: $('input[name="_token"]').val(),
            user: $('#user').val(),
            password: $('#password').val(),
        };

        $.ajax({
            type: 'POST',
            url: loginUrl,
            data: data,
            success: function (respuesta) {
                const partes = respuesta.resultado.split('|');
                const opcion = parseInt(partes[0]);

                switch (opcion) {
                    case 1: // PRIMER INGRESO — redirección directa sin popup
                        window.location.href = urlCambiarContra;
                        break;

                    case 3: // INGRESO DIRECTO AL PANEL
                        window.location.href = dashboardUrl;
                        break;

                    case 2: // CREDENCIALES INCORRECTAS
                        Swal.fire('Error', 'Usuario o contraseña incorrectos', 'error');
                        botonEnviar.prop('disabled', false).html('Ingresar');
                        break;
                }
            },
            error: function (xhr) {
                // ── Manejo de expiración de sesión (419 Page Expired) ────────────────
                if (xhr.status === 419) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesión expirada',
                        text: 'Tu sesión expiró por inactividad. La página se recargará.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#3085d6',
                    }).then(() => { window.location.reload(); });
                // ── Manejo de Rate Limiting (429 Too Many Requests) ──────────────
                } else if (xhr.status === 429) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Se paciente',
                        text: 'Por favor, espera un momento antes de reintentar.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#3085d6',
                    });
                } else {
                    Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                }
                botonEnviar.prop('disabled', false).html('Ingresar');
            }
        });
    });

    function solicitarRecuperacion(formulario, botonEnviar) {
        const usuario = $('#user').val();
        if (!usuario) {
            Swal.fire('Falta información', 'Escribe tu usuario antes de solicitar la recuperación.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Recuperar contraseña',
            html: '<input id="swalNombreCompleto" class="swal2-input" placeholder="Nombre completo (como aparece registrado)">' +
                  '<input id="swalDatoAdicional" class="swal2-input" placeholder="CURP, RFC o teléfono (opcional)">',
            confirmButtonText: 'Enviar solicitud',
            cancelButtonText: 'Cancelar',
            showCancelButton: true,
            allowOutsideClick: false,
            preConfirm: () => {
                const nombre = document.getElementById('swalNombreCompleto').value.trim();
                const dato = document.getElementById('swalDatoAdicional').value.trim();
                if (!nombre) {
                    Swal.showValidationMessage('El nombre completo es obligatorio');
                    return false;
                }
                return { nombre, dato };
            }
        }).then((resultado) => {
            if (!resultado.isConfirmed) return;

            botonEnviar.prop('disabled', true).html('Enviando...');

            $.ajax({
                type: 'POST',
                url: formulario.data('url-recuperar-password'),
                data: {
                    _token: $('input[name="_token"]').val(),
                    user: usuario,
                    nombre: resultado.value.nombre,
                    dato: resultado.value.dato
                },
                success: function (respuesta) {
                    Swal.fire('Solicitud recibida', respuesta.mensaje, 'info');
                    botonEnviar.prop('disabled', false).html('Ingresar');
                    $('#olvido').prop('checked', false).trigger('change');
                },
                error: function (xhr) {
                    if (xhr.status === 419) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sesión expirada',
                            text: 'Tu sesión expiró por inactividad. La página se recargará.',
                            confirmButtonText: 'Entendido',
                        }).then(() => { window.location.reload(); });
                    } else {
                        Swal.fire('Error', 'No se pudo enviar la solicitud. Intenta más tarde.', 'error');
                        botonEnviar.prop('disabled', false).html('Solicitar Recuperación');
                    }
                }
            });
        });
    }
});
