<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\IndexController;
use App\Http\Middleware\EvitarRetrocesoMiddleware;

// Grupo para invitados (Solo pueden ver el Login)
Route::middleware(['guest', EvitarRetrocesoMiddleware::class])->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/validar-login', [LoginController::class, 'login'])->name('login.post');
});

// Grupo para usuarios autenticados (Protección total)
Route::middleware(['auth', EvitarRetrocesoMiddleware::class])->group(function () {
    Route::get('/inicio', [IndexController::class, 'index'])->name('inicio');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Ruta de cambio de contraseña (Consistent with Sistema 1)
    Route::post('/actualizar-password', [LoginController::class, 'updatePassword'])->name('password.update');
});

// Redirección por defecto
Route::get('/', function () {
    return redirect()->route('inicio');
});