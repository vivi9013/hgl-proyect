<div class="card border-0 shadow-sm">
    @if(session('exitog'))
        <div class="alert alert-success alert-dismissible fade show mx-3 mt-3 mb-0" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('exitog') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark text-uppercase small">
                <tr>
                    <th class="ps-4" style="width: 60px;">#</th>
                    <th>Área de Abastecimiento</th>
                    <th class="text-center" style="width: 140px;">Plantillas</th>
                    <th class="text-center" style="width: 140px;">Total Insumos</th>
                    <th class="text-center" style="width: 100px;">Estatus</th>
                    <th class="text-center pe-4" style="width: 120px;">PDF</th>
                </tr>
            </thead>
            <tbody>
                @forelse($areas as $index => $area)
                    @php
                        $plantillasActivas   = $area->plantillas->where('activo', 1);
                        $totalInsumos        = $area->plantillas->sum(fn($p) => $p->detalles->count());
                        $tienePlantillas     = $area->plantillas->count() > 0;
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted small">{{ $areas->firstItem() + $index }}</td>

                        <td>
                            <div class="fw-bold text-dark">{{ $area->nombre }}</div>
                            @if($area->plantillas->count() > 0)
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @foreach($area->plantillas as $plt)
                                        <span class="badge {{ $plt->activo ? 'bg-primary' : 'bg-secondary' }} text-white"
                                              style="font-size: 0.7rem; font-weight: 500;">
                                            {{ $plt->nombre }}
                                            @if($plt->subareaAbastecimiento)
                                                <span class="opacity-75">· {{ $plt->subareaAbastecimiento->nombre }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($tienePlantillas)
                                <span class="badge bg-info text-dark">
                                    <i class="bi bi-clipboard2-check me-1"></i>{{ $area->plantillas->count() }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($totalInsumos > 0)
                                <span class="badge bg-success">
                                    <i class="bi bi-boxes me-1"></i>{{ $totalInsumos }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($area->activo ?? 1)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Activa</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactiva</span>
                            @endif
                        </td>

                        {{-- ► Columna PDF: genera el reporte de la plantilla de esta área --}}
                        <td class="text-center pe-4">
                            @if($tienePlantillas)
                                @php $primera = $area->plantillas->first(); @endphp
                                <a href="{{ route('plantillas_pedido.imprimir_individual', $primera->id_plantilla_pedido) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-danger"
                                   title="Ver formato PDF de {{ $area->nombre }}">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                </a>
                            @else
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Sin plantilla asignada">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-2 d-block mb-2"></i>
                            No se encontraron áreas de abastecimiento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pie: info + paginación --}}
    <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
        <div class="text-muted small">
            Mostrando {{ $areas->firstItem() ?? 0 }} a {{ $areas->lastItem() ?? 0 }}
            de {{ $areas->total() }} áreas
        </div>
        <nav>
            <div id="paginacionPlantillas">
                @if($areas->count() > 0)
                    {{ $areas->links('pagination::bootstrap-4') }}
                @endif
            </div>
        </nav>
    </div>
</div>
