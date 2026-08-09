@extends('layouts.app')
@section('title', 'Reportes – Solicitar Servicio')
@section('content')

<div class="container-fluid py-4">

    <div class="modulo-header">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-bar-chart me-2"></i>Reportes y Analítica
            </h1>
            <p class="text-muted mb-0 small">Estadísticas de tus solicitudes de servicio</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('solicitar_servicio.index') }}"
               class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i>Volver al módulo
            </a>
        </div>
    </div>

    <hr class="mb-4">

    <div class="row g-4">

        {{-- Tarjeta: Reporte de impresión --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fa fa-print fa-3x text-dark"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Reporte Imprimible</h5>
                    <p class="text-muted small mb-3">
                        Genera un reporte detallado de tus solicitudes de servicio con filtros de fecha y estado.
                    </p>
                    <a href="{{ route('solicitar_servicio.imprimir') }}" target="_blank"
                       class="btn btn-sm btn-dark px-4 rounded-pill">
                        <i class="fa fa-file-text-o me-1"></i>Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        {{-- Tarjeta: Gráficas analíticas --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fa fa-pie-chart fa-3x text-dark"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Gráficas Analíticas</h5>
                    <p class="text-muted small mb-3">
                        Visualiza distribución de servicios por estado, área de soporte y tendencia mensual.
                    </p>
                    <a href="{{ route('solicitar_servicio.graficas') }}"
                       class="btn btn-sm btn-outline-dark px-4 rounded-pill">
                        <i class="fa fa-line-chart me-1"></i>Ver Gráficas
                    </a>
                </div>
            </div>
        </div>

        {{-- Tarjeta: Mis servicios activos --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fa fa-list-ul fa-3x text-dark"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Servicios Activos</h5>
                    <p class="text-muted small mb-3">
                        Consulta el estado actual de todos tus servicios en seguimiento.
                    </p>
                    <a href="{{ route('solicitar_servicio.seguimiento') }}"
                       class="btn btn-sm btn-outline-dark px-4 rounded-pill">
                        <i class="fa fa-arrow-right me-1"></i>Ver Seguimiento
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
    @vite('resources/css/soporte_tecnico/solicitar_servicio/solicitar_servicio.css')
@endpush
@endsection
