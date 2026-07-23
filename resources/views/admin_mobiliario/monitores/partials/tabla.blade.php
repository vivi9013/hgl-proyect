{{-- Datos de transporte ocultos para el Javascript --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $monitores->total() }}"
    data-info="Mostrando {{ $monitores->firstItem() ?? 0 }} a {{ $monitores->lastItem() ?? 0 }} de {{ $monitores->total() }} registros">
    <td>
        <div id="htmlLinksPaginacion">
            {{-- Renderiza los enlaces nativos de Bootstrap --}}
            {{ $monitores->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Cuerpo real de la Tabla --}}
@forelse($monitores as $mon)
    <tr class="{{ $mon->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($monitores->currentPage() - 1) * $monitores->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <a href="{{ route('monitores.edit', $mon->id_monitor) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil text-dark"></i>
            </a>
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $mon->inventario }}</span>
        </td>
        <td>
            {{ $mon->marca }} / {{ $mon->modelo }}
        </td>
        <td>
            <span class="badge bg-light text-dark border px-2 py-1">{{ $mon->tipo }}</span>
        </td>
        <td>{{ $mon->serie ?: 'N/A' }}</td>
        <td>{{ $mon->descripcion ?: 'N/A' }}</td>
        <td>
            @if($mon->mobiliario && $mon->mobiliario->area)
                <span class="badge bg-light text-secondary border px-2 py-1">
                    {{ $mon->mobiliario->area->area }}
                </span>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td>
            @if($mon->mobiliario && $mon->mobiliario->persona)
                {{ $mon->mobiliario->persona->nombre }} {{ $mon->mobiliario->persona->ap_paterno }}
            @else
                <span class="text-muted">Sin asignar</span>
            @endif
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('monitores.status', $mon->id_monitor) }}"
               class="btn-toggle-status badge {{ $mon->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-url="{{ route('monitores.status', $mon->id_monitor) }}"
               data-nombre="{{ $mon->inventario }}"
               data-activo="{{ $mon->activo }}"
               title="{{ $mon->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $mon->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $mon->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center py-4 text-muted">
            <i class="fa fa-television fs-3 mb-2 d-block"></i> No hay monitores registrados
        </td>
    </tr>
@endforelse
