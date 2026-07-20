@extends('layouts.app')

@section('title', 'Acceso a Categorías de Archivos')

@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-mensaje="El registro se ha guardado correctamente." style="display:none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-mensaje="El registro se ha actualizado correctamente." style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-permisos-archivos">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-key me-2"></i>Permisos de Archivos
            </h1>
            <p class="text-muted mb-0">Gestión y control de acceso a categorías por trabajador</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                {{-- Cabecera tarjeta --}}
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-users me-2"></i>Lista de Trabajadores del Hospital
                        </h5>
                        <span class="badge bg-primary rounded-pill px-3 py-2" id="totalTrabajadores">
                            {{ $trabajadores->total() }} Registros
                        </span>
                    </div>

                    {{-- ── Panel de filtros ──────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">
                        <x-filtro-buscar id="filtro-buscar" label="Buscar trabajador" placeholder="Nombre o sede..." />
                    </div>
                </div>

                {{-- Tabla de trabajadores --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-secondary">
                            <tr>
                                <th class="ps-4" style="width: 80px;">#</th>
                                <th class="text-center" style="width: 150px;">Asignar</th>
                                <th>Nombre Completo del Trabajador</th>
                                <th class="text-center pe-4" style="width: 220px;">Categorías Asignadas</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTrabajadores">
                            @include('admin_formatos.permisos_archivos.partials.tabla')
                        </tbody>
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $trabajadores->firstItem() ?? 0 }} a {{ $trabajadores->lastItem() ?? 0 }} de {{ $trabajadores->total() }} trabajadores
                    </div>
                    <nav aria-label="Paginación de trabajadores">
                        <div id="contenedorPaginacion">
                            {{ $trabajadores->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

</div>

@push('scripts')
    @vite(['resources/css/trabajador_categorias/permisos.css', 'resources/js/trabajador_categorias/permisos.js'])
@endpush
@endsection