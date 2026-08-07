<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
            <tr>
                <th class="ps-4" style="width: 80px;">#</th>
                <th>Insumo</th>
                <th>Clave</th>
                <th>Área Asignada</th>
                <th>Área de Almacén</th>
                <th class="text-center" style="width: 100px;">Cantidad</th>
                <th>Fecha Baja</th>
                <th>Hora</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bajas as $index => $baja)
                <tr class="{{ $baja->cancelado === 'Si' ? 'text-muted fst-italic' : '' }}">
                    <td class="ps-4 fw-bold">{{ ($bajas->currentPage() - 1) * $bajas->perPage() + $loop->iteration }}</td>
                    <td>
                        <span class="badge {{ $baja->insumo->meta_tipo['badgeClass'] ?? 'bg-secondary' }} me-1">
                            {{ $baja->insumo->tipo ?? '' }}
                        </span>
                        <span class="fw-semibold text-dark">{{ $baja->insumo->descripcion ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="clave-pill">{{ $baja->insumo->clave ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $baja->insumo->areaAbastecimiento->nombre ?? $baja->insumo->areaSurtimiento->nombre ?? 'Sin Asignar' }}
                        </span>
                    </td>
                    <td>{{ $baja->areaAlmacen->nombre ?? '—' }}</td>
                    <td class="text-center fw-bold">{{ $baja->cantidad }}</td>
                    <td>{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '' }}</td>
                    <td>{{ $baja->hora_baja }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                        No hay registros de baja para esta área asignada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($bajas->total() > 0)
    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
        <div class="text-muted small">
            Mostrando {{ $bajas->firstItem() ?? 0 }} a {{ $bajas->lastItem() ?? 0 }} de {{ $bajas->total() }} registros
        </div>
        <nav aria-label="Paginación de historial por área">
            {{ $bajas->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
        </nav>
    </div>
@endif
