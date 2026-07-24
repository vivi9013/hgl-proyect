{{-- Datos de transporte ocultos para el Javascript --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $sedes->total() }}"
    data-info="Mostrando {{ $sedes->firstItem() ?? 0 }} a {{ $sedes->lastItem() ?? 0 }} de {{ $sedes->total() }} registros">
    <td>
        <div id="htmlLinksPaginacion">
            {{-- Renderiza los enlaces nativos de Bootstrap --}}
            {{ $sedes->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Cuerpo real de la Tabla --}}
@forelse($sedes as $sed)
    <tr class="{{ $sed->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($sedes->currentPage() - 1) * $sedes->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <a href="{{ route('sedes.edit', $sed->id) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $sed->nombre }}</span>
        </td>
        <td>
            <span class="badge bg-secondary py-1 px-2">{{ $sed->abreviatura }}</span>
        </td>
        <td>
            {{ $sed->fecha ? \Carbon\Carbon::parse($sed->fecha)->format('d/m/Y') : 'N/A' }}
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('sedes.status', $sed->id) }}"
               class="btn-toggle-status badge {{ $sed->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-url="{{ route('sedes.status', $sed->id) }}"
               data-nombre="{{ $sed->nombre }}"
               data-activo="{{ $sed->activo }}"
               title="{{ $sed->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $sed->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $sed->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-4 text-muted">
            <i class="fa fa-university fs-3 mb-2 d-block"></i> No hay sedes registradas
        </td>
    </tr>
@endforelse
