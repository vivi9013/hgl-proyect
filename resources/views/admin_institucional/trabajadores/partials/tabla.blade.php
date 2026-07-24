{{-- Datos de transporte ocultos para el Javascript --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $trabajadores->total() }}"
    data-info="Mostrando {{ $trabajadores->firstItem() ?? 0 }} a {{ $trabajadores->lastItem() ?? 0 }} de {{ $trabajadores->total() }} registros">
    <td>
        <div id="htmlLinksPaginacion">
            {{-- Renderiza los enlaces nativos de Bootstrap --}}
            {{ $trabajadores->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Cuerpo real de la Tabla --}}
@forelse($trabajadores as $t)
    <tr class="{{ $t->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($trabajadores->currentPage() - 1) * $trabajadores->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary rounded-circle btn-editar-trabajador"
                    data-id="{{ $t->id }}"
                    title="Editar trabajador">
                <i class="fa fa-pencil"></i>
            </button>
        </td>
        <td>
            <span class="badge bg-secondary font-monospace fs-6">{{ $t->num_empleado }}</span>
        </td>
        <td>
            @if($t->persona)
                <span class="fw-bold text-dark">{{ $t->persona->nombre }} {{ $t->persona->ap_paterno }} {{ $t->persona->ap_materno }}</span>
                @if($t->persona->rfc)
                    <br><small class="text-muted">RFC: {{ $t->persona->rfc }}</small>
                @endif
            @else
                <span class="text-danger font-italic">Sin datos de persona</span>
            @endif
        </td>
        <td>
            <span class="badge bg-light text-dark border">{{ $t->sede ? $t->sede->nombre : 'N/A' }}</span>
        </td>
        <td>
            {{ $t->departamento ? $t->departamento->nombre : 'N/A' }}
        </td>
        <td>
            {{ $t->puesto ? $t->puesto->puesto : 'N/A' }}
        </td>
        <td>
            <span class="badge bg-info text-dark">{{ $t->tipoTrabajador ? $t->tipoTrabajador->tipo : 'N/A' }}</span>
        </td>
        <td>
            {{ $t->fecha_ingreso ? \Carbon\Carbon::parse($t->fecha_ingreso)->format('d/m/Y') : 'N/A' }}
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('trabajadores.status', $t->id) }}"
               class="btn-toggle-status badge {{ $t->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               data-url="{{ route('trabajadores.status', $t->id) }}"
               data-nombre="Trabajador No. {{ $t->num_empleado }}"
               data-activo="{{ $t->activo }}"
               title="{{ $t->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $t->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                {{ $t->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center py-4 text-muted">
            <i class="fa fa-male fs-3 mb-2 d-block"></i> No hay trabajadores registrados con los criterios seleccionados.
        </td>
    </tr>
@endforelse
