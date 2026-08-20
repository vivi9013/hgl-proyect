# MEMORIA DEL PROYECTO (PROJECT MEMORY)
## 1. Información General del Proyecto
*   **Nombre del Proyecto:** HGL Proyecto (Sistema de Gestión Hospitalaria - Estadías)
*   **Tecnologías Principales:**
    *   **Backend:** Laravel (PHP)
    *   **Frontend:** Bootstrap / Tailwind CSS (aunque no se usa y se usa mas css) / Vanilla JS (desacoplado y modular)
    *   **Base de Datos:** mysql 
    *   **Bundler:** Vite
*   **Arquitectura de Navegación:** Basada en perfiles (`id_perfil`) que determinan los módulos y categorías dinámicas cargadas en la vista principal (`IndexController` / `/inicio`).
---
## 2. Normas de Trabajo y Convenciones (Cómo Desarrollar Aquí)
Para mantener la coherencia con el trabajo del equipo (3 personas) y evitar inconsistencias en bases de datos compartidas:

*   **Filosofía de Modernización (Sistema 1 -> Sistema 2):**
    *   **Fuente de Verdad (Plano Funcional):** El *Sistema 1 (Legacy en PHP nativo)* es la referencia absoluta de los requerimientos y el comportamiento esperado para cada módulo (como Login, Dashboard, Citas, etc.).
    *   **Evolución a Buenas Prácticas:** No se clona el código antiguo tal cual aunque en la mayoria de casos si nos gustaria replicarlos ya sea copiandolo si realmente funciona bien y tiene buena practica actualemnte o de igual forma si existe. Se extrae la lógica funcional del sistema viejo y se reestructura usando las mejores prácticas de programación moderna y las capacidades nativas de Laravel (Eloquent ORM, controladores RESTful, inyección de dependencias, middlewares de seguridad, migraciones ordenadas, modelos claros y precisos).
    *   **Arquitectura Desacoplada:** Se eliminan los scripts PHP inline antiguos y las consultas SQL crudas dispersas, reemplazándolos por controladores robustos y vistas Blade limpias que operan de manera dinámica (mediante AJAX y APIs internas).
*   **Desacoplamiento de Código:**
    *   **Evita JS inline en archivos Blade:** Todo el código JavaScript complejo debe estar modularizado en archivos `.js` externos dentro de `resources/js/` o carpetas dedicadas y compilados con Vite.
    *   **Vistas Limpias:** Las vistas Blade solo deben contener la estructura HTML semántica y directivas Blade necesarias.
*   **Controladores y Modelos:**
    *   Usa controladores delgados (*Thin Controllers*). La lógica de negocio debe delegarse a modelos, servicios o helpers.
    *   **Uso de Traits:** Centraliza la lógica repetitiva o compatible con el sistema legacy (ej. `Sanitizable`) en Traits para mantener el código DRY y facilitar el mantenimiento global.
    *   Mantén estrictas las convenciones de nombres de Laravel (CamelCase para controladores y modelos, snake_case para base de datos).
*   **Integridad de la Base de Datos:**
    *   Toda modificación a la base de datos debe ser gestionada mediante migraciones de Laravel. **No hagas modificaciones directas** en Mysql que no estén documentadas en código.
    *   Al crear nuevas tablas o modificar columnas mediante migraciones, **asegúrate de declarar las columnas opcionales con `->nullable()`** para que almacenen `NULL` por defecto en lugar de cadenas vacías (`''`).
*   **Seguridad y Sesiones:**
    * Las rutas protegidas deben usar los middlewares adecuados (ej. `auth` y `EvitarRetrocesoMiddleware` para seguridad de historial).
    * Sin fugas de seguridad, nada que el usuario pueda apretar F12 y modificar o inyectar cosas para enviar al backend.
  
