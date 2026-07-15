@forelse($movimientos as $index => $mov)
    <tr class="{{ !$mov->activo ? 'fila-cancelada' : '' }}">

        {{-- # --}}
        <td class="text-center fw-bold text-muted small">{{ $movimientos->firstItem() + $index }}</td>

        {{-- Acciones (2ª posición) — solo Editar si activo --}}
        <td class="text-center">
            @if($mov->activo)
                <a href="#"
                   class="btn-editar-movimiento btn btn-sm btn-outline-dark rounded-pill px-2 py-1"
                   data-id="{{ $mov->id_movimiento }}"
                   title="Editar concepto, fecha o proveedor">
                    <i class="fa fa-pencil"></i>
                </a>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>

        {{-- Tipo --}}
        <td class="text-center">
            <span class="badge rounded-pill px-3 py-2
                {{ $mov->tipo === 'Entrada' ? 'bg-success' : 'bg-danger' }}">
                <i class="fa {{ $mov->tipo === 'Entrada' ? 'fa-arrow-circle-down' : 'fa-arrow-circle-up' }} me-1"></i>
                {{ $mov->tipo }}
            </span>
        </td>

        {{-- Insumo --}}
        <td>
            @if($mov->insumo)
                <strong>{{ $mov->insumo->modelo }}</strong>
                <br><small class="text-muted">{{ $mov->insumo->color }} · {{ $mov->insumo->familia }}</small>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        {{-- Concepto --}}
        <td><span class="small">{{ $mov->concepto }}</span></td>

        {{-- Cantidad --}}
        <td class="text-center fw-bold">{{ $mov->cantidad }}</td>

        {{-- Proveedor --}}
        <td class="small">
            @if($mov->tipo === 'Entrada' && $mov->proveedor)
                <i class="fa fa-truck text-muted me-1"></i>{{ $mov->proveedor }}
            @else
                <span class="text-muted">—</span>
            @endif
        </td>

        {{-- Fecha --}}
        <td class="text-center small text-muted">
            {{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y') }}
        </td>

        {{-- Estado — clic en badge "Activo" para cancelar el movimiento --}}
        <td class="text-center">
            @if($mov->activo)
                <span class="badge-cancelar-movimiento badge bg-success rounded-pill px-2 py-1"
                      style="cursor: pointer;"
                      data-id="{{ $mov->id_movimiento }}"
                      data-tipo="{{ $mov->tipo }}"
                      data-detalle="{{ $mov->tipo }} de {{ $mov->insumo->modelo ?? '' }} ({{ $mov->cantidad }} pz.)"
                      title="Clic para cancelar este movimiento">
                    <i class="fa fa-check-circle me-1"></i>Activo
                </span>
            @else
                <span class="badge bg-secondary rounded-pill px-2 py-1">
                    <i class="fa fa-ban me-1"></i>Cancelado
                </span>
            @endif
        </td>

    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center text-muted py-5">
            <i class="fa fa-exchange fa-2x mb-2 d-block opacity-25"></i>
            No se encontraron movimientos con los filtros seleccionados.
        </td>
    </tr>
@endforelse
