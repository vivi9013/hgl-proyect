{{-- Datos de transporte ocultos para el Javascript --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $tipos->total() }}"
    data-info="Mostrando {{ $tipos->firstItem() ?? 0 }} a {{ $tipos->lastItem() ?? 0 }} de {{ $tipos->total() }} registros">
    <td>
        <div id="htmlLinksPaginacion">
            {{-- Renderiza los enlaces nativos de Bootstrap --}}
            {{ $tipos->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Cuerpo real de la Tabla --}}
@forelse($tipos as $t)
    <tr class="{{ $t->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($tipos->currentPage() - 1) * $tipos->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <a href="{{ route('tipo_trabajador.edit', $t->id) }}"
               class="btn btn-sm btn-outline-secondary rounded-circle"
               title="Editar registro">
                <i class="fa fa-pencil"></i>
            </a>
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $t->tipo }}</span>
        </td>
        <td>
            {{ $t->fecha ? \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') : 'N/A' }}
        </td>
        <td>
            {{ $t->hora ?? 'N/A' }}
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('tipo_trabajador.status', $t->id) }}"
               class="btn-toggle-status badge {{ $t->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-url="{{ route('tipo_trabajador.status', $t->id) }}"
               data-nombre="{{ $t->tipo }}"
               data-activo="{{ $t->activo }}"
               title="{{ $t->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $t->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $t->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-4 text-muted">
            <i class="fa fa-quote-left fs-3 mb-2 d-block"></i> No hay tipos de trabajador registrados
        </td>
    </tr>
@endforelse
