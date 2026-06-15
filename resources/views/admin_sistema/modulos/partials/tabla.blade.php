@forelse ($modulos as $index => $mod)
    @php
        $stu   = ($mod->activo == 1) ? '' : 'text-muted';
        $color = $mod->color ?? 'blue';
        $bgClass = 'bg-' . $color;
    @endphp
    <tr class="{{ $stu }}" id="row_{{ $mod->id }}">
        {{-- 1. # --}}
        <td class="text-center text-muted small fw-bold">
            {{ $modulos->firstItem() + $index }}
        </td>

        {{-- 2. Editar --}}
        <td class="text-center">
            <a href="{{ route('modulos.edit', $mod->id) }}" class="text-decoration-none" title="Editar Módulo">
                <i class="fa fa-pencil-square-o fa-lg" style="color: #000;"></i>
            </a>
        </td>

        {{-- 3. Agregar proyecto --}}
        <td class="text-center">
            <a href="{{ route('modulos.edit', $mod->id) }}?seccion=proyectos" class="text-decoration-none" title="Asignar Proyectos">
                <i class="fa fa-plus fa-lg" style="color: #0073b7;"></i>
            </a>
        </td>

        {{-- 4. Agregar perfil --}}
        <td class="text-center">
            <a href="{{ route('modulos.edit', $mod->id) }}?seccion=perfiles" class="text-decoration-none" title="Asignar Perfiles">
                <i class="fa fa-plus fa-lg" style="color: #0073b7;"></i>
            </a>
        </td>

        {{-- 5. Módulo --}}
        <td class="{{ $bgClass }} text-white fw-bold col-nombre-modulo">
            {{ $mod->nombre }}
        </td>

        {{-- 6. Categoría --}}
        <td class="{{ $bgClass }} text-white">
            {{ $mod->categoria->categoria ?? 'Sin Categoría' }}
        </td>

        {{-- 7. Icono --}}
        <td class="{{ $bgClass }} text-white text-center">
            <i class="{{ $mod->icono ?? 'fa fa-cube' }} fa-lg"></i>
        </td>

        {{-- 8. Creador --}}
        <td class="{{ $bgClass }} text-white text-nowrap">
            {{ $mod->creador ?? '—' }}
        </td>

        {{-- 9. Proyectos --}}
        <td class="{{ $bgClass }} text-white text-center fw-bold">
            {{ $mod->proyectos_count ?? 0 }}
        </td>

        {{-- 10. Perfiles --}}
        <td class="{{ $bgClass }} text-white text-center fw-bold">
            {{ $mod->perfiles_count ?? 0 }}
        </td>

        {{-- 11. Status --}}
        <td class="{{ $bgClass }} text-white text-center">
            <input type="checkbox" class="btn-alternar-estado" data-id="{{ $mod->id }}" {{ $mod->activo == 1 ? 'checked' : '' }} style="cursor: pointer; width: 18px; height: 18px;">
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center py-5 text-muted">
            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
            No se encontraron módulos registrados que coincidan con la búsqueda.
        </td>
    </tr>
@endforelse
