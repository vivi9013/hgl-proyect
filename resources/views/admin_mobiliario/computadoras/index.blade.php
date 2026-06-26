@extends('layouts.app')

@section('title', 'Mobiliario y Equipo: Computadoras - Hospital General')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-desktop text-primary me-2"></i>Computadoras (Mobiliario y Equipo)
            </h1>
            <p class="text-muted mb-0">Control de equipos de cómputo, especificaciones técnicas y asignaciones</p>
        </div> 
    </div>

    {{-- Información de módulo y Submódulos ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light p-3 rounded-circle text-primary">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Panel de Control de Computadoras</h6>
                        <p class="text-muted small mb-0">Módulo administrativo para registrar equipos de cómputo. Al registrar un equipo, se guarda de forma sincronizada en Mobiliario y en Computadoras.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Registrar Nueva Computadora --}}
                    <button type="button" class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#modalCargaComputadora">
                        <i class="fa fa-plus-circle me-2"></i> Registrar Computadora
                    </button>

                    {{-- Reportes --}}
                    <a href="{{ route('computadoras.imprimir', request()->query()) }}" target="_blank" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" id="btnImprimirReporte">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Notificaciones Flash --}}
    @if(session('exitog'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle me-2 fs-4"></i>
                <div>
                    <strong>¡Operación Satisfactoria!</strong> {{ session('exitog') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('exito'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle me-2 fs-4"></i>
                <div>
                    <strong>¡Operación Satisfactoria!</strong> {{ session('exito') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-triangle me-2 fs-4"></i>
                <div>
                    <strong>¡Atención!</strong> Por favor corrige los siguientes errores:
                    <ul class="mb-0 mt-1 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-alert="dismiss" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Equipos de Cómputo
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalArchivos">
                                {{ $computadoras->total() }} Registros
                            </span>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            
                            <select id="filtroCategoria" class="form-select border-gray-300 shadow-sm text-muted" style="min-width: 180px; font-size: 0.85rem;">
                                <option value="Todos">Todas las áreas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->area }}</option>
                                @endforeach
                            </select>

                            <div class="input-group" style="min-width: 240px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                                <input type="search" id="global-search" class="form-control bg-light border-0" placeholder="Buscar equipo..." style="font-size: 0.85rem; box-shadow: none;">
                                <span class="input-group-text bg-light border-0 py-0">
                                    <i class="fa fa-search text-dark"></i>
                                </span>
                            </div>

                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1 sticky-top bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 50px;">#</th>
                                    <th class="text-center" style="width: 80px;">Acciones</th>
                                    <th>Inventario</th>
                                    <th>Nombre del Equipo</th>
                                    <th>Marca / Modelo</th>
                                    <th>S.O. / RAM</th>
                                    <th>Dirección IP</th>
                                    <th>Persona Responsable</th>
                                    <th>Área</th>
                                    <th class="text-center pe-4" style="width: 100px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyArchivos">
                                {{-- Carga inicial del servidor --}}
                                @include('admin_mobiliario.computadoras.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $computadoras->firstItem() ?? 0 }} a {{ $computadoras->lastItem() ?? 0 }} de {{ $computadoras->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de computadoras cargadas">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Los botones se mantendrán sincronizados asíncronamente por JS --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Registrar nueva computadora -->
    <div class="modal fade" id="modalCargaComputadora" tabindex="-1" aria-labelledby="modalCargaComputadoraLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalCargaComputadoraLabel">
                        <i class="fa fa-edit text-dark me-2"></i>Registrar nuevo equipo de cómputo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: brightness(0);"></button>
                </div>
                <form id="formCargaComputadora" action="{{ route('computadoras.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            {{-- Sección 1: Datos Generales (Mobiliario) --}}
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">1. Información Administrativa (Mobiliario)</h6>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="inventario" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-barcode text-dark me-1"></i> No. de Inventario *
                                </label>
                                <input type="text" name="inventario" id="inventario" class="form-control border-gray-300 shadow-sm" value="{{ old('inventario') }}" placeholder="Ej. I1800002200000" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tipo" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-desktop text-dark me-1"></i> Tipo *
                                </label>
                                <select name="tipo" id="tipo" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="CPU" {{ old('tipo') == 'CPU' ? 'selected' : '' }}>CPU (Escritorio)</option>
                                    <option value="Laptop" {{ old('tipo') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="marca" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-building-o text-dark me-1"></i> Marca *
                                </label>
                                <input type="text" name="marca" id="marca" class="form-control border-gray-300 shadow-sm" value="{{ old('marca') }}" placeholder="Ej. HP, Dell" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-cogs text-dark me-1"></i> Modelo *
                                </label>
                                <input type="text" name="modelo" id="modelo" class="form-control border-gray-300 shadow-sm" value="{{ old('modelo') }}" placeholder="Ej. Optiplex 7090" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-hashtag text-dark me-1"></i> No. de Serie
                                </label>
                                <input type="text" name="serie" id="serie" class="form-control border-gray-300 shadow-sm" value="{{ old('serie') }}" placeholder="Número de serie físico">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="id_area" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-hospital-o text-dark me-1"></i> Área *
                                </label>
                                <select name="id_area" id="id_area" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="" disabled selected>Seleccione área</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ old('id_area') == $area->id ? 'selected' : '' }}>{{ $area->area }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="id_departamento" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-building text-dark me-1"></i> Departamento *
                                </label>
                                <select name="id_departamento" id="id_departamento" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="" disabled selected>Seleccione depto.</option>
                                    @foreach($departamentos as $dep)
                                        <option value="{{ $dep->id }}" {{ old('id_departamento') == $dep->id ? 'selected' : '' }}>{{ $dep->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="id_persona" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user text-dark me-1"></i> Responsable *
                                </label>
                                <select name="id_persona" id="id_persona" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="" disabled selected>Seleccione responsable</option>
                                    @foreach($personas as $per)
                                        <option value="{{ $per->id }}" {{ old('id_persona') == $per->id ? 'selected' : '' }}>
                                            {{ $per->nombre }} {{ $per->ap_paterno }} {{ $per->ap_materno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-align-left text-dark me-1"></i> Descripción / Observaciones
                                </label>
                                <textarea name="descripcion" id="descripcion" rows="2" class="form-control border-gray-300 shadow-sm" placeholder="Otros detalles del equipo...">{{ old('descripcion') }}</textarea>
                            </div>

                            {{-- Sección 2: Especificaciones Técnicas (Computadora) --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">2. Especificaciones Técnicas (Computadora)</h6>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="nombre_equipo" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-id-card-o text-dark me-1"></i> Nombre de Equipo
                                </label>
                                <input type="text" name="nombre_equipo" id="nombre_equipo" class="form-control border-gray-300 shadow-sm" value="{{ old('nombre_equipo') }}" placeholder="Ej. SISTEMAS-PC">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="so" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-windows text-dark me-1"></i> Sistema Operativo
                                </label>
                                <select name="so" id="so" class="form-select border-gray-300 shadow-sm">
                                    <option value="Windows 10" {{ old('so') == 'Windows 10' ? 'selected' : '' }}>Windows 10</option>
                                    <option value="Windows 11" {{ old('so') == 'Windows 11' ? 'selected' : '' }}>Windows 11</option>
                                    <option value="Windows 7" {{ old('so') == 'Windows 7' ? 'selected' : '' }}>Windows 7</option>
                                    <option value="Windows XP" {{ old('so') == 'Windows XP' ? 'selected' : '' }}>Windows XP</option>
                                    <option value="Linux / Unix" {{ old('so') == 'Linux / Unix' ? 'selected' : '' }}>Linux / Unix</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="ip" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-globe text-dark me-1"></i> Dirección IP
                                </label>
                                <input type="text" name="ip" id="ip" class="form-control border-gray-300 shadow-sm" value="{{ old('ip') }}" placeholder="Ej. 10.19.36.86">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="ram" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-microchip text-dark me-1"></i> Memoria RAM (MB)
                                </label>
                                <input type="text" name="ram" id="ram" class="form-control border-gray-300 shadow-sm" value="{{ old('ram') }}" placeholder="Ej. 4096">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="disco_duro" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-hdd-o text-dark me-1"></i> Capacidad Disco Duro
                                </label>
                                <input type="text" name="disco_duro" id="disco_duro" class="form-control border-gray-300 shadow-sm" value="{{ old('disco_duro') }}" placeholder="Ej. 240GB SSD">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary py-2 rounded-pill shadow-sm" style="border: 1.5px solid #000;">
                            <i class="fa fa-save me-2"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/computadoras/computadoras.css', 'resources/js/computadoras/computadoras.js'])
@endsection
