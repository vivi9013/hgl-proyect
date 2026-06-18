@extends('layouts.app')

@section('title', 'Reportes de Proyectos - Hospital General')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Módulo de Reportes
            </h1>
            <p class="text-muted mb-0">Centro de exportación y generación de documentos oficiales del catálogo de proyectos</p>
        </div>
    </div>

    {{-- Tarjetas de Reporte --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo' => 'Lista Completa de Proyectos',
                'descripcion' => 'Imprime o genera el documento oficial con la lista completa de los proyectos registrados en el sistema. El listado se genera preordenado alfabéticamente por el nombre del proyecto para una auditoría visual óptima.',
                'rutaImprimir' => route('proyectos.imprimir')
            ])
        </div>
    </div>

</div>

@vite(['resources/css/categoria_modulos/categoria_reportes.css'])
@endsection
