<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EvitarRetrocesoMiddleware;

// Importaciones de Controladores organizadas por módulos
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
use App\Http\Controllers\CategoriaModulos\CategoriaModulosController;
use App\Http\Controllers\ConfiguracionSistema\ConfiguracionController;
use App\Http\Controllers\Modulos\ModuloController;
use App\Http\Controllers\Perfiles\PerfilController;
use App\Http\Controllers\Personas\PersonaController;
use App\Http\Controllers\Proyectos\ProyectoController;
use App\Http\Controllers\Usuarios\UsuarioController;
use App\Http\Controllers\Computadoras\ComputadoraController;

// Controladores del Módulo de Inventario (Añadidos e integrados)
use App\Http\Controllers\Inventario\AreaAlmacenController;
use App\Http\Controllers\Inventario\AreaSurtimientoController;
use App\Http\Controllers\Inventario\BajaInsumoController;
use App\Http\Controllers\Inventario\DevolucionController;
use App\Http\Controllers\Inventario\DetalleDevolucionController;
use App\Http\Controllers\Inventario\EntradaCendisController;
use App\Http\Controllers\Inventario\DetalleEntradaCendisController;
use App\Http\Controllers\Inventario\InsumoController;
use App\Http\Controllers\Inventario\InsumoAreaController;

// Redirección raíz por defecto
Route::get('/', function () {
    return redirect()->route('inicio');
});

// ── Redirects legacy (URLs del sistema antiguo) ────────────────────────────
Route::get('/mMotivos', fn() => redirect()->route('motivos.index'));

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
        Route::put('/actualizar-password', 'updatePassword')->name('password.update');
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
        Route::patch('/cambiar-tema', 'update')->name('cambiar_tema.update');
    });

    // Subgrupo: Mis Datos
    Route::prefix('mis-datos')->name('mis_datos.')->controller(MisDatosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/actualizar', 'update')->name('update');
    });

    // Subgrupo: Buscador de Archivos (Prefijo limpio adaptado)
    Route::prefix('buscador-archivos')->name('busca_archivos.')->controller(BuscadorArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/filtrar', 'filtrar')->name('filtrar');
        Route::get('/descargar/{id}', 'descargar')->name('descargar');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/imprimir', 'imprimirReporte')->name('imprimir');
    });

    // Subgrupo: Carga de Archivos (Prefijo limpio adaptado)
    Route::prefix('carga-archivos')->name('carga_archivos.')->controller(CargaArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/verificar-nombre', 'revisarexistencia')->name('check_availability');
        Route::patch('/status/{id}', 'toggleStatus')->name('status');
        Route::get('/editar/{id}', 'editar')->name('edit');
        Route::put('/actualizar/{id}', 'actualizar')->name('update');
        Route::get('/cargar/{id}', 'cargar')->name('cargar');
        Route::post('/subir-archivo/{id}', 'subirArchivo')->name('subir_archivo');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/imprimir', 'imprimirReporte')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
    });
    
    // Subgrupo: Catálogo de Categorías (Prefijo limpio adaptado)
    Route::prefix('categoria-archivos')->name('categoria_archivos.')->controller(CategoriaArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/verificar', 'verificar')->name('verificar');
    });

    // Subgrupo: Catálogo de Categorías de Módulos (Prefijo limpio adaptado)
    Route::prefix('categoria-modulos')->name('categoria_modulos.')->controller(CategoriaModulosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::patch('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::patch('/{id}/colapsar', 'cambiarColapsar')->name('colapsar');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/graficas', 'graficas')->name('graficas');
    });

    // Subgrupo: Módulos (Prefijo limpio adaptado)
    Route::prefix('modulos')->name('modulos.')->controller(ModuloController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/imprimir/{tipo?}', 'imprimir')->name('reportes.imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/categoria-preview', 'categoriaPreview')->name('categoria_preview');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::patch('/{id}/toggle', 'cambiarStatus')->name('toggle');
        Route::put('/{id}/proyectos', 'actualizarProyectos')->name('proyectos.sync');
        Route::put('/{id}/perfiles', 'actualizarPerfiles')->name('perfiles.sync');
    });

    // Subgrupo: Proyectos
    Route::prefix('proyectos')->name('proyectos.')->controller(ProyectoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::put('/{id}/modulos', 'actualizarModulos')->name('modulos.sync');
    });

    // Subgrupo: Perfiles (Prefijo limpio adaptado)
    Route::prefix('perfiles')->name('perfiles.')->controller(PerfilController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/{id}/modulos', 'agregarModulos')->name('modulos');
        Route::put('/{id}/modulos', 'actualizarModulos')->name('modulos.sync');
    });

    // Subgrupo: Personas
    Route::prefix('personas')->name('personas.')->controller(PersonaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/municipios', 'municipios')->name('municipios');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::patch('/{id}/estudiante', 'cambiarEstudiante')->name('estudiante');
    });

    // Subgrupo: Usuarios
    Route::prefix('usuarios')->name('usuarios.')->controller(UsuarioController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::post('/{id}/restablecer', 'restablecerPassword')->name('restablecer');
    });

    // Módulo: Configuración General del Sistema
    Route::prefix('configuracion-sistema')->name('configuracion_sistema.')->controller(ConfiguracionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/institucion', 'actualizarInstitucion')->name('update.institucion');
        Route::post('/seguridad', 'actualizarSeguridad')->name('update.seguridad');
        Route::post('/encabezado', 'subirEncabezado')->name('update.encabezado');
    });

    // Subgrupo: Permisos de Archivo (Prefijo limpio adaptado)
    Route::prefix('permisos-archivo')->name('trabajador_categorias.')->controller(PermisosArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/asignar', 'asignar')->name('asignar');
        Route::post('/{id}/guardar', 'guardar')->name('guardar');
    });

    // ── Módulo: Áreas de Almacén (Inventario - Optimizado y Limpio) ──────────
    Route::prefix('areas-almacen')->name('areas_almacen.')->controller(AreaAlmacenController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::get('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Módulo: Áreas de Surtimiento (Inventario - Optimizado y Limpio) ───────
    Route::prefix('areas-surtimiento')->name('areas_surtimiento.')->controller(AreaSurtimientoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::get('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Módulo: Motivos de Devoluciones (Inventario) ──────────────────────────
    Route::prefix('motivos')->name('motivos.')->controller(MotivoController::class)->group(function () {
        Route::get('/', 'index')->name('index');                            // Listado
        Route::post('/', 'guardar')->name('store');                         // Registrar
        Route::get('/verificar', 'verificar')->name('verificar');          // Verificar disponibilidad
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');     // Reporte impresión
        Route::get('/{id}/edit', 'editar')->name('edit');                  // Formulario edición
        Route::put('/{id}', 'actualizar')->name('update');                 // Actualizar
        Route::get('/{id}/status', 'cambiarStatus')->name('status');       // Cambiar estado
    });


    // ── Módulo: Bajas de Insumos (Inventario) ──────────────────────────────
    Route::prefix('bajas-insumos')->name('bajas_insumos.')->controller(BajaInsumoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/buscar-insumos', 'buscarInsumos')->name('buscar_insumos');
        Route::get('/consultar-stock', 'consultarStock')->name('consultar_stock');
        Route::get('/{id}/toggle-status', 'toggleStatus')->name('toggle_status');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Módulo: Devoluciones (Inventario) ────────────────────────────────────
    Route::prefix('devoluciones')->middleware('modulo:30')->name('devoluciones.')->controller(DevolucionController::class)->group(function () {
        Route::get('/', 'index')->name('index');                              // Pendientes
        Route::post('/', 'store')->name('store');                             // Crear nueva
        Route::get('/buscar-insumos', 'buscarInsumos')->name('buscar_insumos'); // Autocompletado
        Route::get('/terminadas', 'terminadas')->name('terminadas');          // Terminadas
        Route::get('/reportes', 'reportes')->name('reportes');               // Vista reportes
        Route::get('/reportes/imprimir', 'imprimir')->name('imprimir');      // Imprimir reporte
        Route::get('/{id}/detalle', 'detalle')->name('detalle');             // Ver/agregar insumos
        Route::post('/{id}/finalizar', 'finalizar')->name('finalizar');      // Finalizar devolución
        Route::get('/{id}/comprobante', 'comprobante')->name('comprobante'); // Comprobante PDF
        Route::get('/{id}/toggle-status', 'toggleStatus')->name('toggle_status'); // Alternar estado (Cancelar/Reactivar)
    });

    Route::prefix('detalle-devoluciones')->middleware('modulo:30')->name('detalle_devoluciones.')->controller(DetalleDevolucionController::class)->group(function () {
        Route::post('/', 'store')->name('store');         // Agregar insumo
        Route::put('/{id}', 'update')->name('update');   // Actualizar detalle
        Route::delete('/{id}', 'destroy')->name('destroy'); // Eliminar insumo
    });

    // ── Módulo: Entrada de Insumos al Cendis (Inventario) ──────────────────────
    Route::prefix('entradas-cendis')->middleware('modulo:35')->name('entradas_cendis.')->controller(EntradaCendisController::class)->group(function () {
        Route::get('/', 'index')->name('index');                              // Pendientes
        Route::post('/', 'store')->name('store');                             // Crear nueva
        Route::get('/buscar-insumos', 'buscarInsumos')->name('buscar_insumos'); // Autocompletado
        Route::get('/consultar-stock', 'consultarStock')->name('consultar_stock'); // Consultar stock actual
        Route::get('/terminadas', 'terminadas')->name('terminadas');          // Terminadas
        Route::get('/reportes', 'reportes')->name('reportes');               // Vista reportes
        Route::get('/reportes/imprimir', 'imprimir')->name('imprimir');      // Imprimir reporte
        Route::get('/{id}/detalle', 'detalle')->name('detalle');             // Ver/agregar insumos
        Route::post('/{id}/finalizar', 'finalizar')->name('finalizar');      // Finalizar entrada
        Route::get('/{id}/comprobante', 'comprobante')->name('comprobante'); // Comprobante PDF
        Route::get('/{id}/toggle-status', 'toggleStatus')->name('toggle_status'); // Alternar estado (Cancelar/Reactivar)
    });

    Route::prefix('detalle-entradas-cendis')->middleware('modulo:35')->name('detalle_entradas_cendis.')->controller(DetalleEntradaCendisController::class)->group(function () {
        Route::post('/', 'store')->name('store');         // Agregar insumo
        Route::put('/{id}', 'update')->name('update');   // Actualizar detalle
        Route::delete('/{id}', 'destroy')->name('destroy'); // Eliminar insumo
    });

    // ── Módulo: Catálogo de Insumos (Inventario) ─────────────────────────────
    Route::prefix('insumos')->middleware('modulo:32')->name('insumos.')->controller(InsumoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::get('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Módulo: Insumos por Área (Inventario) ─────────────────────────────
    Route::prefix('insumos-area')->middleware('modulo:36')->name('insumos_area.')->controller(InsumoAreaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/stock', 'updateStock')->name('update_stock');
        Route::patch('/{id}/fondo-fijo', 'updateFondoFijo')->name('update_fondo_fijo');
        Route::get('/buscar-insumos', 'buscarInsumosCatalog')->name('buscar_insumos');
        Route::get('/verificar-clave', 'consultarInsumoClave')->name('verificar_clave');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/datos', 'obtenerReporteDatos')->name('reporte_datos');
        Route::get('/reportes/imprimir', 'imprimir')->name('imprimir');
    });

    // Subgrupo: Radiología RX (Prefijo limpio adaptado)
    Route::prefix('rx-estudios')->name('rx.')->controller(RxController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/estudios', 'estudios')->name('estudios');
    
        // Pacientes
        Route::get('/pacientes/ver/{id}', 'verPaciente')->name('pacientes.ver');
        Route::post('/pacientes/guardar', 'guardarPaciente')->name('pacientes.guardar');
        Route::put('/pacientes/actualizar/{id}', 'actualizarPaciente')->name('pacientes.actualizar');
        Route::delete('/pacientes/eliminar/{id}', 'eliminarPaciente')->name('pacientes.eliminar');
        
        // Estudios
        Route::get('/estudios/ver/{id}', 'verEstudio')->name('estudios.ver');
        Route::post('/estudios/guardar', 'guardarEstudio')->name('estudios.guardar');
        Route::put('/estudios/actualizar/{id}', 'actualizarEstudio')->name('estudios.actualizar');
        Route::delete('/estudios/eliminar/{id}', 'eliminarEstudio')->name('estudios.eliminar');
    });

    // ── Módulo: Computadoras (Mobiliario y Equipo) ──────────────────────────
    Route::prefix('computadoras')->name('computadoras.')->controller(ComputadoraController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });
});