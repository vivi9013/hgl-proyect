{{-- @extends indica la herencia de la plantilla de diseño base de la aplicación. --}}
{{-- Utiliza layouts/app.blade.php para proporcionar el contenedor general con la barra de navegación lateral. --}}
@extends('layouts.app')

{{-- @section inyecta una cadena de texto en un bloque de la plantilla base. --}}
{{-- Define el título visible de la pestaña en el navegador web como "Devoluciones Pendientes". --}}
@section('title', 'Devoluciones Pendientes')

{{-- @section y @endsection definen la sección de contenido principal del listado de devoluciones en proceso. --}}
{{-- Inserta todo el HTML del módulo en el yield('content') de la plantilla base. --}}
@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado del módulo ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-undo text-primary me-2"></i>Devoluciones Pendientes
            </h1>
            <p class="text-muted mb-0">Devoluciones en proceso registradas en el sistema</p>
        </div>
    </div>

    {{-- Subnavegación del Módulo --}}
    <div class="d-flex gap-2 mb-2 flex-wrap">
        {{-- Enlace dinámico que recarga el listado de devoluciones con status en proceso. --}}
        <a href="{{ route('devoluciones.index') }}" class="btn btn-sm btn-primary py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-hourglass-half me-1"></i>Pendientes
        </a>
        {{-- Enlace dinámico hacia la pestaña de histórico de devoluciones terminadas o cerradas. --}}
        <a href="{{ route('devoluciones.terminadas') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-check-circle me-1 text-dark"></i>Terminadas
        </a>
        {{-- Enlace dinámico hacia el generador de reportes en PDF y vistas filtradas de impresión. --}}
        <a href="{{ route('devoluciones.reportes') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-bar-chart me-1 text-dark"></i>Reportes
        </a>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas SweetAlert2 ── --}}
    {{-- @if evalúa la existencia de notificaciones de éxito o error en la sesión flash para almacenarlas en variables data-message de HTML. --}}
    {{-- Permite que el script de JavaScript capture estos datos y dispare alertas animadas de SweetAlert2. --}}
    @if(session('exitog'))
        <div id="alertaExitog" data-message="{{ session('exitog') }}"></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito" data-message="{{ session('exito') }}"></div>
    @endif
    @if(session('error'))
        <div id="alertaError" data-message="{{ session('error') }}"></div>
    @endif

    {{-- ── Buscador + Filtros + Botones ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12 col-md-8">
            {{-- route() genera el endpoint del listado principal de devoluciones pendientes. --}}
            {{-- Utiliza el método GET para enviar la palabra clave y filtros en la URL de consulta. --}}
            <form method="GET" action="{{ route('devoluciones.index') }}" id="formBuscar">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 position-relative">
                        <label for="inputBuscar" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-search me-1"></i>Buscar:
                        </label>
                        <div class="input-group" style="border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                            {{-- value="{{ $buscar }}" inserta el término de búsqueda activo enviado desde el controlador. --}}
                            <input
                                type="text"
                                name="buscar"
                                id="inputBuscar"
                                class="form-control bg-light border-0"
                                placeholder="Buscar por ID, área..."
                                value="{{ $buscar }}"
                                autocomplete="off"
                                style="font-size: 0.9rem; box-shadow: none;"
                            >
                            {{-- @if evalúa si existe algún filtro de búsqueda activo para renderizar un botón de limpieza. --}}
                            @if($buscar)
                                <a href="{{ route('devoluciones.index') }}" class="input-group-text bg-light border-0 text-decoration-none" title="Limpiar">
                                    <i class="fa fa-times text-danger"></i>
                                </a>
                            @endif
                            <button class="input-group-text bg-light border-0" type="submit" title="Buscar">
                                <i class="fa fa-search text-dark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_inicio" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Inicio:
                        </label>
                        {{-- value="{{ $fechaInit }}" inyecta el valor de inicio del rango de fechas. --}}
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control bg-light" value="{{ $fechaInit }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="fecha_fin" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Fin:
                        </label>
                        <div class="input-group">
                            {{-- value="{{ $fechaFin }}" inyecta el valor final del rango de fechas. --}}
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control bg-light" value="{{ $fechaFin }}">
                            {{-- @if evalúa si existen búsquedas o filtros de fecha activos para desplegar un botón general de reinicio. --}}
                            @if($buscar || $fechaInit || $fechaFin)
                                <a href="{{ route('devoluciones.index') }}" class="btn btn-outline-secondary" title="Limpiar Filtros">
                                    <i class="fa fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12 col-md-4 text-md-end d-flex justify-content-md-end align-items-center mt-2 mt-md-0">
            {{-- Atributos de Bootstrap 5 data-bs-toggle y data-bs-target para modales. --}}
            {{-- Abre la ventana emergente para inicializar el registro de una nueva devolución. --}}
            <button type="button"
                    class="btn btn-primary rounded-pill shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalNuevaDevolucion"
                    style="font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.2rem;">
                <i class="fa fa-plus-circle me-1"></i>Nueva Devolución
            </button>
        </div>
    </div>

    {{-- ── Tabla de Devoluciones Pendientes ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list text-secondary me-2"></i>Devoluciones en proceso
                        </h5>
                        {{-- $devoluciones->total() obtiene el número total de devoluciones pendientes en la consulta. --}}
                        <span class="rounded-pill px-3 py-1 fw-bold align-middle d-inline-block" style="background-color: #e9ecef; font-size: 0.78rem; letter-spacing: 0.03em;">
                            <span style="color: #000000;">{{ $devoluciones->total() }}</span>
                            <span style="color: #495057;">{{ $devoluciones->total() === 1 ? 'Registro' : 'Registros' }}</span>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table id="tablaDevoluciones" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 60px;">#</th>
                                    <th>Folio</th>
                                    <th>Área de Almacén</th>
                                    <th>Área Abastecimiento</th>
                                    <th>Motivo</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th class="text-center pe-4" style="width: 110px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @forelse es la directiva Blade para iterar sobre los registros de devoluciones. --}}
                                @forelse($devoluciones as $devolucion)
                                    <tr>
                                        {{-- Calcula el consecutivo global del registro en la paginación. --}}
                                        <td class="ps-4 fw-bold">
                                            {{ ($devoluciones->currentPage() - 1) * $devoluciones->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            {{-- str_pad() aplica un relleno con ceros a la izquierda para formatear visualmente el folio del comprobante. --}}
                                            <span style="font-family: Arial, sans-serif; font-size: 0.82rem; font-weight: 600; color: #1d4ed8; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 3px 10px; display: inline-block;">
                                                DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </td>
                                        <td>{{ $devolucion->areaAlmacen->nombre ?? '—' }}</td>
                                        <td>{{ $devolucion->areaAbastecimiento->nombre ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-white" style="font-size: 0.8rem;">
                                                {{ $devolucion->motivo->descripcion ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- @if evalúa el status de la devolución para mostrar el badge adecuado de advertencia o peligro. --}}
                                            @if($devolucion->status === 'En proceso')
                                                <span class="badge bg-warning text-dark" style="font-size: 0.8rem;">Pendiente</span>
                                            @else
                                                <span class="badge bg-danger text-white" style="font-size: 0.8rem;">Cancelada</span>
                                            @endif
                                        </td>
                                        {{-- Carbon::parse() convierte el string de la fecha de la devolución a objeto Carbon para aplicar formato legible (d/m/Y). --}}
                                        <td>{{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $devolucion->hora_devolucion ?? '—' }}</td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex justify-content-center gap-2">
                                                {{-- Enlace dinámico que abre la pantalla de detalle de insumos para este ID de devolución. --}}
                                                <a href="{{ route('devoluciones.detalle', $devolucion->id_devolucion) }}"
                                                   class="btn btn-sm btn-outline-primary rounded-circle"
                                                   title="Ver detalle y agregar insumos"
                                                   style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                    <i class="fa fa-folder-open"></i>
                                                </a>
                                                {{-- @if evalúa el status para desplegar un botón de cancelar o reactivar. --}}
                                                {{-- Envía un atributo data-folio formateado que JS utilizará para pedir confirmación con SweetAlert2. --}}
                                                @if($devolucion->status === 'En proceso')
                                                    <a href="{{ route('devoluciones.toggle_status', $devolucion->id_devolucion) }}"
                                                       class="btn btn-sm btn-outline-danger rounded-circle btn-cancelar-devolucion"
                                                       data-folio="DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}"
                                                       title="Cancelar devolución"
                                                       style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                         <i class="fa fa-ban"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('devoluciones.toggle_status', $devolucion->id_devolucion) }}"
                                                       class="btn btn-sm btn-outline-success rounded-circle btn-reactivar-devolucion"
                                                       data-folio="DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}"
                                                       title="Reactivar devolución"
                                                       style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                        <i class="fa fa-undo"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                {{-- @empty se activa si no existen registros que cumplan con la consulta. --}}
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay devoluciones pendientes en proceso.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- @if evalúa la existencia de registros antes de renderizar el pie de página del paginador. --}}
                @if($devoluciones->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        <div class="text-muted small">
                            Mostrando {{ $devoluciones->firstItem() ?? 0 }} a {{ $devoluciones->lastItem() ?? 0 }} de {{ $devoluciones->total() }} devoluciones
                        </div>
                        <nav aria-label="Paginación de devoluciones">
                            {{-- links() renderiza la barra HTML de paginación de Bootstrap 5. appends() inyecta los filtros activos en cada cambio de página. --}}
                            {{ $devoluciones->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modal: Nueva Devolución ── --}}
<div class="modal fade" id="modalNuevaDevolucion" tabindex="-1" aria-labelledby="modalNuevaDevolucionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalNuevaDevolucionLabel">
                    <i class="fa fa-plus-circle me-2"></i>Nueva Devolución
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- route('devoluciones.store') apunta a la acción store del controlador para crear el registro de devolución. --}}
            <form method="POST" action="{{ route('devoluciones.store') }}" novalidate id="formNuevaDevolucion">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">

                        {{-- Área de Almacén --}}
                        <div class="col-12 col-md-6">
                            <label for="id_area_almacen" class="form-label fw-bold">
                                <i class="fa fa-building me-1"></i>Área de Almacén: <span class="text-danger">*</span>
                            </label>
                            {{-- @error asigna la clase is-invalid si falló la validación del área de almacén. --}}
                            <select name="id_area_almacen" id="id_area_almacen"
                                    class="form-control @error('id_area_almacen') is-invalid @enderror" required>
                                <option value="">-- Seleccionar área --</option>
                                {{-- @foreach recorre la colección de áreas de almacén para poblar la lista de opciones. --}}
                                @foreach($areasAlmacen as $area)
                                    {{-- El operador ternario comprueba si coincide con el valor enviado anteriormente (old) para preservarlo. --}}
                                    <option value="{{ $area->id_area_almacen }}"
                                        {{ old('id_area_almacen') == $area->id_area_almacen ? 'selected' : '' }}>
                                        {{ $area->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_area_almacen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">El área de almacén es obligatoria.</div>
                            @enderror
                        </div>

                        {{-- Área de Abastecimiento --}}
                        <div class="col-12 col-md-6">
                            <label for="id_area_abastecimiento" class="form-label fw-bold">
                                <i class="fa fa-truck me-1"></i>Área de Abastecimiento:
                            </label>
                            <select name="id_area_abastecimiento" id="id_area_abastecimiento" class="form-control">
                                <option value="">-- Seleccionar (opcional) --</option>
                                {{-- @foreach recorre las subáreas de abastecimiento disponibles. --}}
                                @foreach($areasAbastecimiento as $area)
                                    <option value="{{ $area->id_area_abastecimiento }}"
                                        {{ old('id_area_abastecimiento') == $area->id_area_abastecimiento ? 'selected' : '' }}>
                                        {{ $area->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Motivo de Devolución --}}
                        <div class="col-12">
                            <label for="id_motivo" class="form-label fw-bold">
                                <i class="fa fa-exclamation-circle me-1"></i>Motivo de Devolución: <span class="text-danger">*</span>
                            </label>
                            {{-- @error comprueba si falló la validación del motivo para asignarle estilos visuales de error. --}}
                            <select name="id_motivo" id="id_motivo"
                                    class="form-control @error('id_motivo') is-invalid @enderror" required>
                                <option value="">-- Seleccionar motivo --</option>
                                {{-- @foreach recorre los motivos catalogados de devolución de insumos. --}}
                                @foreach($motivos as $motivo)
                                    <option value="{{ $motivo->id_motivo }}"
                                        {{ old('id_motivo') == $motivo->id_motivo ? 'selected' : '' }}>
                                        {{ $motivo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_motivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">El motivo de devolución es obligatorio.</div>
                            @enderror
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnCrearDevolucion" class="btn btn-primary">
                        <i class="fa fa-plus me-1"></i>Crear Devolución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- $errors->any() evalúa si hay errores de validación de formulario activos para reabrir el modal automáticamente al recargar. --}}
@if($errors->any())
    <script>
        // DOMContentLoaded asegura la inicialización segura de bootstrap.Modal.
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('modalNuevaDevolucion'));
            myModal.show();
        });
    </script>
@endif
@endsection

{{-- @push agrega los recursos CSS/JS finales a la pila de scripts en la plantilla padre. --}}
@push('scripts')
    {{-- @vite compila y añade el archivo de estilos y el script JS interactivo para gestionar la lógica de devoluciones. --}}
    @vite(['resources/css/inventario/devoluciones/devoluciones.css', 'resources/js/inventario/devoluciones/devoluciones.js'])
@endpush
