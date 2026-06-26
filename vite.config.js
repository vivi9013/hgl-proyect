import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/loginstyle.css',
                'resources/css/indexstyle.css',
                'resources/css/buscador_archivos/buscador.css',
                'resources/css/carga_archivos/carga.css',
                'resources/css/mis_datos/mis_datos.css',
                'resources/css/cambiar_contra/contra.css',
                'resources/js/app.js',
                'resources/js/login.js',
                'resources/js/buscador_archivos/buscador.js',
                'resources/js/carga_archivos/carga.js',
                'resources/js/mis_datos/mis_datos.js',
                'resources/css/categoria_archivos/categoria.css',
                'resources/js/categoria_archivos/categoria.js',
                'resources/css/trabajador_categorias/permisos.css',
                'resources/js/trabajador_categorias/permisos.js',
                'resources/css/inventario/areas_almacen/areas.css',
                'resources/js/inventario/areas_almacen/areas.js',
                'resources/css/inventario/areas_surtimiento/surtimiento.css',
                'resources/js/inventario/areas_surtimiento/surtimiento.js',
                'resources/css/inventario/bajas_insumos/bajas.css',
                'resources/js/inventario/bajas_insumos/bajas.js',
                // Devoluciones Assets
                'resources/css/inventario/devoluciones/devoluciones.css',
                'resources/js/inventario/devoluciones/devoluciones.js',
                // Cendis Assets
                'resources/css/inventario/entradas_cendis/entradas.css',
                'resources/js/inventario/entradas_cendis/entradas.js',
                // Insumos Assets
                'resources/css/inventario/insumos/insumos.css',
                'resources/js/inventario/insumos/insumos.js',

                // Insumos por Área Assets
                'resources/css/inventario/insumos_area/insumos_area.css',
                'resources/js/inventario/insumos_area/insumos_area.js',

                // Categoria de Modulos Assets
                'resources/css/categoria_modulos/categoria.css',
                'resources/js/categoria_modulos/categoria.js',
                'resources/js/categoria_modulos/categoria_edit.js',
                'resources/js/categoria_modulos/categoria_reportes.js',
                // Configuración General Assets
                'resources/css/configuracion_sistema/configuracion.css',
                'resources/js/configuracion_sistema/configuracion.js',
                // Módulos CRUD Assets
                'resources/css/modulos/modulos.css',
                'resources/js/modulos/modulos.js',
                // Perfiles Assets
                'resources/css/perfiles/perfiles.css',
                'resources/js/perfiles/perfiles.js',
                'resources/js/perfiles/perfiles_modulos.js',
                // Personas Assets
                'resources/css/personas/personas.css',
                'resources/js/personas/personas.js',
                'resources/js/personas/personas_edit.js',
                // Proyectos Assets
                'resources/css/proyectos/proyectos.css',
                'resources/js/proyectos/proyectos.js',
                // Usuarios Assets
                'resources/css/usuarios/usuarios.css',
                'resources/js/usuarios/usuarios.js',
                'resources/js/usuarios/usuarios_edit.js',
                // Impresoras Assets
                'resources/css/control_insumos/impresoras/impresoras.css',
                'resources/js/control_insumos/impresoras/impresoras.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        outDir: 'public/build',
        manifest: true,
    },
});
