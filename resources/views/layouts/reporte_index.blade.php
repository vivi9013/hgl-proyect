<div class="card border-0 shadow-sm rounded-3 bg-white h-100 d-flex flex-column justify-content-between">

    {{-- Cabecera de la Tarjeta --}}
    <div>
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="fa fa-list-alt text-secondary me-2"></i>{{ $titulo }}
            </h5>
            <span class="text-danger">
                <i class="fa fa-file-pdf-o fa-2x"></i>
            </span>
        </div>

        <div class="card-body px-4 py-3">
            <p class="text-muted small mb-0" style="line-height: 1.6;">
                {{ $descripcion }}
            </p>
        </div>
    </div>

    {{-- Footer de Acción --}}
    <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end border-top">
        <a href="{{ $rutaImprimir }}" target="_blank" class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
            <i class="fa fa-print me-2"></i>{{ $labelBoton ?? 'Imprimir Reporte' }}
        </a>
    </div>

</div>
