@foreach($perfiles as $index => $row)
    @php
        $esInactivo = ($row->activo != 1);
        $claseFila = $esInactivo ? 'text-muted bg-light-gray' : '';
    @endphp
    <tr class="{{ $claseFila }}">
        {{-- Índice incremental correcto basado en paginación --}}
        <td class="ps-4 fw-bold index-cell">{{ ($perfiles->currentPage() - 1) * $perfiles->perPage() + $loop->iteration }}</td>
        
        <td class="fw-semibold">{{ $row->nombre }}</td>
        
        <td>{{ $row->descripcion }}</td>

        {{-- Total Módulos Asignados --}}
        <td class="text-center fw-bold">
            <span class="badge rounded-pill bg-secondary px-3 py-1">
                {{ $row->modulos_count }}
            </span>
        </td>
        
        {{-- Columna interactiva: Status --}}
        <td class="text-center">
            <button type="button" class="btn btn-link btn-sm btn-toggle-status p-0" data-id="{{ $row->id }}">
                @if($row->activo == 1)
                    <i class="fa fa-check-square-o text-success fs-5" title="Activo"></i>
                @else
                    <i class="fa fa-square-o text-danger fs-5" title="Inactivo"></i>
                @endif
            </button>
        </td>
        
        {{-- Columna de Acciones Estándar --}}
        <td class="text-center pe-4">
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('perfiles.edit', $row->id) }}" class="btn btn-sm btn-outline-dark border-0" title="Editar Perfil">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
                <a href="{{ route('perfiles.modulos', $row->id) }}" class="btn btn-sm btn-outline-info border-0" title="Asignar Módulos">
                    <i class="fa fa-plus text-primary"></i> Asignar Módulos
                </a>
            </div>
        </td>
    </tr>
@endforeach

@if($perfiles->isEmpty())
    <tr>
        <td colspan="6" class="text-center py-4 text-muted">
            <i class="fa fa-exclamation-circle me-2"></i>No se encontraron perfiles registrados.
        </td>
    </tr>
@endif
