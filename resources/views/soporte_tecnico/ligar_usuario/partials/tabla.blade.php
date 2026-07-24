{{--
    partials/tabla.blade.php — Tabla de trabajadores / soporte_area.

    Variables esperadas:
      $trabajadores — LengthAwarePaginator
      $soloCuerpo   — bool: si true solo renderiza el <tbody> (respuesta AJAX)
--}}

@php $soloCuerpo = $soloCuerpo ?? false; @endphp

@unless($soloCuerpo)
<thead class="table-light text-uppercase small text-secondary">
    <tr>
        <th style="width:48px;"  class="text-center">#</th>
        <th style="width:110px;" class="text-center">Acción</th>
        <th>Nombre del Trabajador</th>
        <th style="width:140px;" class="text-center">Áreas Asignadas</th>
        <th style="width:110px;" class="text-center">Estado</th>
    </tr>
</thead>
@endunless

<tbody id="cuerpoTablaSoporte">
    @forelse($trabajadores as $row)
        <tr>
            {{-- # --}}
            <td class="text-center">
                {{ ($trabajadores->currentPage() - 1) * $trabajadores->perPage() + $loop->iteration }}
            </td>

            {{-- Botón asignar áreas (accion) --}}
            <td class="text-center">
                <a href="{{ route('soporte_area.asignar', $row->id) }}"
                   class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 text-nowrap"
                   title="Asignar o modificar áreas de soporte">
                    <i class="fa fa-plus me-1"></i>Asignar
                </a>
            </td>

            {{-- Nombre completo del trabajador --}}
            <td class="fw-semibold">{{ $row->nombre_completo }}</td>

            {{-- Contador de áreas asignadas --}}
            <td class="text-center">
                @if($row->cantidad_areas > 0)
                    <span class="badge bg-primary rounded-pill px-3 py-1">{{ $row->cantidad_areas }} área(s)</span>
                @else
                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">0 áreas</span>
                @endif
            </td>

            {{-- Estado --}}
            <td class="text-center">
                @if($row->activo == 1)
                    <span class="badge bg-success rounded-pill px-3 py-1 btn-toggle-status"
                          data-id="{{ $row->id }}"
                          style="cursor: pointer;"
                          title="Clic para desactivar trabajador">
                        <i class="fa fa-check-circle me-1"></i>Activo
                    </span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3 py-1 btn-toggle-status"
                          data-id="{{ $row->id }}"
                          style="cursor: pointer;"
                          title="Clic para activar trabajador">
                        <i class="fa fa-ban me-1"></i>Inactivo
                    </span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center py-5 text-muted">
                <i class="fa fa-users fa-2x mb-2 d-block opacity-25"></i>
                No se encontraron trabajadores.
            </td>
        </tr>
    @endforelse
</tbody>
