@extends('layouts.app')

@section('title', 'Módulos del Sistema - Hospital General')

@section('content')
{{-- Alertas de Sesión renderizadas por SweetAlert2 desde modulos.js --}}
@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display: none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-gestion-modulos">
    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-cubes text-primary me-2"></i>Módulos del Sistema
            </h1>
            <p class="text-muted mb-0">Catálogo general de módulos, sus proyectos asociados y perfiles con acceso</p>
        </div>
    </div>

    {{-- Panel Informativo y de Acciones Rápidas --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light p-3 rounded-circle text-primary">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Administración de Módulos</h6>
                        <p class="text-muted small mb-0">Permite registrar módulos del sistema, asignarlos a proyectos y perfiles, configurar su icono, color y categoría.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Botón Alta de Módulo --}}
                    <button type="button"
                            class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap"
                            data-bs-toggle="collapse" data-bs-target="#colapsarAltaModulo"
                            aria-expanded="false" aria-controls="colapsarAltaModulo">
                        <i class="fa fa-plus-circle me-2"></i>Registrar Nuevo Módulo
                    </button>

                    {{-- Submódulo: Reportes --}}
                    <a href="{{ route('modulos.reportes') }}"
                       class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i>Reportes
                    </a>

                    {{-- Submódulo: Gráficas --}}
                    <a href="{{ route('modulos.graficas') }}"
                       class="btn btn-outline-success px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-bar-chart me-2 text-success"></i>Gráficas
                    </a>
                </div>
            </div>
        </div>
    </div>

   {{-- Formulario Colapsable: Registrar Nuevo Módulo (Diseño Legacy Alta) --}}
    <div class="collapse mb-4 @if($errors->any()) show @endif " id="colapsarAltaModulo">
        <div class="card border-0 shadow-sm p-4 rounded-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="fa fa-edit text-dark me-2"></i>Registra la Información solicitada
                </h5>
                <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#colapsarAltaModulo" aria-label="Close"></button>
            </div>

            <form id="formularioAltaModulo" action="{{ route('modulos.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="row g-3">
                    {{-- Row 1: Nombre, Carpeta, Categoría --}}
                    <div class="col-12 col-md-4">
                        <label for="nombre" class="form-label fw-bold text-secondary">
                            Nombre:
                        </label>
                        <input type="text" name="nombre" id="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre') }}"
                               placeholder="Coloca el nombre del módulo" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="carpeta" class="form-label fw-bold text-secondary">
                            Carpeta:
                        </label>
                        <input type="text" name="carpeta" id="carpeta"
                               class="form-control @error('carpeta') is-invalid @enderror"
                               value="{{ old('carpeta') }}"
                               placeholder="Coloca el nombre de la carpeta" required>
                        @error('carpeta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="id_CategoriaModulo" class="form-label fw-bold text-secondary">
                            Categoría:
                        </label>
                        <select name="id_CategoriaModulo" id="id_CategoriaModulo"
                                class="form-select @error('id_CategoriaModulo') is-invalid @enderror"
                                required>
                            <option value="">— Selecciona una categoría —</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_CategoriaModulo }}"
                                    {{ old('id_CategoriaModulo') == $cat->id_CategoriaModulo ? 'selected' : '' }}>
                                    {{ $cat->categoria }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_CategoriaModulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Row 2: Color de Caja, Icono Font Awesome, Creador --}}
                    <div class="col-12 col-md-4">
                        <label for="color" class="form-label fw-bold text-secondary">
                            Color de Caja:
                        </label>
                        <select name="color" id="color"
                                class="form-select" required>
                            @foreach([
                                'red'=>'Rojo','yellow'=>'Amarillo','aqua'=>'Aqua',
                                'blue'=>'Azul','light-blue'=>'Azul Claro','green'=>'Verde',
                                'navy'=>'Militar','teal'=>'Verde Azulado','olive'=>'Verde Olivo',
                                'lime'=>'Lima','orange'=>'Naranja','fuchsia'=>'Fucsia',
                                'purple'=>'Morado','maroon'=>'Granada','black'=>'Negro'
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ old('color') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="icono" class="form-label fw-bold text-secondary">
                            Icono Font Awesome:
                        </label>
                        <input type="text" name="icono" id="icono"
                               class="form-control @error('icono') is-invalid @enderror"
                               value="{{ old('icono', 'fa fa-cube') }}"
                               placeholder="fa fa-icono" required>
                        @error('icono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="creador" class="form-label fw-bold text-secondary">
                            Creador:
                        </label>
                        <input type="text" name="creador" id="creador"
                               class="form-control @error('creador') is-invalid @enderror"
                               value="{{ old('creador') }}"
                               placeholder="Autor del módulo" required>
                        @error('creador')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Row 3: Descripción (izquierda) & Vista Previa Card (derecha) --}}
                    <div class="col-12 col-md-8 d-flex flex-column justify-content-start">
                        <label for="descripcion" class="form-label fw-bold text-secondary">
                            Descripción:
                        </label>
                        <input type="text" name="descripcion" id="descripcion"
                               class="form-control mb-auto @error('descripcion') is-invalid @enderror"
                               value="{{ old('descripcion') }}"
                               placeholder="Descripción general del módulo" required style="height: calc(100% - 32px); min-height: 50px;">
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4 d-flex align-items-end justify-content-center">
                        <div class="small-box bg-red w-100 mb-0" id="vistaPreviaTarjeta" style="min-height: 100px;">
                            <div class="inner" style="padding: 10px 10px 0 10px;">
                                <h3>&nbsp;</h3>
                                <span class="progress-description fw-bold" id="vistaPreviaNombre">Nombre del módulo</span>
                            </div>
                            <div class="icon" style="top: -5px; right: 10px; font-size: 65px;">
                                <i class="fa fa-cube" id="vistaPreviaIcono"></i>
                            </div>
                            <a href="#" class="small-box-footer py-2" style="margin-top: 10px;">
                                Ingresar al Módulo <i class="fa fa-arrow-circle-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Row 4: Botones Guardar/Cancelar --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-toggle="collapse" data-bs-target="#colapsarAltaModulo">
                        <i class="fa fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" id="btnGuardarModulo" class="btn btn-primary py-2 rounded-pill shadow-sm">
                        <i class="fa fa-save me-2"></i>Guardar Información
                    </button>
                </div>
            </form>

            {{-- Sección Dinámica: Submódulos de la Categoría Seleccionada --}}
            <div class="mt-4 d-none" id="contenedorVistaPreviaCategoria">
                <div class="card border-0 shadow-sm">
                    <div class="card-body bg-light p-3" id="contenidoVistaPreviaCategoria">
                        <!-- Carga dinámica por AJAX desde JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Área Principal: Tabla de Registros --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Módulos
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalModulos">
                                {{ $modulos->total() }} Registros
                            </span>
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            {{-- Buscador Reactivo --}}
                            <div class="input-group" style="min-width: 260px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                                <input type="search" id="busqueda-global" class="form-control bg-light border-0"
                                       placeholder="Buscar módulo o categoría..."
                                       style="font-size: 0.85rem; box-shadow: none;">
                                <span class="input-group-text bg-light border-0 py-0">
                                    <i class="fa fa-search text-dark"></i>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Contenedor de la Tabla Asíncrona --}}
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1 sticky-top bg-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Agregar proyecto</th>
                                    <th class="text-center">Agregar perfil</th>
                                    <th>Módulo</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Icono</th>
                                    <th>Creador</th>
                                    <th class="text-center">Proyectos</th>
                                    <th class="text-center">Perfiles</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaModulos">
                                {{-- Render inicial desde Servidor usando patrón de fragmentos --}}
                                @include('admin_sistema.modulos.partials.tabla')
                            </tbody>
                            <tfoot class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1 bg-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Agregar proyecto</th>
                                    <th class="text-center">Agregar perfil</th>
                                    <th>Módulo</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Icono</th>
                                    <th>Creador</th>
                                    <th class="text-center">Proyectos</th>
                                    <th class="text-center">Perfiles</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Footer con Paginación --}}
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $modulos->firstItem() ?? 0 }} a {{ $modulos->lastItem() ?? 0 }} de {{ $modulos->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de modulos">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Sincronizado dinámicamente por JS --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

 

</div>



{{-- Inyección de activos encapsulada por Vite --}}
@vite(['resources/css/modulos/modulos.css', 'resources/js/modulos/modulos.js'])
@endsection
