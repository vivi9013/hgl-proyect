<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\BuscadorArchivos\BuscadorArchivosController;
use App\Http\Controllers\CargarArchivos\CargaArchivosController;
use App\Http\Controllers\MisDatos\MisDatosController;
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

    // Subgrupo de Carga de Archivos (mCargaArchivos)
    Route::prefix('mCargaArchivos')->name('carga_archivos.')->group(function () {
        Route::get('/', [CargaArchivosController::class, 'index'])->name('index');                        // URL: /mCargaArchivos
        Route::post('/guardar', [CargaArchivosController::class, 'guardar'])->name('store');                // URL: /mCargaArchivos/guardar
        Route::get('/verificar-nombre', [CargaArchivosController::class, 'revisarexistencia'])->name('check_availability'); // URL: /mCargaArchivos/verificar-nombre
        Route::get('/status/{id}', [CargaArchivosController::class, 'toggleStatus'])->name('status');       // URL: /mCargaArchivos/status/{id}
        Route::get('/editar/{id}', [CargaArchivosController::class, 'editar'])->name('edit');               // URL: /mCargaArchivos/editar/{id}
        Route::post('/actualizar/{id}', [CargaArchivosController::class, 'actualizar'])->name('update');    // URL: /mCargaArchivos/actualizar/{id}
        Route::get('/cargar/{id}', [CargaArchivosController::class, 'cargar'])->name('cargar');             // URL: /mCargaArchivos/cargar/{id}
        Route::post('/subir-archivo/{id}', [CargaArchivosController::class, 'subirArchivo'])->name('subir_archivo'); // URL: /mCargaArchivos/subir-archivo/{id}
    });

    // Subgrupo de Mis Datos (mMisDatos)
    Route::prefix('mis-datos')->name('mis_datos.')->group(function () {
        Route::get('/', [MisDatosController::class, 'index'])->name('index');                        // URL: /mis-datos
        Route::post('/actualizar', [MisDatosController::class, 'update'])->name('update');            // URL: /mis-datos/actualizar
    });
});

// Redirección por defecto
Route::get('/', function () {
    return redirect()->route('inicio');
});
