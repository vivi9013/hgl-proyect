@extends('layouts.app')

@section('title', 'Dashboard - Hospital System')

@section('content')
@vite('resources/css/indexstyle.css')

<div class="container-fluid py-4">
    <!-- Encabezado Principal -->
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Panel de Control <small class="text-muted fs-6">Inicio</small></h1>
    </div>

    <!-- SECCIÓN: ADMINISTRACIÓN DE FORMATOS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white py-2">
            <h6 class="mb-0 fw-bold">Administración de formatos</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Buscador de Archivos</h5></div>
                            <i class="bi bi-search"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Carga de Archivos</h5></div>
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Categoría de Archivos</h5></div>
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Permisos de Acceso</h5></div>
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: ESTUDIOS RADIOLÓGICOS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white py-2">
            <h6 class="mb-0 fw-bold">Estudios Radiológicos</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Especialidad RX</h5></div>
                            <i class="bi bi-hospital"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Medicos RX</h5></div>
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Estudios RX</h5></div>
                            <i class="bi bi-file-medical"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Estadísticas y reportes</h5></div>
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: INVENTARIO -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white py-2">
            <h6 class="mb-0 fw-bold">Inventario de medicamentos y material de curación</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Áreas de almacén</h5></div>
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Devoluciones</h5></div>
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Bajas de Insumos</h5></div>
                            <i class="bi bi-trash"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white h-100 border-0 shadow-sm module-card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h5 class="mb-0">Entrada de insumos</h5></div>
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <a href="#" class="card-footer text-white text-decoration-none text-center small py-2 bg-black bg-opacity-10">
                            Ingresar al Módulo <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
