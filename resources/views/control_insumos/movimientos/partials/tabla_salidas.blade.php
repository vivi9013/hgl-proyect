@forelse($movimientos as $index => $mov)
    <tr class="{{ !$mov->activo ? 'fila-cancelada' : '' }}">
        <td class="text-center fw-bold">{{ $movimientos->firstItem() + $index }}</td>
        <td>
            @if($mov->insumo)
                <strong>{{ $mov->insumo->modelo }}</strong>
                <br><small class="text-muted">{{ $mov->insumo->color }} · {{ $mov->insumo->familia }}</small>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>{{ $mov->concepto }}</td>
        <td class="text-center fw-bold">{{ $mov->cantidad }}</td>
        <td>
            @if($mov->impresora)
                {{ $mov->impresora->marca }} {{ $mov->impresora->modelo }}
                @if($mov->impresora->inventario)
                    <br><small class="text-muted">Inv: {{ $mov->impresora->inventario }}</small>
                @endif
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="text-center small">{{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y') }}</td>
        <td class="text-center pe-4">
            @if($mov->activo)
                <a href="#"
                   class="btn-cancelar-movimiento badge bg-success text-decoration-none py-2 px-3 rounded-pill shadow-sm"
                   data-id="{{ $mov->id_movimiento }}"
                   data-tipo="Salida"
                   data-detalle="{{ $mov->insumo->modelo ?? '' }} ({{ $mov->cantidad }} pz.)"
                   title="Activo — Click para cancelar esta salida">
                    <i class="fa fa-check-circle me-1"></i>Activo
                </a>
            @else
                <span class="badge bg-secondary py-2 px-3 rounded-pill">
                    <i class="fa fa-ban me-1"></i>Cancelado
                </span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted py-4">
            <i class="fa fa-arrow-circle-up fa-2x mb-2 d-block"></i>
            No se encontraron salidas registradas.
        </td>
    </tr>
@endforelse
