# Migración del Módulo Legacy "modulos"

Migrar de manera quirúrgica y segura el módulo administrativo de gestión de módulos (`modulos`) desde PHP legacy a una implementación nativa de Laravel (Sistema 2). El módulo permite crear, listar, editar módulos, configurar sus relaciones de asignación con proyectos (`modulo_proyecto`) y perfiles (`modulo_perfil`), además de generar reportes de impresión y visualizaciones estadísticas interactivas.

## User Review Required

> [!IMPORTANT]
> **Modernización de la Asignación de Proyectos y Perfiles:**
> En el código legacy, la asignación a proyectos y perfiles utilizaba una acción AJAX/POST que invertía (toggle) individualmente la relación de cada checkbox enviado. Para hacerlo robusto y libre de condiciones de carrera o inconsistencias, pre-cargaremos en las vistas los elementos ya asignados (casillas marcadas) y utilizaremos el método `sync()` de Eloquent al guardar, actualizando la auditoría (`fecha`, `hora`, `usuario`). Esto previene vulnerabilidades de IDOR y simplifica la experiencia de usuario.

> [!TIP]
> **Consolidación de Gráficas con Chart.js:**
> Legacy utilizaba 6 archivos PHP individuales con scripts Morris.js redundantes para renderizar cada tipo de gráfica. Unificaremos las estadísticas en una única pantalla de Gráficas (`/modulos/graficas`) que renderice dinámicamente gráficos de barras y de dona interactivos para las 3 agrupaciones (Módulos por Categoría, por Proyecto y por Perfil) utilizando Chart.js, manteniendo consistencia visual con `categoria_modulos`.

---

## Open Questions

*Ninguna. La estructura de base de datos se mantiene compatible para no interrumpir el flujo de datos legacy.*

---

## Proposed Changes

### 1. Modelos de Base de Datos

Definir el modelo para proyectos y actualizar relaciones de auditoría.

#### [NEW] [Proyecto.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/app/Models/Proyecto.php)
- Crear el modelo Eloquent `App\Models\Proyecto` mapeado a la tabla legacy `proyectos`.
- Clave primaria: `id_proyecto`.
- Sin timestamps automáticos.

#### [MODIFY] [Modulo.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/app/Models/Modulo.php)
- Añadir la relación de muchos a muchos `proyectos()` mapeando a la tabla pivote `modulo_proyecto`.
- Soportar las columnas de pivote `fecha`, `hora` y `usuario` (ID del usuario de auditoría).

---

### 2. Rutas y Controladores

Configurar el controlador del módulo y registrar todas las rutas administrativas protegidas.

#### [NEW] [ModuloController.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/app/Http/Controllers/Modulos/ModuloController.php)
- Crear controlador RESTful con los métodos:
  - `index`: Listado con buscador AJAX paginado (10 items) y formulario de alta en la misma pantalla.
  - `guardar`: Validación estricta y almacenamiento del módulo.
  - `editar`: Vista de edición individual de metadatos.
  - `actualizar`: Procesamiento y validación de la actualización.
  - `cambiarStatus`: Alternar el estado activo/inactivo vía AJAX (PATCH).
  - `proyectos` / `actualizarProyectos`: Interfaz y guardado seguro (`sync`) de relaciones con proyectos.
  - `perfiles` / `actualizarPerfiles`: Interfaz y guardado seguro (`sync`) de relaciones con perfiles.
  - `reportes`: Panel de reportes disponibles.
  - `imprimir`: Generación de reporte HTML utilizando `layouts.reporte_base`.
  - `graficas`: Estadísticas unificadas agrupando por categorías, proyectos y perfiles.
  - `categoriaPreview`: Endpoint AJAX para renderizar acordeón de previsualización (reemplaza `moduloCatego.php`).

#### [MODIFY] [web.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/routes/web.php)
- Registrar el grupo de rutas con prefijo `/modulos` y alias `modulos.*` protegido por middleware `auth`.

---

### 3. Activos CSS y JS (Compilación Vite)

Crear estilos y lógica JavaScript modularizada para las interacciones dinámicas de la UI.

#### [NEW] [modulos.css](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/css/modulos/modulos.css)
- Reglas CSS personalizadas para tablas administrativas, previsualizaciones de tarjetas `small-box` e interfaces de asignación.

#### [NEW] [modulos.js](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/js/modulos/modulos.js)
- Motor AJAX de paginación asíncrona.
- Control de previsualización en tiempo real del componente `small-box` (color, icono y nombre).
- Control AJAX para cambiar estatus (activo/inactivo) usando SweetAlert2.
- Lógica de asignación rápida para marcar/desmarcar checkboxes de proyectos y perfiles de forma masiva.

#### [MODIFY] [vite.config.js](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/vite.config.js)
- Agregar `resources/css/modulos/modulos.css` y `resources/js/modulos/modulos.js` a las entradas de Vite.

---

### 4. Vistas Blade del Frontend

Generar las interfaces responsivas adaptadas al sistema moderno de layouts y Bootstrap.

#### [NEW] [index.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/index.blade.php)
- Vista unificada de listado y formulario de alta.
- Componente de previsualización `small-box` dinámico.
- Acordeón dinámico inferior de preview de categorías alimentado por AJAX.

#### [NEW] [tabla.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/partials/tabla.blade.php)
- Fragmento HTML de la tabla con los registros de los módulos, contadores de relaciones y estado.

#### [NEW] [editar.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/editar.blade.php)
- Formulario de edición con preview inmediato.

#### [NEW] [proyectos.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/proyectos.blade.php)
- Interfaz de asignación masiva de proyectos con casillas de marcar todo / desmarcar todo.

#### [NEW] [perfiles.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/perfiles.blade.php)
- Interfaz de asignación masiva de perfiles con casillas de marcar todo / desmarcar todo.

#### [NEW] [reportes.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/reportes.blade.php)
- Panel de acceso a listados imprimibles.

#### [NEW] [reporte_impresion.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/reportes/reporte_impresion.blade.php)
- Plantilla imprimible que hereda de `layouts.reporte_base` mostrando el listado general de módulos con su creador y descripción.

#### [NEW] [graficas.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/analitica/graficas.blade.php)
- Panel de gráficas consolidado con 3 secciones duales (Dona y Barra) integradas con Chart.js.

#### [MODIFY] [index.blade.php](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/index.blade.php)
- Asegurar que la redirección dinámica apunte al nuevo path de Laravel (`/modulos`) si el administrador accede a la tarjeta del módulo.

#### [DELETE] [mModulos/](file:///Users/RogueFan/Documents/hospital-estadias/hgl-proyect/resources/views/admin_sistema/modulos/mModulos)
- Eliminar la carpeta legacy original con todos sus scripts PHP obsoletos tras verificar el correcto funcionamiento de la migración.

---

## Verification Plan

### Automated Tests
- Ejecutar `npm run build` o verificar compilación Vite en tiempo real (`pnpm dev` en background) para validar que no haya errores de compilación de assets.

### Manual Verification
1. **Acceso y Navegación:**
   - Iniciar sesión y navegar a `/modulos` desde la tarjeta del panel de control.
2. **Altas y Validaciones:**
   - Intentar registrar un módulo con campos vacíos.
   - Completar campos válidos, verificar que la vista previa del `small-box` se actualiza en tiempo real de forma dinámica y guardar.
3. **Listado, Búsqueda y Paginación:**
   - Buscar un módulo por nombre en la caja de búsqueda global y validar resultados.
   - Paginación asíncrona de 10 registros.
4. **Estatus:**
   - Hacer clic en el estatus para alternar entre activo/inactivo, confirmando con el SweetAlert2.
5. **Edición:**
   - Modificar un registro de módulo existente y validar actualización en base de datos.
6. **Asignaciones de Proyectos / Perfiles:**
   - Entrar al módulo de asignación de proyectos. Marcar/desmarcar algunos ítems, guardar, volver a entrar y verificar que los estados persistieron correctamente. Repetir para perfiles.
7. **Reportes:**
   - Clic en imprimir reporte, validar que se abra ventana nueva con el formato premium unificado y el banner de logos institucional.
8. **Gráficas:**
   - Clic en Ver Gráficas y validar renderización interactiva animada de las estadísticas usando Chart.js.
