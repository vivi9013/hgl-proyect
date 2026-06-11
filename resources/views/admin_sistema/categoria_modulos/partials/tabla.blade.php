@foreach($categorias as $index => $row)
    @php
        // Determinamos si el registro está inactivo para aplicar estilos visuales planos
        $esInactivo = ($row->activo != 1);
        $claseFila = $esInactivo ? 'text-muted bg-light-gray' : '';
    @endphp
    <tr class="{{ $claseFila }}">
        {{-- Índice incremental correcto basado en paginación --}}
        <td class="ps-4 fw-bold index-cell">{{ $row->orden }}</td>
        
        <td>{{ $row->categoria }}</td>
        
        <td>{{ $row->proyecto }}</td>
        
        {{-- Columna interactiva: Panel Abierto --}}
        <td class="text-center">
            <button type="button" class="btn btn-link btn-sm btn-toggle-colapsar p-0" data-id="{{ $row->id_CategoriaModulo }}">
                @if($row->colapsado == 'no')
                    <i class="fa fa-thumbs-o-up text-primary fs-5" title="Abierto"></i>
                @else
                    <i class="fa fa-thumbs-o-down text-secondary fs-5" title="Cerrado"></i>
                @endif
            </button>
        </td>
        
        {{-- Columna interactiva: Status --}}
        <td class="text-center">
            <button type="button" class="btn btn-link btn-sm btn-toggle-status p-0" data-id="{{ $row->id_CategoriaModulo }}">
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
                <a href="{{ route('categoria_modulos.edit', $row->id_CategoriaModulo) }}" class="btn btn-sm btn-outline-dark border-0" title="Editar Categoría">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
            </div>
        </td>
    </tr>
@endforeach

@if($categorias->isEmpty())
    <tr>
        <td colspan="6" class="text-center py-4 text-muted">
            <i class="fa fa-exclamation-circle me-2"></i>No se encontraron categorías registradas.
        </td>
    </tr>
@endif