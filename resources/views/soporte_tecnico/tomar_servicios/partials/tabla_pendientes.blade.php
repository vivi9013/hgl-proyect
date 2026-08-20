{{-- Partial: tabla_pendientes.blade.php (Tomar Servicios) --}}
@if($servicios->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa fa-check-circle-o fa-3x mb-3 text-success d-block"></i>
        <h5 class="fw-bold text-dark">¡Bandeja Limpia!</h5>
        <p class="mb-0">No hay solicitudes pendientes en las áreas de soporte asignadas.</p>
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
                <th>Problema / Descripción</th>
                <th>Fecha Petición</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $i => $s)
            <tr>
                <td>{{ $servicios->firstItem() + $i }}</td>
                <td>
                    <span class="badge bg-dark text-white rounded-pill px-2 py-1 fw-bold">
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
                <td class="text-start" style="max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $s->descripcion_servicio }}">
                    {{ Str::limit($s->descripcion_servicio, 65) }}
                </td>
                <td>
                    @if($s->fecha_peticion)
                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($s->fecha_peticion)->format('d-m-Y') }}</span>
                        <span class="text-muted d-block" style="font-size:0.75rem;">
                            {{ \Carbon\Carbon::parse($s->hora_peticion)->format('h:i A') }}
                        </span>
                    @else —
                    @endif
                </td>
                <td>
                    <button type="button"
                            class="btn btn-sm btn-dark px-3 py-1 fw-semibold shadow-sm"
                            data-accion="abrir-tomar"
                            data-id="{{ $s->id }}"
                            data-folio="{{ $s->id }}"
                            data-solicitante="{{ $s->nombre_solicitante }}"
                            data-area="{{ $s->area ? $s->area->area : 'General' }}"
                            data-descripcion="{{ $s->descripcion_servicio }}"
                            title="Tomar y comenzar a atender esta solicitud">
                        <i class="fa fa-hand-paper-o me-1"></i> Tomar
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
