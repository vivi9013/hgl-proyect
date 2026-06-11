@forelse ($modulos as $index => $mod)
    @php
        $stu   = ($mod->activo == 1) ? '' : 'text-muted';
        $color = $mod->color ?? 'blue';
    @endphp
    <tr class="{{ $stu }}" id="row_{{ $mod->id }}">
        {{-- # --}}
        <td class="ps-4 text-muted small fw-bold">
            {{ $modulos->firstItem() + $index }}
        </td>

        {{-- Módulo --}}
        <td>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-2 py-1" style="background:rgba(0,0,0,.06);">
                    <i class="{{ $mod->icono ?? 'fa fa-cube' }} text-primary"></i>
                </span>
                <div>
                    <span class="fw-semibold col-nombre-modulo">{{ $mod->nombre }}</span><br>
                    <small class="text-muted">{{ Str::limit($mod->descripcion, 50) }}</small>
                </div>
            </div>
        </td>

        {{-- Categoría --}}
        <td>
            <span class="text-dark small">{{ $mod->categoria->categoria ?? 'Sin Categoría' }}</span>
        </td>

        {{-- Icono / Color --}}
        <td>
            <div class="d-flex align-items-center gap-2">
                <i class="{{ $mod->icono ?? 'fa fa-cube' }} fa-lg" style="color: #555;"></i>
                <span class="badge rounded-pill px-2 py-1 small"
                      style="background: rgba(0,0,0,.07); color: #333; font-size:0.7rem;">
                    {{ $mod->color ?? '—' }}
                </span>
            </div>
        </td>

        {{-- Estado --}}
        <td class="text-center">
            <a href="#" class="btn-toggle-status text-decoration-none" data-id="{{ $mod->id }}" title="Cambiar Estado">
                @if($mod->activo == 1)
                    <span class="badge bg-success rounded-pill px-3">Activo</span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3">Inactivo</span>
                @endif
            </a>
        </td>

        {{-- Acciones --}}
        <td class="text-center pe-4">
            <div class="d-flex justify-content-center gap-1">
                <a href="{{ route('modulos.edit', $mod->id) }}"
                   class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1"
                   title="Editar Módulo">
                    <i class="fa fa-pencil"></i>
                </a>
                <a href="{{ route('modulos.proyectos', $mod->id) }}"
                   class="btn btn-sm btn-outline-success rounded-pill px-2 py-1"
                   title="Proyectos">
                    <i class="fa fa-laptop"></i>
                </a>
                <a href="{{ route('modulos.perfiles', $mod->id) }}"
                   class="btn btn-sm btn-outline-info rounded-pill px-2 py-1"
                   title="Perfiles">
                    <i class="fa fa-users"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5 text-muted">
            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
            No se encontraron módulos registrados que coincidan con la búsqueda.
        </td>
    </tr>
@endforelse
