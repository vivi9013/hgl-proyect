@extends('layouts.app')

@section('title', 'Reportes de Categorías de Módulos - Hospital General')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado de Navegación y Título Corporativo --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Módulo de Reportes
            </h1>
            <p class="text-muted mb-0">Centro de exportación y generación de documentos oficiales del catálogo</p>
        </div> 
    </div>

    {{-- Sección de Bloques de Reporte Disponibles --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                
                {{-- Cabecera y Cuerpo de la Tarjeta --}}
                <div>
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fa fa-list-alt text-secondary me-2"></i>Lista Completa de Categorías
                        </h5>
                        <span class="text-danger">
                            <i class="fa fa-file-pdf-o fa-2x"></i>
                        </span>
                    </div>
                    
                    <div class="card-body px-4 py-3">
                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                            Imprime o genera el documento oficial con la lista completa de las categorías en las que se clasifican los módulos del sistema. El listado se genera preordenado de manera descendente por el nombre de la categoría para una auditoría visual óptima.
                        </p>
                    </div>
                </div>

                {{-- Footer de Acción Limpio (Reemplaza el antiguo box-footer) --}}
                <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end border-top">
                    <a href="{{ route('categoria_modulos.imprimir') }}" target="_blank" class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
                        <i class="fa fa-print me-2"></i>Imprimir Reporte
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Activos Vite específicos y limpios, sin dependencias muertas de iCheck, Morris o DataTables heredadas --}}
@vite(['resources/css/categoria_modulos/categoria_reportes.css', 'resources/js/categoria_modulos/categoria_reportes.js'])
@endsection