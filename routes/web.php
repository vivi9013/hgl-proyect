<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\BuscadorArchivos\BuscadorArchivosController;
use App\Http\Controllers\CargarArchivos\CargaArchivosController;
use App\Http\Controllers\MisDatos\MisDatosController;
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