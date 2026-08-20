{{-- Partial: tabla_historial.blade.php (Tomar Servicios) --}}
@if($servicios->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fa fa-history fa-3x mb-3 text-secondary d-block"></i>
        <h5 class="fw-bold text-dark">Sin registros en el historial</h5>
        <p class="mb-0">No se encontraron servicios atendidos con los filtros seleccionados.</p>
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
                <th>Técnico que Atendió</th>
                <th>Equipo</th>
                <th>Petición</th>
                <th>Terminación / Cierre</th>
                <th>Días</th>
                <th>Estado Final</th>
                <th>Hoja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicios as $i => $s)
            @php
                $badgeClass = match($s->estatus_final) {
                    'Liberado'  => 'badge-liberado',
                    'Cancelado' => 'badge-cancelado',
                    default     => 'badge-terminado',
                };
            @endphp
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
                    <span class="fw-bold text-dark d-block" style="font-size:0.88rem;">
                        {{ $s->nombre_solicitante }}
                    </span>
                    <small class="text-muted">{{ $s->departamento }}</small>
                </td>
                <td class="text-start">
                    <span class="fw-semibold text-primary small d-block">
                        {{ $s->nombre_servidor ?: 'Sin asignar' }}
                    </span>
                    @if($s->liberadox)
                        <small class="text-muted">Cerrado por: {{ $s->liberadox }}</small>
                    @endif
                </td>
                <td>
                    <span class="badge bg-light text-dark border small" title="{{ $s->descripcion_mobiliario }}">
                        {{ $s->inventario ? ('Inv: ' . $s->inventario) : 'Sin equipo' }}
                    </span>
                </td>
                <td>
                    @if($s->fecha_peticion)
                        <span class="small d-block">{{ \Carbon\Carbon::parse($s->fecha_peticion)->format('d-m-Y') }}</span>
                        <span class="text-muted" style="font-size:0.72rem;">{{ \Carbon\Carbon::parse($s->hora_peticion)->format('h:i A') }}</span>
                    @else —
                    @endif
                </td>
                <td>
                    @if($s->fecha_finaliza)
                        <span class="small d-block">{{ \Carbon\Carbon::parse($s->fecha_finaliza)->format('d-m-Y') }}</span>
                        <span class="text-muted" style="font-size:0.72rem;">{{ \Carbon\Carbon::parse($s->hora_finaliza)->format('h:i A') }}</span>
                    @elseif($s->fecha_termino)
                        <span class="small d-block">{{ \Carbon\Carbon::parse($s->fecha_termino)->format('d-m-Y') }}</span>
                        <span class="text-muted" style="font-size:0.72rem;">{{ \Carbon\Carbon::parse($s->hora_termino)->format('h:i A') }}</span>
                    @else —
                    @endif
                </td>
                <td>
                    <span class="fw-bold">{{ $s->dias_transcurridos }}</span>
                    <small class="text-muted">días</small>
                </td>
                <td>
                    <span class="badge-estado {{ $badgeClass }}">
                        {{ $s->estatus_final ?: ($s->terminado ? 'Terminado' : 'Pendiente') }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('tomar_servicios.hoja_servicio', $s->id) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-dark px-2 py-1"
                       title="Visualizar e Imprimir Hoja de Servicio">
                        <i class="fa fa-print"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
