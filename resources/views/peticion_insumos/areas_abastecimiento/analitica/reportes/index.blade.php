@extends('layouts.app')

@section('title', 'Reportes - Áreas de Abastecimiento')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 fw-bold text-dark">
                <i class="bi bi-file-earmark-pdf me-2 text-primary"></i> Reportes de Áreas de Abastecimiento
            </h2>
            <small class="text-muted">Generación e impresión del catálogo oficial de áreas principales de abastecimiento.</small>
        </div>
        <a href="{{ route('areas_abastecimiento.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-secondary">
                        <i class="bi bi-funnel me-1"></i> Filtros de Reporte
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('areas_abastecimiento.imprimir') }}" method="GET" target="_blank">
                        <div class="mb-3">
                            <label for="buscar" class="form-label fw-bold small">Búsqueda General</label>
                            <input type="text" class="form-control" id="buscar" name="buscar" placeholder="Buscar por nombre de área...">
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold small">Estatus del Área</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">-- Todos --</option>
                                <option value="Activo">Activos</option>
                                <option value="Inactivo">Inactivos</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-printer me-2"></i> Generar Reporte Imprimible
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
