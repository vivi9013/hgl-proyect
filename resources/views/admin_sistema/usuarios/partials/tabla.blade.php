@foreach($usuarios as $index => $row)
    @php
        $esInactivo = ($row->activo != 1);
        $claseFila = $esInactivo ? 'text-muted bg-light-gray' : '';
    @endphp
    <tr class="{{ $claseFila }}">
        {{-- Índice incremental correcto basado en paginación --}}
        <td class="ps-4 fw-bold index-cell">{{ ($usuarios->currentPage() - 1) * $usuarios->perPage() + $loop->iteration }}</td>
        
        {{-- Columna: Editar --}}
        <td class="text-center">
            <a href="{{ route('usuarios.edit', $row->id) }}" class="btn btn-link btn-sm p-0" title="Editar Usuario">
                <i class="fa fa-pencil-square-o"></i>
            </a>
        </td>

        {{-- Columna: Nombre --}}
        <td class="fw-semibold">
            @if($row->persona)
                {{ $row->persona->ap_paterno }} {{ $row->persona->ap_materno }} {{ $row->persona->nombre }}
            @else
                <span class="text-danger small"><i>Sin persona vinculada</i></span>
            @endif
        </td>
        
        {{-- Columna: Usuario --}}
        <td>{{ $row->nombre_usuario }}</td>

        {{-- Columna: Perfil --}}
        <td>
            @if($row->perfil)
                {{ $row->perfil->name ?? $row->perfil->nombre }}
            @else
                <span class="text-warning small"><i>Sin perfil asignado</i></span>
            @endif
        </td>
        
        {{-- Columna interactiva: Reiniciar contraseña --}}
        <td class="text-center">
            <button type="button" class="btn btn-link btn-sm btn-restablecer-password p-0" data-id="{{ $row->id }}">
                <i class="fa fa-refresh fs-5 text-dark" title="Reiniciar Contraseña"></i>
            </button>
        </td>

        {{-- Columna interactiva: Status --}}
        <td class="text-center pe-4">
            <button type="button" class="btn btn-link btn-sm btn-toggle-status p-0" data-id="{{ $row->id }}">
                @if($row->activo == 1)
                    <i class="fa fa-check-square-o text-success fs-5" title="Activo"></i>
                @else
                    <i class="fa fa-square-o text-danger fs-5" title="Inactivo"></i>
                @endif
            </button>
        </td>
    </tr>
@endforeach

@if($usuarios->isEmpty())
    <tr>
        <td colspan="7" class="text-center py-4 text-muted">
            <i class="fa fa-exclamation-circle me-2"></i>No se encontraron usuarios registrados.
        </td>
    </tr>
@endif
