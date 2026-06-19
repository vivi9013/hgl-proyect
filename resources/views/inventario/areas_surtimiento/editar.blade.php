{{-- @extends indica la herencia de plantillas Blade de Laravel. --}}
{{-- Incorpora la estructura HTML general contenida en layouts/app.blade.php. --}}
{{-- En este caso, establece el marco y la barra de navegación para el formulario. --}}
@extends('layouts.app')

{{-- @section inyecta una cadena de texto en un marcador de posición de la plantilla base. --}}
{{-- Pasa el título estático de la página al bloque @yield('title'). --}}
{{-- Modifica la pestaña del navegador para mostrar "Editar Área de Surtimiento". --}}
@section('title', 'Editar Área de Surtimiento')

{{-- @section y @endsection delimitan el bloque que contiene la interfaz principal. --}}
{{-- Envía el HTML completo del formulario al marcador @yield('content') en layouts/app. --}}
{{-- Permite estructurar de manera limpia la interfaz gráfica del formulario. --}}
@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-tags text-primary me-2"></i>Áreas de Surtimiento
            </h1>
            <p class="text-muted mb-0">Actualización / Edición</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    {{-- route() es una función auxiliar de Laravel para obtener la URL de una ruta por su nombre. --}}
                    {{-- Genera la URL absoluta para volver al Panel de Control (ruta 'inicio'). --}}
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    {{-- route() obtiene de manera dinámica la URL de la ruta 'areas_surtimiento.index'. --}}
                    {{-- Permite retornar al listado de áreas de surtimiento. --}}
                    <a href="{{ route('areas_surtimiento.index') }}">Áreas de Surtimiento</a>
                </li>
                <li class="breadcrumb-item active">Edición</li>
            </ol>
        </nav>
    </div>

    {{-- ── Formulario de Edición ── --}}
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="fa fa-pencil-square-o me-2"></i>Actualizar datos del área</h5>
                </div>
                {{-- route() construye la URL de actualización pasando como parámetro el identificador único. --}}
                {{-- Apunta a la ruta 'areas_surtimiento.update' con el ID del área para identificar el registro. --}}
                {{-- Transmite los datos editados al controlador para ejecutar los cambios. --}}
                <form action="{{ route('areas_surtimiento.update', $area->id_area_surtimiento) }}" method="POST" novalidate>
                    {{-- @csrf genera un input hidden con un token único de sesión. --}}
                    {{-- Protege al backend de ataques de Falsificación de Petición en Sitios Cruzados (CSRF). --}}
                    {{-- Asegura que las peticiones POST provengan únicamente de formularios autorizados. --}}
                    @csrf
                    {{-- @method('PUT') simula un método PUT de HTTP inyectando un input oculto llamado _method. --}}
                    {{-- Permite evadir las limitaciones de los navegadores que solo procesan métodos GET y POST. --}}
                    {{-- Dirige la petición a la acción update del controlador de recursos de Laravel. --}}
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label for="nombre" class="form-label fw-semibold text-secondary">Nombre del área de surtimiento:</label>
                            {{-- @error evalúa la presencia de errores en la sesión para el campo 'nombre'. --}}
                            {{-- Agrega dinámicamente la clase 'is-invalid' si la validación del nombre falla. --}}
                            {{-- old() recupera el valor de 'nombre' en el último envío fallido o utiliza el actual ($area->nombre). --}}
                            {{-- Evita que el usuario tenga que volver a escribir el nombre si otro campo falla en la validación. --}}
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre', $area->nombre) }}"
                                placeholder="Ej. Farmacia Interna, ISSSTE, IMSS..."
                                autocomplete="off"
                                maxlength="255"
                                autofocus
                                required
                            >
                            {{-- @error captura los errores del campo 'nombre' y nos expone la variable interna $message. --}}
                            {{-- Renderiza el contenedor del mensaje de error únicamente si la validación del campo falló. --}}
                            {{-- Muestra la descripción textual detallada del error de validación bajo el input. --}}
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="tipo" class="form-label fw-semibold text-secondary">Tipo de área:</label>
                            {{-- @error comprueba si hay fallos de validación en la sesión para el campo 'tipo'. --}}
                            {{-- Asigna condicionalmente la clase CSS de Bootstrap 'is-invalid' en caso de error. --}}
                            <select
                                name="tipo"
                                id="tipo"
                                class="form-control @error('tipo') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Seleccionar --</option>
                                {{-- old('tipo', $area->tipo) recupera la selección previa en la sesión o el valor almacenado en BD. --}}
                                {{-- El operador ternario compara el resultado con 'Interno' e imprime 'selected' para dejar activa la opción correcta. --}}
                                {{-- Garantiza la persistencia de la selección del usuario en el elemento select. --}}
                                <option value="Interno" {{ old('tipo', $area->tipo) == 'Interno' ? 'selected' : '' }}>Interno</option>
                                {{-- El operador ternario evalúa si la opción seleccionada coincide con 'Externo'. --}}
                                {{-- Agrega el atributo 'selected' para marcar visualmente la opción correcta en la lista desplegable. --}}
                                <option value="Externo" {{ old('tipo', $area->tipo) == 'Externo' ? 'selected' : '' }}>Externo</option>
                            </select>
                            {{-- @error expone la variable $message con el detalle del error de validación para el campo 'tipo'. --}}
                            {{-- Renderiza el div 'invalid-feedback' con el mensaje correspondiente si la validación no se cumple. --}}
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('areas_surtimiento.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                            <i class="fa fa-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" id="btnGuardar" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-1"></i> Actualizar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

{{-- @push agrega recursos al final de la pila 'scripts' definida en layouts/app.blade.php. --}}
{{-- Inserta estilos o archivos JavaScript complementarios específicos de la vista. --}}
@push('scripts')
    {{-- @vite invoca el bundle loader de Laravel Vite para compilar y servir assets. --}}
    {{-- Enlaza el archivo de estilos CSS específico para las áreas de surtimiento. --}}
    @vite(['resources/css/inventario/areas_surtimiento/surtimiento.css'])
@endpush
