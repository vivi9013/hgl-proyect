{{--
    partials/tabla.blade.php — Tabla de tipos de servicio.

    Variables esperadas:
      $servicios  — LengthAwarePaginator
      $soloCuerpo — bool: si true solo renderiza el <tbody> (respuesta AJAX)
--}}

@php $soloCuerpo = $soloCuerpo ?? false; @endphp

@unless($soloCuerpo)
<thead class="table-light text-uppercase small text-secondary">
    <tr>
        <th style="width:48px;"  class="text-center">#</th>
        <th>Tipo de Servicio</th>
        <th style="width:200px;">Área de Soporte</th>
        <th style="width:100px;" class="text-center">Estado</th>
        <th style="width:80px;"  class="text-center">Editar</th>
    </tr>
</thead>
@endunless

<tbody id="cuerpoTabla">
    @forelse($servicios as $row)
        <tr>
            {{-- # --}}
            <td class="text-center text-muted small">
                {{ ($servicios->currentPage() - 1) * $servicios->perPage() + $loop->iteration }}
            </td>

            {{-- Tipo de servicio --}}
            <td class="fw-semibold celda-servicio" title="{{ $row->servicio }}">
                {{ $row->servicio }}
            </td>

            {{-- Área --}}
            <td>
                @if($row->area)
                    <span class="badge bg-light text-dark border rounded-pill px-2">
                        {{ $row->area->area }}
                    </span>
                @else
                    <span class="text-muted small">—</span>
                @endif
            </td>

            {{-- Estado (toggle AJAX) --}}
            <td class="text-center">
                @if($row->activo == 1)
                    <span class="badge bg-success rounded-pill px-3 py-1 btn-toggle-status"
                          data-id="{{ $row->id }}"
                          title="Clic para desactivar">
                        <i class="fa fa-check-circle me-1"></i>Activo
                    </span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3 py-1 btn-toggle-status"
                          data-id="{{ $row->id }}"
                          title="Clic para activar">
                        <i class="fa fa-ban me-1"></i>Inactivo
                    </span>
                @endif
            </td>

            {{-- Editar --}}
            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 btn-editar"
                        data-id="{{ $row->id }}"
                        data-servicio="{{ addslashes($row->servicio) }}"
                        data-area="{{ $row->id_area }}"
                        title="Editar tipo de servicio">
                    <i class="fa fa-pencil"></i>
                </button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center py-5 text-muted">
                <i class="fa fa-tags fa-2x mb-2 d-block opacity-25"></i>
                No se encontraron tipos de servicio.
            </td>
        </tr>
    @endforelse
</tbody>
