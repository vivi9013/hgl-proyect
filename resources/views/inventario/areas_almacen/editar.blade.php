{{-- @extends es la directiva de Blade para heredar una plantilla base de diseño. --}}
{{-- Busca el archivo layouts/app.blade.php y lo toma como la estructura HTML principal de la página. --}}
{{-- En este caso, hereda el diseño del panel de control que incluye menús y barras de navegación. --}}
@extends('layouts.app')

{{-- @section define un bloque de contenido estático o dinámico para la plantilla padre. --}}
{{-- Envía un string simple al marcador de posición @yield('title') de la plantilla base. --}}
{{-- En este caso, establece el título específico de la pestaña del navegador para este módulo. --}}
@section('title', 'Editar Área de Almacén')

{{-- @section con cierre @endsection define una sección de contenido dinámico más extensa. --}}
{{-- Agrupa todo el código HTML/Blade del formulario de edición para insertarlo en @yield('content'). --}}
{{-- Permite estructurar la interfaz del formulario dentro del diseño general de la aplicación. --}}
@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-archive text-primary me-2"></i>Áreas de Almacén
            </h1>
            <p class="text-muted mb-0">Actualización / Edición</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    {{-- route() es una función auxiliar de Laravel para generar URLs a partir de nombres de rutas. --}}
                    {{-- Retorna la URL absoluta de la ruta nombrada 'inicio' registrada en la web. --}}
                    {{-- Permite crear un enlace seguro y dinámico que vuelve al panel principal. --}}
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    {{-- route() genera dinámicamente la URL absoluta de la ruta 'areas_almacen.index'. --}}
                    {{-- Facilita la navegación al listado general de áreas de almacén. --}}
                    <a href="{{ route('areas_almacen.index') }}">Áreas de Almacén</a>
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
                {{-- route() genera la URL de destino del formulario pasando el parámetro requerido. --}}
                {{-- Obtiene el ID del área ($area->id_area_almacen) para generar la ruta correcta areas_almacen.update. --}}
                {{-- Enruta los datos modificados al controlador adecuado para realizar la actualización. --}}
                <form action="{{ route('areas_almacen.update', $area->id_area_almacen) }}" method="POST" novalidate>
                    {{-- @csrf genera un input hidden con el token CSRF generado por Laravel. --}}
                    {{-- Protege los formularios del sitio web contra ataques de falsificación de petición en sitios cruzados. --}}
                    {{-- Valida que la petición POST provenga genuinamente de la sesión de este usuario. --}}
                    @csrf
                    {{-- @method('PUT') inyecta un campo oculto con valor PUT en el formulario. --}}
                    {{-- Realiza spoofing o simulación de método HTTP para simular una petición PUT. --}}
                    {{-- Permite que la petición sea recibida por el método update del controlador de recursos. --}}
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label for="nombre" class="form-label fw-semibold text-secondary">Nombre del área de almacén:</label>
                            {{-- @error comprueba si hay un error de validación para el campo 'nombre' en el contenedor de errores. --}}
                            {{-- Inserta condicionalmente la clase de Bootstrap 'is-invalid' si la validación del nombre falló. --}}
                            {{-- Permite aplicar estilos de borde rojo si hay algún error de entrada. --}}
                            {{-- old() busca el valor enviado anteriormente del campo 'nombre' en la sesión flash. --}}
                            {{-- Muestra el valor de la base de datos si es la primera carga, o el valor corregido si falló la validación. --}}
                            {{-- Evita que el usuario pierda lo que escribió tras una validación fallida. --}}
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre', $area->nombre) }}"
                                placeholder="Ej. Almacén General, Farmacia Central..."
                                autocomplete="off"
                                maxlength="255"
                                autofocus
                                required
                            >
                            {{-- @error captura los errores del campo 'nombre' y nos provee la variable local $message. --}}
                            {{-- Renderiza el contenedor del mensaje de error únicamente si la validación del campo falló. --}}
                            {{-- Muestra la descripción textual detallada del error de validación bajo el input. --}}
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('areas_almacen.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
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
{{-- @endsection finaliza el bloque de la sección 'content'. --}}
{{-- Delimita el final del código de la vista que se inyectará en la plantilla padre. --}}
@endsection

{{-- @push agrega el contenido especificado al final de la pila 'scripts' definida en el diseño padre. --}}
{{-- Permite apilar selectivamente archivos JS, CSS o etiquetas script adicionales para esta vista. --}}
{{-- Agrega en este caso los estilos específicos de administración de áreas. --}}
@push('scripts')
    {{-- @vite invoca el bundle loader de Laravel Vite para procesar assets. --}}
    {{-- Compila y carga el archivo CSS específico de áreas de almacén para aplicarlo en esta página. --}}
    {{-- Optimiza la carga del archivo utilizando módulos ESM en desarrollo y bundles minificados en producción. --}}
    @vite(['resources/css/inventario/areas_almacen/areas.css'])
@endpush
