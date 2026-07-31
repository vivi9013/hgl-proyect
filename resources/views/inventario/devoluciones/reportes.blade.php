@extends('layouts.app')

@section('title', 'Reportes de Devoluciones')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-bar-chart text-primary me-2"></i>Reportes de Devoluciones
            </h1>
            <p class="text-muted mb-0">Filtra y exporta el historial de devoluciones</p>
        </div>
    </div>

    {{-- Subnavegación del Módulo --}}
    @include('inventario.devoluciones.partials.subnav', ['activo' => 'reportes'])

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Tarjeta de Filtros ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fa fa-filter text-secondary me-2"></i>Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body px-4 pb-4">
            <form method="GET" action="{{ route('devoluciones.imprimir') }}" target="_blank" id="formReportes">
                <div class="row g-3 align-items-end">

                    {{-- Fecha Inicio --}}
                    <div class="col-12 col-md-4">
                        <label for="rpt_fecha_inicio" class="form-label fw-bold small">
                            <i class="fa fa-calendar me-1"></i>Fecha Inicio:
                        </label>
                        <input type="date" name="fecha_inicio" id="rpt_fecha_inicio" class="form-control bg-light">
                    </div>

                    {{-- Fecha Fin --}}
                    <div class="col-12 col-md-4">
                        <label for="rpt_fecha_fin" class="form-label fw-bold small">
                            <i class="fa fa-calendar me-1"></i>Fecha Fin:
                        </label>
                        <input type="date" name="fecha_fin" id="rpt_fecha_fin" class="form-control bg-light">
                    </div>

                    {{-- Área de Almacén --}}
                    <div class="col-12 col-md-4">
                        <label for="rpt_area_almacen" class="form-label fw-bold small">
                            <i class="fa fa-building me-1"></i>Área de Almacén:
                        </label>
                        <select name="id_area_almacen" id="rpt_area_almacen" class="form-control">
                            <option value="">-- Todas --</option>
                            @foreach($areasAlmacen as $area)
                                <option value="{{ $area->id_area_almacen }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Botones --}}
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-print me-1"></i>Generar Reporte
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ── Información de uso ── --}}
    <div class="mt-4 p-4 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
        <h6 class="fw-bold mb-2"><i class="fa fa-info-circle text-primary me-2"></i>¿Cómo usar los reportes?</h6>
        <ul class="mb-0 text-muted small">
            <li>Selecciona el rango de fechas deseado para filtrar por fecha de registro.</li>
            <li>Puedes filtrar por Área de Almacén específica o ver todas.</li>
            <li>El reporte incluirá únicamente las devoluciones confirmadas como <strong>Terminadas</strong>.</li>
            <li>El reporte se abrirá en una nueva pestaña listo para imprimir.</li>
            <li>Sin filtros: se mostrarán los últimos 500 registros.</li>
        </ul>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/inventario/devoluciones/devoluciones.css'])
@endpush
