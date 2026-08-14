@extends('layouts.loginstyle')

@section('title', 'Hospital - Iniciar Sesión')

@section('content')
<div class="login-card">
    <div class="login-title">
        <img src="{{ asset('images/avatar.webp') }}" alt="Logo de mi App" class="login-logo-img">
    </div>
    
    <form id="loginForm" action="{{ route('login.post') }}" method="POST" 
          data-update-url="{{ route('password.update') }}" 
          data-dashboard-url="{{ url('/inicio') }}" 
          data-logout-url="{{ route('logout') }}"
          data-url-cambiar-contra="{{ route('cambiar_contra.index') }}"
          data-url-recuperar-password="{{ route('recuperar_password.solicitar') }}">
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
                <input type="checkbox" id="olvido" name="olvido">
                <label for="olvido">Olvidé mi contraseña</label>
            </div>

            <button type="submit" class="login-button" id="btnLoginSubmit">
                Ingresar
            </button>
        </div>

    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Carga del script procesado por Vite -->
@vite(['resources/js/login.js'])
@endsection
