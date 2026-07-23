{{-- Datos de transporte ocultos para el Javascript --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $mobiliarios->total() }}"
    data-info="Mostrando {{ $mobiliarios->firstItem() ?? 0 }} a {{ $mobiliarios->lastItem() ?? 0 }} de {{ $mobiliarios->total() }} registros">
    <td>
        <div id="htmlLinksPaginacion">
            {{-- Renderiza los enlaces nativos de Bootstrap --}}
            {{ $mobiliarios->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Cuerpo real de la Tabla --}}
@forelse($mobiliarios as $mob)
    <tr class="{{ $mob->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($mobiliarios->currentPage() - 1) * $mobiliarios->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <a href="{{ route('mobiliario.edit', $mob->id) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $mob->inventario }}</span>
        </td>
        <td>
            @if($mob->tipoMobiliario)
                <span class="badge bg-secondary text-white">{{ $mob->tipoMobiliario->tipo }}</span>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td>{{ $mob->descripcion }}</td>
        <td>{{ $mob->marca }} / {{ $mob->modelo }}</td>
        <td>{{ $mob->serie ?: 'N/A' }}</td>
        <td>
            @if($mob->persona)
                {{ $mob->persona->nombre }} {{ $mob->persona->ap_paterno }}
            @else
                <span class="text-muted">Sin asignar</span>
            @endif
        </td>
        <td>
            @if($mob->area)
                <span class="badge bg-light text-secondary border px-2 py-1">
                    {{ $mob->area->area }}
                </span>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td>
            @if($mob->departamento)
                {{ $mob->departamento->nombre }}
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('mobiliario.status', $mob->id) }}"
               class="btn-toggle-status badge {{ $mob->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-url="{{ route('mobiliario.status', $mob->id) }}"
               data-nombre="{{ $mob->inventario }}"
               data-activo="{{ $mob->activo }}"
               title="{{ $mob->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $mob->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $mob->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center py-4 text-muted">
            <i class="fa fa-cubes fs-3 mb-2 d-block"></i> No hay mobiliario registrado
        </td>
    </tr>
@endforelse
