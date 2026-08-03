{{--
    Partial: tabla_historial.blade.php
    Historial de servicios liberados o cancelados
--}}

@if($servicios->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
        No hay servicios en el historial aún.
    </div>
@else
<div class="table-responsive">
    <table class="tabla-servicios">
        <thead>
            <tr>
                <th>#</th>
                <th>Folio</th>
                <th>Área</th>
                <th>Descripción</th>
                <th>Fecha Petición</th>
                <th>Técnico</th>
                <th>Días</th>
                <th>Estado</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $i => $s)
            @php
                $estadoClass = match($s->estatus_final) {
                    'Liberado'  => 'badge-liberado',
                    'Cancelado' => 'badge-cancelado',
                    default     => 'badge-pendiente',
                };
            @endphp
            <tr>
                <td>{{ $servicios->firstItem() + $i }}</td>
                <td>
                    <button type="button"
                            class="btn btn-sm btn-outline-dark rounded-pill px-2 py-0"
                            data-accion="ver-detalle"
                            data-id="{{ $s->id }}"
                            title="Ver detalle completo">
                        <i class="{{ $s->area ? $s->area->icono : 'fa fa-tag' }} me-1"></i>#{{ $s->id }}
                    </button>
                </td>
                <td>{{ $s->area ? $s->area->area : '—' }}</td>
                <td class="text-start" style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ Str::limit($s->descripcion_servicio, 55) }}
                </td>
                <td>
                    @if($s->fecha_peticion)
                        {{ \Carbon\Carbon::parse($s->fecha_peticion)->format('d-m-Y') }}
                    @else —
                    @endif
                </td>
                <td>
                    @if($s->nombre_servidor)
                        <small class="fw-semibold">{{ $s->nombre_servidor }}</small>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
                <td>
                    <span class="fw-bold">{{ $s->dias_transcurridos }}</span>
                    <small class="text-muted">días</small>
                </td>
                <td>
                    <span class="badge-estado {{ $estadoClass }}">
                        {{ $s->estatus_final ?? '—' }}
                    </span>
                </td>
                <td>
                    <button type="button"
                            class="btn-tabla-accion btn-outline-dark"
                            data-accion="ver-detalle"
                            data-id="{{ $s->id }}"
                            title="Ver detalle del servicio">
                        <i class="fa fa-eye"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
