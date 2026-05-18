$(document).ready(function () {
    // Forzar que el check empiece desmarcado siempre
    $('#cambio').prop('checked', false);

    $('form').submit(function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');

        // Deshabilitar botón para evitar doble envío y errores de CSRF por peticiones concurrentes
        submitBtn.prop('disabled', true).html('Ingresando');

        const loginUrl = form.attr('action');
        const updatePasswordUrl = form.data('update-url');
        const dashboardUrl = form.data('dashboard-url');
        const logoutUrl = form.data('logout-url');

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
            success: function (res) {
                const parts = res.resultado.split('|');
                const opcion = parseInt(parts[0]);

                switch (opcion) {
                    case 1: // OBLIGATORIO (Sistema 1)
                    case 4: // VOLUNTARIO (Sistema 1)
                        Swal.fire({
                            title: opcion == 1 ? 'Cambio Obligatorio' : 'Cambio de Contraseña',
                            text: opcion == 1 ? 'Debes actualizar tu contraseña para continuar' : 'Has solicitado cambiar tu contraseña',
                            input: 'password',
                            inputAttributes: { autocomplete: 'new-password' },
                            inputPlaceholder: 'Ingresa tu nueva contraseña',
                            showCancelButton: true,
                            confirmButtonText: 'Actualizar y Entrar',
                            cancelButtonText: 'Cancelar',
                            allowOutsideClick: false,
                            inputValidator: (value) => {
                                if (!value || value.length < 4) {
                                    return 'Mínimo 4 caracteres'
                                }
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: 'POST',
                                    url: updatePasswordUrl,
                                    data: {
                                        _token: res.new_token,
                                        pass: result.value
                                    },
                                    success: function (response) {
                                        if (response.success) {
                                            Swal.fire('¡Éxito!', 'Contraseña actualizada', 'success').then(() => {
                                                window.location.href = dashboardUrl;
                                            });
                                        } else {
                                            Swal.fire('Error', response.message, 'error');
                                            submitBtn.prop('disabled', false).html('Ingresar');
                                        }
                                    },
                                    error: function () {
                                        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                                        submitBtn.prop('disabled', false).html('Ingresar');
                                    }
                                });
                            } else {
                                // Si cancela el cambio voluntario (opcion 4), entra igual al dashboard
                                // Si es obligatorio (opcion 1), lo sacamos
                                if (opcion == 4) {
                                    window.location.href = dashboardUrl;
                                } else {
                                    window.location.href = logoutUrl;
                                }
                            }
                        });

                        break;

                    case 3: // EXITO DIRECTO (Sistema 1)
                        window.location.href = dashboardUrl;
                        break;

                    case 2: // FALLO (Sistema 1)
                        Swal.fire('Error', 'Usuario o contraseña incorrectos', 'error');
                        submitBtn.prop('disabled', false).html('Ingresar');
                        break;
                }
            },
            error: function () {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                submitBtn.prop('disabled', false).html('Ingresar');
            }
        });
    });
});
