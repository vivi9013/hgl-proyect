@forelse($impresoras as $index => $imp)
    <tr class="{{ !$imp->activo ? 'text-muted table-light' : '' }}">
        <td class="text-center fw-bold">{{ $impresoras->firstItem() + $index }}</td>
        <td class="text-center">
            <a href="{{ route('impresoras.edit', $imp->id_impresora) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
        <td class="fw-semibold text-dark">{{ $imp->inventario }}</td>
        <td>{{ $imp->serie }}</td>
        <td>{{ $imp->modelo }}</td>
        <td>{{ $imp->marca }}</td>
        <td>{{ $imp->descripcion ?? '' }}</td>
        <td>{{ $imp->tecnologia ?? 'N/A' }}</td>
        <td class="text-center">{{ $imp->consumible }}</td>
        <td class="text-center">
            <span class="badge {{ $imp->red === 'Si' ? 'bg-success' : 'bg-secondary' }}">
                {{ $imp->red }}
            </span>
        </td>
        <td class="font-monospace"><code>{{ $imp->ip ?? 'N/A' }}</code></td>
        <td class="text-center pe-4">
            <a href="{{ route('impresoras.status', $imp->id_impresora) }}"
               class="btn-toggle-status badge {{ $imp->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-id="{{ $imp->id_impresora }}"
               data-url="{{ route('impresoras.status', $imp->id_impresora) }}"
               data-nombre="{{ $imp->inventario }}"
               data-marca-modelo="{{ $imp->marca }} {{ $imp->modelo }}"
               data-activo="{{ $imp->activo }}"
               title="{{ $imp->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $imp->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $imp->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="text-center text-muted py-4">
            <i class="fa fa-print fa-2x mb-2 d-block"></i>
            No se encontraron impresoras en el catálogo.
        </td>
    </tr>
@endforelse
