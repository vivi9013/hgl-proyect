<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th style="width: 100px;">Acciones</th>
                <th>Subárea de Abastecimiento</th>
                <th style="width: 120px;">Siglas</th>
                <th>Área Principal de Abastecimiento</th>
                <th class="text-center" style="width: 120px;">Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subareas as $index => $row)
                <tr>
                    <td class="text-center fw-bold text-secondary">
                        {{ $subareas->firstItem() + $index }}
                    </td>
                    <td>
                        <a href="{{ route('subareas_abastecimiento.edit', $row->id_subarea_abastecimiento) }}" class="text-dark me-2" title="Editar Subárea">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </td>
                    <td class="fw-semibold text-dark">
                        {{ $row->nombre }}
                    </td>
                    <td>
                        @if($row->siglas)
                            <span class="badge bg-light text-secondary border font-monospace px-2 py-1">{{ $row->siglas }}</span>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-medium text-primary">
                            <i class="fa fa-boxes me-1"></i> {{ $row->areaAbastecimiento->nombre ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-status btn-toggle-status {{ $row->activo == 1 ? 'bg-success' : 'bg-danger' }}"
                              data-id="{{ $row->id_subarea_abastecimiento }}"
                              data-status="{{ $row->activo }}"
                              title="Click para cambiar estatus">
                            {{ $row->activo == 1 ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fa fa-folder-open me-2"></i> No se encontraron subáreas de abastecimiento registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($subareas->hasPages())
    <div class="p-3 border-top d-flex justify-content-end">
        {{ $subareas->links() }}
    </div>
@endif
