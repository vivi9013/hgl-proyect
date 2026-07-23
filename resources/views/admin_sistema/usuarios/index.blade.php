@extends('layouts.app')

@section('title', 'Usuarios - Hospital General')

@section('content')
{{-- Alertas de Sesión renderizadas por SweetAlert2 desde usuarios.js --}}
@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display: none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif

<div class="container-fluid py-4 usuarios-index">
    {{-- Encabezado del Módulo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-user-plus text-primary me-2"></i>Catálogo de Usuarios
            </h1>
            <p class="text-muted mb-0">Gestione los usuarios de acceso al sistema y sus temas preferidos</p>
        </div> 
    </div>

    {{-- Área Principal: Tabla de Registros --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Lista de Usuarios
                        </h5>
                    </div>

                    {{-- ── Panel de filtros ────────────────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">
                        <x-filtro-buscar id="filtro-buscar" label="Buscar usuario" placeholder="Nombre, usuario o perfil..." clase="col-12 col-md-4" />
                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registrar, Reportes, Gráficas) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalAltaUsuario">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Nuevo Usuario
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('usuarios.graficas') }}"
                               class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-bar-chart me-1"></i>Gráficas
                            </a>
                            <a href="{{ route('usuarios.reportes') }}"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Reportes
                            </a>
                        </div>
                    </div>
                </div>
                
                {{-- Contenedor de la Tabla Asíncrona --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 60px;">#</th>
                                <th class="text-center" style="width: 90px;">Editar</th>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Perfil</th>
                                <th class="text-center" style="width: 100px;">Reiniciar</th>
                                <th class="text-center pe-4" style="width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaUsuarios">
                            @include('admin_sistema.usuarios.partials.tabla')
                        </tbody>
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionUsuarios">
                        Mostrando {{ $usuarios->firstItem() ?? 0 }} a {{ $usuarios->lastItem() ?? 0 }} de {{ $usuarios->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de usuarios">
                        <div id="paginacionUsuarios">
                            {{ $usuarios->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal: Registrar Nuevo Usuario --}}
    <div class="modal fade" id="modalAltaUsuario" tabindex="-1" aria-labelledby="modalAltaUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalAltaUsuarioLabel">
                        <i class="fa fa-edit text-dark me-2"></i>Registrar Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0);"></button>
                </div>
                
                <form id="formAltaUsuario" action="{{ route('usuarios.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        
                        <div class="row g-3">
                            {{-- Seleccionar Persona --}}
                            <div class="col-12 col-md-6">
                                <label for="persona" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user me-1 text-dark"></i> Selecciona el trabajador:
                                </label>
                                <select name="persona" id="persona" class="form-select border-gray-300 shadow-sm @error('persona') is-invalid @enderror" required>
                                    <option value="" disabled selected>-- Seleccione un trabajador sin usuario --</option>
                                    @foreach($personasSinUsuario as $p)
                                        <option value="{{ $p->id }}">{{ $p->ap_paterno }} {{ $p->ap_materno }} {{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('persona')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Nombre de usuario --}}
                            <div class="col-12 col-md-6">
                                <label for="username" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user-circle-o me-1 text-dark"></i> Nombre de usuario:
                                </label>
                                <input type="text" name="nombre" id="username" 
                                       class="form-control border-gray-300 shadow-sm @error('nombre') is-invalid @enderror" 
                                       value="{{ old('nombre') }}"
                                       placeholder="Coloque el nombre de usuario" 
                                       required>
                                <div id="feedbackDisponibilidad" class="mt-1 small fw-semibold"></div>
                                <div id="loadingSpinner" class="spinner-border spinner-border-sm text-primary mt-1" role="status" style="display: none;">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Seleccionar Perfil --}}
                            <div class="col-12 col-md-6">
                                <label for="perfil" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-users me-1 text-dark"></i> Selecciona el perfil:
                                </label>
                                <select name="perfil" id="perfil" class="form-select border-gray-300 shadow-sm @error('perfil') is-invalid @enderror" required>
                                    <option value="" disabled selected>-- Seleccione un perfil --</option>
                                    @foreach($perfiles as $perf)
                                        <option value="{{ $perf->id }}">{{ $perf->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('perfil')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Seleccionar Tema --}}
                            <div class="col-12 col-md-6">
                                <label for="tema" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-palette me-1 text-dark"></i> Tema de pantalla:
                                </label>
                                <select name="tema" id="tema" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="green" selected>Verde</option>
                                    <option value="black">Negro</option>
                                    <option value="black-light">Negro ligero</option>
                                    <option value="blue">Azul</option>
                                    <option value="blue-light">Azul ligero</option>
                                    <option value="green-light">Verde ligero</option>
                                    <option value="yellow">Amarillo</option>
                                    <option value="yellow-light">Amarillo ligero</option>
                                    <option value="red">Rojo</option>
                                    <option value="red-light">Rojo ligero</option>
                                    <option value="purple">Morado</option>
                                    <option value="purple-light">Morado ligero</option>
                                    <option value="pink">Rosa</option>
                                    <option value="pink-light">Rosa ligero</option>
                                </select>
                            </div>

                            {{-- Contraseña --}}
                            <div class="col-12 col-md-6">
                                <label for="pass" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-key me-1 text-dark"></i> Contraseña:
                                </label>
                                <input type="password" name="pass" id="pass" 
                                       class="form-control border-gray-300 shadow-sm @error('pass') is-invalid @enderror" 
                                       value="{{ $defaultPassword }}"
                                       placeholder="Coloque la contraseña" 
                                       required>
                                @error('pass')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Repetir Contraseña --}}
                            <div class="col-12 col-md-6">
                                <label for="repass" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-key me-1 text-dark"></i> Repetir contraseña:
                                </label>
                                <input type="password" name="repass" id="repass" 
                                       class="form-control border-gray-300 shadow-sm @error('repass') is-invalid @enderror" 
                                       value="{{ $defaultPassword }}"
                                       placeholder="Repita la contraseña" 
                                       required>
                                @error('repass')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/usuarios/usuarios.css', 'resources/js/usuarios/usuarios.js'])
@endsection