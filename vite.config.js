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
                'resources/js/app.js',
                'resources/js/login.js',
                'resources/js/buscador_archivos/buscador.js',
                'resources/js/carga_archivos/carga.js',
                'resources/js/mis_datos/mis_datos.js'
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
