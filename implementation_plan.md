# Implementation Plan - Migration of "Categoría de Módulos" Module

Migrate the legacy `Categoria de Módulos` module (`mCategoModulos`) into Laravel using clean architecture, dynamic features (AJAX pagination, search, validation, and status toggles), and premium interactive reports and charts (using Chart.js).

## User Review Required

> [!IMPORTANT]
> **Database Mapping**: The migration uses the existing model `App\Models\CategoriaModulo` mapping to table `categoria_modulo` with primary key `id_CategoriaModulo`.
> **Chart.js Integration**: The two legacy charts (bar/pie) represent the count of active modules under each category. We will implement them cleanly using modern **Chart.js** via the Vite bundler.
> **Blade Refactoring**: The file `resources/views/admin_sistema/categoria_modulos/analitica/grafica_barras.blade.php` currently contains Javascript code by mistake. We will overwrite it with a proper Blade structure and move the JS logic to a dedicated file `resources/js/categoria_modulos/grafica_barras.js`.

---

## Proposed Changes

### Backend (Routing and Controllers)

#### [NEW] [CategoriaModulosController.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/app/Http/Controllers/CategoriaModulos/CategoriaModulosController.php)
Create a RESTful controller to handle all actions:
- `index`: Paginated listing supporting text search (`buscar`) and AJAX rendering.
- `guardar`: Add a new category with server-side unique validation (case-insensitive) and automatic metadata registration (date, time, user ID).
- `editar`: Display the edit view.
- `actualizar`: Update the category name, project, and panel status.
- `cambiarStatus` [AJAX/PATCH]: Toggle active/inactive status.
- `cambiarColapsar` [AJAX/PATCH]: Toggle panel initial state (colapsado: si/no).
- `verificar` [AJAX/GET]: Perform real-time validation of category name availability.
- `reportes`: Display the report options view.
- `imprimir`: Render a print-friendly premium HTML report of the categories.
- `graficas`: Display the charts selector.
- `graficaPie` and `graficaBar`: Fetch and calculate the count of modules per category to inject into interactive Chart.js views.

#### [MODIFY] [web.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/routes/web.php)
Define the routing group `/categoria-modulos` under the `auth` middleware group:
```php
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
    Route::get('/graficas/pie', 'graficaPie')->name('graficas.pie');
    Route::get('/graficas/bar', 'graficaBar')->name('graficas.bar');
});
```

---

### Vistas Blade

#### [MODIFY] [index.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/categoria_modulos/index.blade.php)
- Correct the sub-template path from `admin_formatos.categoria_modulos.partials.tabla` to `admin_sistema.categoria_modulos.partials.tabla`.
- Inject a hidden template for AJAX paginator.
- Add an alert wrapper to display success messages utilizing SweetAlert2.

#### [MODIFY] [tabla.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/categoria_modulos/partials/tabla.blade.php)
- Implement the invisible `"datosPaginacionTransporte"` table row to transmit Laravel pagination data to JavaScript.
- Adjust status and colapsar toggle buttons to include `data-` attributes (`data-url`, `data-activo`, `data-colapsado`) for smooth AJAX interactions.

#### [MODIFY] [editar.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/categoria_modulos/editar.blade.php)
- Ensure form maps correctly to route `categoria_modulos.update` using `@method('PATCH')`.

#### [MODIFY] [grafica_barras.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/categoria_modulos/analitica/grafica_barras.blade.php)
- Overwrite this file with the correct HTML Blade template extending `layouts.app` and defining a Canvas for the Chart.js bar chart.

#### [NEW] [reporte_impresion.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/categoria_modulos/reportes/reporte_impresion.blade.php)
- Create a print-friendly premium layout to display all categories, triggering a automatic standard print dialog (`window.print()`).

---

### Frontend Assets (CSS and JS)

#### [NEW] Assets inside `resources/js/categoria_modulos/`
- **`categoria.js`**: AJAX pagination, debounced search, real-time availability check, SweetAlert2-confirmed AJAX status toggle, and initial panel toggle.
- **`categoria_edit.js`**: Edit form validation.
- **`categoria_graficas.js`**: Navigation and styling tweaks.
- **`grafica_pie.js`**: Initialize Chart.js with dynamic datasets to draw the pie chart.
- **`grafica_barras.js`**: Initialize Chart.js to draw the bar chart.
- **`categoria_reportes.js`**: Interactive report links.

#### [NEW] Assets inside `resources/css/categoria_modulos/`
- Create `categoria.css`, `categoria_edit.css`, `categoria_graficas.css`, and `categoria_reportes.css` containing premium layout, typography, borders, shadows, and interactive hover animations.

#### [MODIFY] [vite.config.js](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/vite.config.js)
Register all new CSS and JS entrypoints in the input list for Vite compilation.

---

## Verification Plan

### Manual Verification
- **List & Search**: Load `/categoria-modulos`, write text in search input, and verify results update asynchronously with a 300ms debounce.
- **AJAX Paginator**: Navigate using pagination buttons and verify rows update without full page reloads.
- **Toggles**: Toggle the "Activo" and "Panel Abierto" status icons and verify status toggles smoothly with AJAX and SweetAlert2 confirmation.
- **Availability Check**: Type a category name in the alta modal and verify validation message displays "Categoría disponible" or "Esta categoría ya existe" based on database existence.
- **CRUD Operations**: Create and update categories and verify data updates accurately in the MySQL database.
- **Reports**: Open reports section, click print, and verify a clean HTML report appears with the browser print dialog.
- **Charts**: View bar and pie charts, hovering over slices/bars to see accurate counts.
- **Asset Compilation**: Run `pnpm build` (or verify active `pnpm dev` builds successfully) without compilation errors.
