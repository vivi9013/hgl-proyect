<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 0.875rem;">
        <thead class="bg-light text-secondary">
            <tr>
                <th class="ps-3" style="width: 80px;">ID</th>
                <th>FECHA / HORA</th>
                <th>ÁREA SOLICITANTE</th>
                <th>SUBÁREA</th>
                <th>ÁREA DE ALMACÉN</th>
                <th>SOLICITADO POR</th>
                <th class="text-center">ESTADO</th>
                <th class="text-end pe-3">ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                <tr>
                    <td class="ps-3 font-monospace fw-bold text-dark">#{{ $pedido->id_pedido }}</td>
                    <td>
                        <div><i class="bi bi-calendar-event me-1 text-muted"></i>{{ $pedido->fecha_registro ? $pedido->fecha_registro->format('d/m/Y') : 'N/A' }}</div>
                        <div class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $pedido->hora_registro ?: '--:--' }}</div>
                    </td>
                    <td class="fw-semibold text-dark">
                        {{ $pedido->areaAbastecimiento ? $pedido->areaAbastecimiento->nombre : 'N/A' }}
                    </td>
                    <td>
                        {{ $pedido->subareaAbastecimiento ? $pedido->subareaAbastecimiento->nombre : 'Sin subárea' }}
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $pedido->areaAlmacen ? $pedido->areaAlmacen->nombre : 'General' }}
                        </span>
                    </td>
                    <td>
                        {{ $pedido->usuario && $pedido->usuario->persona ? $pedido->usuario->persona->nombre . ' ' . $pedido->usuario->persona->ap_paterno : 'Sistema' }}
                    </td>
                    <td class="text-center">
                        @if($pedido->status === 'terminado')
                            <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Enviado a CENDIS</span>
                        @elseif($pedido->status === 'Aceptado')
                            <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Surtido y Aceptado</span>
                        @elseif($pedido->status === 'cancelado')
                            <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i>Cancelado</span>
                        @elseif($pedido->status === 'borrador')
                            <span class="badge bg-secondary px-2 py-1"><i class="bi bi-pencil-square me-1"></i>Borrador</span>
                        @else
                            <span class="badge bg-dark px-2 py-1">{{ ucfirst($pedido->status) }}</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-dark btn-ver-detalle" data-id="{{ $pedido->id_pedido }}" title="Ver Detalle">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="{{ route('pedido_insumos.imprimir', $pedido->id_pedido) }}" target="_blank" class="btn btn-outline-primary" title="Imprimir Comprobante">
                                <i class="bi bi-printer"></i>
                            </a>
                            @if(in_array($pedido->status, ['terminado', 'borrador']))
                                <button type="button" class="btn btn-outline-danger btn-cancelar-pedido" data-id="{{ $pedido->id_pedido }}" title="Cancelar Pedido">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        No se encontraron pedidos de insumos registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Transport dynamic pagination data for JavaScript AJAX renderer -->
<div id="ajax-pagination-data" 
     data-current-page="{{ $pedidos->currentPage() }}" 
     data-last-page="{{ $pedidos->lastPage() }}" 
     data-total="{{ $pedidos->total() }}" 
     data-from="{{ $pedidos->firstItem() ?? 0 }}" 
     data-to="{{ $pedidos->lastItem() ?? 0 }}" 
     style="display: none;">
</div>
