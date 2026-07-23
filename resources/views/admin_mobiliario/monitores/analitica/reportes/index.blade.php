@extends('layouts.app')

@section('title', 'Reportes de Monitores - Hospital General')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Módulo de Reportes de Monitores
            </h1>
            <p class="text-muted mb-0">Centro de exportación y generación de documentos oficiales del catálogo de monitores</p>
        </div>
    </div>

    {{-- Tarjetas de Reporte --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo' => 'Lista Completa de Monitores',
                'descripcion' => 'Imprime o genera el documento oficial con la lista completa de los monitores registrados en el sistema. El listado se genera ordenado de manera descendente para ver las últimas incorporaciones de forma prioritaria.',
                'rutaImprimir' => route('monitores.imprimir'),
                'rutaVolver'  => route('monitores.index'),
                'labelVolver' => 'Volver a monitores',
            ])
        </div>
    </div>

</div>

@endsection
