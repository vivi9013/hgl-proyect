@extends('layouts.app')
@section('title', 'Solicitar Servicio – Soporte Técnico')
@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-solicitar-servicio">

    {{-- ─── Cabecera del módulo ───────────────────────────────────────────── --}}
    <div class="modulo-header">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-ticket me-2"></i>Solicitar Servicio
            </h1>
            <p class="text-muted mb-0 small">
                Selecciona el área de soporte a la que deseas enviar tu solicitud
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('solicitar_servicio.seguimiento') }}"
               class="btn btn-sm btn-outline-dark rounded-pill px-3">
                <i class="fa fa-list-ul me-1"></i>Mis Servicios
            </a>
            <a href="{{ route('solicitar_servicio.historial') }}"
               class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa fa-history me-1"></i>Historial
            </a>
            <a href="{{ route('solicitar_servicio.reportes') }}"
               class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa fa-bar-chart me-1"></i>Reportes / Analítica
            </a>
        </div>
    </div>

    <hr class="mb-4">

    {{-- ─── Mensaje si no hay áreas ─────────────────────────────────────────── --}}
    @if($areas->isEmpty())
        <div class="alert alert-warning text-center">
            <i class="fa fa-exclamation-triangle me-2"></i>
            No hay áreas de soporte técnico activas en este momento.
        </div>
    @else

    {{-- ─── Tarjetas de Áreas ───────────────────────────────────────────────── --}}
    <div class="row g-3">
        @foreach($areas as $area)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="area-card h-100">
                {{-- Header con color del área --}}
                <div class="area-card-header {{ $area->color ?? 'bg-dark' }}">
                    <i class="{{ $area->icono ?? 'fa fa-cogs' }}"></i>
                    <span>{{ $area->area }}</span>
                </div>

                {{-- Cuerpo --}}
                <div class="area-card-body d-flex flex-column gap-2">
                    {{-- Badge de servicios pendientes --}}
                    <div>
                        @if($area->pendientes_count > 0)
                            <span class="badge-pendientes">
                                <i class="fa fa-clock-o me-1"></i>
                                {{ $area->pendientes_count }} servicio{{ $area->pendientes_count != 1 ? 's' : '' }} pendiente{{ $area->pendientes_count != 1 ? 's' : '' }}
                            </span>
                        @else
                            <span class="badge-pendientes sin-pendientes">
                                Sin servicios pendientes
                            </span>
                        @endif
                    </div>

                    {{-- Botón de solicitud --}}
                    <button type="button"
                            class="btn-generar-solicitud"
                            data-id-area="{{ $area->id }}"
                            data-nombre-area="{{ $area->area }}">
                        <i class="fa fa-plus me-1"></i>Generar Solicitud
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL — Nueva Solicitud de Servicio
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-nueva-solicitud" tabindex="-1"
     aria-labelledby="modal-nueva-solicitud-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-bottom" style="border-bottom: 2px solid #000 !important;">
                <h5 class="modal-title fw-bold" id="modal-nueva-solicitud-label">
                    <i class="fa fa-ticket me-2"></i>Generar Solicitud
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border d-flex align-items-center gap-2 py-2 mb-3">
                    <i class="fa fa-info-circle text-dark"></i>
                    <span class="small">
                        Área de soporte: <strong id="modal-nombre-area">—</strong>
                    </span>
                </div>

                <form id="form-solicitud" novalidate>
                    @csrf
                    <input type="hidden" id="modal-id-area" name="id_area">

                    <div class="mb-3">
                        <label for="input-descripcion" class="form-label fw-semibold small">
                            Descripción del servicio solicitado
                            <span class="text-danger">*</span>
                        </label>
                        <textarea id="input-descripcion"
                                  name="descripcion"
                                  class="form-control"
                                  rows="5"
                                  minlength="10"
                                  maxlength="2000"
                                  required
                                  placeholder="Describe detalladamente el servicio que necesitas..."></textarea>
                        <div class="form-text text-muted small">Mínimo 10 caracteres.</div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cancelar
                </button>
                <button type="submit" form="form-solicitud"
                        class="btn btn-sm btn-dark px-4"
                        id="btn-enviar-solicitud">
                    <i class="fa fa-paper-plane me-1"></i>Generar Solicitud
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.routes = {
            store      : "{{ route('solicitar_servicio.store') }}",
            seguimiento: "{{ route('solicitar_servicio.seguimiento') }}",
            historial  : "{{ route('solicitar_servicio.historial') }}",
            liberar    : "{{ url('solicitar-servicio') }}/__ID__/liberar",
            detalles   : "{{ url('solicitar-servicio') }}/__ID__/detalles",
        };
    </script>
    @vite([
        'resources/css/soporte_tecnico/solicitar_servicio/solicitar_servicio.css',
        'resources/js/soporte_tecnico/solicitar_servicio/solicitar_servicio.js'
    ])
@endpush
@endsection
