@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 px-4">

    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold mb-0 text-dark">
                <i class="bi bi-file-earmark-text me-2"></i>Reportes de Pedidos de Insumos
            </h1>
            <p class="text-secondary small mb-0">Consulte e imprima los comprobantes oficiales de los pedidos solicitados a CENDIS</p>
        </div>
        <a href="{{ route('pedido_insumos.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Volver a Pedidos
        </a>
    </div>

    <div class="row g-4 mb-4">
        @forelse($pedidos as $p)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="font-monospace fw-bold text-primary">#{{ $p->id_pedido }}</span>
                                <span class="badge bg-light text-dark border">{{ $p->fecha_registro ? $p->fecha_registro->format('d/m/Y') : '' }}</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">
                                {{ $p->areaAbastecimiento ? $p->areaAbastecimiento->nombre : 'Área no especificada' }}
                            </h6>
                            <p class="small text-muted mb-2">
                                Subárea: {{ $p->subareaAbastecimiento ? $p->subareaAbastecimiento->nombre : 'General' }}
                            </p>
                        </div>
                        <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                            <span class="badge bg-secondary">{{ ucfirst($p->status) }}</span>
                            <a href="{{ route('pedido_insumos.imprimir', $p->id_pedido) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-printer me-1"></i>Imprimir Comprobante
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                No hay pedidos registrados para generar comprobantes.
            </div>
        @endforelse
    </div>

</div>
@endsection
