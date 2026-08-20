{{-- Partial: tabla_en_proceso.blade.php (Mis Servicios Tomados) --}}
@if($servicios->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa fa-coffee fa-3x mb-3 text-secondary d-block"></i>
        <h5 class="fw-bold text-dark">Sin servicios en proceso</h5>
        <p class="mb-0">No tienes servicios pendientes de resolución actualmente. Puedes tomar uno nuevo desde la bandeja de pendientes.</p>
    </div>
@else
<div class="table-responsive">
    <table class="tabla-servicios">
        <thead>
            <tr>
                <th>#</th>
                <th>Folio</th>
                <th>Área</th>
                <th>Solicitante</th>
                <th>Departamento / Sede</th>
                <th>Ext.</th>
                <th>Prioridad</th>
                <th>Problema Reportado</th>
                <th>Tomado el</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $i => $s)
            <tr>
                <td>{{ $servicios->firstItem() + $i }}</td>
                <td>
                    <span class="badge bg-primary text-white rounded-pill px-2 py-1 fw-bold">
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
                </td>
                <td>
                    <span class="d-block small fw-semibold text-secondary">{{ $s->departamento }}</span>
                    <span class="badge bg-light text-muted border" style="font-size:0.7rem;">{{ $s->sede }}</span>
                </td>
                <td>
                    <span class="badge bg-light text-dark border">
                        {{ $s->ext_telefonica ?: 'S/Ext' }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-warning text-dark">
                        {{ $s->clasificacion_servicio ?: 'Ordinario' }}
                    </span>
                </td>
                <td class="text-start" style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $s->descripcion_servicio }}">
                    {{ Str::limit($s->descripcion_servicio, 60) }}
                </td>
                <td>
                    @if($s->fecha_tomado)
                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($s->fecha_tomado)->format('d-m-Y') }}</span>
                        <span class="text-muted d-block" style="font-size:0.75rem;">
                            {{ \Carbon\Carbon::parse($s->hora_tomado)->format('h:i A') }}
                        </span>
                    @else —
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-1 justify-content-center">
                        {{-- Botón Concluir --}}
                        <button type="button"
                                class="btn btn-sm btn-success px-2 py-1 fw-semibold shadow-sm"
                                data-accion="abrir-concluir"
                                data-id="{{ $s->id }}"
                                data-folio="{{ $s->id }}"
                                data-id-area="{{ $s->id_area }}"
                                data-solicitante="{{ $s->nombre_solicitante }}"
                                data-descripcion="{{ $s->descripcion_servicio }}"
                                title="Concluir y resolver este servicio">
                            <i class="fa fa-check-square-o me-1"></i>Concluir
                        </button>

                        {{-- Botón Ajustar Fechas --}}
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary px-2 py-1"
                                data-accion="abrir-ajustar-fechas"
                                data-id="{{ $s->id }}"
                                data-fecha-pet="{{ $s->fecha_peticion }}"
                                data-hora-pet="{{ $s->hora_peticion }}"
                                data-fecha-tom="{{ $s->fecha_tomado }}"
                                data-hora-tom="{{ $s->hora_tomado }}"
                                title="Ajustar fechas/horas con auditoría">
                            <i class="fa fa-calendar"></i>
                        </button>

                        {{-- Botón Reasignar / Liberar de atención --}}
                        <button type="button"
                                class="btn btn-sm btn-outline-danger px-2 py-1"
                                data-accion="reasignar"
                                data-id="{{ $s->id }}"
                                title="Devolver a la bandeja de pendientes">
                            <i class="fa fa-undo"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
