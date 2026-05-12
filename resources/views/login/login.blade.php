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
                    <input type="checkbox" id="cc" name="cc">
                    <label for="cc">Cambiar Contraseña</label>
                </div>

                <button type="submit" class="login-button">
                    Ingresar
                </button>
            </div>
        </form>
    </div>
@endsection
