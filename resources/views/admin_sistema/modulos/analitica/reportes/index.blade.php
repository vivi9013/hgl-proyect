@extends('layouts.app')

@section('title', 'Reportes - Gestión de Módulos')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Módulo de Reportes
            </h1>
            <p class="text-muted mb-0">Impresión y exportación de formatos y listas del sistema</p>
        </div>
    </div>

    {{-- Tarjetas de Reporte --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo' => 'Lista Completa de Módulos',
                'descripcion' => 'Imprime una lista completa de los módulos contenidos en el sistema.',
                'rutaImprimir' => route('modulos.reportes.imprimir', 'completa'),
                'rutaVolver'  => route('modulos.index'),
                'labelVolver' => 'Volver a módulos',
            ])
        </div>
    </div>

</div>

@endsection
