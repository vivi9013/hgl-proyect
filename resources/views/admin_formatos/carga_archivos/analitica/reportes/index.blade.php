@extends('layouts.app')

@section('title', 'Módulo de Reportes - Carga de Archivos')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Módulo de Reportes
            </h1>
            <p class="text-muted mb-0">Centro de exportación y generación de documentos oficiales del repositorio de archivos</p>
        </div>
    </div>

    <div class="row g-4">

        {{-- Tarjeta estándar: Reporte completo (componente compartido) --}}
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo' => 'Lista Completa de Archivos',
                'descripcion' => 'Genera el listado oficial del repositorio completo con todos los archivos y formatos registrados en el sistema.',
                'rutaImprimir' => route('carga_archivos.imprimir'),
                'labelBoton' => 'Imprimir Reporte Completo',
                'rutaVolver'  => route('carga_archivos.index'),
                'labelVolver' => 'Volver a la lista',
            ])
        </div>

        {{-- Tarjeta especial: Reporte con Filtros — exclusivo del módulo de Carga de Archivos --}}
        <div class="col-12 col-md-6 col-lg-7">
            <div class="card reporte-card border-0 shadow-sm rounded-3 bg-white h-100">
                <form id="formReporteFiltrado" action="{{ route('carga_archivos.imprimir') }}" method="GET" target="_blank" class="h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fa fa-filter text-secondary me-2"></i>Reporte con Filtros
                            </h5>
                            <span class="text-danger">
                                <i class="fa fa-file-pdf-o fa-2x"></i>
                            </span>
                        </div>
                        <div class="card-body px-4 py-3">
                            <p class="text-muted small mb-3" style="line-height: 1.6;">
                                Filtre el repositorio por categoría para obtener un reporte segmentado.
                            </p>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="filtro_categoria" class="form-label fw-semibold small text-secondary">Seleccionar Categoría</label>
                                    <select name="tipo" id="filtro_categoria" class="form-select shadow-sm" required>
                                        <option value="" disabled selected>Seleccione una categoría...</option>
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat->id_catego_archivos }}">{{ $cat->categoria }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end border-top">
                        <button type="submit" class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
                            <i class="fa fa-print me-2"></i>Imprimir Reporte Filtrado
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
