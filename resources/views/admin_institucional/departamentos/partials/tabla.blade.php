{{-- Datos de transporte ocultos para el Javascript --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $departamentos->total() }}"
    data-info="Mostrando {{ $departamentos->firstItem() ?? 0 }} a {{ $departamentos->lastItem() ?? 0 }} de {{ $departamentos->total() }} registros">
    <td>
        <div id="htmlLinksPaginacion">
            {{-- Renderiza los enlaces nativos de Bootstrap --}}
            {{ $departamentos->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Cuerpo real de la Tabla --}}
@forelse($departamentos as $dep)
    <tr class="{{ $dep->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($departamentos->currentPage() - 1) * $departamentos->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <a href="{{ route('departamentos.edit', $dep->id) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $dep->nombre }}</span>
        </td>
        <td>
            <span class="badge bg-secondary bg-opacity-10 text-dark px-2 py-1 rounded-pill">{{ $dep->abreviatura }}</span>
        </td>
        <td>
            {{ $dep->extension ?? '—' }}
        </td>
        <td>
            @if($dep->responsable)
                {{ $dep->responsable->nombre }} {{ $dep->responsable->ap_paterno }}
            @else
                <span class="text-muted">Sin asignar</span>
            @endif
        </td>
        <td>
            {{ $dep->fecha ? \Carbon\Carbon::parse($dep->fecha)->format('d/m/Y') : 'N/A' }}
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('departamentos.status', $dep->id) }}"
               class="btn-toggle-status badge {{ $dep->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-url="{{ route('departamentos.status', $dep->id) }}"
               data-nombre="{{ $dep->nombre }}"
               data-activo="{{ $dep->activo }}"
               title="{{ $dep->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $dep->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $dep->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4 text-muted">
            <i class="fa fa-cube fs-3 mb-2 d-block"></i> No hay departamentos registrados
        </td>
    </tr>
@endforelse
