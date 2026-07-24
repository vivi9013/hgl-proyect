<div class="table-responsive bg-white border rounded shadow-sm p-3">
    @if(session('exitog'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('exitog') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @forelse($almacenes as $almacen)
        @php
            $totalBajos = $almacen->detalles ? $almacen->detalles->filter(fn($d) => $d->cantidad < $d->fondo_fijo)->count() : 0;
        @endphp
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
                    @if($totalBajos > 0)
                        <span class="badge bg-warning text-dark me-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $totalBajos }} bajo(s) fondo fijo
                        </span>
                    @endif

                    <span class="badge {{ $almacen->activo == 1 ? 'bg-success' : 'bg-danger' }}">
                        {{ $almacen->activo == 1 ? 'Activo' : 'Inactivo' }}
                    </span>
                    
                    <button class="btn btn-sm btn-outline-primary btn-agregar-insumo" 
                            data-id="{{ $almacen->id_almacen_subarea }}"
                            data-subarea="{{ $almacen->subareaAbastecimiento->nombre ?? 'Subárea' }}">
                        <i class="bi bi-plus-circle me-1"></i> Asignar Insumo
                    </button>

                    <button class="btn btn-sm {{ $almacen->activo == 1 ? 'btn-outline-danger' : 'btn-outline-success' }} btn-toggle-status" 
                            data-id="{{ $almacen->id_almacen_subarea }}"
                            data-activo="{{ $almacen->activo }}"
                            data-nombre="{{ $almacen->subareaAbastecimiento->nombre ?? 'esta subárea' }}"
                            data-url="{{ route('almacen_subareas.status', $almacen->id_almacen_subarea) }}"
                            title="{{ $almacen->activo == 1 ? 'Desactivar' : 'Activar' }} almacén">
                        <i class="bi {{ $almacen->activo == 1 ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                @if($almacen->detalles && $almacen->detalles->count() > 0)
                    @php
                        $detallesColeccion = $almacen->detalles;
                        if (!empty($buscar)) {
                            $b = mb_strtolower($buscar);
                            $filtrados = $detallesColeccion->filter(function($det) use ($b) {
                                $cve = mb_strtolower($det->cve_insumo ?? '');
                                $clave = mb_strtolower($det->insumo->clave ?? '');
                                $desc = mb_strtolower($det->insumo->descripcion ?? '');
                                return str_contains($cve, $b) || str_contains($clave, $b) || str_contains($desc, $b);
                            });
                            if ($filtrados->count() > 0) {
                                $detallesColeccion = $filtrados;
                            }
                        }
                    @endphp
                    <div class="tabla-insumos-paginada" data-per-page="10">
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
                                @foreach($detallesColeccion as $detalle)
                                    @php
                                        $insumo = $detalle->insumo;
                                        $esBajoFondo = $detalle->cantidad < $detalle->fondo_fijo;
                                    @endphp
                                    <tr class="fila-insumo {{ $esBajoFondo ? 'fila-bajo-fondo' : '' }}">
                                        <td class="fw-bold">{{ $detalle->cve_insumo ?: ($insumo->clave ?? 'N/A') }}</td>
                                        <td>
                                            {{ $insumo->descripcion ?? 'N/A' }}
                                            @if($esBajoFondo)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> Bajo mínimo
                                                </span>
                                            @endif
                                        </td>
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
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 paginacion-insumos-info border-top bg-light">
                            <small class="text-muted texto-info-paginacion"></small>
                            <nav><ul class="pagination pagination-sm mb-0 controles-paginacion"></ul></nav>
                        </div>
                    </div>
                @else
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-info-circle me-1"></i> No se han asignado insumos a este almacén de subárea.
                    </div>
                @endif
            </div>
        </div>
    @empty
        @if($sinFiltro ?? false)
            <div class="text-center py-5 text-muted">
                <i class="bi bi-funnel text-secondary" style="font-size: 3rem;"></i>
                <h5 class="mt-3 fw-bold text-dark">Selecciona un filtro para ver los registros</h5>
                <p class="mb-0 small">Elige un <strong>Área de Abastecimiento</strong> o escribe en el buscador para consultar un almacén.</p>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <h5>No se encontraron almacenes de subáreas</h5>
                <p class="mb-0">Pruebe ajustando los filtros de búsqueda o registre un nuevo almacén de subárea.</p>
            </div>
        @endif
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
