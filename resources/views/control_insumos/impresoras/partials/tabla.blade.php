@forelse($impresoras as $index => $imp)
    <tr class="{{ !$imp->activo ? 'text-muted table-light' : '' }}">
        <td class="text-center fw-bold">{{ $impresoras->firstItem() + $index }}</td>
        <td class="text-center">
            <a href="{{ route('impresoras.edit', $imp->id_impresora) }}"
               class="text-decoration-none"
               title="Editar impresora">
                <i class="fa fa-pencil-square-o fa-lg"></i>
            </a>
        </td>
        <td class="fw-semibold">{{ $imp->inventario }}</td>
        <td>{{ $imp->marca }}</td>
        <td>{{ $imp->modelo }}</td>
        <td>{{ $imp->tipo }}</td>
        <td>{{ $imp->serie }}</td>
        <td>{{ $imp->tecnologia ?? 'N/A' }}</td>
        <td class="text-center">{{ $imp->consumible }}</td>
        <td class="text-center">
            <span class="badge {{ $imp->red === 'Si' ? 'bg-success' : 'bg-secondary' }}">
                {{ $imp->red }}
            </span>
        </td>
        <td class="font-monospace">{{ $imp->ip ?? 'N/A' }}</td>
        <td class="text-center">
            <span class="badge {{ $imp->comodato === 'Si' ? 'bg-info text-dark' : 'bg-light text-dark border' }}">
                {{ $imp->comodato }}
            </span>
        </td>
        <td class="text-center">
            <button type="button"
                    class="btn btn-sm btn-alternar-estado border-0 bg-transparent p-0"
                    data-id="{{ $imp->id_impresora }}"
                    data-marca-modelo="{{ $imp->marca }} {{ $imp->modelo }}"
                    data-activo="{{ $imp->activo }}"
                    title="{{ $imp->activo ? 'Desactivar' : 'Activar' }}">
                <i class="fa {{ $imp->activo ? 'fa-check-square-o text-success' : 'fa-square-o text-muted' }} fa-lg"></i>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="13" class="text-center text-muted py-4">
            <i class="fa fa-print fa-2x mb-2 d-block"></i>
            No se encontraron impresoras en el catálogo.
        </td>
    </tr>
@endforelse
