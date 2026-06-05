@extends('layouts.app')

@section('title', 'Acceso a Categorías de Archivos')

@section('content')
<div class="container-fluid py-4">

    {{-- ── 1. Encabezado del Módulo (Patrón unificado del Hospital) ──────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-key text-primary me-2"></i>Permisos de Archivos
            </h1>
            <p class="text-muted mb-0">Gestión y control de acceso a categorías por trabajador</p>
        </div>
    </div>

    {{-- ── 2. Navegación Interna y Contenedor con los Submódulos --> ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-8">
           <div class="card border-0 shadow-sm p-4 rounded-3 bg-white h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-light p-3 rounded-circle text-primary">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Panel de Control de Repositorio</h6>
                        <p class="text-muted small mb-0">Modulo para asignar, editar y revisar permisos de categorias para los trabajadores </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contenedor con los Submódulos -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-md-end">
                    <a href="{{ route('categoria_archivos.reportes') }}"
                       class="btn btn-sm btn-outline-secondary px-4 py-2 rounded-pill shadow-sm">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes 
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Alertas de Operación (SweetAlert2 o Notificaciones) ──────── --}}
    @if(session('exitog'))
        <div id="alertaExitog" data-mensaje="El registro se ha guardado correctamente."></div>
    @endif
    @if(session('exito'))
        <div id="alertaExito" data-mensaje="El registro se ha actualizado correctamente."></div>
    @endif

    {{-- ── 4. Listado de Trabajadores y Permisos Asignados ────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-users text-secondary me-2"></i>Lista de Trabajadores del Hospital
                            </h5>
                            <span class="badge bg-primary rounded-pill px-4 py-2 shadow-sm fw-bold" id="totalTrabajadores">
                                {{ $trabajadores->total() }} Registros
                            </span>
                        </div>
                        
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            <div class="input-group" style="min-width: 240px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                                <input type="search" id="global-search" class="form-control bg-light border-0" placeholder="Buscar trabajador..." style="font-size: 0.85rem; box-shadow: none;">
                                <span class="input-group-text bg-light border-0 py-0">
                                    <i class="fa fa-search text-dark"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaTrabajadoresPermisos" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">#</th>
                                    <th class="text-center" style="width: 150px;">Asignar</th>
                                    <th>Nombre Completo del Trabajador</th>
                                    <th class="text-center pe-4" style="width: 220px;">Categorías Asignadas</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTrabajadores">
                                {{-- Carga inicial del servidor --}}
                                @include('permisos_archivos.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── 5. Footer con Controles de Paginación Asíncrona (10 registros) ── --}}
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $trabajadores->firstItem() ?? 0 }} a {{ $trabajadores->lastItem() ?? 0 }} de {{ $trabajadores->total() }} trabajadores
                    </div>
                    <nav aria-label="Paginacion de trabajadores">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{-- Los botones se sincronizan asíncronamente por JS --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

</div>

@vite(['resources/css/trabajador_categorias/permisos.css', 'resources/js/trabajador_categorias/permisos.js'])
@endsection