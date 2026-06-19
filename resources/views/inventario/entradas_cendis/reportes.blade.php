@extends('layouts.app')

@section('title', 'Reportes - Entradas CENDIS')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-bar-chart text-primary me-2"></i>Reportes de Entradas al CENDIS
            </h1>
            <p class="text-muted mb-0">Genere reportes históricos filtrando por fechas y área</p>
        </div>
    </div>

    {{-- Subnavegación del Módulo --}}
    <div class="d-flex gap-2 mb-2 flex-wrap">
        <a href="{{ route('entradas_cendis.index') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-hourglass-half me-1 text-dark"></i>Pendientes
        </a>
        <a href="{{ route('entradas_cendis.terminadas') }}" class="btn btn-sm btn-outline-dark bg-white py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-check-circle me-1 text-dark"></i>Terminadas
        </a>
        <a href="{{ route('entradas_cendis.reportes') }}" class="btn btn-sm btn-primary py-2 px-3 fw-bold shadow-sm" style="border: 1.5px solid #000; border-radius: 8px;">
            <i class="fa fa-bar-chart me-1"></i>Reportes
        </a>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Alertas ── --}}
    @if(session('error'))
        <div id="alertaError" data-message="{{ session('error') }}"></div>
    @endif

    {{-- ── Formulario de Filtros ── --}}
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3 px-4">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa fa-filter me-2"></i>Filtros del Reporte
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('entradas_cendis.imprimir') }}" target="_blank" novalidate id="formReporte">
                        <div class="row g-3">

                            {{-- Rango de Fechas --}}
                            <div class="col-12 col-md-6">
                                <label for="fecha_inicio_rep" class="form-label fw-bold small">
                                    <i class="fa fa-calendar me-1"></i>Fecha Inicio:
                                </label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio_rep"
                                       class="form-control" value="{{ request('fecha_inicio') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="fecha_fin_rep" class="form-label fw-bold small">
                                    <i class="fa fa-calendar me-1"></i>Fecha Fin:
                                </label>
                                <input type="date" name="fecha_fin" id="fecha_fin_rep"
                                       class="form-control" value="{{ request('fecha_fin') }}">
                            </div>

                            {{-- Área de Almacén --}}
                            <div class="col-12 col-md-6">
                                <label for="id_area_almacen_rep" class="form-label fw-bold small">
                                    <i class="fa fa-building me-1"></i>Área de Almacén:
                                </label>
                                <select name="id_area_almacen" id="id_area_almacen_rep" class="form-control">
                                    <option value="">— Todas las áreas —</option>
                                    @foreach($areasAlmacen as $area)
                                        <option value="{{ $area->id_area_almacen }}"
                                            {{ request('id_area_almacen') == $area->id_area_almacen ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Área de Surtimiento --}}
                            <div class="col-12 col-md-6">
                                <label for="id_area_surtimiento_rep" class="form-label fw-bold small">
                                    <i class="fa fa-building-o me-1"></i>Área de Surtimiento:
                                </label>
                                <select name="id_area_surtimiento" id="id_area_surtimiento_rep" class="form-control">
                                    <option value="">— Todas las áreas —</option>
                                    @foreach($areasSurtimiento as $area)
                                        <option value="{{ $area->id_area_surtimiento }}"
                                            {{ request('id_area_surtimiento') == $area->id_area_surtimiento ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Búsqueda libre --}}
                            <div class="col-12">
                                <label for="buscar_rep" class="form-label fw-bold small">
                                    <i class="fa fa-search me-1"></i>Búsqueda adicional (folio, área):
                                </label>
                                <input type="text" name="buscar" id="buscar_rep"
                                       class="form-control"
                                       placeholder="Ej: ENT-00001 o nombre de área"
                                       value="{{ request('buscar') }}"
                                       autocomplete="off">
                            </div>

                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('entradas_cendis.reportes') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-times me-1"></i>Limpiar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-print me-1"></i>Generar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Nota informativa --}}
            <div class="alert alert-info mt-4 border-0 shadow-sm" role="alert" style="font-size: 0.88rem;">
                <i class="fa fa-info-circle me-2"></i>
                El reporte se abrirá en una nueva pestaña con formato imprimible. Solo se incluirán entradas con estatus <strong>Terminado</strong>. El límite máximo es de <strong>500 registros</strong>.
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/entradas_cendis/entradas.css', 'resources/js/inventario/entradas_cendis/entradas.js'])
@endpush
