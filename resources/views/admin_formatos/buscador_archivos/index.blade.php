@extends('layouts.app')

@section('title', 'Buscador de Archivos - Hospital General')

@section('content')

<div class="container-fluid py-4" id="modulo-buscador-archivos">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-search me-2"></i>Buscador de Archivos
            </h1>
            <p class="text-muted mb-0">Administración de formatos institucionales</p>
        </div>
    </div>

    {{-- ─── Tabla unificada de Archivos ────────────────────────────────────── --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                {{-- Cabecera tarjeta --}}
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Lista de Archivos
                        </h5>
                    </div>

                    {{-- ── Panel de filtros ────────────────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">

                        {{-- Búsqueda por nombre/descripción --}}
                        <x-filtro-buscar id="filtro-buscar" label="Buscar archivo" placeholder="Nombre o descripción..." clase="col-12 col-md-4" />

                        {{-- Filtro Desplegable por Categoría --}}
                        <x-filtro-dropdown id="dropdownFiltros" label="Filtrar por categoría" titulo="Categorías" labelDefault="Todas las categorías" clase="col-12 col-md-4">
                            <div class="mb-2">
                                @foreach($categorias as $cat)
                                    <div class="form-check py-1">
                                        <input class="form-check-input chk-categoria" type="checkbox" value="{{ $cat->id_catego_archivos }}" id="chkCategoria{{ $cat->id_catego_archivos }}">
                                        <label class="form-check-label text-dark cursor-pointer" for="chkCategoria{{ $cat->id_catego_archivos }}">{{ $cat->categoria }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </x-filtro-dropdown>

                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Reportes) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center mt-3 pt-3 border-top">
                        <a href="{{ route('busca_archivos.reportes') }}"
                           class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                            <i class="fa fa-file-pdf-o me-1 text-danger"></i>Reportes
                        </a>
                    </div>
                </div>

                {{-- Tabla de archivos --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-secondary">
                            <tr>
                                <th class="ps-4" style="width: 80px;">#</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th class="text-center" style="width: 180px;">Versión</th>
                                <th class="text-center pe-4" style="width: 150px;">Descargar</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaArchivos">
                            @include('admin_formatos.buscador_archivos.partials.tabla')
                        </tbody>
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionArchivos">
                        Mostrando {{ $archivos->firstItem() ?? 0 }} a {{ $archivos->lastItem() ?? 0 }}
                        de {{ $archivos->total() }} registros
                    </div>
                    <nav>
                        <div id="paginacionArchivos">
                            @if ($archivos->count() > 0)
                                {{ $archivos->links('pagination::bootstrap-4') }}
                            @endif
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </div>

</div>

@vite(['resources/css/buscador_archivos/buscador.css', 'resources/js/buscador_archivos/buscador.js'])
@endsection
