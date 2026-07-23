{{-- Datos de transporte ocultos para el Javascript --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $puestos->total() }}"
    data-info="Mostrando {{ $puestos->firstItem() ?? 0 }} a {{ $puestos->lastItem() ?? 0 }} de {{ $puestos->total() }} registros">
    <td>
        <div id="htmlLinksPaginacion">
            {{-- Renderiza los enlaces nativos de Bootstrap --}}
            {{ $puestos->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Cuerpo real de la Tabla --}}
@forelse($puestos as $pue)
    <tr class="{{ $pue->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($puestos->currentPage() - 1) * $puestos->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <a href="{{ route('puestos.edit', $pue->id) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $pue->puesto }}</span>
        </td>
        <td>
            {{ $pue->fecha ? \Carbon\Carbon::parse($pue->fecha)->format('d/m/Y') : 'N/A' }}
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('puestos.status', $pue->id) }}"
               class="btn-toggle-status badge {{ $pue->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-url="{{ route('puestos.status', $pue->id) }}"
               data-nombre="{{ $pue->puesto }}"
               data-activo="{{ $pue->activo }}"
               title="{{ $pue->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $pue->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $pue->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-4 text-muted">
            <i class="fa fa-briefcase fs-3 mb-2 d-block"></i> No hay puestos registrados
        </td>
    </tr>
@endforelse
