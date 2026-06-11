@extends('layouts.app')

@section('title', 'Reportes - Carga de Archivos')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-file-pdf-o text-danger me-2"></i>Reportes de Repositorio
            </h1>
            <p class="text-muted mb-0">Impresión y exportación de archivos por categoría</p>
        </div>
    </div>

    {{-- ── Formulario de Selección ── --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm h-100 overflow-hidden rounded-3 bg-white">
                <form method="POST" action="{{ route('carga_archivos.imprimir') }}" target="_blank">
                    @csrf
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-danger me-3">
                                <i class="fa fa-print fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">Lista de Archivos por Categoría</h5>
                                <span class="badge bg-light text-secondary border">Formato PDF / Impresión</span>
                            </div>
                        </div>

                        <p class="text-secondary mb-4">
                            Genera una lista completa de los archivos ingresados en el sistema filtrados por la categoría seleccionada.
                        </p>

                        <div class="form-group mb-3">
                            <label class="form-label fw-bold text-dark"><i class="fa fa-tag me-1 text-primary"></i> Seleccionar Categoría:</label>
                            <select name="tipo" class="form-select select2 rounded-pill py-2" required>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id_catego_archivos }}">{{ $cat->categoria }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 py-3 px-4 border-top d-flex justify-content-between align-items-center">
                        <a href="{{ route('carga_archivos.index') }}" class="btn btn-link text-decoration-none p-0 text-secondary fw-medium">
                            <i class="fa fa-arrow-left me-1"></i> Volver a la lista
                        </a>
                        <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill shadow-sm fw-bold">
                            <i class="fa fa-print me-1"></i> Imprimir Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@vite(['resources/css/carga_archivos/carga.css'])
@endsection
