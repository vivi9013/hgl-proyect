{{--
    partials/tabla.blade.php — Tabla de movimientos de insumos.

    Variables esperadas:
      $movimientos  — LengthAwarePaginator o Collection con los registros
      $soloCuerpo   — bool: si true, solo renderiza el <tbody> (respuesta AJAX)
                            si false (o no definido), renderiza thead + tbody completo
--}}

@php $soloCuerpo = $soloCuerpo ?? false; @endphp

@unless($soloCuerpo)
<thead class="table-light text-uppercase small text-secondary">
    <tr>
        <th style="width:48px;"  class="text-center">#</th>
        <th style="width:105px;" class="text-center">Acciones</th>
        <th style="width:100px;" class="text-center">Tipo</th>
        <th>Insumo</th>
        <th>Concepto</th>
        <th style="width:80px;"  class="text-center fw-bold">Cantidad</th>
        <th>Proveedor</th>
        <th style="width:100px;" class="text-center">Fecha</th>
        <th style="width:115px;" class="text-center">Estado</th>
    </tr>
</thead>
@endunless

<tbody id="cuerpoTablaMovimientos">
    @forelse($movimientos as $row)
        <tr class="{{ $row->activo == 0 ? 'fila-cancelada' : '' }}">

            {{-- # (índice paginado) --}}
            <td class="text-center">
                {{ ($movimientos->currentPage() - 1) * $movimientos->perPage() + $loop->iteration }}
            </td>

            {{-- Acciones --}}
            <td class="text-center">
                @include('control_insumos.movimientos.partials.celdas-movimiento', ['row' => $row, 'celda' => 'acciones'])
            </td>

            {{-- Tipo --}}
            <td class="text-center">
                @include('control_insumos.movimientos.partials.celdas-movimiento', ['row' => $row, 'celda' => 'tipo'])
            </td>

            {{-- Insumo: modelo en bold + color · familia en muted --}}
            <td>
                @php
                    $principal  = $row->insumo->modelo ?? '—';
                    $secundario = collect([$row->insumo->color ?? null, $row->insumo->familia ?? null])
                        ->filter()->join(' · ');
                @endphp
                <div class="fw-semibold">{{ $principal }}</div>
                @if($secundario)
                    <small class="text-muted">{{ $secundario }}</small>
                @endif
            </td>

            {{-- Concepto --}}
            <td>{{ $row->concepto ?: '—' }}</td>

            {{-- Cantidad --}}
            <td class="text-center fw-bold">{{ $row->cantidad }}</td>

            {{-- Proveedor --}}
            <td>
                @include('control_insumos.movimientos.partials.celdas-movimiento', ['row' => $row, 'celda' => 'proveedor'])
            </td>

            {{-- Fecha --}}
            <td class="text-center">
                @include('control_insumos.movimientos.partials.celdas-movimiento', ['row' => $row, 'celda' => 'fecha'])
            </td>

            {{-- Estado --}}
            <td class="text-center">
                @include('control_insumos.movimientos.partials.celdas-movimiento', ['row' => $row, 'celda' => 'estado'])
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center py-5 text-muted">
                <i class="fa fa-exchange fa-2x mb-2 d-block opacity-25"></i>
                No se encontraron movimientos con los filtros seleccionados.
            </td>
        </tr>
    @endforelse
</tbody>
