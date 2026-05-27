# MEMORIA DEL PROYECTO (PROJECT MEMORY)

> [!IMPORTANT]
> **INSTRUCCIÓN PARA LA IA / ASISTENTE:** Este es el archivo de memoria viva de este proyecto. Léelo completo en tu primer análisis para entender el contexto, las reglas de desarrollo, el estado del proyecto y el historial de cambios. Al terminar una tarea, actualiza este archivo con los cambios realizados para que tu sucesor tenga contexto inmediato.

---

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
    *   Mantén estrictas las convenciones de nombres de Laravel (CamelCase para controladores y modelos, snake_case para base de datos).
*   **Integridad de la Base de Datos:**
    *   Toda modificación a la base de datos debe ser gestionada mediante migraciones de Laravel. **No hagas modificaciones directas** en Mysql que no estén documentadas en código.
*   **Seguridad y Sesiones:**
    *   Las rutas protegidas deben usar los middlewares adecuados (ej. `auth` y `EvitarRetrocesoMiddleware` para seguridad de historial).
    *   **Eficiencia de Contexto:** No escanees ni abras archivos de todo el proyecto de forma automática. Si necesitas conocer la estructura de una tabla o de un modelo previo, pídemelo en el chat o solicita que yo abra el archivo en el editor.

##  2.5 Configuración de Skills del Agente (Control de Cuota)
El proyecto cuenta con el framework de automatización en la carpeta `/skills/`. Para proteger la cuota del plan gratuito, se aplican las siguientes restricciones:

* **Prohibición de Sub-Agentes:** No utilices los flujos de `subagent-driven-development` ni `dispatching-parallel-agents`. Toda tarea debe ser resuelta de forma lineal por ti (el agente principal) en el chat.
* **Modo de Trabajo Permitido:** Utiliza únicamente `writing-plans` (escribir el plan en el chat) y `systematic-debugging` si hay un error, pero hazlo de forma teórica en el chat antes de tocar archivos.
* **Ejecución Manual:** Ignora la carpeta `/skills/` para ejecuciones automáticas en segundo plano. Yo gobernaré el flujo de trabajo basándome en tus respuestas del chat.

## 3. Estado Actual del Proyecto y Roadmap

### Módulos Implementados 100%
- [x] **Autenticación:** Login seguro con redirección inteligente y control de historial (`EvitarRetrocesoMiddleware`).
- [x] **Panel de Inicio (/inicio):** Carga dinámica de módulos y categorías dependientes del perfil asignado (`id_perfil`).
- [x] **Cambio de Contraseña:** Flujo seguro para la actualización de password de usuarios autenticados.
- [x] **Buscador de Archivos (mBuscaArchivos):** Buscador de formatos asíncrono con filtrado AJAX, permisos dinámicos y reportes listos para impresión.

### En Desarrollo Actual
- [ ] **Módulo de Citas / Pacientes:** Desarrollo de CRUD dinámico (usando modales AJAX y JS desacoplado).

### Siguientes Pasos
1.  Completar el flujo dinámico de CRUD en base de datos mysql para citas.
2.  Estandarizar las vistas del resto de los módulos con Bootstrap e iconos dinámicos.

---

## 4. Historial de Cambios y Decisiones (Living Changelog)
*(Por favor, registra aquí cada cambio significativo indicando la fecha, el autor o número de chat de IA y una breve descripción de los archivos afectados).*

| Fecha (AAAA-MM-DD) | Autor / Rol | Cambios Realizados y Motivación | Archivos Afectados | Estado |
| :--- | :--- | :--- | :--- | :--- |
| **2026-05-10** | Antigravity AI | Instalación de las skills de Superpowers para guiar el flujo de desarrollo de IA. | `/skills/`, `GEMINI.md`, `CLAUDE.md`, etc. | Completado |
| **2026-05-25** | Antigravity AI | Creación e inicialización del archivo `PROJECT_MEMORY.md` para sincronización de contexto inter-máquinas y equipos. | `PROJECT_MEMORY.md`, `GEMINI.md` | Completado |
| **2026-05-25** | Antigravity AI | Creación del enlace simbólico al Sistema Legacy 1 y configuración local de `.gitignore`. | `legacy-system-1`, `.gitignore` | Completado |
| **2026-05-25** | Antigravity AI | Integración de las bases y normas de la Filosofía de Modernización (Sistema 1 -> Sistema 2). | `PROJECT_MEMORY.md` | Completado |
| **2026-05-26** | Antigravity AI | Migración del módulo "Buscador de Archivos" (mBuscaArchivos) a Laravel con arquitectura modular, AJAX y descargas seguras. | `app/Models/BuscadorArchivos/*`, `app/Http/Controllers/BuscadorArchivos/*`, `resources/views/buscador_archivos/*`, `resources/js/buscador_archivos/*`, `resources/css/buscador_archivos/*`, `routes/web.php`, `vite.config.js` | Completado |
| **2026-05-27** | Antigravity AI | Resolución de colisión de clases en CambiarFotoController y modernización interactiva del módulo de fotografía (Bootstrap 5 y cabecera dinámica). | `app/Http/Controllers/CambiarFoto/*`, `app/Models/User.php`, `resources/views/layouts/header.blade.php`, `resources/views/layouts/CambiarFoto/*` | Completado |

---

> [!TIP]
> **Consejo para el Equipo:** Al iniciar una conversación en cualquier computadora, pide a la IA: *"Lee el archivo `PROJECT_MEMORY.md` para entender el estado del desarrollo y qué se hizo en la última sesión"*.
