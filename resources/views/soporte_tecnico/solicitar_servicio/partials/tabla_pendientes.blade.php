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
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $i => $s)
            @php
                $esPendiente = $s->pendiente && !$s->proceso && !$s->terminado;
                $esEnProceso = $s->proceso && !$s->terminado;
                $puedeLiberar = $s->terminado && !$s->liberado;

                if ($esPendiente) {
                    $estadoLabel = 'Pendiente';
                    $estadoClass = 'badge-pendiente';
                } elseif ($esEnProceso) {
                    $estadoLabel = 'En proceso';
                    $estadoClass = 'badge-proceso';
                } elseif ($puedeLiberar) {
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
                            class="btn btn-sm btn-outline-dark rounded-pill px-2 py-0 fw-semibold"
                            data-accion="ver-detalle"
                            data-id="{{ $s->id }}"
                            title="Ver detalle del servicio">
                        <i class="{{ $s->area ? $s->area->icono : 'fa fa-tag' }} me-1"></i>#{{ $s->id }}
                    </button>
                </td>
                <td>
                    <span class="badge bg-light text-dark border">
                        {{ $s->area ? $s->area->area : '—' }}
                    </span>
                </td>
                <td class="text-start" style="max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ Str::limit($s->descripcion_servicio, 65) }}
                </td>
                <td>
                    @if($s->fecha_peticion)
                        {{ \Carbon\Carbon::parse($s->fecha_peticion)->format('d-m-Y') }}
                        <span class="text-muted d-block" style="font-size:0.75rem;">
                            {{ \Carbon\Carbon::parse($s->hora_peticion)->format('h:i A') }}
                        </span>
                    @else —
                    @endif
                </td>
                <td>
                    @if($s->nombre_servidor)
                        <span class="fw-semibold small text-primary">{{ $s->nombre_servidor }}</span>
                    @else
                        <span class="text-muted small">Sin asignar</span>
                    @endif
                </td>
                <td>
                    <span class="badge-estado {{ $estadoClass }}">{{ $estadoLabel }}</span>
                </td>
                <td>
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button"
                                class="btn btn-sm btn-outline-dark px-2 py-1"
                                data-accion="ver-detalle"
                                data-id="{{ $s->id }}"
                                title="Ver detalles">
                            <i class="fa fa-eye"></i>
                        </button>

                        @if($puedeLiberar)
                            <button type="button"
                                    class="btn btn-sm btn-success px-2 py-1"
                                    data-accion="liberar"
                                    data-id="{{ $s->id }}"
                                    title="Liberar servicio de conformidad">
                                <i class="fa fa-check-circle me-1"></i>Liberar
                            </button>
                        @elseif($esPendiente)
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger px-2 py-1"
                                    data-accion="cancelar"
                                    data-id="{{ $s->id }}"
                                    title="Cancelar esta solicitud">
                                <i class="fa fa-times-circle me-1"></i>Cancelar
                            </button>
                        @else
                            <span class="badge bg-secondary text-white py-1 px-2 small align-self-center">
                                En atención
                            </span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
