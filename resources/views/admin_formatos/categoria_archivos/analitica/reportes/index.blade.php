@extends('layouts.app')

@section('title', 'Reportes de Categorías de Archivos - Hospital General')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Módulo de Reportes
            </h1>
            <p class="text-muted mb-0">Centro de exportación y generación de documentos oficiales del catálogo</p>
        </div>
    </div>

    {{-- Tarjetas de Reporte --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo'       => 'Lista Completa de Categorías',
                'descripcion'  => 'Genera el documento oficial con la lista completa de la categorización de archivos registradas en el sistema, ordenadas por categoría ascendente, con el conteo de archivos activos por cada una.',
                'rutaImprimir' => route('categoria_archivos.imprimir'),
                'rutaVolver'   => route('categoria_archivos.index'),
                'labelVolver'  => 'Volver a categorías',
            ])
        </div>
    </div>

</div>

@vite(['resources/css/categoria_archivos/categoria.css'])
@endsection