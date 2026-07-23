@extends('layouts.app')

@section('title', 'Registro de Actividades - Hospital General')

@push('styles')
    @vite(['resources/css/miscelaneo/actividades/actividades.css'])
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado Principal --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="h2 text-dark fw-bold mb-1">
                <i class="fa fa-history text-primary me-2"></i>Registro de Actividades
            </h1>
            <p class="text-muted mb-0">Historial completo de actividades y accesos registrados en el sistema.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('actividades.graficas') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm me-2">
                <i class="fa fa-pie-chart me-1"></i>Ver Gráficas
            </a>
            <a href="{{ route('inicio') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                <i class="fa fa-arrow-left me-1"></i>Regresar a Inicio
            </a>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- Panel de Filtros --}}
    <div class="card filter-card border-0 mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-end g-3">
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="form-group mb-0">
                        <label for="search-input" class="form-label small fw-bold text-muted mb-1.5">Búsqueda libre</label>
                        <input class="form-control py-2 fw-semibold" type="text" id="search-input" placeholder="Buscar por descripción...">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="form-group mb-0">
                        <label for="filtro-select" class="form-label small fw-bold text-muted mb-1.5">Filtro / Tipo de Actividad</label>
                        <select class="form-select py-2 fw-semibold" id="filtro-select">
                            <option value="">-- Todos --</option>
                            @foreach($filtros as $filtro)
                                <option value="{{ $filtro }}">{{ $filtro }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="form-group mb-0">
                        <label for="fecha-inicio" class="form-label small fw-bold text-muted mb-1.5">Fecha Inicial</label>
                        <input class="form-control py-2 fw-semibold" type="date" id="fecha-inicio">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-2">
                    <div class="form-group mb-0">
                        <label for="fecha-fin" class="form-label small fw-bold text-muted mb-1.5">Fecha Final</label>
                        <input class="form-control py-2 fw-semibold" type="date" id="fecha-fin">
                    </div>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-2">
                    <button type="button" id="btn-limpiar" class="btn btn-outline-secondary w-100 py-2 fw-bold rounded-pill shadow-sm">
                        <i class="fa fa-eraser me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Actividades --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="border: 1px solid #e5e7eb !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tabla-actividades">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-center" style="width: 8%;">Clave</th>
                            <th class="py-3 px-4">Descripción</th>
                            <th class="py-3 px-4" style="width: 18%;">Filtro</th>
                            <th class="py-3 px-4 text-center" style="width: 12%;">Fecha</th>
                            <th class="py-3 px-4 text-center" style="width: 10%;">Hora</th>
                            <th class="py-3 px-4" style="width: 22%;">Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-actividades-body">
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <span class="text-muted small" id="total-registros">Mostrando 0 de 0 registros</span>
            <div id="paginacion-container"></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/js/miscelaneo/actividades/actividades.js'])
@endpush
