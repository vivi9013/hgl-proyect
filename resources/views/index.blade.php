@extends('layouts.app')

@section('title', 'Dashboard - Hospital System')

@section('content')
<div class="container-fluid">
    <!-- Encabezado de página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Dashboard</h1>
            <p class="text-muted">Bienvenido de nuevo al sistema de gestión hospitalaria.</p>
        </div>
        <button class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-2"></i> Nueva Cita
        </button>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                            <i class="bi bi-calendar-event fs-4"></i>
                        </div>
                        <h6 class="card-title mb-0 text-muted">Citas Hoy</h6>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h3 class="mb-0 fw-bold">24</h3>
                        <span class="ms-2 text-success small fw-bold"><i class="bi bi-arrow-up"></i> 12%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <h6 class="card-title mb-0 text-muted">Pacientes</h6>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h3 class="mb-0 fw-bold">1,250</h3>
                        <span class="ms-2 text-success small fw-bold"><i class="bi bi-arrow-up"></i> 5%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                        <h6 class="card-title mb-0 text-muted">Médicos Activos</h6>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h3 class="mb-0 fw-bold">48</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                        <h6 class="card-title mb-0 text-muted">Pendientes</h6>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h3 class="mb-0 fw-bold">7</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de contenido (Ej. Citas Recientes) -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Citas Recientes</h5>
                    <a href="#" class="btn btn-sm btn-light text-primary fw-bold">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0">Paciente</th>
                                    <th class="border-0">Doctor</th>
                                    <th class="border-0">Fecha</th>
                                    <th class="border-0">Estado</th>
                                    <th class="border-0 text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-secondary rounded-circle me-3" style="width: 32px; height: 32px;"></div>
                                            <div>
                                                <p class="mb-0 fw-bold text-dark">Ana García</p>
                                                <small class="text-muted">ID: #4582</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Dr. Ricardo Luna</td>
                                    <td>12 May, 10:30 AM</td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Confirmada</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-secondary rounded-circle me-3" style="width: 32px; height: 32px;"></div>
                                            <div>
                                                <p class="mb-0 fw-bold text-dark">Carlos Ruiz</p>
                                                <small class="text-muted">ID: #4583</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Dra. Elena Soler</td>
                                    <td>12 May, 11:15 AM</td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">En espera</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light"><i class="bi bi-three-dots"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Avisos del Sistema</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex mb-4">
                        <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3 me-3 h-100">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <p class="mb-0 fw-bold text-dark">Mantenimiento de Servidor</p>
                            <small class="text-muted">Hoy a las 11:00 PM</small>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="bg-info bg-opacity-10 text-info p-2 rounded-3 me-3 h-100">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <p class="mb-0 fw-bold text-dark">Nueva actualización v2.4</p>
                            <small class="text-muted">Ya disponible para revisión</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
