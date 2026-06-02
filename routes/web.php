<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EvitarRetrocesoMiddleware;

// Importaciones de Controladores organizadas
use App\Http\Controllers\LoginController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\MisDatos\MisDatosController;
use App\Http\Controllers\CambiarFoto\CambiarFotoController;
use App\Http\Controllers\Cumpleanos\CumpleanosController;
use App\Http\Controllers\Tema\TemaController;
use App\Http\Controllers\BuscadorArchivos\BuscadorArchivosController;
use App\Http\Controllers\CargarArchivos\CargaArchivosController;
use App\Http\Controllers\CategoriaArchivos\CategoriaArchivosController;
use App\Http\Controllers\PermisosArchivos\PermisosArchivosController;
use App\Http\Controllers\Pacientes\RxController;

// Redirección raíz por defecto
Route::get('/', function () {
    return redirect()->route('inicio');
});

// ── GRUPO PARA INVITADOS ───────────────────────────────────────────────────
Route::middleware(['guest', EvitarRetrocesoMiddleware::class])->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/validar-login', [LoginController::class, 'login'])->name('login.post');
});

// ── GRUPO PARA USUARIOS AUTENTICADOS ───────────────────────────────────────
Route::middleware(['auth', EvitarRetrocesoMiddleware::class])->group(function () {
    
    Route::get('/inicio', [IndexController::class, 'index'])->name('inicio');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    // Gestión de Credenciales de Usuario
    Route::controller(LoginController::class)->group(function () {
        Route::get('/cambiar-contrasena', 'showCambiarContra')->name('cambiar_contra.index');
        Route::put('/actualizar-password', 'updatePassword')->name('password.update'); // Cambiado a PUT
    });

    // Cambiar Fotografía
    Route::controller(CambiarFotoController::class)->group(function () {
        Route::get('/cambiar-foto', 'index')->name('cambiar_foto.index');
        Route::post('/cambiar-foto', 'store')->name('cambiar_foto.store');
    });

    // Cumpleaños y Preferencias
    Route::get('/cumpleanos', [CumpleanosController::class, 'index'])->name('cumpleanos.index');
    
    Route::controller(TemaController::class)->group(function () {
        Route::get('/cambiar-tema', 'index')->name('cambiar_tema.index');
        Route::patch('/cambiar-tema', 'update')->name('cambiar_tema.update'); // Cambiado a PATCH
    });

    // Subgrupo: Mis Datos
    Route::prefix('mis-datos')->name('mis_datos.')->controller(MisDatosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/actualizar', 'update')->name('update'); // Cambiado a PUT
    });

    // Subgrupo: Buscador de Archivos
    Route::prefix('buscador-archivos')->name('busca_archivos.')->controller(BuscadorArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/filtrar', 'filtrar')->name('filtrar');
        Route::get('/descargar/{id}', 'descargar')->name('descargar');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/imprimir', 'imprimirReporte')->name('imprimir');
    });

    // Subgrupo: Carga de Archivos
    Route::prefix('carga-archivos')->name('carga_archivos.')->controller(CargaArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/verificar-nombre', 'revisarexistencia')->name('check_availability');
        Route::patch('/status/{id}', 'toggleStatus')->name('status'); // Cambiado a PATCH por seguridad
        Route::get('/editar/{id}', 'editar')->name('edit');
        Route::put('/actualizar/{id}', 'actualizar')->name('update'); // Cambiado a PUT
        Route::get('/cargar/{id}', 'cargar')->name('cargar');
        Route::post('/subir-archivo/{id}', 'subirArchivo')->name('subir_archivo');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::post('/reportes/imprimir', 'imprimirReporte')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
    });
    
    // Subgrupo: Catálogo de Categorías
    Route::prefix('categoria-archivos')->name('categoria_archivos.')->controller(CategoriaArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status'); // Cambiado a PATCH
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/verificar', 'verificar')->name('verificar');
    });

    // Subgrupo: Permisos de Archivo
    Route::prefix('permisos-archivo')->name('trabajador_categorias.')->controller(PermisosArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/asignar', 'asignar')->name('asignar');
        Route::post('/guardar', 'guardar')->name('guardar');
    });

    // Subgrupo: Radiología RX
    Route::prefix('rx-estudios')->name('rx.')->controller(RxController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/estudios', 'estudios')->name('estudios');
        
        // Pacientes
        Route::get('/pacientes/ver/{id}', 'verPaciente')->name('pacientes.ver');
        Route::post('/pacientes/guardar', 'guardarPaciente')->name('pacientes.guardar');
        Route::put('/pacientes/actualizar/{id}', 'actualizarPaciente')->name('pacientes.actualizar'); // Cambiado a PUT
        Route::delete('/pacientes/eliminar/{id}', 'eliminarPaciente')->name('pacientes.eliminar');
        
        // Estudios
        Route::get('/estudios/ver/{id}', 'verEstudio')->name('estudios.ver');
        Route::post('/estudios/guardar', 'guardarEstudio')->name('estudios.guardar');
        Route::put('/estudios/actualizar/{id}', 'actualizarEstudio')->name('estudios.actualizar'); // Cambiado a PUT
        Route::delete('/estudios/eliminar/{id}', 'eliminarEstudio')->name('estudios.eliminar');
    });
});