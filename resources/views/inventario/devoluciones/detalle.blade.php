{{-- @extends indica la herencia de la plantilla de diseño común de la aplicación. --}}
{{-- Utiliza layouts/app.blade.php para proporcionar la estructura común (menús, barra lateral). --}}
{{-- Inyecta los componentes en el layout general de la plataforma. --}}
@extends('layouts.app')

{{-- @section inyecta el título dinámico de la página concatenando "Detalle de Devolución" con el folio formateado. --}}
{{-- str_pad() ajusta el ID numérico de la devolución a una longitud de 5 caracteres rellenados con ceros a la izquierda. --}}
@section('title', 'Detalle de Devolución – DEV-' . str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT))

{{-- @section y @endsection definen la sección principal del contenido dinámico de la vista. --}}
{{-- Inserta todo el HTML del detalle de la devolución en el yield('content') de la plantilla base. --}}
@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            {{-- route('devoluciones.index') genera el enlace URL dinámico para retornar al listado de devoluciones pendientes. --}}
            <a href="{{ route('devoluciones.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fa fa-arrow-left me-1"></i>Regresar a Pendientes
            </a>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-undo text-primary me-2"></i>
                {{-- str_pad() formatea el ID de la devolución a 5 dígitos rellenando con ceros a la izquierda para mostrar el folio. --}}
                Devolución: <span style="color: #1d4ed8;">DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}</span>
            </h1>
            <p class="text-muted mb-0">
                Área de Almacén: <strong>{{ $devolucion->areaAlmacen->nombre ?? '—' }}</strong>
                &nbsp;|&nbsp;
                {{-- Carbon::parse() convierte el string de la fecha de devolución en un objeto Carbon para aplicarle format('d/m/Y'). --}}
                Fecha: <strong>{{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : '—' }}</strong>
                &nbsp;|&nbsp;
                Motivo: <strong>{{ $devolucion->motivo->descripcion ?? '—' }}</strong>
                &nbsp;|&nbsp;
                {{-- El operador ternario condicional aplica la clase de Bootstrap 'bg-warning text-dark' si está en proceso. --}}
                {{-- Muestra un badge visual de estilo verde si el status ha sido finalizado. --}}
                <span class="badge {{ $devolucion->status === 'En proceso' ? 'bg-warning text-dark' : 'bg-success' }}">
                    {{ $devolucion->status }}
                </span>
            </p>
        </div>
        {{-- @if evalúa si el status de la devolución está en proceso para habilitar los controles de edición y finalización. --}}
        @if($devolucion->status === 'En proceso')
            <div class="d-flex gap-2 align-items-center">
                {{-- Formulario para disparar el flujo de guardado y finalización del estado de la devolución. --}}
                <form method="POST" action="{{ route('devoluciones.finalizar', $devolucion->id_devolucion) }}" id="formFinalizar">
                    @csrf
                    <button type="button" id="btnFinalizar" class="btn btn-success rounded-pill shadow-sm"
                            style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                        <i class="fa fa-check-circle me-1"></i>Finalizar Devolución
                    </button>
                </form>
            </div>
        @elseif($devolucion->status === 'Terminado')
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('devoluciones.comprobante', $devolucion->id_devolucion) }}" target="_blank" class="btn btn-outline-primary rounded-pill shadow-sm"
                   style="font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.4rem;">
                    <i class="fa fa-print me-1"></i>Imprimir Comprobante
                </a>
            </div>
        @endif
    </div>

    <hr class="my-3" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas ── --}}
    {{-- @if evalúa la presencia de notificaciones de éxito o error en la sesión flash para almacenarlas en variables data-message de HTML. --}}
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

    <div class="row g-4">

        {{-- ── Panel izquierdo: Agregar insumo ── --}}
        {{-- @if restringe la visualización del panel de adición de insumos si el status es diferente a "En proceso". --}}
        @if($devolucion->status === 'En proceso')
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa fa-plus-circle me-2"></i>Agregar Insumo
                    </h6>
                </div>
                <div class="card-body p-4">
                    {{-- route('detalle_devoluciones.store') calcula la URL del controlador para insertar renglones de insumos. --}}
                    <form method="POST" action="{{ route('detalle_devoluciones.store') }}" novalidate id="formAgregarInsumo">
                        @csrf
                        {{-- Input hidden que vincula este detalle a la devolución principal mediante su ID. --}}
                        <input type="hidden" name="id_devolucion" value="{{ $devolucion->id_devolucion }}">

                        {{-- Buscador de insumo --}}
                        <div class="mb-3 position-relative">
                            <label for="buscarInsumoDetalle" class="form-label fw-bold small">
                                Insumo (clave o descripción): <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="buscarInsumoDetalle"
                                   class="form-control"
                                   placeholder="Buscar insumo… (doble clic para ver claves)"
                                   autocomplete="off">
                            {{-- Input oculto necesario para enviar el identificador único del insumo seleccionado al backend. --}}
                            <input type="hidden" name="id_insumo" id="id_insumo_detalle">
                            <input type="hidden" id="modal_id_area_almacen_detalle" value="{{ $devolucion->id_area_almacen }}">
                            {{-- Contenedor flotante que el script de JavaScript poblará con sugerencias AJAX al escribir en el input. --}}
                            <div id="sugerenciasDetalle" class="list-group position-absolute w-100"
                                 style="z-index:1060; display:none; max-height:200px; overflow-y:auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            </div>
                            <x-panel-claves :input-id="'buscarInsumoDetalle'" :panel-id="'panelClavesDetalle'" :endpoint="'/devoluciones/buscar-insumos'" :columna-extra="'stock'" :area-input-id="'modal_id_area_almacen_detalle'" />
                            @error('id_insumo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Cantidad --}}
                        <div class="mb-3">
                            <label for="cantidad_detalle" class="form-label fw-bold small">
                                Cantidad: <span class="text-danger">*</span>
                            </label>
                            {{-- Input numérico. @error asigna la clase is-invalid en caso de error. old('cantidad') restaura el dato enviado. --}}
                            <input type="number"
                                   name="cantidad"
                                   id="cantidad_detalle"
                                   class="form-control @error('cantidad') is-invalid @enderror"
                                   placeholder="Ej. 5"
                                   min="1"
                                   value="{{ old('cantidad') }}"
                                   required>
                            @error('cantidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fecha de Caducidad --}}
                        <div class="mb-3">
                            <label for="fecha_caducidad_detalle" class="form-label fw-bold small">
                                Fecha de Caducidad: <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <input type="date"
                                   name="fecha_caducidad"
                                   id="fecha_caducidad_detalle"
                                   class="form-control @error('fecha_caducidad') is-invalid @enderror"
                                   value="{{ old('fecha_caducidad') }}">
                            @error('fecha_caducidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-plus me-1"></i>Agregar Insumo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Panel derecho: Lista de insumos ── --}}
        {{-- El operador ternario condicional ajusta el ancho de la columna a col-lg-8 si el panel de inserción está activo, o col-12 completo si ya fue finalizado. --}}
        <div class="col-12 {{ $devolucion->status === 'En proceso' ? 'col-lg-8' : '' }}">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 pb-2 px-4 d-flex align-items-center gap-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa fa-list text-secondary me-2"></i>Insumos en esta devolución
                    </h6>
                    {{-- count() obtiene de manera dinámica el número total de insumos agregados. --}}
                    <span class="rounded-pill px-3 py-1 fw-bold" style="background-color: #e9ecef; font-size: 0.78rem;">
                        {{ $devolucion->detalles->count() }} {{ $devolucion->detalles->count() === 1 ? 'insumo' : 'insumos' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaDetalles">
                            <thead class="table-light text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Caducidad</th>
                                    @if($devolucion->status === 'En proceso')
                                        <th class="text-center pe-4">Acción</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @forelse es la directiva Blade para iterar sobre los detalles e incluye la sección @empty si está vacío. --}}
                                @forelse($devolucion->detalles as $index => $detalle)
                                    <tr data-id="{{ $detalle->id_detalle_devolucion }}">
                                        {{-- $loop->iteration entrega el conteo secuencial correlativo de la fila en pantalla. --}}
                                        <td class="ps-4 fw-bold">{{ $loop->iteration }}</td>
                                        <td>
                                            <span style="font-family: Arial, sans-serif; font-size: 0.8rem; font-weight: 600; color: #374151; background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px 8px; display: inline-block;">
                                                {{ $detalle->insumo->clave ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $detalle->insumo->descripcion ?? '—' }}</td>
                                        <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                                        <td class="text-center">
                                            {{ $detalle->fecha_caducidad ? \Carbon\Carbon::parse($detalle->fecha_caducidad)->format('d/m/Y') : '—' }}
                                        </td>
                                        @if($devolucion->status === 'En proceso')
                                            <td class="text-center pe-4">
                                                {{-- Botón de eliminación del detalle. Los atributos data-* envían metadatos que JS lee para confirmar la acción vía AJAX. --}}
                                                {{-- route('detalle_devoluciones.destroy') provee el endpoint para mandar el método DELETE en el controlador. --}}
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-eliminar-detalle"
                                                        data-id="{{ $detalle->id_detalle_devolucion }}"
                                                        data-insumo="{{ $detalle->insumo->descripcion ?? 'Insumo' }}"
                                                        data-url="{{ route('detalle_devoluciones.destroy', $detalle->id_detalle_devolucion) }}"
                                                        title="Eliminar insumo de la devolución">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                {{-- @empty se activa si la relación detalles de la devolución no contiene elementos vinculados. --}}
                                @empty
                                    <tr id="filaVacia">
                                        {{-- El operador condicional ternario expande la celda a 6 columnas si se puede editar, o 5 si ya está cerrado. --}}
                                        <td colspan="{{ $devolucion->status === 'En proceso' ? 6 : 5 }}" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay insumos agregados todavía.
                                            @if($devolucion->status === 'En proceso')
                                                Utilice el formulario de la izquierda para agregar.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Formulario oculto para finalizar (CSRF) --}}
{{-- Se utiliza para enviar de forma segura la confirmación mediante método POST a través del disparador JavaScript. --}}
<form method="POST" id="formFinalizarHidden" action="{{ route('devoluciones.finalizar', $devolucion->id_devolucion) }}" style="display:none;">
    @csrf
</form>

@endsection

{{-- @push agrega los recursos CSS/JS finales a la pila de scripts en la plantilla padre. --}}
@push('scripts')
    {{-- @vite compila y añade el archivo de estilos y el script JS interactivo para gestionar la lógica de devoluciones. --}}
    @vite(['resources/css/inventario/devoluciones/devoluciones.css', 'resources/js/inventario/devoluciones/devoluciones.js'])
@endpush
