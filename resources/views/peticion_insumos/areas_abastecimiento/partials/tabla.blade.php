<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th style="width: 100px;">Acciones</th>
                <th>Nombre del Área de Abastecimiento</th>
                <th class="text-center" style="width: 160px;">Subáreas Vinculadas</th>
                <th class="text-center" style="width: 120px;">Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($areas as $index => $row)
                <tr>
                    <td class="text-center fw-bold text-secondary">
                        {{ $areas->firstItem() + $index }}
                    </td>
                    <td>
                        <a href="{{ route('areas_abastecimiento.edit', $row->id_area_abastecimiento) }}" class="text-dark me-2" title="Editar Área">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </td>
                    <td class="fw-semibold text-dark">
                        {{ $row->nombre }}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border px-2 py-1">
                            <i class="fa fa-sitemap me-1 text-primary"></i> &mdash;
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-status btn-toggle-status {{ $row->activo == 1 ? 'bg-success' : 'bg-danger' }}"
                              data-id="{{ $row->id_area_abastecimiento }}"
                              data-status="{{ $row->activo }}"
                              title="Click para cambiar estatus">
                            {{ $row->activo == 1 ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fa fa-folder-open me-2"></i> No se encontraron áreas de abastecimiento registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="p-3 border-top d-flex justify-content-between align-items-center">
    <small class="text-muted">
        Mostrando {{ $areas->firstItem() ?? 0 }} a {{ $areas->lastItem() ?? 0 }} de {{ $areas->total() }} áreas
    </small>
    @if($areas->hasPages())
        <div class="pagination-wrapper">
            {{ $areas->links() }}
        </div>
    @endif
</div>
