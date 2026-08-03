{{--
    Partial: tabla_pendientes.blade.php
    Lista de servicios activos (sin liberar) del usuario
--}}

@if($servicios->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
        No tienes servicios activos en este momento.
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
                <th>Atiende</th>
                <th>Estado</th>
                <th>Liberar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $i => $s)
            @php
                $puedeLiberar = $s->pendiente && $s->proceso && $s->terminado;
                $nombreServidor = $s->nombre_servidor ?? 'Sin asignar';

                if ($s->pendiente && !$s->proceso && !$s->terminado) {
                    $estadoLabel = 'Pendiente';
                    $estadoClass = 'badge-pendiente';
                } elseif ($s->proceso && !$s->terminado) {
                    $estadoLabel = 'En proceso';
                    $estadoClass = 'badge-proceso';
                } elseif ($s->terminado) {
                    $estadoLabel = 'Terminado';
                    $estadoClass = 'badge-terminado';
                } else {
                    $estadoLabel = 'Pendiente';
                    $estadoClass = 'badge-pendiente';
                }
            @endphp
            <tr>
                <td>{{ $servicios->firstItem() + $i }}</td>
                <td>
                    <button type="button"
                            class="btn btn-sm btn-outline-dark rounded-pill px-2 py-0"
                            data-accion="ver-detalle"
                            data-id="{{ $s->id }}"
                            title="Ver detalle del servicio">
                        <i class="{{ $s->area ? $s->area->icono : 'fa fa-tag' }} me-1"></i>#{{ $s->id }}
                    </button>
                </td>
                <td>{{ $s->area ? $s->area->area : '—' }}</td>
                <td class="text-start" style="max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ Str::limit($s->descripcion_servicio, 60) }}
                </td>
                <td>
                    @if($s->fecha_peticion)
                        {{ \Carbon\Carbon::parse($s->fecha_peticion)->format('d-m-Y') }}
                        <span class="text-muted" style="font-size:0.78rem;">
                            {{ \Carbon\Carbon::parse($s->hora_peticion)->format('h:i a') }}
                        </span>
                    @else —
                    @endif
                </td>
                <td>
                    @if($s->nombre_servidor)
                        <small class="fw-semibold">{{ $s->nombre_servidor }}</small>
                    @else
                        <span class="text-muted small">Sin asignar</span>
                    @endif
                </td>
                <td>
                    <span class="badge-estado {{ $estadoClass }}">{{ $estadoLabel }}</span>
                </td>
                <td>
                    @if($puedeLiberar)
                        <button type="button"
                                class="btn-tabla-accion btn-outline-success text-success"
                                data-accion="liberar"
                                data-id="{{ $s->id }}"
                                title="Liberar servicio">
                            <i class="fa fa-check-circle"></i> Liberar
                        </button>
                    @else
                        <button type="button"
                                class="btn-tabla-accion text-muted border-secondary"
                                disabled title="Disponible cuando el servicio esté terminado">
                            <i class="fa fa-times-circle"></i> N/D
                        </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
