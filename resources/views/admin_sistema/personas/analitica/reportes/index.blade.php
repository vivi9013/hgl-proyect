@extends('layouts.app')

@section('title', 'Reportes de Personas - Hospital General de Linares')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-print text-primary me-2"></i>Módulo de Reportes
            </h1>
            <p class="text-muted mb-0">Centro de exportación y generación de documentos oficiales del padrón de personas</p>
        </div>
    </div>

    <div class="row g-4">

        {{-- Reporte: Lista Completa --}}
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list-alt text-secondary me-2"></i>Lista Completa de Personas
                        </h5>
                        <span class="text-danger">
                            <i class="fa fa-file-pdf-o fa-2x"></i>
                        </span>
                    </div>
                    <div class="card-body px-4 py-3">
                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                            Genera el listado oficial del padrón completo de personas registradas en el sistema.
                            El reporte se ordena por apellido paterno de forma ascendente para una auditoría visual óptima.
                        </p>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end border-top">
                    <a href="{{ route('personas.imprimir') }}" target="_blank"
                       class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
                        <i class="fa fa-print me-2"></i>Imprimir Reporte Completo
                    </a>
                </div>
            </div>
        </div>

        {{-- Reporte con Filtros --}}
        <div class="col-12 col-md-6 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-filter text-secondary me-2"></i>Reporte con Filtros
                        </h5>
                        <span class="text-primary">
                            <i class="fa fa-sliders fa-2x"></i>
                        </span>
                    </div>
                    <div class="card-body px-4 py-3">
                        <p class="text-muted small mb-3">
                            Filtre el padrón por sexo y/o estado de residencia para obtener un reporte segmentado.
                        </p>
                        <form id="formReporteFiltrado" action="{{ route('personas.imprimir') }}" method="GET" target="_blank">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label for="filtro_sexo" class="form-label fw-semibold small text-secondary">Sexo</label>
                                    <select name="sexo" id="filtro_sexo" class="form-select shadow-sm">
                                        <option value="">Todos</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="filtro_estado" class="form-label fw-semibold small text-secondary">Estado</label>
                                    <select name="estado" id="filtro_estado" class="form-select shadow-sm">
                                        <option value="">Todos los estados</option>
                                        @foreach($estados as $est)
                                            <option value="{{ $est }}">{{ $est }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end border-top">
                    <button type="submit" class="btn btn-outline-primary py-2 px-4 rounded-pill shadow-sm">
                        <i class="fa fa-print me-2"></i>Imprimir Reporte Filtrado
                    </button>
                </div>
                </form>
            </div>
        </div>

    </div>
</div>

@vite(['resources/css/personas/personas.css'])
@endsection
