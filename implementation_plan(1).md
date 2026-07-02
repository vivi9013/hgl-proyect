# Plan de Implementación: Reorganización de vistas `sidebar/` + Normalización de módulos

**Proyecto:** HGL Proyecto (Laravel 11)
**Repositorio:** vivi9013/hgl-proyect
**Ejecutor:** Agente de IA en IDE (Claude Code)

---

## 1. Contexto

Los módulos `cambiar_contra`, `cambiar_foto` (actualmente `CambiarFoto`), `cumpleanos`, `mis_datos` y `tema` son enlaces **estáticos/hardcodeados** en `resources/views/layouts/sidebar.blade.php` — no se generan dinámicamente desde la tabla `modulos` como el resto de módulos del sistema (`admin_sistema`, `admin_formatos`, `inventario`). Se agrupan en una nueva carpeta de vistas `resources/views/sidebar/` para reflejar esa categoría común.

Adicionalmente se normalizan dos inconsistencias de nomenclatura detectadas:
- La carpeta de vista `CambiarFoto` (PascalCase) es la única en todo `resources/views/` que no sigue snake_case.
- El nombre de ruta `cambiar_tema.*` no coincide con el nombre de su carpeta de vista (`tema`), a diferencia de los otros 4 módulos donde ruta y carpeta ya coinciden.

## 2. Alcance — qué se toca y qué NO

**Se modifica:**
- Ubicación de 5 archivos `.blade.php` (movidos a `resources/views/sidebar/`)
- 5 llamadas `view(...)` en controladores
- Nombre de ruta `cambiar_tema.index` / `cambiar_tema.update` → `tema.index` / `tema.update`

**NO se modifica (verificado explícitamente, cero referencias adicionales encontradas):**
- URIs públicas (`/cambiar-contrasena`, `/cambiar-foto`, `/cumpleanos`, `/mis-datos`, `/cambiar-tema` se mantienen tal cual)
- Nombres de ruta de `cambiar_contra`, `cambiar_foto`, `cumpleanos`, `mis_datos` (ya coinciden con sus carpetas)
- `app/Http/Controllers/CambiarFoto/` — el namespace PHP en PascalCase es la convención correcta de Laravel/PSR-4 y coincide con el resto de carpetas de `Controllers/` (`BuscadorArchivos`, `CargarArchivos`, etc.). No confundir con la carpeta de vista, que sí usa snake_case.
- `resources/css`, `resources/js`, `public/assets/css` (assets de estos módulos, incluida la migración legacy→Vite de `tema` y `cambiar_foto`, quedan fuera de este plan)
- Tabla `modulos` en base de datos: confirmado que ni `tema` ni `cambiar_tema` existen como clave `carpeta` en el `$routeMap` dinámico de `resources/views/index.blade.php`, ni en seeders/migraciones. Cero acoplamiento con datos.
- JS/AJAX: confirmado que no existe Ziggy ni referencias hardcodeadas a `/cambiar-tema` en `resources/js` ni `public/assets/js`.

## 3. Tabla de cambios de vistas (fuente de verdad)

| # | Módulo | Vista actual | Vista nueva | Archivo(s) a editar (view call) | Línea |
|---|---|---|---|---|---|
| 1 | cambiar_contra | `cambiar_contra.index` | `sidebar.cambiar_contra.index` | `app/Http/Controllers/LoginController.php` | 73 |
| 2 | cambiar_foto | `layouts.CambiarFoto.cambiar_foto` | `sidebar.cambiar_foto.index` | `app/Http/Controllers/CambiarFoto/CambiarFotoController.php` | 21 |
| 3 | cumpleanos | `cumpleanos.index` | `sidebar.cumpleanos.index` | `app/Http/Controllers/Cumpleanos/CumpleanosController.php` | 38 |
| 4 | mis_datos | `mis_datos.index` | `sidebar.mis_datos.index` | `app/Http/Controllers/MisDatos/MisDatosController.php` | 34 |
| 5 | tema | `tema.index` | `sidebar.tema.index` | `app/Http/Controllers/Tema/TemaController.php` | 35 |

## 4. Convención de comandos: usar `git mv`, no `mv`

Todos los archivos están trackeados en git. Usar `git mv` en lugar de `mv` + `git add` porque:
- Es atómico (rename + stage en un solo comando)
- Preserva `git blame` / `git log --follow`
- Facilita que `git status` muestre el rename con similarity 100% para revisión en PR

---

## FASE 1 — Mover y renombrar vistas

```bash
mkdir -p resources/views/sidebar

git mv resources/views/cambiar_contra/index.blade.php resources/views/sidebar/cambiar_contra/index.blade.php
git mv resources/views/layouts/CambiarFoto/cambiar_foto.blade.php resources/views/sidebar/cambiar_foto/index.blade.php
git mv resources/views/cumpleanos/index.blade.php resources/views/sidebar/cumpleanos/index.blade.php
git mv resources/views/mis_datos/index.blade.php resources/views/sidebar/mis_datos/index.blade.php
git mv resources/views/tema/index.blade.php resources/views/sidebar/tema/index.blade.php

# Limpiar carpetas de origen (deben quedar vacías; si find no devuelve nada, proceder)
find resources/views/cambiar_contra resources/views/layouts/CambiarFoto resources/views/cumpleanos resources/views/mis_datos resources/views/tema -type f 2>/dev/null
rmdir resources/views/cambiar_contra resources/views/layouts/CambiarFoto resources/views/cumpleanos resources/views/mis_datos resources/views/tema 2>/dev/null

git status
```

## FASE 2 — Actualizar las 5 llamadas `view(...)`

```php
// app/Http/Controllers/LoginController.php:73
- return view('cambiar_contra.index');
+ return view('sidebar.cambiar_contra.index');

// app/Http/Controllers/CambiarFoto/CambiarFotoController.php:21
- return view('layouts.CambiarFoto.cambiar_foto', compact('user'));
+ return view('sidebar.cambiar_foto.index', compact('user'));

// app/Http/Controllers/Cumpleanos/CumpleanosController.php:38
- return view('cumpleanos.index', compact('cumpleaneros', 'nombreMes', 'colores'));
+ return view('sidebar.cumpleanos.index', compact('cumpleaneros', 'nombreMes', 'colores'));

// app/Http/Controllers/MisDatos/MisDatosController.php:34
- return view('mis_datos.index', compact('persona', 'estados'));
+ return view('sidebar.mis_datos.index', compact('persona', 'estados'));

// app/Http/Controllers/Tema/TemaController.php:35
- return view('tema.index', compact('themes', 'user'));
+ return view('sidebar.tema.index', compact('themes', 'user'));
```

## FASE 3 — Normalizar nombre de ruta `cambiar_tema.*` → `tema.*`

Cambio interno de bajo riesgo: solo afecta el identificador usado por el helper `route()`, no la URI pública (`/cambiar-tema` no cambia). Ejecutar **después** de la Fase 1, ya que uno de los 5 archivos afectados cambió de ubicación.

```php
// routes/web.php:78
- Route::get('/cambiar-tema', 'index')->name('cambiar_tema.index');
+ Route::get('/cambiar-tema', 'index')->name('tema.index');

// routes/web.php:79
- Route::patch('/cambiar-tema', 'update')->name('cambiar_tema.update');
+ Route::patch('/cambiar-tema', 'update')->name('tema.update');

// app/Http/Controllers/Tema/TemaController.php:86
- return redirect()->route('cambiar_tema.index')->with('success', 'El tema del sistema ha sido modificado con éxito.');
+ return redirect()->route('tema.index')->with('success', 'El tema del sistema ha sido modificado con éxito.');

// resources/views/layouts/sidebar.blade.php:56
- <a href="{{ route('cambiar_tema.index') }}" class="nav-link d-flex align-items-center p-3 {{ request()->routeIs('cambiar_tema.*') ? 'active-menu-item' : 'text-dark' }}">
+ <a href="{{ route('tema.index') }}" class="nav-link d-flex align-items-center p-3 {{ request()->routeIs('tema.*') ? 'active-menu-item' : 'text-dark' }}">

// resources/views/sidebar/tema/index.blade.php:83  (ruta nueva tras Fase 1)
- <form action="{{ route('cambiar_tema.update') }}" method="POST">
+ <form action="{{ route('tema.update') }}" method="POST">
```

## FASE 4 — Verificación (control de calidad obligatorio)

```bash
# No debe devolver ningún resultado — confirma que no quedan referencias a paths/rutas viejas
grep -rn "view('cambiar_contra\.\|view('cumpleanos\.\|view('mis_datos\.\|view('tema\.index\|view('layouts\.CambiarFoto" app/ resources/ routes/
grep -rn "cambiar_tema" app/ resources/ routes/

# Debe devolver vacío — confirma que las carpetas de origen ya no existen
find resources/views/cambiar_contra resources/views/layouts/CambiarFoto resources/views/cumpleanos resources/views/mis_datos resources/views/tema 2>&1

# Debe listar los 5 archivos en su nueva ubicación
find resources/views/sidebar -type f
```

## FASE 5 — Pruebas manuales (checklist funcional)

- [ ] `/cambiar-contrasena` carga y el formulario de cambio de contraseña funciona
- [ ] `/cambiar-foto` carga, formulario de subida de imagen funciona, redirect post-submit correcto
- [ ] `/cumpleanos` carga con listado de cumpleañeros
- [ ] `/mis-datos` carga y `route('mis_datos.update')` funciona
- [ ] `/cambiar-tema` carga y `route('tema.update')` (antes `cambiar_tema.update`) funciona correctamente
- [ ] Sidebar resalta el ítem activo correctamente en los 5 módulos (especialmente `tema`, por el cambio de `routeIs('cambiar_tema.*')` a `routeIs('tema.*')`)
- [ ] Header (`resources/views/layouts/header.blade.php`) — enlace a `mis_datos.index` sigue funcionando

## FASE 6 — Actualizar `PROJECT_MEMORY.md`

Agregar entrada con fecha actual, agente ejecutor, descripción del cambio (reorganización de 5 vistas a `sidebar/`, normalización de carpeta `CambiarFoto` → `cambiar_foto`, normalización de nombre de ruta `cambiar_tema` → `tema`), archivos afectados (tabla de la sección 3 + los archivos de la Fase 3), estado "Completado".
