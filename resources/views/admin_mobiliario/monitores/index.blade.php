@extends('layouts.app')

@section('title', 'Mobiliario y Equipo: Monitores - Hospital General')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-television text-primary me-2"></i>Monitores (Mobiliario y Equipo)
            </h1>
            <p class="text-muted mb-0">Control de pantallas, monitores, especificaciones técnicas y asignaciones</p>
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
                        <h6 class="fw-bold mb-1 text-dark">Panel de Control de Monitores</h6>
                        <p class="text-muted small mb-0">Módulo administrativo para registrar especificaciones técnicas de monitores. Seleccione un número de inventario de mobiliario registrado de tipo Monitor para asignarle sus especificaciones.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                    {{-- Registrar Nuevo Monitor --}}
                    <button type="button" class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#modalCargaMonitor">
                        <i class="fa fa-plus-circle me-2"></i> Registrar Monitor
                    </button>

                    {{-- Reportes --}}
                    <a href="{{ route('monitores.reportes') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap" id="btnImprimirReporte">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes
                    </a>

                    {{-- Gráficas --}}
                    <a href="{{ route('monitores.graficas') }}" class="btn btn-outline-success px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-bar-chart me-2 text-success"></i> Gráficas
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-list-ul text-secondary me-2"></i>Lista de Monitores
                            </h5>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fw-bold" id="totalArchivos">
                                {{ $monitores->total() }} Registros
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
                                <input type="search" id="global-search" class="form-control bg-light border-0" placeholder="Buscar monitor..." style="font-size: 0.85rem; box-shadow: none;">
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
                                    <th>Marca / Modelo</th>
                                    <th>Tipo</th>
                                    <th>No. de Serie</th>
                                    <th>Descripción</th>
                                    <th>Área</th>
                                    <th>Persona Responsable</th>
                                    <th class="text-center pe-4" style="width: 100px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyArchivos">
                                {{-- Carga inicial del servidor --}}
                                @include('admin_mobiliario.monitores.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $monitores->firstItem() ?? 0 }} a {{ $monitores->lastItem() ?? 0 }} de {{ $monitores->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de monitores cargados">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Los botones se mantendrán sincronizados asíncronamente por JS --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Registrar nuevo monitor -->
    <div class="modal fade" id="modalCargaMonitor" tabindex="-1" aria-labelledby="modalCargaMonitorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: #ffffff; border: 2px solid #000000 !important;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalCargaMonitorLabel">
                        <i class="fa fa-edit text-dark me-2"></i>Registra la Información solicitada
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter: brightness(0);"></button>
                </div>
                <form id="formCargaMonitor" action="{{ route('monitores.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="inventario" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-barcode text-dark me-1"></i> Inventario: *
                                </label>
                                <select name="inventario" id="inventario" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="" disabled selected>Seleccione Inventario</option>
                                    @foreach($inventario as $inv)
                                        <option value="{{ $inv }}" {{ old('inventario') == $inv ? 'selected' : '' }}>{{ $inv }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-hashtag text-dark me-1"></i> Serie:
                                </label>
                                <input type="text" name="serie" id="serie" class="form-control border-gray-300 shadow-sm" value="{{ old('serie') }}" placeholder="ghvfghchgcfg">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="marca" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-building-o text-dark me-1"></i> Marca: *
                                </label>
                                <select name="marca" id="marca" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="" disabled selected>Seleccione Marca</option>
                                    @foreach($marcas as $marca)
                                        <option value="{{ $marca }}" {{ old('marca') == $marca ? 'selected' : '' }}>{{ $marca }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-cogs text-dark me-1"></i> Modelo: *
                                </label>
                                <input type="text" name="modelo" id="modelo" class="form-control border-gray-300 shadow-sm" value="{{ old('modelo') }}" placeholder="ghfghcfghfghf" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="descripcion" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-align-left text-dark me-1"></i> Descripcion:
                                </label>
                                <input type="text" name="descripcion" id="descripcion" class="form-control border-gray-300 shadow-sm" value="{{ old('descripcion') }}" placeholder="yhdfgxdfgdsfg">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="tipo" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-television text-dark me-1"></i> Tipo: *
                                </label>
                                <select name="tipo" id="tipo" class="form-select border-gray-300 shadow-sm" required>
                                    <option value="" disabled selected>Seleccione Tipo</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo }}" {{ old('tipo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light py-2 rounded-pill shadow-sm" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2 text-dark"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="btn btn-primary py-2 rounded-pill shadow-sm text-white" style="border: 1.5px solid #000; background-color: #2b6cb0;">
                            <i class="fa fa-save me-2 text-white"></i>Guardar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/monitores/monitores.css', 'resources/js/monitores/monitores.js'])
@endsection
