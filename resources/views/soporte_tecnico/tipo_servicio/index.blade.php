@extends('layouts.app')
@section('title', 'Tipo de Servicio – Soporte Técnico')
@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-tipo-servicio">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-tags me-2"></i>Tipo de Servicio
            </h1>
            <p class="text-muted mb-0 small">Catálogo de tipos de servicio técnico por área de soporte</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tipo_servicio.imprimir') }}" target="_blank"
               id="btnImprimir"
               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Imprimir
            </a>
            <button type="button" id="btnNuevo"
                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                    data-bs-toggle="modal" data-bs-target="#modalAlta">
                <i class="fa fa-plus me-1"></i>Nuevo Tipo
            </button>
        </div>
    </div>

    {{-- Tarjeta principal --}}
    <div class="card shadow-sm border-0">

        {{-- Cabecera de la tarjeta —  filtros --}}
        <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
            <div class="row g-2 align-items-end" id="panelFiltros">

                {{-- Buscador --}}
                <x-filtro-buscar id="filtro-buscar" label="Buscar tipo de servicio"
                    placeholder="Nombre del servicio o área..." clase="col-12 col-md-5" />

                {{-- Dropdown de filtros --}}
                <x-filtro-dropdown id="dropdownFiltros" label="Filtrar" titulo="Filtros"
                    labelDefault="Todos los tipos" clase="col-12 col-md-6">

                    {{-- Estatus --}}
                    <p class="text-uppercase text-muted small fw-semibold mb-1 mt-1"
                       style="font-size:0.7rem; letter-spacing:.04em;">Estatus</p>
                    <div class="mb-2">
                        <div class="form-check py-1">
                            <input class="form-check-input chk-estatus" type="checkbox" value="1" id="chkActivo">
                            <label class="form-check-label text-dark" for="chkActivo">
                                <span class="badge bg-success rounded-pill px-2">Activo</span>
                            </label>
                        </div>
                        <div class="form-check py-1">
                            <input class="form-check-input chk-estatus" type="checkbox" value="0" id="chkInactivo">
                            <label class="form-check-label text-dark" for="chkInactivo">
                                <span class="badge bg-secondary rounded-pill px-2">Inactivo</span>
                            </label>
                        </div>
                    </div>

                    {{-- Área --}}
                    <p class="text-uppercase text-muted small fw-semibold mb-1 mt-2"
                       style="font-size:0.7rem; letter-spacing:.04em;">Área de Soporte</p>
                    <div class="mb-1">
                        @foreach($areasActivas as $area)
                        <div class="form-check py-1">
                            <input class="form-check-input chk-area" type="checkbox"
                                   value="{{ $area->id }}" id="chkArea{{ $area->id }}">
                            <label class="form-check-label text-dark" for="chkArea{{ $area->id }}">
                                {{ $area->area }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </x-filtro-dropdown>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                @include('soporte_tecnico.tipo_servicio.partials.tabla')
            </table>
        </div>

        {{-- Pie: info + paginación --}}
        <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top flex-wrap gap-2">
            <div class="text-muted small" id="infoPaginacion">
                Mostrando {{ $servicios->firstItem() ?? 0 }} a {{ $servicios->lastItem() ?? 0 }}
                de {{ $servicios->total() }} registros
            </div>
            <nav>
                <div id="paginacion">
                    {{ $servicios->links('pagination::bootstrap-4') }}
                </div>
            </nav>
        </div>

    </div>
</div>

{{-- Modal Alta --}}
@include('soporte_tecnico.tipo_servicio.partials.modal_alta')

@push('scripts')
    @vite([
        'resources/css/soporte_tecnico/tipo_servicio/tipo_servicio.css',
        'resources/js/soporte_tecnico/tipo_servicio/tipo_servicio.js',
    ])
@endpush
@endsection
