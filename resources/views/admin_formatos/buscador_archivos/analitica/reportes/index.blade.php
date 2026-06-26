@extends('layouts.app')

@section('title', 'Reportes - Buscador de Archivos')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Reportes del Módulo
            </h1>
            <p class="text-muted mb-0">Impresión y exportación de formatos y listas del sistema</p>
        </div>
    </div>

    <div class="row g-4">

        {{-- Tarjeta: Lista Completa de Archivos --}}
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo'      => 'Lista Completa de Archivos',
                'descripcion' => 'Genera una lista completa con todos los archivos y formatos ingresados en el sistema a los que tu perfil de usuario tiene autorización para acceder.',
                'rutaImprimir' => route('busca_archivos.imprimir'),
                'labelBoton'  => 'Imprimir Reporte',
                'rutaVolver'  => route('busca_archivos.index'),
                'labelVolver' => 'Volver al buscador',
            ])
        </div>

    </div>
</div>

@endsection
