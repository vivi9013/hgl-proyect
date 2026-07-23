@forelse($categorias as $index => $cat)
    <tr class="{{ $cat->activo == 0 ? 'text-muted fst-italic' : '' }}">
        <td class="fw-bold">
            {{ ($categorias->currentPage() - 1) * $categorias->perPage() + $loop->iteration }}
        </td>
        <td class="text-center">
            <a href="{{ route('categoria_archivos.edit', $cat->id_catego_archivos) }}"
               title="Editar">
                <i class="fa fa-pencil-square-o text-primary fs-5" aria-hidden="true"></i>
            </a>
        </td>
        <td class="fw-semibold">{{ $cat->categoria }}</td>
        <td>{{ $cat->fecha_registro }}</td>
        <td>{{ $cat->hora_registro }}</td>
        <td class="text-center">
            <a href="#"
               class="btn-toggle-status"
               data-url="{{ route('categoria_archivos.status', $cat->id_catego_archivos) }}"
               data-nombre="{{ $cat->categoria }}"
               data-activo="{{ $cat->activo }}"
               title="{{ $cat->activo == 1 ? 'Desactivar' : 'Activar' }}">
                @if($cat->activo == 1)
                    <i class="fa fa-check-square-o text-success fs-5" aria-hidden="true"></i>
                @else
                    <i class="fa fa-square-o text-danger fs-5" aria-hidden="true"></i>
                @endif
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center text-muted py-4">
            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
            No hay categorías registradas.
        </td>
    </tr>
@endforelse