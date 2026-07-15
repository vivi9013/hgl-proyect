@forelse($insumos as $index => $insumo)
    <tr class="{{ !$insumo->activo ? 'text-muted table-light' : '' }}">
        <td class="text-center fw-bold">{{ $insumos->firstItem() + $index }}</td>
        <td class="text-center">
            <a href="{{ route('insumos_impresoras.edit', $insumo->id_insumo_impresora) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
        <td>{{ $insumo->familia }}</td>
        <td class="fw-semibold text-dark">{{ $insumo->modelo }}</td>
        <td>{{ $insumo->color }}</td>
        <td class="small text-muted">{{ $insumo->modelos_compatibles ?? '—' }}</td>
        <td class="text-center">
            @if($insumo->hojas_uso_total)
                {{ number_format($insumo->hojas_uso_total) }} hojas
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td class="text-center small">{{ $insumo->tiempo_uso ?: '—' }}</td>
        <td class="text-center">
            <span class="badge {{ $insumo->stock > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
                {{ $insumo->stock }}
            </span>
        </td>
        <td class="text-center pe-4">
            <a href="#"
               class="btn-toggle-status badge {{ $insumo->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-id="{{ $insumo->id_insumo_impresora }}"
               data-nombre="{{ $insumo->modelo }}"
               data-activo="{{ $insumo->activo }}"
               title="{{ $insumo->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $insumo->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $insumo->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center text-muted py-4">
            <i class="fa fa-th-large fa-2x mb-2 d-block"></i>
            No se encontraron insumos en el catálogo.
        </td>
    </tr>
@endforelse
