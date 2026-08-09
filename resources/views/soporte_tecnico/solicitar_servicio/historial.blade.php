@extends('layouts.app')
@section('title', 'Historial de Servicios – Soporte Técnico')
@section('content')

<div class="container-fluid py-4" id="modulo-historial-servicio">

    {{-- ─── Cabecera ─────────────────────────────────────────────────────────── --}}
    <div class="modulo-header">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-history me-2"></i>Historial de Servicios
            </h1>
            <p class="text-muted mb-0 small">Servicios liberados y cancelados</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('solicitar_servicio.index') }}"
               class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="fa fa-plus me-1"></i>Nueva Solicitud
            </a>
            <a href="{{ route('solicitar_servicio.seguimiento') }}"
               class="btn btn-sm btn-outline-dark rounded-pill px-3">
                <i class="fa fa-list-ul me-1"></i>Servicios Activos
            </a>
            <a href="{{ route('solicitar_servicio.reportes') }}"
               class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa fa-print me-1"></i>Reportes
            </a>
        </div>
    </div>

    <hr class="mb-3">

    {{-- ─── Panel de Filtros ────────────────────────────────────────────────── --}}
    <div class="filtros-panel">
        <x-filtro-buscar
            id="filtro-buscar"
            label="Buscar en historial"
            placeholder="Folio, descripción, área, estado..."
            clase="col-12 col-md-4"
        />
    </div>

    {{-- ─── Tabla de historial ──────────────────────────────────────────────── --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div id="contenedor-tabla">
                @include('soporte_tecnico.solicitar_servicio.partials.tabla_historial', ['servicios' => $servicios])
            </div>

            {{-- Info + paginador --}}
            <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top flex-wrap gap-2">
                <div class="text-muted small" id="info-registros">
                    Mostrando {{ $servicios->firstItem() ?? 0 }} a {{ $servicios->lastItem() ?? 0 }}
                    de {{ $servicios->total() }} registros
                </div>
                <div id="paginador-historial">
                    {{ $servicios->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL — Detalle del Servicio
     ══════════════════════════════════════════════════════════════════════════ --}}
@include('soporte_tecnico.solicitar_servicio.partials.modal_detalle')

@push('scripts')
    <script>
        window.routes = {
            historial: "{{ route('solicitar_servicio.historial') }}",
            detalles : "{{ url('solicitar-servicio') }}/__ID__/detalles",
        };
    </script>
    @vite([
        'resources/css/soporte_tecnico/solicitar_servicio/solicitar_servicio.css',
        'resources/js/soporte_tecnico/solicitar_servicio/solicitar_servicio.js'
    ])
@endpush
@endsection
