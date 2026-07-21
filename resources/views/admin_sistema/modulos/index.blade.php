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

{{-- ================================================================
     MODAL: Registrar Nuevo Módulo
     ================================================================ --}}
<div class="modal fade" id="modalAltaModulo" tabindex="-1"
     aria-labelledby="modalAltaModuloLabel" aria-hidden="true"
     @if($errors->any()) data-auto-open="true" @endif>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">

            {{-- Cabecera --}}
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark mb-0" id="modalAltaModuloLabel">
                    <i class="fa fa-edit text-dark me-2"></i>Registra la Información solicitada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- Cuerpo --}}
            <div class="modal-body px-4 py-4">
                <form id="formularioAltaModulo" action="{{ route('modulos.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="row g-3">
                        {{-- Row 1: Nombre, Carpeta, Categoría --}}
                        <div class="col-12 col-md-4">
                            <label for="nombre" class="form-label fw-bold text-secondary">Nombre:</label>
                            <input type="text" name="nombre" id="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre') }}"
                                   placeholder="Coloca el nombre del módulo" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="carpeta" class="form-label fw-bold text-secondary">Carpeta:</label>
                            <input type="text" name="carpeta" id="carpeta"
                                   class="form-control @error('carpeta') is-invalid @enderror"
                                   value="{{ old('carpeta') }}"
                                   placeholder="Coloca el nombre de la carpeta" required>
                            @error('carpeta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="id_CategoriaModulo" class="form-label fw-bold text-secondary">Categoría:</label>
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

                        {{-- Row 2: Color, Icono, Creador --}}
                        <div class="col-12 col-md-4">
                            <label for="color" class="form-label fw-bold text-secondary">Color de Caja:</label>
                            <select name="color" id="color" class="form-select" required>
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
                            <label for="icono" class="form-label fw-bold text-secondary">Icono Font Awesome:</label>
                            <input type="text" name="icono" id="icono"
                                   class="form-control @error('icono') is-invalid @enderror"
                                   value="{{ old('icono', 'fa fa-cube') }}"
                                   placeholder="fa fa-icono" required>
                            @error('icono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="creador" class="form-label fw-bold text-secondary">Creador:</label>
                            <input type="text" name="creador" id="creador"
                                   class="form-control @error('creador') is-invalid @enderror"
                                   value="{{ old('creador') }}"
                                   placeholder="Autor del módulo" required>
                            @error('creador')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Row 3: Descripción + Vista Previa --}}
                        <div class="col-12 col-md-8 d-flex flex-column justify-content-start">
                            <label for="descripcion" class="form-label fw-bold text-secondary">Descripción:</label>
                            <input type="text" name="descripcion" id="descripcion"
                                   class="form-control mb-auto @error('descripcion') is-invalid @enderror"
                                   value="{{ old('descripcion') }}"
                                   placeholder="Descripción general del módulo" required
                                   style="height: calc(100% - 32px); min-height: 50px;">
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

                    {{-- Sección Dinámica: Submódulos de la Categoría Seleccionada --}}
                    <div class="mt-4 d-none" id="contenedorVistaPreviaCategoria">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body bg-light p-3" id="contenidoVistaPreviaCategoria">
                                {{-- Carga dinámica por AJAX desde JS --}}
                            </div>
                        </div>
                    </div>

                    {{-- Botones dentro del modal-body para mantener form cerrado --}}
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardarModulo" class="btn btn-primary py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>{{-- /modal-body --}}

        </div>{{-- /modal-content --}}
    </div>{{-- /modal-dialog --}}
</div>{{-- /#modalAltaModulo --}}


    {{-- Área Principal: Tabla de Registros --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Lista de Módulos
                        </h5>
                    </div>

                    {{-- ── Panel de filtros ────────────────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">
                        <x-filtro-buscar id="filtro-buscar" label="Buscar módulo" placeholder="Módulo o categoría..." clase="col-12 col-md-4" />
                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registrar, Reportes, Gráficas) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalAltaModulo">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Nuevo Módulo
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('modulos.graficas') }}"
                               class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-bar-chart me-1"></i>Gráficas
                            </a>
                            <a href="{{ route('modulos.reportes') }}"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Reportes
                            </a>
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

                {{-- Pie: info + paginación --}}
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionModulos">
                        Mostrando {{ $modulos->firstItem() ?? 0 }} a {{ $modulos->lastItem() ?? 0 }} de {{ $modulos->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de modulos">
                        <div id="paginacionModulos">
                            {{ $modulos->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

 

</div>



{{-- Inyección de activos encapsulada por Vite --}}
@vite(['resources/css/modulos/modulos.css', 'resources/js/modulos/modulos.js'])
@endsection