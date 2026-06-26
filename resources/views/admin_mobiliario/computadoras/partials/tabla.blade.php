{{-- Datos de transporte ocultos para el Javascript --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $computadoras->total() }}"
    data-info="Mostrando {{ $computadoras->firstItem() ?? 0 }} a {{ $computadoras->lastItem() ?? 0 }} de {{ $computadoras->total() }} registros">
    <td>
        <div id="htmlLinksPaginacion">
            {{-- Renderiza los enlaces nativos de Bootstrap --}}
            {{ $computadoras->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Cuerpo real de la Tabla --}}
@forelse($computadoras as $comp)
    <tr class="{{ $comp->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($computadoras->currentPage() - 1) * $computadoras->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <a href="{{ route('computadoras.edit', $comp->id_computadora) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $comp->inventario }}</span>
        </td>
        <td>{{ $comp->nombre_equipo ?: 'N/A' }}</td>
        <td>
            @if($comp->mobiliario)
                {{ $comp->mobiliario->marca }} / {{ $comp->mobiliario->modelo }}
            @else
                <span class="text-danger">Mobiliario no enlazado</span>
            @endif
        </td>
        <td>{{ $comp->so ?: 'N/A' }} / {{ $comp->ram ? $comp->ram . ' MB' : 'N/A' }}</td>
        <td><code>{{ $comp->ip ?: 'N/A' }}</code></td>
        <td>
            @if($comp->mobiliario && $comp->mobiliario->persona)
                {{ $comp->mobiliario->persona->nombre }} {{ $comp->mobiliario->persona->ap_paterno }}
            @else
                <span class="text-muted">Sin asignar</span>
            @endif
        </td>
        <td>
            @if($comp->mobiliario && $comp->mobiliario->area)
                <span class="badge bg-light text-secondary border px-2 py-1">
                    {{ $comp->mobiliario->area->area }}
                </span>
            @else
                <span class="text-muted">N/A</span>
            @endif
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('computadoras.status', $comp->id_computadora) }}"
               class="btn-toggle-status badge {{ $comp->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-url="{{ route('computadoras.status', $comp->id_computadora) }}"
               data-nombre="{{ $comp->inventario }}"
               data-activo="{{ $comp->activo }}"
               title="{{ $comp->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $comp->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $comp->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center py-4 text-muted">
            <i class="fa fa-desktop fs-3 mb-2 d-block"></i> No hay computadoras registradas
        </td>
    </tr>
@endforelse
