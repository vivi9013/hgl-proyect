@extends('layouts.app')
@section('title', 'Soporte Técnico – Áreas de Atención')
@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-soporte-area">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-wrench me-2"></i>Soporte Técnico – Áreas
            </h1>
            <p class="text-muted mb-0">Asignación de áreas de atención por técnico</p>
        </div>
    </div>

    {{-- Tabla de trabajadores --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                {{-- Cabecera tarjeta --}}
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-users me-2"></i>Lista de Trabajadores
                        </h5>
                        <div>
                            <a href="{{ route('soporte_area.imprimir') }}" target="_blank"
                               id="btnImprimir"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Imprimir
                            </a>
                        </div>
                    </div>

                    {{-- Panel de filtros --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">

                        {{-- Búsqueda por nombre --}}
                        <x-filtro-buscar id="filtro-buscar" label="Buscar trabajador" placeholder="Nombre, apellido..." clase="col-12 col-md-4" />

                        {{-- Filtro desplegable --}}
                        <x-filtro-dropdown id="dropdownFiltros" label="Filtrar por categoría" titulo="Filtros" labelDefault="Todos los trabajadores" clase="col-12 col-md-5">
                            {{-- Estatus --}}
                            <p class="text-uppercase text-muted small fw-semibold mb-1 mt-1" style="font-size:0.7rem; letter-spacing:.04em;">Estatus</p>
                            <div class="mb-2">
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-estatus" type="checkbox" value="1" id="chkActivo">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkActivo">
                                        <span class="badge bg-success rounded-pill px-2">Activo</span>
                                    </label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-estatus" type="checkbox" value="0" id="chkInactivo">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkInactivo">
                                        <span class="badge bg-secondary rounded-pill px-2">Inactivo</span>
                                    </label>
                                </div>
                            </div>
                            {{-- Áreas asignadas --}}
                            <p class="text-uppercase text-muted small fw-semibold mb-1 mt-2" style="font-size:0.7rem; letter-spacing:.04em;">Áreas asignadas</p>
                            <div class="mb-1">
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-areas" type="checkbox" value="con" id="chkConAreas">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkConAreas">
                                        <span class="badge bg-primary rounded-pill px-2">Con áreas</span>
                                    </label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-areas" type="checkbox" value="sin" id="chkSinAreas">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkSinAreas">
                                        <span class="badge bg-light text-secondary border rounded-pill px-2">Sin áreas</span>
                                    </label>
                                </div>
                            </div>
                        </x-filtro-dropdown>

                    </div>
                    {{-- /panelFiltros --}}
                </div>

                {{-- Tabla --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        @include('soporte_tecnico.ligar_usuario.partials.tabla')
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionSoporte">
                        Mostrando {{ $trabajadores->firstItem() ?? 0 }} a {{ $trabajadores->lastItem() ?? 0 }}
                        de {{ $trabajadores->total() }} registros
                    </div>
                    <nav>
                        <div id="paginacionSoporte">
                            {{ $trabajadores->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

</div>

@push('scripts')
    @vite(['resources/css/soporte_tecnico/soporte_area/soporte_area.css',
            'resources/js/soporte_tecnico/soporte_area/soporte_area.js'])
@endpush
@endsection
