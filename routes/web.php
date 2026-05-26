<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\BuscadorArchivos\BuscadorArchivosController;
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

    // Subgrupo del Buscador de Archivos (mBuscaArchivos)
    Route::prefix('mBuscaArchivos')->name('busca_archivos.')->group(function () {
        Route::get('/', [BuscadorArchivosController::class, 'index'])->name('index');                 // URL: /mBuscaArchivos          | Nombre: busca_archivos.index
        Route::get('/filtrar', [BuscadorArchivosController::class, 'filtrar'])->name('filtrar');     // URL: /mBuscaArchivos/filtrar  | Nombre: busca_archivos.filtrar
        Route::get('/descargar/{id}', [BuscadorArchivosController::class, 'descargar'])->name('descargar'); // URL: /mBuscaArchivos/descargar/{id} | Nombre: busca_archivos.descargar
        Route::get('/reportes', [BuscadorArchivosController::class, 'reportes'])->name('reportes');   // URL: /mBuscaArchivos/reportes | Nombre: busca_archivos.reportes
        Route::get('/imprimir', [BuscadorArchivosController::class, 'imprimirReporte'])->name('imprimir'); // URL: /mBuscaArchivos/imprimir | Nombre: busca_archivos.imprimir
    });
});

// Redirección por defecto
Route::get('/', function () {
    return redirect()->route('inicio');
});