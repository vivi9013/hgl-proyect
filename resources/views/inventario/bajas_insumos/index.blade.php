{{-- @extends indica la herencia de la plantilla de diseño común de la aplicación. --}}
{{-- Utiliza layouts/app.blade.php como el contenedor HTML base que incluye barra lateral y navegación. --}}
{{-- Inyecta los componentes en el layout general de la plataforma. --}}
@extends('layouts.app')

{{-- @section inyecta una cadena de texto corta en la plantilla base. --}}
{{-- Envía "Bajas de Insumos" al marcador de posición @yield('title'). --}}
{{-- Cambia el título de la pestaña del navegador para este módulo. --}}
@section('title', 'Bajas de Insumos')

{{-- @section y @endsection definen la sección principal del contenido dinámico de la vista. --}}
{{-- Inserta todo el HTML del módulo (filtros, historial y modales) en el yield('content') de la plantilla base. --}}
@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-minus-circle text-primary me-2"></i>Bajas de Insumos
            </h1>
            <p class="text-muted mb-0">Registro y control de bajas de medicamentos y material de curación por área de almacén</p>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas SweetAlert2 ── --}}
    {{-- @if evalúa la existencia de variables flash en la sesión del servidor. --}}
    {{-- session('exitog') detecta si se registró exitosamente un elemento para disparar un SweetAlert de creación global en JS. --}}
    @if(session('exitog'))
        <div id="alertaExitog"></div>
    @endif
    {{-- session('exito') se activa tras actualizar datos o cambiar estados de baja exitosamente. --}}
    @if(session('exito'))
        <div id="alertaExito"></div>
    @endif
    {{-- session('error') captura mensajes de error del backend, guardando el texto en data-message para que SweetAlert lo exponga. --}}
    {{-- Permite alertar visualmente al usuario si hubo un problema al procesar la baja (por ejemplo, falta de stock). --}}
    @if(session('error'))
        <div id="alertaError" data-message="{{ session('error') }}"></div>
    @endif

    {{-- ── Buscador y Filtros ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-8">
            {{-- route('bajas_insumos.index') calcula la URL del listado principal de bajas de insumos. --}}
            {{-- Utiliza el método GET para adjuntar filtros de búsqueda a la URL como parámetros Query String. --}}
            <form method="GET" action="{{ route('bajas_insumos.index') }}" id="formBuscar">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-search me-1"></i>Buscar:</label>
                        <div class="input-group" style="border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                            {{-- value="{{ $buscar }}" inserta el término de búsqueda actual enviado desde el controlador. --}}
                            {{-- Preserva en el input de búsqueda la frase ingresada por el usuario para su referencia visual. --}}
                            <input
                                type="text"
                                name="buscar"
                                id="inputBuscar"
                                class="form-control bg-light border-0"
                                placeholder="Buscar por insumo, área o motivo..."
                                value="{{ $buscar }}"
                                autocomplete="off"
                                style="font-size: 0.9rem; box-shadow: none;"
                            >
                            {{-- @if evalúa si existe algún filtro de búsqueda escrito en el input. --}}
                            {{-- Renderiza un enlace en forma de tachado (X) para recargar el listado sin filtros. --}}
                            @if($buscar)
                                <a href="{{ route('bajas_insumos.index') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar Filtros">
                                    <i class="fa fa-times text-danger"></i>
                                </a>
                            @endif
                            <button class="input-group-text bg-light border-0" type="submit" title="Buscar">
                                <i class="fa fa-search text-dark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_inicio" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-calendar me-1"></i>Fecha Inicio:</label>
                        {{-- value="{{ $fechaInit }}" inyecta la fecha inicial de filtrado configurada en el controlador. --}}
                        <input
                            type="date"
                            name="fecha_inicio"
                            id="fecha_inicio"
                            class="form-control bg-light"
                            value="{{ $fechaInit }}"
                        >
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_fin" class="form-label small fw-bold mb-1 text-dark"><i class="fa fa-calendar me-1"></i>Fecha Fin:</label>
                        <div class="input-group">
                            {{-- value="{{ $fechaFin }}" inyecta la fecha final de filtrado configurada en el controlador. --}}
                            <input
                                type="date"
                                name="fecha_fin"
                                id="fecha_fin"
                                class="form-control bg-light"
                                value="{{ $fechaFin }}"
                            >
                            {{-- @if evalúa si existen búsquedas o filtros de fecha activos para desplegar un botón general de reinicio. --}}
                            {{-- Facilita volver al listado original completo de registros de bajas. --}}
                            @if($buscar || $fechaInit || $fechaFin)
                                <a href="{{ route('bajas_insumos.index') }}" class="btn btn-outline-secondary" title="Limpiar Filtros">
                                    <i class="fa fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12 col-md-4 text-md-end d-flex justify-content-md-end align-items-center mt-2 mt-md-0" style="gap: 0.75rem;">
            {{-- request()->query() recupera el array de parámetros GET actuales. --}}
            {{-- Adjunta los filtros a la URL de impresión de forma que la vista de PDF respete los mismos rangos. --}}
            <a href="{{ route('bajas_insumos.imprimir', request()->query()) }}"
               target="_blank"
               class="btn btn-outline-secondary rounded-pill shadow-sm"
               id="btnImprimirReporte"
               style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-print me-1 text-dark"></i> Imprimir Reporte
            </a>
            {{-- Atributos de Bootstrap 5 data-bs-toggle y data-bs-target para modales. --}}
            {{-- Ejecutan automáticamente el despliegue del modal de registro de bajas sin script manual. --}}
            <button type="button" class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAltaBaja"
                     style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-plus-circle me-1"></i>Registrar Baja
            </button>
        </div>
    </div>

    {{-- ── Tabla de Bajas ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Historial de bajas de insumos
                        </h5>
                        {{-- $bajas->total() calcula y muestra el número total de registros coincidentes en la base de datos. --}}
                        <span class="rounded-pill px-3 py-1 fw-bold align-middle d-inline-block" style="background-color: #e9ecef; font-size: 0.78rem; letter-spacing: 0.03em;">
                            <span style="color: #000000;">{{ $bajas->total() }}</span> <span style="color: #495057;">{{ $bajas->total() === 1 ? 'Registro' : 'Registros' }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table id="tablaAreas" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">#</th>
                                    <th>Insumo</th>
                                    <th>Clave</th>
                                    <th>Área de Almacén</th>
                                    <th>Motivo</th>
                                    <th class="text-center" style="width: 100px;">Cantidad</th>
                                    <th>Fecha Baja</th>
                                    <th>Hora</th>
                                    <th class="text-center pe-4" style="width: 150px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @forelse es la directiva Blade para iterar sobre una colección con una cláusula de escape @empty si no existen elementos. --}}
                                {{-- Itera sobre la lista de bajas para dibujar cada renglón de datos en pantalla. --}}
                                @forelse($bajas as $index => $baja)
                                    {{-- El operador ternario condicional evalúa si la baja está cancelada ($baja->cancelado === 'Si'). --}}
                                    {{-- Aplica la clase 'text-muted fst-italic' de Bootstrap para opacar las filas de registros cancelados. --}}
                                    <tr class="{{ $baja->cancelado === 'Si' ? 'text-muted fst-italic' : '' }}">
                                        {{-- Calcula el número correlativo global del registro tomando la página actual y el total por página. --}}
                                        {{-- Garantiza que el número de fila sea consecutivo independientemente de la página de resultados. --}}
                                        <td class="ps-4 fw-bold">{{ ($bajas->currentPage() - 1) * $bajas->perPage() + $loop->iteration }}</td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $baja->insumo->descripcion ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span style="font-family: Arial, sans-serif; font-size: 0.82rem; font-weight: 600; color: #374151; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 3px 10px; display: inline-block; letter-spacing: 0.03em; box-shadow: 0 1px 2px rgba(0,0,0,0.07);">{{ $baja->insumo->clave ?? '—' }}</span>
                                        </td>
                                        <td>{{ $baja->areaAlmacen->nombre ?? '—' }}</td>
                                        <td>
                                            <small class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $baja->motivo }}">
                                                {{ $baja->motivo }}
                                            </small>
                                        </td>
                                        <td class="text-center fw-bold">{{ $baja->cantidad }}</td>
                                        {{-- Carbon::parse() convierte el string de la fecha de la baja a objeto Carbon para aplicar formato legible (d/m/Y). --}}
                                        <td>{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '' }}</td>
                                        <td>{{ $baja->hora_baja }}</td>
                                        <td class="text-center pe-4">
                                            {{-- Los atributos data-* guardan metadatos necesarios para que el script JavaScript gestione la confirmación de estado. --}}
                                            {{-- Envían parámetros dinámicos a SweetAlert2 para cancelar o reactivar una baja mediante AJAX sin recargar el sitio. --}}
                                            @if($baja->cancelado === 'Si')
                                                <a href="#"
                                                   class="btn-toggle-baja-status badge bg-danger text-decoration-none py-2 px-3 rounded-pill shadow-sm"
                                                   data-url="{{ route('bajas_insumos.toggle_status', $baja->id_baja_insumo) }}"
                                                   data-insumo="{{ $baja->insumo->descripcion ?? 'Insumo' }}"
                                                   data-cantidad="{{ $baja->cantidad }}"
                                                   data-accion="activar"
                                                   title="Haga clic para reactivar esta baja">
                                                    <i class="fa fa-times-circle me-1"></i> Cancelada
                                                </a>
                                            @else
                                                <a href="#"
                                                   class="btn-toggle-baja-status badge bg-success text-decoration-none py-2 px-3 rounded-pill shadow-sm"
                                                   data-url="{{ route('bajas_insumos.toggle_status', $baja->id_baja_insumo) }}"
                                                   data-insumo="{{ $baja->insumo->descripcion ?? 'Insumo' }}"
                                                   data-cantidad="{{ $baja->cantidad }}"
                                                   data-accion="cancelar"
                                                   title="Haga clic para cancelar esta baja">
                                                    <i class="fa fa-check-circle me-1"></i> Activa
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                {{-- @empty se ejecuta cuando la consulta del controlador no devolvió ningún registro de baja. --}}
                                {{-- Renderiza un renglón centralizado indicando la ausencia de datos en el historial. --}}
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay bajas de insumos registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- @if condiciona la visualización del pie de página y controles de paginación solo si hay al menos un registro en la tabla. --}}
                @if($bajas->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        {{-- firstItem() y lastItem() retornan los índices de registros mostrados en la página actual. --}}
                        <div class="text-muted small">
                            Mostrando {{ $bajas->firstItem() ?? 0 }} a {{ $bajas->lastItem() ?? 0 }} de {{ $bajas->total() }} bajas
                        </div>
                        <nav aria-label="Paginación de bajas de insumos">
                            {{-- links() renderiza la navegación HTML. appends() añade variables GET de filtros de búsqueda para mantener la vista consistente al paginar. --}}
                            {{ $bajas->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modal de Registro de Baja ── --}}
<div class="modal fade" id="modalAltaBaja" tabindex="-1" aria-labelledby="modalAltaBajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalAltaBajaLabel">
                    <i class="fa fa-plus-circle me-2"></i>Registrar nueva baja de insumo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- route('bajas_insumos.store') especifica la URL de guardado en el controlador de bajas. --}}
            {{-- Envía los datos capturados en el formulario al servidor mediante POST. --}}
            <form method="POST" action="{{ route('bajas_insumos.store') }}" novalidate id="formBaja">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">

                        {{-- Área de Almacén --}}
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="id_area_almacen" class="form-label fw-bold">
                                    Área de Almacén:
                                </label>
                                {{-- @error valida si el área de almacén falló las comprobaciones para asignarle estilo de error. --}}
                                <select
                                    name="id_area_almacen"
                                    id="id_area_almacen"
                                    class="form-control @error('id_area_almacen') is-invalid @enderror"
                                    required
                                >
                                    <option value="">-- Seleccionar área --</option>
                                    {{-- @foreach recorre la colección de áreas de almacén para poblar la lista de opciones. --}}
                                    @foreach($areas as $area)
                                        {{-- El operador ternario valida si la ID de la iteración coincide con el valor enviado previamente (old). --}}
                                        {{-- Agrega 'selected' para preservar la selección del usuario si el envío general falló. --}}
                                        <option value="{{ $area->id_area_almacen }}"
                                            {{ old('id_area_almacen') == $area->id_area_almacen ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_area_almacen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Insumo --}}
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group position-relative">
                                <label for="buscarInsumo" class="form-label fw-bold">
                                    Insumo (clave o descripción):
                                </label>
                                {{-- Input text de búsqueda autocomplete que enlaza con los eventos JavaScript del módulo. --}}
                                {{-- Preserva el texto de búsqueda anterior a través de old('buscarInsumo'). --}}
                                <input
                                    type="text"
                                    id="buscarInsumo"
                                    class="form-control @error('id_insumo') is-invalid @enderror"
                                    placeholder="Buscar insumo… (doble clic para ver claves)"
                                    autocomplete="off"
                                    value="{{ old('buscarInsumo', '') }}"
                                    title="Doble clic para ver listado de claves disponibles"
                                >
                                {{-- Input oculto que almacena el ID del insumo seleccionado, necesario para el envío en POST. --}}
                                <input type="hidden" name="id_insumo" id="id_insumo" value="{{ old('id_insumo') }}">
                                {{-- Contenedores flotantes para desplegar resultados dinámicos y stock disponible mediante llamadas AJAX en JS. --}}
                                <div id="sugerenciasInsumo" class="list-group position-absolute w-100" style="z-index:1060; display:none; max-height:220px; overflow-y:auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                                <div id="infoStock" class="mt-1 small text-muted" style="display:none;">
                                    <i class="fa fa-cubes me-1"></i>Stock disponible: <strong id="stockDisponible">0</strong> piezas
                                </div>
                                @error('id_insumo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                {{-- Panel de acceso rápido que se abre al hacer doble clic en el input de insumos --}}
                                {{-- Permite listar directamente todas las claves e insumos asociados con su respectivo stock --}}
                                <div id="panelClaves" style="display:none; position:absolute; left:0; top:calc(100% + 6px); z-index:1070; width:580px; background:#fff; border:1px solid #cbd5e1; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.13); overflow:hidden;">
                                    <div style="background:#1d4ed8; padding:8px 14px; display:flex; justify-content:space-between; align-items:center;">
                                        <span style="color:#fff; font-weight:700; font-size:0.82rem;">
                                            <i class="fa fa-list-alt me-1"></i> Claves disponibles
                                        </span>
                                        <button type="button" id="cerrarPanelClaves" style="background:transparent; border:none; color:#fff; font-size:1rem; cursor:pointer; line-height:1;" title="Cerrar">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                    <div style="padding:8px 10px; border-bottom:1px solid #e5e7eb;">
                                        <input type="text" id="filtroPanelClaves" placeholder="Filtrar claves…" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:5px 10px; font-size:0.8rem; outline:none;">
                                    </div>
                                    {{-- Aviso sin área --}}
                                    <div id="avisoSinArea" style="display:none; align-items:center; gap:8px; padding:7px 14px; background:#fffbeb; border-bottom:1px solid #fde68a; font-size:0.78rem; color:#92400e;">
                                        <i class="fa fa-info-circle" style="color:#d97706;"></i>
                                        <span>Selecciona primero un <strong>Área de Almacén</strong> para ver el stock real de cada insumo.</span>
                                    </div>
                                    <div id="cuerpoPanelClaves" style="max-height:260px; overflow-y:auto; overflow-x:hidden;">
                                        <table style="width:100%; border-collapse:collapse; font-size:0.8rem; table-layout:fixed;">
                                            <colgroup>
                                                <col style="width:140px;">
                                                <col style="width:360px;">
                                                <col style="width:80px;">
                                            </colgroup>
                                            <thead>
                                                <tr style="background:#f8fafc; position:sticky; top:0; z-index:1;">
                                                    <th style="padding:6px 10px; text-align:left; color:#374151; font-weight:700; border-bottom:1px solid #e5e7eb; font-family:Arial,sans-serif;">Clave</th>
                                                    <th style="padding:6px 10px; text-align:left; color:#374151; font-weight:700; border-bottom:1px solid #e5e7eb;">Descripcion</th>
                                                    <th style="padding:6px 10px; text-align:center; color:#374151; font-weight:700; border-bottom:1px solid #e5e7eb;">Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody id="filasClaves"></tbody>
                                        </table>
                                        <div id="panelClavesLoading" style="text-align:center; padding:18px; color:#6b7280; font-size:0.82rem;">
                                            <i class="fa fa-circle-o-notch fa-spin me-1"></i> Cargando claves…
                                        </div>
                                        <div id="panelClavesVacio" style="display:none; text-align:center; padding:18px; color:#9ca3af; font-size:0.82rem;">
                                            <i class="fa fa-search me-1"></i> Sin resultados
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Cantidad --}}
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="cantidad" class="form-label fw-bold">
                                    Cantidad a dar de baja:
                                </label>
                                {{-- Input numérico para recibir la cantidad. old('cantidad') preserva el dato ingresado en caso de error. --}}
                                <input
                                    type="number"
                                    name="cantidad"
                                    id="cantidad"
                                    class="form-control @error('cantidad') is-invalid @enderror"
                                    value="{{ old('cantidad') }}"
                                    placeholder="Ej. 5"
                                    min="1"
                                    required
                                >
                                @error('cantidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Motivo --}}
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="motivo" class="form-label fw-bold">
                                    Motivo de la baja:
                                </label>
                                {{-- Textarea para el motivo detallado de la baja. old('motivo') restaura la descripción. --}}
                                <textarea
                                    name="motivo"
                                    id="motivo"
                                    class="form-control @error('motivo') is-invalid @enderror"
                                    rows="3"
                                    placeholder="Ej. Producto caducado, daño físico, pérdida..."
                                    maxlength="500"
                                    required
                                >{{ old('motivo') }}</textarea>
                                @error('motivo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" id="btnGuardar" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>Registrar Baja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- $errors->any() evalúa si hay errores de validación activos al enviar el formulario. --}}
{{-- Utiliza un script inline de JS para reabrir automáticamente el modal de creación y mostrar los errores del backend. --}}
@if ($errors->any())
    <script>
        // DOMContentLoaded asegura que el DOM esté completamente mapeado antes de inicializar el modal de Bootstrap.
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('modalAltaBaja'));
            myModal.show();
        });
    </script>
@endif
@endsection

{{-- @push acumula recursos en la sección de scripts definida al final de layouts/app. --}}
@push('scripts')
    {{-- @vite carga y procesa los archivos específicos CSS y JS de la funcionalidad de bajas de insumos. --}}
    @vite(['resources/css/inventario/bajas_insumos/bajas.css', 'resources/js/inventario/bajas_insumos/bajas.js'])
@endpush
