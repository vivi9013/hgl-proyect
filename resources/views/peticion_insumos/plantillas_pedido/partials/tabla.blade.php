<div class="table-responsive bg-white border rounded shadow-sm p-3">
    @if(session('exitog'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('exitog') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @forelse($plantillas as $plantilla)
        <div class="card card-plantilla mb-3">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-clipboard2-check me-2"></i>
                        {{ $plantilla->nombre }}
                    </h6>
                    <div class="d-flex gap-3 mt-1">
                        <small class="text-muted">
                            <i class="bi bi-building me-1"></i>
                            {{ $plantilla->areaAbastecimiento->nombre ?? 'N/A' }}
                            @if($plantilla->subareaAbastecimiento)
                                <span class="mx-1 text-dark">/</span>
                                <span class="text-secondary">{{ $plantilla->subareaAbastecimiento->nombre }}</span>
                            @endif
                        </small>
                        <small class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ $plantilla->fecha_registro ? \Carbon\Carbon::parse($plantilla->fecha_registro)->format('d/m/Y') : 'N/A' }}
                        </small>
                        @if($plantilla->descripcion)
                            <small class="text-muted fst-italic">
                                {{ Str::limit($plantilla->descripcion, 60) }}
                            </small>
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $plantilla->activo == 1 ? 'bg-success' : 'bg-danger' }}">
                        {{ $plantilla->activo == 1 ? 'Activa' : 'Inactiva' }}
                    </span>
                    <span class="badge bg-secondary">
                        <i class="bi bi-boxes me-1"></i>{{ $plantilla->detalles->count() }} insumo(s)
                    </span>

                    <button class="btn btn-sm btn-outline-primary btn-agregar-insumo"
                            data-id="{{ $plantilla->id_plantilla_pedido }}"
                            data-nombre="{{ $plantilla->nombre }}">
                        <i class="bi bi-plus-circle me-1"></i> Agregar Insumo
                    </button>

                    <button class="btn btn-sm {{ $plantilla->activo == 1 ? 'btn-outline-danger' : 'btn-outline-success' }} btn-toggle-status"
                            data-url="{{ route('plantillas_pedido.status', $plantilla->id_plantilla_pedido) }}"
                            data-nombre="{{ $plantilla->nombre }}"
                            data-activo="{{ $plantilla->activo }}">
                        <i class="bi {{ $plantilla->activo == 1 ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                @if($plantilla->detalles && $plantilla->detalles->count() > 0)
                    <div class="tabla-insumos-paginada" data-per-page="10">
                        <table class="table table-hover table-striped table-almacen mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Clave Insumo</th>
                                    <th style="width: 55%;">Descripción del Insumo</th>
                                    <th class="text-center" style="width: 15%;">Cantidad Prestablecida</th>
                                    <th class="text-center" style="width: 15%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plantilla->detalles as $detalle)
                                    @php $insumo = $detalle->insumo; @endphp
                                    <tr class="fila-insumo">
                                        <td class="fw-bold">{{ $detalle->cve_insumo ?: ($insumo->clave ?? 'N/A') }}</td>
                                        <td>{{ $insumo->descripcion ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <input type="number"
                                                   class="form-control form-control-sm stock-editable-input input-cantidad"
                                                   value="{{ $detalle->cantidad }}"
                                                   min="1">
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-success btn-guardar-detalle me-1"
                                                    data-id="{{ $detalle->id_detalle_plantilla }}"
                                                    title="Guardar cantidad">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-eliminar-detalle"
                                                    data-id="{{ $detalle->id_detalle_plantilla }}"
                                                    title="Quitar insumo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 paginacion-insumos-info border-top bg-light">
                            <small class="text-muted texto-info-paginacion"></small>
                            <nav><ul class="pagination pagination-sm mb-0 controles-paginacion"></ul></nav>
                        </div>
                    </div>
                @else
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-info-circle me-1"></i> Esta plantilla aún no tiene insumos asignados.
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-clipboard2-x fs-1 d-block mb-2"></i>
            <h5>No se encontraron plantillas de pedido</h5>
            <p class="mb-0">Ajusta los filtros de búsqueda o crea una nueva plantilla.</p>
        </div>
    @endforelse

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            Mostrando {{ $plantillas->firstItem() ?? 0 }} a {{ $plantillas->lastItem() ?? 0 }} de {{ $plantillas->total() }} plantillas
        </div>
        <div>
            {{ $plantillas->links() }}
        </div>
    </div>
</div>
