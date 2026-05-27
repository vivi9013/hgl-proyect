<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\BuscadorArchivos\BuscadorArchivosController;
use App\Http\Middleware\EvitarRetrocesoMiddleware;
use App\Http\Controllers\CambiarFoto\CambiarFotoController;

// Grupo para invitados
Route::middleware(['guest', EvitarRetrocesoMiddleware::class])->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/validar-login', [LoginController::class, 'login'])->name('login.post');
});

// Grupo para usuarios autenticados
Route::middleware(['auth', EvitarRetrocesoMiddleware::class])->group(function () {
    Route::get('/inicio', [IndexController::class, 'index'])->name('inicio');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    // Cambio de contraseña
    Route::post('/actualizar-password', [LoginController::class, 'updatePassword'])->name('password.update');

    // Buscador de Archivos
    Route::prefix('mBuscaArchivos')->name('busca_archivos.')->group(function () {
        Route::get('/', [BuscadorArchivosController::class, 'index'])->name('index');
        Route::get('/filtrar', [BuscadorArchivosController::class, 'filtrar'])->name('filtrar');
        Route::get('/descargar/{id}', [BuscadorArchivosController::class, 'descargar'])->name('descargar');
        Route::get('/reportes', [BuscadorArchivosController::class, 'reportes'])->name('reportes');
        Route::get('/imprimir', [BuscadorArchivosController::class, 'imprimirReporte'])->name('imprimir');
    });

    // Cambiar Fotografía
    Route::get('/cambiar-foto', [CambiarFotoController::class, 'index'])->name('cambiar_foto.index');
    Route::post('/cambiar-foto', [CambiarFotoController::class, 'store'])->name('cambiar_foto.store');
});

// Redirección por defecto
Route::get('/', function () {
    return redirect()->route('inicio');
});
