@extends('layouts.app')

@section('title', 'Reportes de Trabajadores - Hospital General')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Módulo de Reportes de Trabajadores
            </h1>
            <p class="text-muted mb-0">Centro de exportación y generación de documentos oficiales del expediente de trabajadores</p>
        </div>
    </div>

    {{-- Tarjetas de Reporte --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo'       => 'Reporte Oficial de Trabajadores',
                'descripcion'  => 'Genera e imprime el documento oficial con el listado completo de trabajadores, incluyendo sus datos de adscripción (Sede, Departamento, Puesto, Categoría).',
                'rutaImprimir' => route('trabajadores.imprimir'),
                'rutaVolver'   => route('trabajadores.index'),
                'labelVolver'  => 'Volver al catálogo de trabajadores',
            ])
        </div>
    </div>

</div>

@endsection
