@extends('layouts.app')

@section('title', 'Reportes - Plantillas de Pedido')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold text-dark">
                <i class="bi bi-file-earmark-pdf me-2 text-primary"></i>Reportes de Plantillas de Pedido
            </h2>
            <small class="text-muted">Generación e impresión de reportes oficiales de plantillas con sus insumos asignados</small>
        </div>
        <a href="{{ route('plantillas_pedido.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-secondary">
                        <i class="bi bi-funnel me-1"></i> Filtros de Reporte
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('plantillas_pedido.imprimir') }}" method="GET" target="_blank">
                        <div class="mb-3">
                            <label for="id_area_abastecimiento" class="form-label fw-bold small">Área de Abastecimiento</label>
                            <select class="form-select" id="id_area_abastecimiento" name="id_area_abastecimiento">
                                <option value="">-- Todas las Áreas --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area_abastecimiento }}">{{ $area->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="id_subarea_abastecimiento" class="form-label fw-bold small">Subárea de Abastecimiento</label>
                            <select class="form-select" id="id_subarea_abastecimiento" name="id_subarea_abastecimiento">
                                <option value="">-- Todas las Subáreas --</option>
                                @foreach($subareas as $subarea)
                                    <option value="{{ $subarea->id_subarea_abastecimiento }}">{{ $subarea->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold small">Estatus de la Plantilla</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">-- Todos --</option>
                                <option value="Activo">Activa</option>
                                <option value="Inactivo">Inactiva</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="buscar" class="form-label fw-bold small">Búsqueda General</label>
                            <input type="text" class="form-control" id="buscar" name="buscar"
                                   placeholder="Nombre de plantilla, área...">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-printer me-2"></i> Generar Reporte de Impresión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
