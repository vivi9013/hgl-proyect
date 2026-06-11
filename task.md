# Migración de Módulo "modulos"

- `[x]` Crear el modelo `Proyecto` (`app/Models/Proyecto.php`) y registrar la relación en `Modulo.php`
- `[x]` Crear y registrar rutas en `routes/web.php`
- `[x]` Crear el controlador `ModuloController` (`app/Http/Controllers/Modulos/ModuloController.php`)
- `[x]` Crear archivos CSS y JS (`resources/css/modulos/modulos.css`, `resources/js/modulos/modulos.js`) y registrar en `vite.config.js`
- `[x]` Crear la vista de listado y alta (`resources/views/admin_sistema/modulos/index.blade.php` y `partials/tabla.blade.php`)
- `[x]` Crear la vista de edición (`resources/views/admin_sistema/modulos/editar.blade.php`)
- `[x]` Crear las vistas de asignación de proyectos y perfiles (`resources/views/admin_sistema/modulos/proyectos.blade.php`, `resources/views/admin_sistema/modulos/perfiles.blade.php`)
- `[x]` Crear las vistas de reportes y de impresión (`resources/views/admin_sistema/modulos/reportes.blade.php`, `reportes/reporte_impresion.blade.php`)
- `[x]` Crear la vista consolidada de gráficas (`resources/views/admin_sistema/modulos/analitica/graficas.blade.php`)
- `[x]` Actualizar redirecciones en la vista principal (`resources/views/index.blade.php`)
- `[x]` Validar y probar todo el flujo migrado (CRUD, asignación, gráficas, reportes)
- `[x]` Eliminar directorio legacy `resources/views/admin_sistema/modulos/mModulos` y actualizar `PROJECT_MEMORY.md`
