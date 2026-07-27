<div class="card border-0 shadow-sm">
    @if(session('exitog'))
        <div class="alert alert-success alert-dismissible fade show mx-3 mt-3 mb-0" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('exitog') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-dark text-uppercase" style="font-size: 0.72rem;">
                <tr>
                    <th class="ps-3" style="width:40px;">#</th>
                    <th style="width:80px;">Editar</th>
                    <th style="width:120px;">Agregar Insumos</th>
                    <th>Almacén</th>
                    <th>Área</th>
                    <th>Subárea</th>
                    <th>Plantilla</th>
                    <th class="text-center" style="width:100px;">Total Insumos</th>
                    <th class="text-center" style="width:90px;">Estatus</th>
                    <th class="text-center" style="width:60px;">PDF</th>
                    <th class="text-center pe-3" style="width:70px;">Eliminar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plantillas as $index => $plantilla)
                    <tr>
                        {{-- # --}}
                        <td class="ps-3 text-muted">{{ $plantillas->firstItem() + $index }}</td>

                        {{-- Editar --}}
                        <td>
                            <button type="button"
                                class="btn btn-sm btn-outline-primary btn-editar-plantilla"
                                data-id="{{ $plantilla->id_plantilla_pedido }}"
                                data-nombre="{{ $plantilla->nombre }}"
                                data-descripcion="{{ $plantilla->descripcion }}"
                                data-area="{{ $plantilla->id_area_abastecimiento }}"
                                data-subarea="{{ $plantilla->id_subarea_abastecimiento }}"
                                title="Editar plantilla">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </td>

                        {{-- Agregar Insumos --}}
                        <td>
                            <a href="{{ route('plantillas_pedido.insumos', $plantilla->id_plantilla_pedido) }}"
                               class="btn btn-sm btn-primary"
                               title="Gestionar insumos de esta plantilla">
                                <i class="bi bi-plus-circle me-1"></i>Insumos
                            </a>
                        </td>

                        {{-- Almacén --}}
                        <td>
                            @if($plantilla->areaAlmacen)
                                <span class="text-dark">{{ $plantilla->areaAlmacen->nombre }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Área --}}
                        <td>
                            <span class="fw-semibold">{{ $plantilla->areaAbastecimiento->nombre ?? '—' }}</span>
                        </td>

                        {{-- Subárea --}}
                        <td>{{ $plantilla->subareaAbastecimiento->nombre ?? '—' }}</td>

                        {{-- Nombre Plantilla --}}
                        <td class="fw-bold text-dark">{{ $plantilla->nombre }}</td>

                        {{-- Total Insumos --}}
                        <td class="text-center">
                            @if($plantilla->detalles->count() > 0)
                                <span class="badge bg-success">{{ $plantilla->detalles->count() }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>

                        {{-- Estatus toggle --}}
                        <td class="text-center">
                            <button type="button"
                                class="btn btn-sm btn-toggle-status {{ $plantilla->activo ? 'btn-success' : 'btn-secondary' }}"
                                data-url="{{ route('plantillas_pedido.status', $plantilla->id_plantilla_pedido) }}"
                                data-nombre="{{ $plantilla->nombre }}"
                                data-activo="{{ $plantilla->activo }}"
                                title="{{ $plantilla->activo ? 'Activa — clic para desactivar' : 'Inactiva — clic para activar' }}">
                                {{ $plantilla->activo ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>

                        {{-- PDF --}}
                        <td class="text-center">
                            <a href="{{ route('plantillas_pedido.imprimir_individual', $plantilla->id_plantilla_pedido) }}"
                               target="_blank"
                               class="btn btn-sm btn-danger"
                               title="Ver PDF de {{ $plantilla->nombre }}">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                        </td>

                        {{-- Eliminar --}}
                        <td class="text-center pe-3">
                            <button type="button"
                                class="btn btn-sm btn-outline-danger btn-eliminar-plantilla"
                                data-id="{{ $plantilla->id_plantilla_pedido }}"
                                data-nombre="{{ $plantilla->nombre }}"
                                title="Eliminar plantilla">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-2 d-block mb-2"></i>
                            No se encontraron plantillas de pedido.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pie: info + paginación --}}
    <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
        <div class="text-muted small">
            Mostrando {{ $plantillas->firstItem() ?? 0 }} a {{ $plantillas->lastItem() ?? 0 }}
            de {{ $plantillas->total() }} plantillas
        </div>
        <nav>
            <div id="paginacionPlantillas">
                @if($plantillas->count() > 0)
                    {{ $plantillas->links('pagination::bootstrap-4') }}
                @endif
            </div>
        </nav>
    </div>
</div>
