@extends('layouts.app')

@section('title', 'Reportes de Usuarios - Hospital General de Linares')

@section('content')
<div class="container-fluid py-4 usuarios-reportes">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Reportes de Usuarios
            </h1>
            <p class="text-muted mb-0">Impresión y exportación de reportes del módulo de usuarios</p>
        </div>
        <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Volver al Catálogo
        </a>
    </div>

    {{-- Tarjetas de Reporte --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            @include('layouts.reporte_index', [
                'titulo'       => 'Lista Completa de Usuarios',
                'descripcion'  => 'Imprime un reporte completo con todos los usuarios registrados en el sistema, ordenado alfabéticamente por apellido paterno, materno y nombre.',
                'rutaImprimir' => route('usuarios.imprimir'),
                'labelBoton'   => 'Imprimir Reporte',
                'rutaVolver'   => route('usuarios.index'),
                'labelVolver'  => 'Volver a usuarios',
            ])
        </div>
    </div>

</div>

@endsection
