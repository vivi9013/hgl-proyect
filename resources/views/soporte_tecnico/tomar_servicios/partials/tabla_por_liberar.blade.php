{{-- Partial: tabla_por_liberar.blade.php --}}
@if($servicios->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa fa-folder-open-o fa-3x mb-3 text-secondary d-block"></i>
        <h5 class="fw-bold text-dark">No hay servicios pendientes de liberación</h5>
        <p class="mb-0">Todos los servicios concluidos han sido liberados o no hay registros en esta etapa.</p>
    </div>
@else
<div class="table-responsive">
    <table class="tabla-servicios">
        <thead>
            <tr>
                <th>#</th>
                <th>Folio</th>
                <th>Área</th>
                <th>Solicitante / Depto</th>
                <th>Técnico que Atendió</th>
                <th>Solución / Acción</th>
                <th>Equipo</th>
                <th>Terminado el</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $i => $s)
            <tr>
                <td>{{ $servicios->firstItem() + $i }}</td>
                <td>
                    <span class="badge bg-success text-white rounded-pill px-2 py-1 fw-bold">
                        #{{ $s->id }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-light text-dark border">
                        <i class="{{ $s->area ? $s->area->icono : 'fa fa-tag' }} me-1"></i>
                        {{ $s->area ? $s->area->area : '—' }}
                    </span>
                </td>
                <td class="text-start">
                    <span class="fw-bold text-dark d-block" style="font-size:0.9rem;">
                        {{ $s->nombre_solicitante }}
                    </span>
                    <small class="text-muted">{{ $s->departamento }} ({{ $s->ext_telefonica ?: 'S/Ext' }})</small>
                </td>
                <td>
                    <span class="fw-semibold text-primary small d-block">
                        {{ $s->nombre_servidor }}
                    </span>
                </td>
                <td class="text-start" style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $s->accion_realizada }}">
                    {{ Str::limit($s->accion_realizada, 50) }}
                </td>
                <td>
                    <span class="badge bg-light text-dark border small">
                        {{ $s->inventario ? ('Inv: ' . $s->inventario) : 'Sin equipo' }}
                    </span>
                </td>
                <td>
                    @if($s->fecha_termino)
                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($s->fecha_termino)->format('d-m-Y') }}</span>
                        <span class="text-muted d-block" style="font-size:0.75rem;">
                            {{ \Carbon\Carbon::parse($s->hora_termino)->format('h:i A') }}
                        </span>
                    @else —
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-1 justify-content-center">
                        {{-- Botón Hoja de Servicio --}}
                        <a href="{{ route('tomar_servicios.hoja_servicio', $s->id) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-dark px-2 py-1"
                           title="Visualizar e Imprimir Hoja de Servicio">
                            <i class="fa fa-print"></i>
                        </a>

                        {{-- Botón Liberar por Soporte --}}
                        <button type="button"
                                class="btn btn-sm btn-success px-2 py-1 fw-semibold shadow-sm"
                                data-accion="liberar-soporte"
                                data-id="{{ $s->id }}"
                                title="Liberar y cerrar definitivamente este servicio">
                            <i class="fa fa-check-circle me-1"></i>Liberar
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
