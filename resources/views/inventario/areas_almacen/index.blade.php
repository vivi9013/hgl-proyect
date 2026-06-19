{{-- @extends es la directiva de Blade para heredar una plantilla base de diseño. --}}
{{-- Carga el archivo layouts/app.blade.php como marco contenedor de la página actual. --}}
{{-- En este caso, provee la barra de navegación lateral y superior para el listado de áreas. --}}
@extends('layouts.app')

{{-- @section define un bloque de texto que se inyectará en la plantilla padre. --}}
{{-- Envía una cadena estática al marcador @yield('title') en la cabecera HTML general. --}}
{{-- Establece el título "Áreas de Almacén" en la pestaña activa del navegador. --}}
@section('title', 'Áreas de Almacén')

{{-- @section con cierre @endsection delimita un bloque dinámico para el contenedor de la aplicación. --}}
{{-- Captura todos los contenedores, listados y modales para inyectarlos en @yield('content'). --}}
{{-- Organiza la sección principal de control y visualización de áreas de almacén. --}}
@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-archive text-primary me-2"></i>Áreas de Almacén
            </h1>
            <p class="text-muted mb-0">Registro, edición y control de las áreas de almacén de medicamentos</p>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas SweetAlert2 ── --}}
    {{-- @if evalúa una condición booleana o la existencia de una variable/valor. --}}
    {{-- session('exitog') verifica si existe un mensaje de éxito global en la sesión flash. --}}
    {{-- Crea un div marcador de posición que el JS del frontend detectará para disparar un SweetAlert de éxito de creación. --}}
    @if(session('exitog'))
        <div id="alertaExitog"></div>
    @endif
    {{-- session('exito') recupera el mensaje de éxito genérico desde la sesión flash del controlador. --}}
    {{-- Provee el ancla visual para levantar notificaciones emergentes tras operaciones satisfactorias. --}}
    @if(session('exito'))
        <div id="alertaExito"></div>
    @endif

    {{-- ── Buscador y Acciones ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-8">
            {{-- route('areas_almacen.index') genera la ruta URL para recargar el listado actual de áreas. --}}
            {{-- El método GET pasa los parámetros de búsqueda mediante Query Strings en la URL. --}}
            {{-- Permite refrescar la tabla aplicando los filtros de búsqueda especificados por el usuario. --}}
            <form method="GET" action="{{ route('areas_almacen.index') }}" id="formBuscar">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-search me-1"></i>Buscar:</label>
                        <div class="input-group" style="border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                            {{-- value="{{ $buscar }}" inyecta directamente la variable de búsqueda recibida del controlador. --}}
                            {{-- Muestra en el cuadro de texto la palabra clave de búsqueda que está activa actualmente. --}}
                            {{-- Mantiene la persistencia visual del filtro utilizado por el usuario. --}}
                            <input
                                type="text"
                                name="buscar"
                                id="inputBuscar"
                                class="form-control bg-light border-0"
                                placeholder="Buscar por nombre de área..."
                                value="{{ $buscar }}"
                                autocomplete="off"
                                style="font-size: 0.9rem; box-shadow: none;"
                            >
                            {{-- @if evalúa la existencia de una búsqueda activa para mostrar condicionalmente un botón de limpieza. --}}
                            {{-- Si $buscar no está vacío, renderiza el enlace que recarga el índice limpio de parámetros. --}}
                            {{-- Proporciona una forma rápida de deshacer el filtro y volver a listar todo. --}}
                            @if($buscar)
                                <a href="{{ route('areas_almacen.index') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar Filtros">
                                    <i class="fa fa-times text-danger"></i>
                                </a>
                            @endif
                            <button class="input-group-text bg-light border-0" type="submit" title="Buscar">
                                <i class="fa fa-search text-dark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12 col-md-4 text-md-end d-flex justify-content-md-end align-items-center mt-2 mt-md-0" style="gap: 0.75rem;">
            {{-- request()->query() obtiene todos los parámetros de consulta actuales del Request. --}}
            {{-- Pasa este array asociativo de parámetros a la ruta areas_almacen.imprimir para conservar filtros de búsqueda en el reporte. --}}
            {{-- Permite generar un PDF o vista de impresión que solo contenga los registros filtrados en pantalla. --}}
            <a href="{{ route('areas_almacen.imprimir', request()->query()) }}"
               target="_blank"
               class="btn btn-outline-secondary rounded-pill shadow-sm"
               id="btnImprimirReporte"
               style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-print me-1 text-dark"></i> Imprimir Reporte
            </a>
            {{-- data-bs-toggle y data-bs-target son atributos de datos nativos de Bootstrap 5 para modales. --}}
            {{-- Configuran el botón para disparar de manera declarativa la visualización del modal sin escribir código JS adicional. --}}
            {{-- Abre la ventana emergente para registrar una nueva área de almacén. --}}
            <button type="button"
                    class="btn btn-primary rounded-pill shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalRegistrarArea"
                    style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-plus-circle me-1"></i>Registrar Área
            </button>
        </div>
    </div>

    {{-- ── Tabla de Áreas ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Lista de áreas de almacén
                        </h5>
                        {{-- $areas->total() obtiene el número total de registros coincidentes en toda la base de datos (con o sin paginación). --}}
                        {{-- Retorna un entero y lo imprime dinámicamente en el badge. --}}
                        {{-- Le indica al usuario cuántas áreas cumplen con los criterios de búsqueda actuales. --}}
                        <span class="rounded-pill px-3 py-1 fw-bold align-middle d-inline-block" style="background-color: #e9ecef; font-size: 0.78rem; letter-spacing: 0.03em;">
                            <span style="color: #000000;">{{ $areas->total() }}</span> <span style="color: #495057;">{{ $areas->total() === 1 ? 'Área' : 'Áreas' }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table id="tablaAreas" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">#</th>
                                    <th class="text-center" style="width: 100px;">Editar</th>
                                    <th>Nombre del Área</th>
                                    <th>Fecha Registro</th>
                                    <th style="width: 120px;">Hora</th>
                                    <th class="text-center pe-4" style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @forelse es una estructura de control de Blade que combina un bucle @foreach con un fallback @empty. --}}
                                {{-- Itera sobre la colección de áreas y, si está vacía, salta inmediatamente a la sección @empty. --}}
                                {{-- Dibuja un renglón por cada área en pantalla, o un mensaje de error/advertencia si no hay registros. --}}
                                @forelse($areas as $index => $area)
                                    {{-- El operador ternario ?: evalúa si el área está inactiva ($area->activo == 0). --}}
                                    {{-- Agrega clases CSS para opacar y poner en cursiva el texto si el registro está desactivado. --}}
                                    {{-- Permite distinguir de un vistazo las áreas activas de las inactivas. --}}
                                    <tr class="{{ $area->activo == 0 ? 'text-muted fst-italic' : '' }}">
                                        {{-- Calcula el consecutivo global del registro en la paginación. --}}
                                        {{-- Toma la página actual, le resta 1, multiplica por registros por página y suma la iteración actual ($loop->iteration). --}}
                                        {{-- Garantiza que el número correlativo sea correcto a lo largo de todas las páginas de la tabla. --}}
                                        <td class="ps-4 fw-bold">{{ ($areas->currentPage() - 1) * $areas->perPage() + $loop->iteration }}</td>
                                        <td class="text-center">
                                            {{-- route() genera el link a la pantalla de edición pasándole la llave primaria del área. --}}
                                            {{-- Construye el link seguro que redirige a la acción de edición en el controlador. --}}
                                            <a href="{{ route('areas_almacen.edit', $area->id_area_almacen) }}"
                                               class="btn btn-sm btn-outline-primary rounded-circle"
                                               title="Editar"
                                               style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $area->nombre }}</td>
                                        {{-- Carbon::parse() parsea un string de fecha en una instancia de Carbon para poder formatearla. --}}
                                        {{-- Convierte la fecha del formato de base de datos a formato legible en español (d/m/Y). --}}
                                        {{-- Muestra la fecha de creación de manera amigable para el usuario. --}}
                                        <td>{{ $area->fecha_registro ? \Carbon\Carbon::parse($area->fecha_registro)->format('d/m/Y') : '' }}</td>
                                        <td>{{ $area->hora_registro }}</td>
                                        <td class="text-center pe-4">
                                            {{-- Los atributos data-url, data-nombre y data-activo almacenan metadatos del registro. --}}
                                            {{-- Son leídos por el script JavaScript para disparar el flujo de SweetAlert de confirmación AJAX. --}}
                                            {{-- Proporcionan una interfaz interactiva y asíncrona para cambiar de estado al registro sin recargar. --}}
                                            @if($area->activo == 1)
                                                <a href="#"
                                                   class="btn-toggle-status badge-status-activo"
                                                   data-url="{{ route('areas_almacen.status', $area->id_area_almacen) }}"
                                                   data-nombre="{{ $area->nombre }}"
                                                   data-activo="1"
                                                   title="Haga clic para desactivar">
                                                    <i class="fa fa-check-circle"></i> Activo
                                                </a>
                                            @else
                                                <a href="#"
                                                   class="btn-toggle-status badge-status-inactivo"
                                                   data-url="{{ route('areas_almacen.status', $area->id_area_almacen) }}"
                                                   data-nombre="{{ $area->nombre }}"
                                                   data-activo="0"
                                                   title="Haga clic para activar">
                                                    <i class="fa fa-times-circle"></i> Inactivo
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                {{-- @empty define el bloque HTML renderizado cuando la colección iterada está totalmente vacía. --}}
                                {{-- Muestra una advertencia visual centralizada para indicarle al usuario que no se encontraron resultados. --}}
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay áreas de almacén registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- @if verifica que existan registros ($areas->total() > 0) antes de renderizar la paginación. --}}
                {{-- Evita mostrar elementos de navegación vacíos en la parte inferior si no hay datos. --}}
                @if($areas->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        {{-- $areas->firstItem() y lastItem() devuelven el índice del primer y último elemento de la página actual. --}}
                        {{-- Muestra un texto informativo del rango de registros visualizados actualmente. --}}
                        <div class="text-muted small">
                            Mostrando {{ $areas->firstItem() ?? 0 }} a {{ $areas->lastItem() ?? 0 }} de {{ $areas->total() }} áreas de almacén
                        </div>
                        <nav aria-label="Paginación de áreas de almacén">
                            {{-- links() renderiza la paginación HTML de Bootstrap. appends() añade los query strings activos para mantener la búsqueda en cada cambio de página. --}}
                            {{-- Enlaza las diferentes páginas de resultados preservando el término buscado. --}}
                            {{ $areas->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modal de Registro de Área ── --}}
<div class="modal fade" id="modalRegistrarArea" tabindex="-1" aria-labelledby="modalRegistrarAreaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalRegistrarAreaLabel">
                    <i class="fa fa-plus-circle me-2"></i>Registrar nueva área de almacén
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- route('areas_almacen.store') apunta al controlador en el método store para registrar un nuevo recurso. --}}
            {{-- Envía los campos de texto vía POST para el registro. --}}
            <form method="POST" action="{{ route('areas_almacen.store') }}" novalidate id="formNuevaArea">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label for="nombre" class="form-label fw-bold">
                            Nombre del área:
                        </label>
                        {{-- old('nombre') recupera el input nombre del formulario fallido anterior. --}}
                        {{-- Mantiene escrito el texto si ocurre un error de validación en el servidor al enviar el formulario. --}}
                        <input
                            type="text"
                            name="nombre"
                            id="nombre"
                            class="form-control @error('nombre') is-invalid @enderror"
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Almacén General, Farmacia Central..."
                            autocomplete="off"
                            maxlength="255"
                            autofocus
                            required
                        >
                        <div id="feedbackDisponibilidad" class="mt-1 small"></div>
                        <div id="loadingSpinner" class="mt-1 small text-muted" style="display:none;">
                            <i class="fa fa-spinner fa-spin me-1"></i>Verificando...
                        </div>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" id="btnGuardar" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>Guardar Información
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- $errors->any() comprueba si hay errores de validación de cualquier campo en la sesión flash. --}}
{{-- Ejecuta un script inline de Bootstrap para volver a abrir automáticamente el modal en pantalla si el backend rechazó la validación. --}}
{{-- Evita que el usuario tenga que presionar de nuevo el botón "Registrar Área" para ver los mensajes de error. --}}
@if ($errors->any())
    <script>
        // DOMContentLoaded espera a que el documento HTML esté completamente cargado.
        // Asegura que las librerías de Bootstrap y elementos del DOM estén disponibles antes de instanciar el Modal.
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('modalRegistrarArea'));
            myModal.show();
        });
    </script>
@endif
@endsection

{{-- @push agrega los recursos CSS/JS específicos a la pila 'scripts' definida en el layout base layouts/app. --}}
{{-- Acumula los estilos y lógica de comportamiento específicos del listado de áreas sin interferir con otras páginas. --}}
@push('scripts')
    {{-- @vite carga y compila mediante Vite los recursos del módulo de áreas (css y js de comportamiento de tabla y AJAX). --}}
    @vite(['resources/css/inventario/areas_almacen/areas.css', 'resources/js/inventario/areas_almacen/areas.js'])
@endpush
