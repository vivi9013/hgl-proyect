@extends('layouts.app')

@section('title', 'Reportes de Sedes - Hospital General')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Módulo de Reportes de Sedes
            </h1>
            <p class="text-muted mb-0">Centro de exportación y generación de documentos oficiales del catálogo de sedes</p>
        </div>
    </div>

    {{-- Tarjetas de Reporte --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo'       => 'Lista Completa de Sedes',
                'descripcion'  => 'Imprime o genera el documento oficial con la lista completa del catálogo de sedes registradas en el sistema. El listado se genera ordenado alfabéticamente.',
                'rutaImprimir' => route('sedes.imprimir'),
                'rutaVolver'   => route('sedes.index'),
                'labelVolver'  => 'Volver al catálogo',
            ])
        </div>
    </div>

</div>

@endsection
