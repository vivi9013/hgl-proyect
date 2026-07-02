$(document).ready(function () {
    // Forzar que el check empiece desmarcado siempre
    $('#cambio').prop('checked', false);

    $('form').submit(function (evento) {
        evento.preventDefault();


        const formulario  = $(this);
        const botonEnviar = formulario.find('button[type="submit"]');

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
            cambio: $('#cambio').prop('checked') ? 1 : 0
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

                    case 4: // CAMBIO VOLUNTARIO (Check marcado)
                        Swal.fire({
                            title: 'Cambio de Contraseña',
                            text: 'Has solicitado cambiar tu contraseña',
                            input: 'password',
                            inputAttributes: { autocomplete: 'new-password' },
                            inputPlaceholder: 'Ingresa tu nueva contraseña',
                            showCancelButton: true,
                            confirmButtonText: 'Actualizar y Entrar',
                            cancelButtonText: 'Cancelar',
                            allowOutsideClick: false,
                            inputValidator: (valor) => {
                                if (!valor || valor.length < 4) {
                                    return 'Mínimo 4 caracteres';
                                }
                            }
                        }).then((resultado) => {
                            if (resultado.isConfirmed) {
                                $.ajax({
                                    type: 'POST',
                                    url: urlActualizarPass,
                                    data: {
                                        _token: respuesta.new_token,
                                        pass: resultado.value
                                    },
                                    success: function (respuestaPass) {
                                        if (respuestaPass.success) {
                                            Swal.fire('¡Éxito!', 'Contraseña actualizada', 'success').then(() => {
                                                window.location.href = dashboardUrl;
                                            });
                                        } else {
                                            Swal.fire('Error', respuestaPass.message, 'error');
                                            botonEnviar.prop('disabled', false).html('Ingresar');
                                        }
                                    },
                                    error: function () {
                                        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                                        botonEnviar.prop('disabled', false).html('Ingresar');
                                    }
                                });
                            } else {
                                // Si cancela el cambio voluntario, entra directo al panel
                                window.location.href = dashboardUrl;
                            }
                        });

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
            error: function () {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                botonEnviar.prop('disabled', false).html('Ingresar');
            }
        });
    });
});
