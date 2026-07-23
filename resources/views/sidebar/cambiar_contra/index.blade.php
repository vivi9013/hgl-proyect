@extends('layouts.app')

@section('title', 'Cambiar Contraseña - Hospital General')

@push('styles')
    @vite('resources/css/cambiar_contra/contra.css')
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-lock"></i> Cambiar Contraseña
            </h1>
            <p class="text-muted mb-0">Actualiza tu contraseña de acceso al sistema</p>
        </div>

    </div>

    <div class="row">
        <div class="col-12 col-md-6 col-lg-5 mx-auto">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-key text-secondary me-2 "></i>Nueva contraseña
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <!-- Alertas -->
                    <div id="alertaExito" class="alert alert-success border-0 rounded-3 d-none" role="alert">
                        <i class="fa fa-check-circle me-2"></i>¡Contraseña actualizada correctamente!
                    </div>
                    <div id="alertaError" class="alert alert-danger border-0 rounded-3 d-none" role="alert">
                        <i class="fa fa-exclamation-triangle me-2"></i><span id="mensajeError">Error al actualizar.</span>
                    </div>
                    <div id="mensaje4" class="alert alert-warning border-0 rounded-3 d-none" role="alert">
                        <i class="fa fa-exclamation-circle me-2"></i>Las contraseñas no coinciden.
                    </div>

                    <form id="formCambiarContra" autocomplete="off">
                        @csrf

                        <!-- Nueva Contraseña -->
                        <div class="mb-3">
                            <label for="pass" class="form-label fw-bold text-secondary">
                                <i class="fa fa-lock"></i> Nueva contraseña:
                            </label>
                            <input type="password" name="pass" id="pass"
                                   class="form-control border-gray-300 shadow-sm"
                                   placeholder="Ingresa tu nueva contraseña"
                                   minlength="4"
                                   required>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mb-4">
                            <label for="repass" class="form-label fw-bold text-secondary">
                                <i class="fa fa-lock"></i>Confirmar contraseña:
                            </label>
                            <input type="password" name="repass" id="repass"
                                   class="form-control border-gray-300 shadow-sm"
                                   placeholder="Repite tu nueva contraseña"
                                   minlength="4"
                                   required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" id="boton" class="btn btn-primary py-2 rounded-pill shadow-sm">
                                <i class="fa fa-save me-2"></i>Actualizar Contraseña
                            </button>
                            <a href="{{ route('inicio') }}" class="btn btn-light border rounded-pill shadow-sm">
                                <i class="fa fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const passInput  = document.getElementById('pass');
    const repassInput = document.getElementById('repass');
    const mensaje4   = document.getElementById('mensaje4');
    const alertaExito = document.getElementById('alertaExito');
    const alertaError = document.getElementById('alertaError');
    const mensajeError = document.getElementById('mensajeError');

    // Verificación visual en tiempo real
    repassInput.addEventListener('keyup', function () {
        if (passInput.value !== repassInput.value) {
            repassInput.classList.add('is-invalid');
            repassInput.classList.remove('is-valid');
        } else {
            repassInput.classList.add('is-valid');
            repassInput.classList.remove('is-invalid');
            mensaje4.classList.add('d-none');
        }
    });

    // Acción del botón
    document.getElementById('boton').addEventListener('click', function () {
        mensaje4.classList.add('d-none');
        alertaExito.classList.add('d-none');
        alertaError.classList.add('d-none');

        if (passInput.value !== repassInput.value) {
            mensaje4.classList.remove('d-none');
            return;
        }

        if (passInput.value.length < 4) {
            mensajeError.textContent = 'La contraseña debe tener al menos 4 caracteres.';
            alertaError.classList.remove('d-none');
            return;
        }

        const formData = new FormData();
        formData.append('pass', passInput.value);
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('_method', 'PUT');

        fetch('{{ route("password.update") }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alertaExito.classList.remove('d-none');
                passInput.value = '';
                repassInput.value = '';
                repassInput.classList.remove('is-valid', 'is-invalid');
                setTimeout(() => window.location.href = '{{ route("inicio") }}', 1500);
            } else {
                mensajeError.textContent = data.message || 'Error al actualizar la contraseña.';
                alertaError.classList.remove('d-none');
            }
        })
        .catch(() => {
            mensajeError.textContent = 'Error de conexión. Intenta nuevamente.';
            alertaError.classList.remove('d-none');
        });
    });
});
</script>
@endpush
