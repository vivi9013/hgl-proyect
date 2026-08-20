{{--
    celdas-movimiento.blade.php — Escape hatches unificados del módulo movimientos.

    Recibe:
      $row   — el modelo MovimientoInsumo
      $celda — string que identifica qué celda renderizar:
               'acciones' | 'tipo' | 'proveedor' | 'fecha' | 'estado'

    Uso en la config de columnas:
      'tipo'       => 'personalizado',
      'vista'      => 'control_insumos.movimientos_insumos.partials.celdas-movimiento',
      'vista_datos' => ['celda' => '<nombre>'],
--}}
@switch($celda)

    {{-- ──────────────────────────────────────────────────────────────────── --}}
    {{-- ACCIONES: botón editar (abre modal via JS) o guión si cancelado     --}}
    {{-- ──────────────────────────────────────────────────────────────────── --}}
    @case('acciones')
        @if($row->activo)
            <a href="#"
               class="btn-editar-movimiento btn btn-sm btn-outline-dark rounded-pill px-2 py-1"
               data-id="{{ $row->id_movimiento }}"
               title="Editar concepto, fecha o proveedor">
                <i class="fa fa-pencil"></i>
            </a>
        @else
            <span class="text-muted small">—</span>
        @endif
        @break

    {{-- ──────────────────────────────────────────────────────────────────── --}}
    {{-- TIPO: badge verde Entrada / rojo Salida con ícono de flecha         --}}
    {{-- ──────────────────────────────────────────────────────────────────── --}}
    @case('tipo')
        <span class="badge rounded-pill px-3 py-2
                     {{ $row->tipo === 'Entrada' ? 'bg-success' : 'bg-danger' }}">
            <i class="fa {{ $row->tipo === 'Entrada' ? 'fa-arrow-circle-down' : 'fa-arrow-circle-up' }} me-1"></i>
            {{ $row->tipo }}
        </span>
        @break

    {{-- ──────────────────────────────────────────────────────────────────── --}}
    {{-- PROVEEDOR: ícono camión solo si es Entrada y tiene proveedor        --}}
    {{-- ──────────────────────────────────────────────────────────────────── --}}
    @case('proveedor')
        @if($row->tipo === 'Entrada' && $row->proveedor)
            <i class="fa fa-truck text-muted me-1"></i>{{ $row->proveedor }}
        @else
            <span class="text-muted">—</span>
        @endif
        @break

    {{-- ──────────────────────────────────────────────────────────────────── --}}
    {{-- FECHA: formateada como d/m/Y via Carbon                             --}}
    {{-- ──────────────────────────────────────────────────────────────────── --}}
    @case('fecha')
        {{ \Carbon\Carbon::parse($row->fecha_movimiento)->format('d/m/Y') }}
        @break

    {{-- ──────────────────────────────────────────────────────────────────── --}}
    {{-- ESTADO: badge cancelable (activo) o badge estático (cancelado)      --}}
    {{-- El JS de movimientos_insumos.js escucha .badge-cancelar-movimiento  --}}
    {{-- ──────────────────────────────────────────────────────────────────── --}}
    @case('estado')
        @if($row->activo)
            <span class="badge-cancelar-movimiento badge bg-success rounded-pill px-2 py-1"
                  style="cursor: pointer;"
                  data-id="{{ $row->id_movimiento }}"
                  data-tipo="{{ $row->tipo }}"
                  data-detalle="{{ $row->tipo }} de {{ $row->insumo->modelo ?? '' }} ({{ $row->cantidad }} pz.)"
                  title="Clic para cancelar este movimiento">
                <i class="fa fa-check-circle me-1"></i>Activo
            </span>
        @else
            <span class="badge bg-secondary rounded-pill px-2 py-1">
                <i class="fa fa-ban me-1"></i>Cancelado
            </span>
        @endif
        @break

@endswitch
