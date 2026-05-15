@extends('layouts.loginstyle')

@section('title', 'Hospital - Iniciar Sesión')

@section('content')
<div class="login-card">
    <div class="login-title flex justify-center">
        <img src="{{ asset('images/avatar.webp') }}" alt="Logo de mi App" class="w-32 h-auto">
    </div>
    
    <form action="#" method="POST">
        @csrf
        <div class="form-group">
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </span>
                <input id="user" name="user" type="text" required class="login-input" placeholder="Usuario">
            </div>
        </div>

        <div class="form-group">
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </span>
                <input id="password" name="password" type="password" required class="login-input" placeholder="Contraseña">
            </div>
        </div>

        <div class="login-checkbox">
            <div style="display: flex; align-items: center;">
                <input type="checkbox" id="cambio" name="cambio">
                <label for="cambio">Cambiar Contraseña</label>
            </div>

            <button type="submit" class="login-button">
                Ingresar
            </button>
        </div>

    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Forzar que el check empiece desmarcado siempre
    $('#cambio').prop('checked', false);

    $('form').submit(function(e) {
        e.preventDefault();

        const data = {
            _token: "{{ csrf_token() }}",
            user: $('#user').val(),
            password: $('#password').val(),
            cambio: $('#cambio').prop('checked') ? 1 : 0
        };


        $.ajax({
            type: 'POST',
            url: "{{ route('login.post') }}",
            data: data,
            success: function(res) {
                const parts = res.resultado.split('|');
                const opcion = parseInt(parts[0]);

                switch(opcion) {
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
                            inputValidator: (value) => {
                                if (!value || value.length < 4) {
                                    return 'Mínimo 4 caracteres'
                                }
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: 'POST',
                                    url: "{{ route('password.update') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        pass: result.value
                                    },
                                    success: function(response) {
                                        if(response.success) {
                                            Swal.fire('¡Éxito!', 'Contraseña actualizada', 'success').then(() => {
                                                window.location.href = "{{ url('/inicio') }}";
                                            });
                                        } else {
                                            Swal.fire('Error', response.message, 'error');
                                        }
                                    }
                                });
                            } else {
                                // Si cancela el cambio voluntario (opcion 4), entra igual al dashboard
                                // Si es obligatorio (opcion 1), lo sacamos
                                if (opcion == 4) {
                                    window.location.href = "{{ url('/inicio') }}";
                                } else {
                                    window.location.href = "{{ route('logout') }}";
                                }
                            }
                        });

                        break;

                    case 3: // EXITO DIRECTO (Sistema 1)
                        window.location.href = "{{ url('/inicio') }}";
                        break;

                    case 2: // FALLO (Sistema 1)
                        Swal.fire('Error', 'Usuario o contraseña incorrectos', 'error');
                        break;
                }
            },


            error: function() {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            }
        });
    });
});
</script>
@endsection
