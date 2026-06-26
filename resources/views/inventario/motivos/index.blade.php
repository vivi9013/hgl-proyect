{{-- @extends indica la herencia de la plantilla de diseño base de la aplicación. --}}
{{-- Utiliza layouts/app.blade.php para proporcionar la estructura común (menús, barra lateral). --}}
{{-- En este punto, envuelve la vista en el contenedor principal de la plataforma. --}}
@extends('layouts.app')

{{-- @section inyecta una cadena de texto en un bloque de la plantilla base. --}}
{{-- Pasa el título estático de la página al marcador @yield('title') de la cabecera HTML. --}}
{{-- Define el título visible de la pestaña en el navegador web. --}}
@section('title', 'Motivos de Devoluciones')

{{-- @section y @endsection definen la sección principal de contenido dinámico. --}}
{{-- Inserta toda la estructura HTML de la tabla, buscadores y modales en @yield('content') del layout. --}}
{{-- Proporciona el cuerpo funcional de la administración de motivos de devoluciones. --}}
@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-tags text-primary me-2"></i>Motivos de Devoluciones
            </h1>
            <p class="text-muted mb-0">Registro, edición y control de los motivos de devolución de medicamentos y material de curación</p>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas SweetAlert2 ── --}}
    {{-- @if evalúa la existencia de una clave en la sesión flash. --}}
    {{-- session('exitog') comprueba si se ha guardado una confirmación de registro global en la sesión. --}}
    {{-- Renderiza un div vacío con ID específico que sirve de disparador para mostrar notificaciones SweetAlert en JavaScript. --}}
    @if(session('exitog'))
        <div id="alertaExitog"></div>
    @endif
    {{-- session('exito') recupera el mensaje de confirmación de edición o cambio de estado. --}}
    {{-- Sirve para notificar visualmente al usuario cuando una acción finaliza con éxito. --}}
    @if(session('exito'))
        <div id="alertaExito"></div>
    @endif

    {{-- ── Buscador y Acciones ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-8">
            {{-- route('motivos.index') genera la URL para recargar esta misma vista. --}}
            {{-- Utiliza el método GET para enviar la palabra clave de búsqueda en los parámetros de la URL. --}}
            {{-- Ejecuta la consulta de filtrado y actualiza la tabla de datos en pantalla. --}}
            <form method="GET" action="{{ route('motivos.index') }}" id="formBuscar">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-search me-1"></i>Buscar:</label>
                        <div class="input-group" style="border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                            {{-- value="{{ $buscar }}" inyecta directamente el texto de búsqueda activo del controlador. --}}
                            {{-- Preserva en la caja de texto el término que el usuario buscó para mantener la referencia. --}}
                            <input
                                type="text"
                                name="buscar"
                                id="inputBuscar"
                                class="form-control bg-light border-0"
                                placeholder="Buscar por descripción..."
                                value="{{ $buscar }}"
                                autocomplete="off"
                                style="font-size: 0.9rem; box-shadow: none;"
                            >
                            {{-- @if evalúa si existe un término de búsqueda para renderizar un botón de limpieza rápida. --}}
                            {{-- Crea un enlace que recarga el índice vacío de parámetros restableciendo la vista original. --}}
                            {{-- Permite quitar filtros activos con un solo clic. --}}
                            @if($buscar)
                                <a href="{{ route('motivos.index') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar Filtros">
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
            {{-- request()->query() recupera los parámetros GET de búsqueda actuales en formato de array. --}}
            {{-- Pasa este conjunto de parámetros al generador de rutas motivos.imprimir. --}}
            {{-- Permite que el reporte imprimible se abra aplicando exactamente los mismos filtros de búsqueda de la pantalla. --}}
            <a href="{{ route('motivos.imprimir', request()->query()) }}"
               target="_blank"
               class="btn btn-outline-secondary rounded-pill shadow-sm"
               id="btnImprimirReporte"
               style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-print me-1 text-dark"></i> Imprimir Reporte
            </a>
            {{-- Atributos de datos data-bs-toggle y data-bs-target de Bootstrap 5. --}}
            {{-- Configuran de manera declarativa la apertura del modal sin necesidad de inicializarlo con JS. --}}
            {{-- Abre la ventana flotante que contiene el formulario de registro de motivo. --}}
            <button type="button"
                    class="btn btn-primary rounded-pill shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalRegistrarMotivo"
                    style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-plus-circle me-1"></i>Registrar Motivo
            </button>
        </div>
    </div>

    {{-- ── Tabla de Motivos ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Lista de motivos de devolución
                        </h5>
                        {{-- $motivos->total() obtiene el recuento total de registros devueltos por el paginador de Laravel. --}}
                        {{-- Muestra la cantidad total de motivos que existen en el sistema para esta búsqueda. --}}
                        <span class="rounded-pill px-3 py-1 fw-bold align-middle d-inline-block" style="background-color: #e9ecef; font-size: 0.78rem; letter-spacing: 0.03em;">
                            <span style="color: #000000;">{{ $motivos->total() }}</span> <span style="color: #495057;">{{ $motivos->total() === 1 ? 'Motivo' : 'Motivos' }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table id="tablaMotivos" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">#</th>
                                    <th class="text-center" style="width: 100px;">Editar</th>
                                    <th>Descripción del motivo</th>
                                    <th style="width: 140px;">Modifica Stock</th>
                                    <th>Fecha Registro</th>
                                    <th style="width: 120px;">Hora</th>
                                    <th class="text-center pe-4" style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @forelse es la directiva Blade que combina un foreach de repetición con una cláusula de escape @empty. --}}
                                {{-- Itera sobre la lista de motivos y ejecuta el bloque @empty si no hay registros. --}}
                                {{-- Dibuja un renglón de datos para cada registro de la base de datos. --}}
                                @forelse($motivos as $index => $motivo)
                                    {{-- El operador condicional ternario evalúa si la columna de estado está inactiva ($motivo->activo == 0). --}}
                                    {{-- Aplica clases CSS para atenuar e inclinar la fuente de texto si el registro se encuentra deshabilitado. --}}
                                    <tr class="{{ $motivo->activo == 0 ? 'text-muted fst-italic' : '' }}">
                                        {{-- Calcula el número correlativo global del registro tomando la página actual y registros por página. --}}
                                        {{-- Multiplica la página previa por el límite de registros por página y le suma el índice actual ($loop->iteration). --}}
                                        {{-- Asegura la continuidad de la numeración a lo largo de las múltiples páginas. --}}
                                        <td class="ps-4 fw-bold">{{ ($motivos->currentPage() - 1) * $motivos->perPage() + $loop->iteration }}</td>
                                        <td class="text-center">
                                            {{-- route() genera el enlace URL dinámico para acceder a la edición de este registro específico. --}}
                                            {{-- Envía como parámetro la clave primaria para levantar el recurso en la pantalla de edición. --}}
                                            <a href="{{ route('motivos.edit', $motivo->id_motivo) }}"
                                               class="btn btn-sm btn-outline-primary rounded-circle"
                                               title="Editar"
                                               style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $motivo->descripcion }}</td>
                                        <td>
                                            <span class="badge-tipo">{{ $motivo->modificar }}</span>
                                        </td>
                                        {{-- Carbon::parse() convierte una cadena de fecha de base de datos a formato de objeto fecha. --}}
                                        {{-- Aplica el método format('d/m/Y') para pintarla en el formato español estándar de lectura. --}}
                                        <td>{{ $motivo->fecha_registro ? \Carbon\Carbon::parse($motivo->fecha_registro)->format('d/m/Y') : '' }}</td>
                                        <td>{{ $motivo->hora_registro }}</td>
                                        <td class="text-center pe-4">
                                            {{-- Los atributos data-url, data-nombre y data-activo guardan variables dentro del nodo DOM. --}}
                                            {{-- Son capturados en el script de JS asociado al módulo para controlar el cambio de estado con confirmación SweetAlert. --}}
                                            {{-- Ofrece un mecanismo asíncrono e interactivo de activación o desactivación de registros sin recargar la pantalla. --}}
                                            @if($motivo->activo == 1)
                                                <a href="#"
                                                   class="btn-toggle-status badge-status-activo"
                                                   data-url="{{ route('motivos.status', $motivo->id_motivo) }}"
                                                   data-nombre="{{ $motivo->descripcion }}"
                                                   data-activo="1"
                                                   title="Haga clic para desactivar">
                                                    <i class="fa fa-check-circle"></i> Activo
                                                </a>
                                            @else
                                                <a href="#"
                                                   class="btn-toggle-status badge-status-inactivo"
                                                   data-url="{{ route('motivos.status', $motivo->id_motivo) }}"
                                                   data-nombre="{{ $motivo->descripcion }}"
                                                   data-activo="0"
                                                   title="Haga clic para activar">
                                                    <i class="fa fa-times-circle"></i> Inactivo
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                {{-- @empty entra en acción cuando no existen registros en la colección $motivos. --}}
                                {{-- Dibuja un mensaje centralizado indicando la ausencia de resultados para alertar al usuario. --}}
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay motivos de devolución registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- @if evalúa la existencia de registros para renderizar el pie de página de la paginación. --}}
                @if($motivos->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        {{-- firstItem() y lastItem() entregan el índice del primer y último elemento de la página de resultados activa. --}}
                        <div class="text-muted small">
                            Mostrando {{ $motivos->firstItem() ?? 0 }} a {{ $motivos->lastItem() ?? 0 }} de {{ $motivos->total() }} motivos
                        </div>
                        <nav aria-label="Paginación de motivos">
                            {{-- links() renderiza la barra HTML de paginación de Bootstrap 5. appends() inyecta los filtros del query string para preservarlos al cambiar de página. --}}
                            {{ $motivos->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modal de Registro de Motivo ── --}}
<div class="modal fade" id="modalRegistrarMotivo" tabindex="-1" aria-labelledby="modalRegistrarMotivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalRegistrarMotivoLabel">
                    <i class="fa fa-plus-circle me-2"></i>Registrar nuevo motivo de devolución
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- route('motivos.store') apunta a la ruta de almacenamiento en el controlador para registrar los datos. --}}
            {{-- Envía la petición del formulario al backend utilizando el verbo POST. --}}
            <form method="POST" action="{{ route('motivos.store') }}" novalidate id="formNuevoMotivo">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="descripcion" class="form-label fw-bold">
                                    Descripción del motivo:
                                </label>
                                {{-- old('descripcion') recupera la cadena del motivo enviado en el formulario anterior si falló la validación. --}}
                                {{-- Mantiene escrito el texto ingresado por el usuario para evitar la reescritura. --}}
                                <input
                                    type="text"
                                    name="descripcion"
                                    id="descripcion"
                                    class="form-control @error('descripcion') is-invalid @enderror"
                                    value="{{ old('descripcion') }}"
                                    placeholder="Ej. Caducado, Dañado, Error en pedido..."
                                    autocomplete="off"
                                    maxlength="255"
                                    autofocus
                                    required
                                >
                                <div id="feedbackDisponibilidad" class="mt-1 small"></div>
                                <div id="loadingSpinner" class="mt-1 small text-muted" style="display:none;">
                                    <i class="fa fa-spinner fa-spin me-1"></i>Verificando...
                                </div>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="modificar" class="form-label fw-bold">
                                    Modifica Stock:
                                </label>
                                {{-- @error evalúa la validación de 'modificar' para aplicar o no la clase 'is-invalid'. --}}
                                <select
                                    name="modificar"
                                    id="modificar"
                                    class="form-control @error('modificar') is-invalid @enderror"
                                    required
                                >
                                    <option value="">-- Seleccionar --</option>
                                    {{-- old('modificar') recupera la opción seleccionada previamente por el usuario. --}}
                                    {{-- El operador ternario añade 'selected' para restaurar la selección de 'Si' si hubo error. --}}
                                    <option value="Si" {{ old('modificar') == 'Si' ? 'selected' : '' }}>Sí</option>
                                    {{-- Restaura condicionalmente la selección de 'No' en caso de error de validación. --}}
                                    <option value="No" {{ old('modificar') == 'No' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('modificar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted mt-1">
                                    <i class="fa fa-info-circle me-1"></i>
                                    Indica si este motivo afecta el stock al registrar una devolución.
                                </div>
                            </div>
                        </div>
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

{{-- $errors->any() comprueba si hay algún error de validación activo en la sesión flash. --}}
{{-- Utiliza un bloque script inline de JavaScript para reabrir automáticamente el modal en pantalla de manera que los errores sean visibles. --}}
@if ($errors->any())
    <script>
        // DOMContentLoaded espera la carga completa del DOM para instanciar y abrir de forma segura el modal de Bootstrap.
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('modalRegistrarMotivo'));
            myModal.show();
        });
    </script>
@endif
@endsection

{{-- @push agrega los recursos finales a la pila de scripts en la plantilla padre. --}}
@push('scripts')
    {{-- @vite compila y añade el archivo CSS de estilos y el JS de comportamiento de motivos. --}}
    @vite(['resources/css/inventario/motivos/motivos.css', 'resources/js/inventario/motivos/motivos.js'])
@endpush
