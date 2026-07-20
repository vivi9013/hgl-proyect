@forelse($trabajadores as $index => $trabajador)
    <tr>
        {{-- Cálculo del índice continuo respetando la paginación de 10 --}}
        <td class="ps-4 fw-medium text-secondary">
            {{ ($trabajadores->currentPage() - 1) * $trabajadores->perPage() + $loop->iteration }}
        </td>

        {{-- Botón de Acción para asignar categorías --}}
        <td class="text-center">
            <a href="{{ route('trabajador_categorias.asignar', $trabajador->id) }}"
               class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm text-primary fw-bold d-inline-flex align-items-center gap-1">
                <i class="fa fa-plus-circle"></i> Asignar
            </a>
        </td>

        {{-- Nombre Completo sanitizado --}}
        <td>
            <div class="fw-bold text-dark fs-6">
                {{ $trabajador->ap_paterno }} {{ $trabajador->ap_materno }} {{ $trabajador->nombre }}
            </div>
            <small class="text-muted"><i class="fa fa-building-o me-1"></i>Sede: {{ $trabajador->sede_nombre }}</small>
        </td>

        {{-- Contador de categorías asignadas --}}
        <td class="text-center pe-4">
            @if($trabajador->categorias_count > 0)
                <span class="badge bg-success px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                    <i class="fa fa-check me-1"></i>{{ $trabajador->categorias_count }} Asignadas
                </span>
            @else
                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill">
                    <i class="fa fa-lock me-1"></i>Ninguna
                </span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center py-5">
            <div class="py-4">
                <i class="fa fa-user-times fa-3x mb-3 text-secondary opacity-40"></i>
                <p class="mb-0 fw-medium text-secondary">No se encontraron trabajadores activos en el sistema.</p>
            </div>
        </td>
    </tr>
@endforelse