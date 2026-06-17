# MEMORIA DEL PROYECTO (PROJECT CONTEXT & CONVENTIONS)
> [!IMPORTANT]
> **INSTRUCCIÓN PARA CLAUDE CODE:** Este es el archivo de contexto vivo del sistema. Léelo completo para comprender las reglas de diseño, decisiones arquitectónicas y convenciones de código antes de modificar el repositorio. Mantén la consistencia absoluta con los módulos ya implementados.
---
## 1. Información General y Arquitectura
* **Nombre del Proyecto:** HGL Proyecto (Sistema de Gestión Hospitalaria - Estadías)
* **Tecnologías:** PHP 8.x / Laravel 11 / MySQL / Vite.
* **Diseño Visual:** Estilo clásico, corporativo y plano (sin tarjetas con sombras pronunciadas ni bordes redondeados exagerados). Íconos unificados a color negro, botones de acción en azul con borde negro, y botones de cancelar/regresar en blanco o gris. Tablas modernas con bordes limpios y paginación negra sencilla.
* **Estrategia de Frontend:** Uso prioritario de CSS clásico/estructurado y Vanilla JS (JavaScript nativo desacoplado mediante peticiones AJAX/Fetch). Se evitan librerías de componentes pesadas.
* **Acceso y Navegación:** Sistema dinámico basado en perfiles (`id_perfil`) mapeados en la columna `carpeta` de la tabla `modulos`. Determinan las tarjetas disponibles en el Panel de Control (`/inicio`).
---
## 2. Reglas de Oro para el Desarrollo (Cómo Programar Aquí)

### A. Filosofía de Modernización (Legacy -> Laravel)
* **Plano Funcional:** El sistema legacy en PHP nativo es la referencia absoluta de lógica de negocio y comportamiento esperado.
* **Refactorización Limpia:** No se copia código spaguetti antiguo. Se extrae el requerimiento y se implementa usando buenas prácticas de Laravel: Eloquent ORM, transacciones de base de datos (`DB::transaction`), controladores RESTful e inyección de dependencias.
* **Consultas a la Base de Datos:** Quedan estrictamente **prohibidas** las consultas SQL crudas (`DB::raw` o queries nativas) dispersas en vistas o controladores, a menos que sea un requerimiento de optimización extremo por volumen de datos. Usa Eloquent o el Query Builder ordenado.

### B. Desacoplamiento de Código
* **Cero JS/CSS Inline:** Queda prohibido meter bloques `<script>` o estilos inline dentro de las vistas Blade. Todo comportamiento dinámico se programa en archivos `.js` y `.css` independientes dentro de `resources/` y se compila mediante **Vite**.
* **Vistas Limpias:** Los archivos Blade solo deben contener maquetado HTML semántico, directivas de Blade (`@extends`, `@section`, `@if`, `@foreach`) y el llamado a los assets correspondientes.

### C. Controladores, Modelos y Helpers
* **Thin Controllers (Controladores Delgados):** Los controladores solo manejan el flujo de la petición, validación y respuesta. La lógica compleja va a modelos o clases de servicio.
* **Trait `Sanitizable`:** Usa este Trait existente para limpiar inputs provenientes de la base de datos legacy o peticiones de usuario.
* **Modelos Fuertes:** Las llaves primarias legacy personalizadas (ej. `id_area_almacen` o `id_area_surtimiento`) y la desactivación de `timestamps` automáticos deben declararse explícitamente en los modelos correspondientes.

### D. Reportes e Impresión
* **`layouts/reporte_base.blade.php`:** Todos los reportes imprimibles del sistema deben extender obligatoriamente de esta plantilla base para mantener la uniformidad de los logos duales (`encabezado.jpg`), tipografías y el estilo de impresión gris (`shadeCol`).

### E. Seguridad Estricta
* Las rutas protegidas deben implementar `auth` y el middleware `EvitarRetrocesoMiddleware`.
* Toda acción que altere datos sensibles debe mitigar vulnerabilidades IDOR (ej. pasar IDs por parámetros de ruta validados en lugar de inputs ocultos `<input type="hidden">` modificables por F12).
---
## 3. Estado del Repositorio y Módulos Migrados (100%)

Para evitar el escaneo innecesario de archivos y ahorrar tokens en la API, utiliza esta lista como mapa de lo que ya existe y funciona bajo el estándar del proyecto:

* **Autenticación & Seguridad:** Login seguro, control de historial y flujo de cambio de contraseña (`LoginController`, `EvitarRetrocesoMiddleware`).
* **Dashboard (`/inicio`):** Carga e indexación dinámica de módulos según perfil.
* **Buscador de Archivos (`mBuscaArchivos`):** Búsqueda asíncrona con debounce de 300ms, filtrado AJAX y closures de aislamiento de información.
* **Carga de Archivos (`mCargaArchivos`):** Integración con el Facade `Storage`, Accessors en el modelo para verificar existencia física de PDFs, analíticas interactivas con Chart.js y reportes estructurados.
* **Módulos de Inventario:**
    * `mAreasAlmacen` y `mAreaSurtimiento`: CRUDs interactivos con validación asíncrona de duplicados y paginación fija de 10 registros.
    * `mBajasInsumos`: Gestión de stock en tiempo real con transacciones de base de datos, autocompletado AJAX, límites de impresión a 500 registros para evitar fatiga de memoria, y reversión automática de inventario al cancelar.
* **Administración del Sistema:**
    * `mCategoModulos`: Soporte de ordenamiento manual mediante algoritmos de shifting en BD y compactación secuencial (1-N).
    * `mModulos` / `mPerfiles` / `mPersonas`: CRUDs administrativos avanzados con búsquedas reactivas (RFC/CURP/Email), combos dinámicos de estados/municipios, y tableros analíticos empotrados usando Chart.js (gráficas de barras y dona por género).
---
## 4. Control de Ejecución (Restricciones del Agente)
* **Modo Lineal:** No se permite el uso de sub-agentes en paralelo ni herramientas automáticas en segundo plano. Trabaja directamente en la sesión del chat de la terminal.
* **Análisis Previo:** Ante bugs o refactorizaciones, propón el plan de depuración técnica de manera teórica en el chat antes de realizar modificaciones sobre los archivos físicos.