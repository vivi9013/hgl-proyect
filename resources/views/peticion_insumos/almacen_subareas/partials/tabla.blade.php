<div class="table-responsive bg-white border rounded shadow-sm p-3">
    @if(session('exitog'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('exitog') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @forelse($almacenes as $almacen)
        <div class="card card-subarea mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-building me-2"></i>
                        {{ $almacen->areaAbastecimiento->nombre ?? 'N/A' }} 
                        <span class="text-dark me-2">/</span>
                        <span class="text-secondary">{{ $almacen->subareaAbastecimiento->nombre ?? 'N/A' }}</span>
                    </h6>
                    <small class="text-muted">
                        Registrado: {{ $almacen->fecha_registro ? \Carbon\Carbon::parse($almacen->fecha_registro)->format('d/m/Y') : 'N/A' }}
                    </small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $almacen->activo == 1 ? 'bg-success' : 'bg-danger' }}">
                        {{ $almacen->activo == 1 ? 'Activo' : 'Inactivo' }}
                    </span>
                    
                    <button class="btn btn-sm btn-outline-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalAgregarInsumo" 
                            data-id-almacen="{{ $almacen->id_almacen_subarea }}">
                        <i class="bi bi-plus-circle me-1"></i> Asignar Insumo
                    </button>

                    <button class="btn btn-sm {{ $almacen->activo == 1 ? 'btn-outline-danger' : 'btn-outline-success' }} btn-toggle-status" 
                            data-id="{{ $almacen->id_almacen_subarea }}"
                            data-status="{{ $almacen->activo }}"
                            data-url="{{ route('almacen_subareas.status', $almacen->id_almacen_subarea) }}">
                        <i class="bi {{ $almacen->activo == 1 ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                @if($almacen->detalles && $almacen->detalles->count() > 0)
                    <table class="table table-hover table-striped table-almacen mb-0">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Clave Insumo</th>
                                <th style="width: 45%;">Descripción del Insumo</th>
                                <th class="text-center" style="width: 15%;">Cantidad (Stock)</th>
                                <th class="text-center" style="width: 15%;">Fondo Fijo</th>
                                <th class="text-center" style="width: 10%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($almacen->detalles as $detalle)
                                @php
                                    $insumo = $detalle->insumo;
                                    $esBajoFondo = $detalle->cantidad < $detalle->fondo_fijo;
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $detalle->cve_insumo ?: ($insumo->clave ?? 'N/A') }}</td>
                                    <td>{{ $insumo->descripcion ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <input type="number" 
                                               class="form-control form-control-sm stock-editable-input input-cantidad" 
                                               value="{{ $detalle->cantidad }}" 
                                               min="0">
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               class="form-control form-control-sm stock-editable-input input-fondo-fijo" 
                                               value="{{ $detalle->fondo_fijo }}" 
                                               min="0">
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-success btn-guardar-detalle me-1" 
                                                data-id="{{ $detalle->id_detalle_almacen_subarea }}"
                                                title="Guardar cambios de stock">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-eliminar-detalle" 
                                                data-id="{{ $detalle->id_detalle_almacen_subarea }}"
                                                title="Remover insumo">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-info-circle me-1"></i> No se han asignado insumos a este almacén de subárea.
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <h5>No se encontraron almacenes de subáreas</h5>
            <p class="mb-0">Pruebe ajustando los filtros de búsqueda o registre un nuevo almacén de subárea.</p>
        </div>
    @endforelse

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            Mostrando {{ $almacenes->firstItem() ?? 0 }} a {{ $almacenes->lastItem() ?? 0 }} de {{ $almacenes->total() }} almacenes
        </div>
        <div>
            {{ $almacenes->links() }}
        </div>
    </div>
</div>
