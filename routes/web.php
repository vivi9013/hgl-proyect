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
use App\Http\Controllers\Pacientes\RxEspecialidadController;
use App\Http\Controllers\Pacientes\RxMedicoController;
use App\Http\Controllers\Pacientes\RxEstadisticaController;
use App\Http\Controllers\CategoriaModulos\CategoriaModulosController;
use App\Http\Controllers\ConfiguracionSistema\ConfiguracionController;
use App\Http\Controllers\Modulos\ModuloController;
use App\Http\Controllers\Perfiles\PerfilController;
use App\Http\Controllers\Personas\PersonaController;
use App\Http\Controllers\Proyectos\ProyectoController;
use App\Http\Controllers\Usuarios\UsuarioController;
use App\Http\Controllers\Computadoras\ComputadoraController;
use App\Http\Controllers\Miscelaneo\ActividadController;
use App\Http\Controllers\Mobiliario\MobiliarioController;
use App\Http\Controllers\Mobiliario\TipoMobiliarioController;
use App\Http\Controllers\ControlInsumos\ImpresoraController;
use App\Http\Controllers\Monitores\MonitorController;
use App\Http\Controllers\ControlInsumos\InsumoImpresoraController;
use App\Http\Controllers\ControlInsumos\MovimientoInsumoController;
use App\Http\Controllers\SoporteTecnico\SoporteAreaController;
use App\Http\Controllers\SoporteTecnico\SolicitarServicioController;
use App\Http\Controllers\Departamentos\DepartamentoController;
use App\Http\Controllers\Puestos\PuestoController;
use App\Http\Controllers\Sedes\SedeController;
use App\Http\Controllers\Trabajadores\TipoTrabajadorController;
use App\Http\Controllers\Trabajadores\TrabajadorController;
use App\Http\Controllers\PeticionInsumos\AlmacenSubareaController;
use App\Http\Controllers\PeticionInsumos\AreaAbastecimientoController;
use App\Http\Controllers\PeticionInsumos\SubareaAbastecimientoController;
use App\Http\Controllers\PeticionInsumos\PlantillaPedidoController;
use App\Http\Controllers\PeticionInsumos\PedidoInsumoController;
use App\Http\Controllers\PeticionInsumos\PedidoInsumoDiferenciaController;


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
use App\Http\Controllers\Inventario\MotivoController;
use App\Http\Controllers\Inventario\ReporteInventarioController;
use App\Http\Controllers\Inventario\PedidoRecibidoController;

// Redirección raíz por defecto
Route::get('/', function () {
    return redirect()->route('inicio');
});


// ── Redirects legacy (URLs del sistema antiguo) ────────────────────────────
Route::get('/mMotivos', fn() => redirect()->route('motivos.index'));
Route::get('/mPedidosRecibidos', fn() => redirect()->route('pedidos_recibidos.index'));
Route::get('/mRXespecialidad', fn() => redirect()->route('rx_especialidades.index'));
Route::get('/mRXmedicos', fn() => redirect()->route('rx_medicos.index'));
Route::get('/mRXestudios', fn() => redirect()->route('rx.index'));
Route::get('/mEstReportesRX', fn() => redirect()->route('rx_estadisticas.index'));
Route::get('/mRegActividades', fn() => redirect()->route('actividades.index'));
Route::get('/mImpresoras', fn() => redirect()->route('impresoras.index'));
Route::get('/mMonitores', fn() => redirect()->route('monitores.index'));
Route::get('/mTipoMobiliario', fn() => redirect()->route('tipo_mobiliario.index'));
Route::get('/mSoporteArea', fn() => redirect()->route('soporte_area.index'));
Route::get('/MsoporteArea', fn() => redirect()->route('soporte_area.index'));
Route::get('/mSolicitarServicio', fn() => redirect()->route('solicitar_servicio.index'));
Route::get('/mDepartamentos', fn() => redirect()->route('departamentos.index'));
Route::get('/mPuestos', fn() => redirect()->route('puestos.index'));
Route::get('/mSedes', fn() => redirect()->route('sedes.index'));
Route::get('/mPlantillasPedidos', fn() => redirect()->route('plantillas_pedido.index'));
Route::get('/mPlantillasPedido', fn() => redirect()->route('plantillas_pedido.index'));
Route::get('/mPlantillaPedido', fn() => redirect()->route('plantillas_pedido.index'));
Route::get('/mPedidoInsumos', fn() => redirect()->route('pedido_insumos.index'));
Route::get('/mPedidoInsumosDif', fn() => redirect()->route('pedido_insumos_dif.index'));
Route::get('/mTipoTrabajador', fn() => redirect()->route('tipo_trabajador.index'));
Route::get('/mAlmacenSubAreas', fn() => redirect()->route('almacen_subareas.index'));
Route::get('/mAlmacenSubarea', fn() => redirect()->route('almacen_subareas.index'));
Route::get('/mAreaAbastecimiento', fn() => redirect()->route('areas_abastecimiento.index'));
Route::get('/mAreasAbastecimiento', fn() => redirect()->route('areas_abastecimiento.index'));
Route::get('/mSubareaAbastecimiento', fn() => redirect()->route('subareas_abastecimiento.index'));
Route::get('/mSubareasAbastecimiento', fn() => redirect()->route('subareas_abastecimiento.index'));

// ── GRUPO PARA INVITADOS ───────────────────────────────────────────────────
Route::middleware(['guest', EvitarRetrocesoMiddleware::class])->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/validar-login', [LoginController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.post');
});


// ── GRUPO PARA USUARIOS AUTENTICADOS ───────────────────────────────────────
Route::middleware(['auth', EvitarRetrocesoMiddleware::class])->group(function () {

    // =========================================================================
    // SECCIÓN 1: MÓDULOS GENERALES Y UTILIDADES DE PERFIL
    // Accesibles por cualquier usuario autenticado. Sin restricción por módulo.
    // =========================================================================

    Route::get('/inicio', [IndexController::class, 'index'])->name('inicio');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    // Gestión de Credenciales de Usuario
    Route::controller(LoginController::class)->group(function () {
        Route::get('/cambiar-contrasena', 'showCambiarContra')->name('cambiar_contra.index');
        Route::put('/actualizar-password', 'updatePassword')->name('password.update');
    });

    // Cambiar Fotografía de Perfil
    Route::controller(CambiarFotoController::class)->group(function () {
        Route::get('/cambiar-foto', 'index')->name('cambiar_foto.index');
        Route::post('/cambiar-foto', 'store')->name('cambiar_foto.store');
    });

    // Cambiar Tema de la Interfaz
    Route::controller(TemaController::class)->group(function () {
        Route::get('/cambiar-tema', 'index')->name('tema.index');
        Route::patch('/cambiar-tema', 'update')->name('tema.update');
    });

    // Mis Datos Personales
    Route::prefix('mis-datos')->name('mis_datos.')->controller(MisDatosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/actualizar', 'update')->name('update');
    });

    // Cumpleaños (utilidad de perfil, accesible desde el sidebar)
    Route::get('/cumpleanos', [CumpleanosController::class, 'index'])->name('cumpleanos.index');


    // =========================================================================
    // SECCIÓN 2: MÓDULOS RESTRINGIDOS CON VERIFICACIÓN DE PERFIL
    // Cada grupo requiere que el perfil del usuario tenga asignado el módulo.
    // El middleware 'modulo:ID' valida contra la tabla modulo_perfil en la BD.
    // =========================================================================

    // ── Admin Sistema: Configuración General (ID: 1) ─────────────────────────
    Route::prefix('configuracion-sistema')->middleware('modulo:1')->name('configuracion_sistema.')->controller(ConfiguracionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/institucion', 'actualizarInstitucion')->name('update.institucion');
        Route::post('/seguridad', 'actualizarSeguridad')->name('update.seguridad');
        Route::post('/encabezado', 'subirEncabezado')->name('update.encabezado');
    });

    // ── Admin Sistema: Perfiles (ID: 2) ──────────────────────────────────────
    Route::prefix('perfiles')->middleware('modulo:2')->name('perfiles.')->controller(PerfilController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/{id}/modulos', 'agregarModulos')->name('modulos');
        Route::put('/{id}/modulos', 'actualizarModulos')->name('modulos.sync');
    });

    // ── Admin Sistema: Usuarios (ID: 3) ──────────────────────────────────────
    Route::prefix('usuarios')->middleware('modulo:3')->name('usuarios.')->controller(UsuarioController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::post('/{id}/restablecer', 'restablecerPassword')->name('restablecer');
    });

    // ── Admin Sistema: Módulos (ID: 4) ───────────────────────────────────────
    Route::prefix('modulos')->middleware('modulo:4')->name('modulos.')->controller(ModuloController::class)->group(function () {
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

    // ── Admin Sistema: Personas (ID: 5) ──────────────────────────────────────
    Route::prefix('personas')->middleware('modulo:5')->name('personas.')->controller(PersonaController::class)->group(function () {
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

    // ── Admin Sistema: Categoría de Módulos (ID: 6) ──────────────────────────
    Route::prefix('categoria-modulos')->middleware('modulo:6')->name('categoria_modulos.')->controller(CategoriaModulosController::class)->group(function () {
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

    // ── Admin Sistema: Proyectos (ID: 14) ────────────────────────────────────
    Route::prefix('proyectos')->middleware('modulo:14')->name('proyectos.')->controller(ProyectoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::put('/{id}/modulos', 'actualizarModulos')->name('modulos.sync');
    });

    // ── Módulos Institucionales: Tipo de Trabajador (ID: 7) ───────────────────
    Route::prefix('tipo-trabajador')->middleware('modulo:7')->name('tipo_trabajador.')->controller(TipoTrabajadorController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/graficas',          'graficas')     ->name('graficas');
        Route::get('/verificar',         'verificar')    ->name('verificar');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
    });

    // ── Módulos Institucionales: Departamentos (ID: 9) ───────────────────────
    Route::prefix('departamentos')->middleware('modulo:9')->name('departamentos.')->controller(DepartamentoController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/graficas',          'graficas')     ->name('graficas');
        Route::get('/verificar',         'verificar')    ->name('verificar');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
    });

    // ── Módulos Institucionales: Puestos (ID: 8) ─────────────────────────────
    Route::prefix('puestos')->middleware('modulo:8')->name('puestos.')->controller(PuestoController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/graficas',          'graficas')     ->name('graficas');
        Route::get('/verificar',         'verificar')    ->name('verificar');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
    });

    // ── Módulos Institucionales: Sedes (ID: 11) ──────────────────────────────
    Route::prefix('sedes')->middleware('modulo:11')->name('sedes.')->controller(SedeController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/graficas',          'graficas')     ->name('graficas');
        Route::get('/verificar',         'verificar')    ->name('verificar');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
    });

    // ── Módulos Institucionales: Trabajadores (ID: 10) ───────────────────────
    Route::prefix('trabajadores')->middleware('modulo:10')->name('trabajadores.')->controller(TrabajadorController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/graficas',          'graficas')     ->name('graficas');
        Route::get('/verificar',         'verificar')    ->name('verificar');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
    });

    // ── Petición de Insumos: Almacén de Subáreas (ID: 38) ────────────────────
    Route::prefix('peticion-insumos/almacen-subareas')->middleware('modulo:38')->name('almacen_subareas.')->controller(AlmacenSubareaController::class)->group(function () {
        Route::get('/',                      'index')             ->name('index');
        Route::post('/guardar',              'guardar')           ->name('store');
        Route::get('/subareas-por-area',     'subareasPorArea')   ->name('subareas_por_area');
        Route::post('/{id}/insumo',          'agregarInsumo')     ->name('insumo.store');
        Route::put('/detalle/{id}',          'actualizarDetalle') ->name('detalle.update');
        Route::delete('/detalle/{id}',       'eliminarDetalle')   ->name('detalle.destroy');
        Route::get('/reportes',              'reportes')          ->name('reportes');
        Route::get('/reportes/imprimir',     'imprimir')          ->name('imprimir');
        Route::patch('/{id}/status',         'cambiarStatus')     ->name('status');
    });


    // ── Petición de Insumos: Áreas de Abastecimiento (ID: 36) ──────────────────
    Route::prefix('peticion-insumos/areas-abastecimiento')->middleware('modulo:36')->name('areas_abastecimiento.')->controller(AreaAbastecimientoController::class)->group(function () {
        Route::get('/',                             'index')             ->name('index');
        Route::post('/guardar',                     'guardar')           ->name('store');
        Route::get('/graficas',                     'graficas')          ->name('graficas');
        Route::get('/verificar',                    'verificar')         ->name('verificar');
        Route::get('/reportes',                     'reportes')          ->name('reportes');
        Route::get('/reportes/imprimir',            'imprimir')          ->name('imprimir');
        Route::get('/relacionar',                   'relacionar')        ->name('relacionar');
        Route::get('/{id}/edit',                    'editar')            ->name('edit');
        Route::put('/{id}',                         'actualizar')        ->name('update');
        Route::patch('/{id}/status',                'cambiarStatus')     ->name('status');
        Route::post('/{id}/subareas',               'vincularSubarea')   ->name('vincular_subarea');
        Route::patch('/{id}/subareas/{idSubarea}/desvincular', 'desvincularSubarea')->name('desvincular_subarea');
    });

    // ── Petición de Insumos: Subáreas de Abastecimiento (ID: 37) ───────────────
    Route::prefix('peticion-insumos/subareas-abastecimiento')->middleware('modulo:37')->name('subareas_abastecimiento.')->controller(SubareaAbastecimientoController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/graficas',          'graficas')     ->name('graficas');
        Route::get('/verificar',         'verificar')    ->name('verificar');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
    });

    // ── Mobiliario y Equipo: Mobiliario General (ID: 21) ─────────────────────
    Route::prefix('mobiliario')->middleware('modulo:21')->name('mobiliario.')->controller(MobiliarioController::class)->group(function () {
        // Rutas estáticas primero
        Route::get('/',                  'index')        ->name('index');
        Route::post('/',                 'guardar')      ->name('store');
        Route::get('/graficas',          'graficas')     ->name('graficas');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        // Rutas con parámetros al final
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
    });

    // ── Mobiliario y Equipo: Computadoras (ID: 22) ───────────────────────────
    Route::prefix('computadoras')->middleware('modulo:22')->name('computadoras.')->controller(ComputadoraController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Mobiliario y Equipo: Impresoras (ID: 23) ─────────────────────────────
    Route::prefix('control-insumos/impresoras')->middleware('modulo:23')->name('impresoras.')->controller(ImpresoraController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
        Route::get('/verificar-ip',      'verificarIp')  ->name('verificar_ip');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        Route::get('/graficas',          'graficas')     ->name('graficas');
    });

    // ── Mobiliario y Equipo: Tipo de Mobiliario (ID: 25) ─────────────────────
    Route::prefix('tipo-mobiliario')->middleware('modulo:25')->name('tipo_mobiliario.')->controller(TipoMobiliarioController::class)->group(function () {
        // Rutas estáticas primero (antes de las rutas con parámetros /{id})
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/graficas',          'graficas')     ->name('graficas');
        Route::get('/verificar',         'verificar')    ->name('verificar');
        Route::get('/reportes',          'reportes')     ->name('reportes');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
        // Rutas con parámetros al final
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
    });

    // ── Mobiliario y Equipo: Monitores (ID: 24) ──────────────────────────────
    Route::prefix('monitores')->middleware('modulo:24')->name('monitores.')->controller(MonitorController::class)->group(function () {
        Route::get('/',                     'index')              ->name('index');
        Route::post('/guardar',             'guardar')            ->name('store');
        Route::get('/{id}/edit',            'editar')             ->name('edit');
        Route::put('/{id}',                 'actualizar')         ->name('update');
        Route::patch('/{id}/status',        'cambiarStatus')      ->name('status');
        Route::get('/mobiliario-info/{inv}','getMobiliarioInfo')  ->name('mobiliario_info');
        Route::get('/reportes',             'reportes')           ->name('reportes');
        Route::get('/reportes/imprimir',    'imprimir')           ->name('imprimir');
        Route::get('/graficas',             'graficas')           ->name('graficas');
    });

    // ── Control Insumos: Catálogo de Insumos de Impresora (ID: 49) ───────────
    Route::prefix('control-insumos/insumos-impresoras')->middleware('modulo:49')->name('insumos_impresoras.')->controller(InsumoImpresoraController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/status',     'cambiarStatus')->name('status');
        Route::get('/buscar',            'buscar')       ->name('buscar');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
    });

    // ── Control Insumos: Movimientos de Insumos (Entradas/Salidas) (ID: 51) ──
    Route::prefix('control-insumos/movimientos-insumos')->middleware('modulo:51')->name('movimientos_insumos.')->controller(MovimientoInsumoController::class)->group(function () {
        Route::get('/',                  'index')        ->name('index');
        Route::post('/guardar',          'guardar')      ->name('store');
        Route::get('/{id}/edit',         'editar')       ->name('edit');
        Route::put('/{id}',              'actualizar')   ->name('update');
        Route::patch('/{id}/cancelar',   'cancelar')     ->name('cancelar');
        Route::get('/reportes/imprimir', 'imprimir')     ->name('imprimir');
    });

    // ── Admin Formatos: Buscador de Archivos (ID: 26) ────────────────────────
    Route::prefix('buscador-archivos')->middleware('modulo:26')->name('busca_archivos.')->controller(BuscadorArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/filtrar', 'filtrar')->name('filtrar');
        Route::get('/descargar/{id}', 'descargar')->name('descargar');
        Route::get('/imprimir', 'imprimirReporte')->name('imprimir');
    });

    // ── Admin Formatos: Carga de Archivos (ID: 27) ───────────────────────────
    Route::prefix('carga-archivos')->middleware('modulo:27')->name('carga_archivos.')->controller(CargaArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'guardar')->name('store');
        Route::get('/verificar-nombre', 'revisarexistencia')->name('check_availability');
        Route::patch('/status/{id}', 'toggleStatus')->name('status');
        Route::get('/editar/{id}', 'editar')->name('edit');
        Route::put('/actualizar/{id}', 'actualizar')->name('update');
        Route::get('/cargar/{id}', 'cargar')->name('cargar');
        Route::post('/subir-archivo/{id}', 'subirArchivo')->name('subir_archivo');
        Route::get('/reportes/imprimir', 'imprimirReporte')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
    });

    // ── Admin Formatos: Categoría de Archivos (ID: 28) ───────────────────────
    Route::prefix('categoria-archivos')->middleware('modulo:28')->name('categoria_archivos.')->controller(CategoriaArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/verificar', 'verificar')->name('verificar');
    });

    // ── Admin Formatos: Permisos de Acceso a Archivos (ID: 29) ───────────────
    Route::prefix('permisos-archivo')->middleware('modulo:29')->name('trabajador_categorias.')->controller(PermisosArchivosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/asignar', 'asignar')->name('asignar');
        Route::post('/{id}/guardar', 'guardar')->name('guardar');
    });

    // ── Inventario: Devoluciones (ID: 30) ────────────────────────────────────
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
        Route::get('/{id}/toggle-status', 'toggleStatus')->name('toggle_status'); // Alternar estado
    });

    Route::prefix('detalle-devoluciones')->middleware('modulo:30')->name('detalle_devoluciones.')->controller(DetalleDevolucionController::class)->group(function () {
        Route::post('/', 'store')->name('store');         // Agregar insumo
        Route::put('/{id}', 'update')->name('update');   // Actualizar detalle
        Route::delete('/{id}', 'destroy')->name('destroy'); // Eliminar insumo
    });

    // ── Inventario: Pedidos Recibidos (ID: 31) ────────────────────────────────
    Route::prefix('pedidos-recibidos')->middleware('modulo:31')->name('pedidos_recibidos.')->controller(PedidoRecibidoController::class)->group(function () {
        Route::get('/', 'index')->name('index');                                            // Pendientes
        Route::get('/aceptados', 'aceptados')->name('aceptados');                          // Surtidos/Aceptados
        Route::get('/cancelados', 'cancelados')->name('cancelados');                       // Cancelados
        Route::get('/{id}/detalle', 'detalle')->name('detalle');                           // Ver/Surtir detalle
        Route::post('/{id}/liberar', 'liberar')->name('liberar');                          // Liberar pedido
        Route::post('/{id}/cancelar', 'cancelar')->name('cancelar');                       // Cancelar pedido
        Route::patch('/detalle/{id}/guardar-surtido', 'guardarSurtido')->name('guardar_surtido'); // AJAX surtido
        Route::get('/{id}/comprobante', 'comprobante')->name('comprobante');               // Comprobante PDF
    });

    // ── Inventario: Catálogo de Insumos (ID: 32) ─────────────────────────────
    Route::prefix('insumos')->middleware('modulo:32')->name('insumos.')->controller(InsumoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::get('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Inventario: Bajas de Insumos (ID: 33) ────────────────────────────────
    Route::prefix('bajas-insumos')->middleware('modulo:33')->name('bajas_insumos.')->controller(BajaInsumoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/buscar-insumos', 'buscarInsumos')->name('buscar_insumos');
        Route::get('/consultar-stock', 'consultarStock')->name('consultar_stock');
        Route::get('/historial-area-asignada', 'historialPorAreaAsignada')->name('historial_area_asignada');
        Route::get('/exportar-excel', 'exportarExcel')->name('exportar_excel');
        Route::get('/{id}/toggle-status', 'toggleStatus')->name('toggle_status');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Inventario: Áreas de Almacén (ID: 34) ────────────────────────────────
    Route::prefix('areas-almacen')->middleware('modulo:34')->name('areas_almacen.')->controller(AreaAlmacenController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::get('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Inventario: Entrada de Insumos al CENDIS (ID: 35) ────────────────────
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
        Route::get('/{id}/toggle-status', 'toggleStatus')->name('toggle_status'); // Alternar estado
    });

    Route::prefix('detalle-entradas-cendis')->middleware('modulo:35')->name('detalle_entradas_cendis.')->controller(DetalleEntradaCendisController::class)->group(function () {
        Route::post('/', 'store')->name('store');         // Agregar insumo
        Route::put('/{id}', 'update')->name('update');   // Actualizar detalle
        Route::delete('/{id}', 'destroy')->name('destroy'); // Eliminar insumo
    });

    // ── Inventario: Insumos por Área (ID: 36) ────────────────────────────────
    Route::prefix('insumos-area')->middleware('modulo:36')->name('insumos_area.')->controller(InsumoAreaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::patch('/{id}/stock', 'updateStock')->name('update_stock');
        Route::patch('/{id}/fondo-fijo', 'updateFondoFijo')->name('update_fondo_fijo');
        Route::get('/buscar-insumos', 'buscarInsumosCatalog')->name('buscar_insumos');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/datos', 'obtenerReporteDatos')->name('reporte_datos');
        Route::get('/reportes/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Inventario: Áreas de Surtimiento (ID: 37) ────────────────────────────
    Route::prefix('areas-surtimiento')->middleware('modulo:37')->name('areas_surtimiento.')->controller(AreaSurtimientoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'guardar')->name('store');
        Route::get('/{id}/edit', 'editar')->name('edit');
        Route::put('/{id}', 'actualizar')->name('update');
        Route::get('/{id}/status', 'cambiarStatus')->name('status');
        Route::get('/verificar', 'verificar')->name('verificar');
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Inventario: Reportes Generales de Inventario (ID: 42) ────────────────
    Route::prefix('reportes-inventario')->middleware('modulo:42')->name('reportes_inventario.')->controller(ReporteInventarioController::class)->group(function () {
        Route::get('/', 'index')->name('index');                                          // Vista principal
        Route::get('/areas-abastecimiento', 'areasAbastecimiento')->name('areas_abastecimiento'); // AJAX
        Route::get('/subareas/{idArea}', 'subareasAbastecimiento')->name('subareas');             // AJAX
        Route::get('/areas-almacen', 'areasAlmacen')->name('areas_almacen');                      // AJAX
        Route::get('/imprimir-entregas', 'imprimirEntregas')->name('imprimir_entregas');           // Reporte 1
        Route::get('/imprimir-concentrado', 'imprimirConcentrado')->name('imprimir_concentrado'); // Reporte 2
    });

    // ── Inventario: Motivos de Devoluciones (ID: 44) ─────────────────────────
    Route::prefix('motivos')->middleware('modulo:44')->name('motivos.')->controller(MotivoController::class)->group(function () {
        Route::get('/', 'index')->name('index');                            // Listado
        Route::post('/', 'guardar')->name('store');                         // Registrar
        Route::get('/verificar', 'verificar')->name('verificar');          // Verificar disponibilidad
        Route::get('/reporte/imprimir', 'imprimir')->name('imprimir');     // Reporte impresión
        Route::get('/{id}/edit', 'editar')->name('edit');                  // Formulario edición
        Route::put('/{id}', 'actualizar')->name('update');                 // Actualizar
        Route::get('/{id}/status', 'cambiarStatus')->name('status');       // Cambiar estado
    });

    // ── Módulo: Especialidad RX (Estudios Radiológicos) ────────────────────────
    Route::prefix('rx-especialidades')->name('rx_especialidades.')->controller(RxEspecialidadController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'store')->name('store');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::patch('/{id}/status', 'toggleStatus')->name('status');
    });

    // ── Módulo: Médicos RX (Estudios Radiológicos) ─────────────────────────────
    Route::prefix('rx-medicos')->name('rx_medicos.')->controller(RxMedicoController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/guardar', 'store')->name('store');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::patch('/{id}/status', 'toggleStatus')->name('status');
    });

    // ── Pacientes: Radiología / Estudios RX (ID: 45) ─────────────────────────
    Route::prefix('rx-estudios')->middleware('modulo:45')->name('rx.')->controller(RxController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/estudios', 'estudios')->name('estudios');
        Route::get('/reportes', 'reportes')->name('reportes');
        Route::get('/reportes/impresion', 'imprimir')->name('imprimir');
        Route::get('/graficas', 'graficas')->name('graficas');

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

    // ── Soporte Técnico: Asignación de Áreas (mSoporteArea) (ID: 16) ────────
    Route::prefix('soporte-tecnico/areas')
        ->middleware('modulo:16')
        ->name('soporte_area.')
        ->controller(SoporteAreaController::class)
        ->group(function () {
            Route::get('/',                        'index')          ->name('index');
            Route::get('/reportes/imprimir',       'imprimir')       ->name('imprimir');
            Route::get('/{id}/asignar',            'asignarAreas')   ->name('asignar');
            Route::post('/{id}/sincronizar',       'sincronizarAreas')->name('sincronizar');
            Route::patch('/{id}/status',           'cambiarStatus')  ->name('status');
        });

    // Subgrupo: Estadísticas y Reportes RX
    Route::prefix('rx-estadisticas')->name('rx_estadisticas.')->controller(RxEstadisticaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/datos', 'datos')->name('datos');
        Route::get('/imprimir', 'imprimir')->name('imprimir');
    });

    // ── Módulo: Registro de Actividades (Misceláneos) ──────────────────────
    Route::prefix('actividades')->name('actividades.')->controller(ActividadController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/datos', 'datos')->name('datos');
        Route::get('/graficas', 'graficas')->name('graficas');
        Route::get('/graficas/datos', 'datosGraficas')->name('graficas.datos');
    });
    // ── Petición de Insumos: Plantillas de Pedido (ID: 40) ────────────────────
    // Endpoint de combo en cascada: accesible con sólo auth (sin restricción modulo:40)
    Route::get('/peticion-insumos/plantillas-pedido/subareas-por-area',
        [PlantillaPedidoController::class, 'subareasPorArea'])
        ->name('plantillas_pedido.subareas_por_area');

    Route::prefix('peticion-insumos/plantillas-pedido')->middleware('modulo:40')->name('plantillas_pedido.')->controller(PlantillaPedidoController::class)->group(function () {
        Route::get('/',                              'index')            ->name('index');
        Route::post('/guardar',                      'guardar')          ->name('store');
        Route::post('/{id}/insumo',                  'agregarInsumo')    ->name('insumo.store');
        Route::put('/detalle/{id}',                  'actualizarDetalle')->name('detalle.update');
        Route::delete('/detalle/{id}',               'eliminarDetalle')  ->name('detalle.destroy');
        Route::patch('/{id}/status',                 'cambiarStatus')    ->name('status');
        Route::get('/reportes',                      'reportes')         ->name('reportes');
        Route::get('/reportes/impresion',            'imprimir')         ->name('imprimir');
        Route::get('/{id}/imprimir-individual',      'imprimirIndividual')->name('imprimir_individual');
        Route::get('/{id}/insumos',                  'editarInsumos')    ->name('insumos');
        Route::post('/{id}/insumos/guardar',         'guardarInsumos')   ->name('insumos.guardar');
        Route::put('/{id}',                          'actualizar')       ->name('update');
        Route::delete('/{id}',                       'eliminar')         ->name('destroy');
    });


    // ── Petición de Insumos: Pedido de Insumos (ID: 41) ──────────────────────
    Route::prefix('peticion-insumos/pedidos')->middleware('modulo:41')->name('pedido_insumos.')->controller(PedidoInsumoController::class)->group(function () {
        Route::get('/',                     'index')               ->name('index');
        Route::get('/subareas',             'subareasPorArea')     ->name('subareas');
        Route::get('/autocompletar-insumo', 'autocompletarInsumo')  ->name('autocompletar');
        Route::get('/plantilla/{id}',       'insumosPlantilla')    ->name('insumos_plantilla');
        Route::post('/guardar',             'guardar')             ->name('store');
        Route::get('/detalle/{id}',         'detalle')             ->name('detalle');
        Route::patch('/cancelar/{id}',      'cancelar')            ->name('cancelar');
        Route::get('/reportes',             'reportes')            ->name('reportes');
        Route::get('/imprimir/{id}',        'imprimir')            ->name('imprimir');
    });

    // ── Petición de Insumos: Pedido de Insumos por Diferencia (ID: 43) ────────
    Route::prefix('peticion-insumos/pedidos-diferencia')->middleware('modulo:43')->name('pedido_insumos_dif.')->controller(PedidoInsumoDiferenciaController::class)->group(function () {
        Route::get('/',                     'index')               ->name('index');
        Route::get('/calcular',             'calcularDiferencias') ->name('calcular');
        Route::post('/guardar',             'guardar')             ->name('store');
        Route::get('/reportes',             'reportes')            ->name('reportes');
        Route::get('/imprimir/{id}',        'imprimir')            ->name('imprimir');
    });


    // ── Soporte Técnico: Solicitar Servicio (mSolicitarServicio) ──────────────
    Route::prefix('solicitar-servicio')->name('solicitar_servicio.')->controller(SolicitarServicioController::class)->group(function () {
        Route::get('/',                      'index')          ->name('index');
        Route::post('/guardar',              'store')          ->name('store');
        Route::get('/seguimiento',           'seguimiento')    ->name('seguimiento');
        Route::get('/historial',             'historial')      ->name('historial');
        Route::post('/{id}/liberar',         'liberar')        ->name('liberar');
        Route::get('/{id}/detalles',         'detalles')       ->name('detalles');
        Route::get('/reportes',              'reportes')       ->name('reportes');
        Route::get('/reportes/imprimir',     'imprimirReporte')->name('imprimir');
        Route::get('/graficas',              'graficas')       ->name('graficas');
    });
});

Route::get('/log-js-error', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::error('JS DIAGNOSTIC: ' . $request->input('error') . ' en L: ' . $request->input('line') . ' C: ' . $request->input('col') . ' en archivo: ' . $request->input('file'));
    return response()->json(['status' => 'logged']);
});