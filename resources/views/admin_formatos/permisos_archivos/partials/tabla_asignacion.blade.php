{{-- Datos de transporte ocultos para que el JS controle la paginación --}}
<tr id="datosPaginacionTransporte" style="display: none;"
    data-total="{{ $categorias->total() }}"
    data-info="Mostrando {{ $categorias->firstItem() ?? 0 }} a {{ $categorias->lastItem() ?? 0 }} de {{ $categorias->total() }} categorías">
    <td colspan="3">
        <div id="htmlLinksPaginacion">
            {{ $categorias->links('pagination::bootstrap-4') }}
        </div>
    </td>
</tr>

{{-- Renglones de la matriz --}}
@forelse($categorias as $index => $cat)
    @php
        $tieneAcceso = $trabajador->categorias->contains($cat->id_catego_archivos);
    @endphp
    <tr class="fila-categoria {{ $tieneAcceso ? 'table-success-soft' : '' }}" data-id="{{ $cat->id_catego_archivos }}">
        <td class="ps-4 fw-medium text-secondary">
            {{ ($categorias->currentPage() - 1) * $categorias->perPage() + $loop->iteration }}
        </td>
        
        <td>
            <div class="fw-bold text-dark fs-6 label-categoria">
                {{ $cat->categoria }}
            </div>
            <small class="text-muted">Estatus en catálogo: {{ $cat->activo == 1 ? 'Activo' : 'Inactivo' }}</small>
        </td>

        <td class="text-center pe-4">
            <div class="form-check form-switch d-inline-block">
                {{-- Nota: Quitamos el atributo 'name' para evitar que inputs desactualizados en el DOM interfieran con el submit --}}
                <input 
                    class="form-check-input chk-permiso" 
                    type="checkbox" 
                    value="{{ $cat->id_catego_archivos }}" 
                    id="cat_{{ $cat->id_catego_archivos }}"
                    {{ $tieneAcceso ? 'checked' : '' }}
                >
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center py-5">
            <div class="py-4">
                <i class="fa fa-folder-open-o fa-3x mb-3 text-secondary opacity-40"></i>
                <p class="mb-0 fw-medium text-secondary">No hay categorías registradas en el sistema.</p>
            </div>
        </td>
    </tr>
@endforelse