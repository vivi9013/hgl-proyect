@forelse($proyectos as $index => $p)
    <tr class="{{ !$p->activo ? 'text-muted table-light' : '' }}">
        <td class="text-center fw-bold">{{ $proyectos->firstItem() + $index }}</td>
        <td class="text-center">
            <a href="{{ route('proyectos.edit', $p->id_proyecto) }}"
               class="text-decoration-none"
               title="Editar proyecto">
                <i class="fa fa-pencil-square-o fa-lg" style="color: #000;"></i>
            </a>
        </td>
        <td class="text-center">
            <a href="{{ route('proyectos.edit', $p->id_proyecto) }}?seccion=modulos"
               class="text-decoration-none"
               title="Gestionar Módulos">
                <i class="fa fa-plus fa-lg" style="color: #0073b7;"></i>
            </a>
        </td>
        <td class="fw-semibold">{{ $p->proyecto }}</td>
        <td class="text-center fw-semibold">{{ $p->modulos_count }}</td>
        <td class="text-center">
            <button type="button"
                    class="btn btn-sm btn-alternar-estado border-0 bg-transparent p-0"
                    data-id="{{ $p->id_proyecto }}"
                    data-nombre="{{ $p->proyecto }}"
                    data-activo="{{ $p->activo }}"
                    title="{{ $p->activo ? 'Desactivar' : 'Activar' }}">
                <i class="fa {{ $p->activo ? 'fa-check-square-o text-success' : 'fa-square-o text-muted' }} fa-lg"></i>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center text-muted py-4">
            <i class="fa fa-folder-open-o fa-2x mb-2 d-block"></i>
            No se encontraron proyectos en el catálogo.
        </td>
    </tr>
@endforelse
