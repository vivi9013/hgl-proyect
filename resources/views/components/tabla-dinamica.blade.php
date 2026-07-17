{{--
    x-tabla-dinamica — Componente de tabla dinámica con tipos de columna.
    Renderiza <thead> + <tbody> para usarse dentro de un <table> existente.

    Props:
      columnas           array  — Definición de columnas (ver tipos abajo)
      filas              mixed  — LengthAwarePaginator con los registros
      vacio              string — Mensaje cuando no hay registros
      tbodyId            string — id="" para el <tbody> (necesario si el JS lo referencia)
      claseFilaInactiva  string — Clase CSS para filas donde activo == 0
      vacoIcono          string — Clases del icono fa en el estado vacío

    Tipos de columna ('tipo'):
      indice        → Número de fila paginado (o campo directo si 'campo' está definido)
      texto         → $row->campo (con fallback '—' por ?:, atrapa null y cadena vacía)
      texto-combo   → Principal en bold + secundario en small/muted
                      Usa data_get() para dot-notation (ej: 'insumo.modelo').
                      Si 'secundario_campos' (array) está definido, los une con 'separador'.
                      Si solo 'secundario' (string) está definido, lo usa directamente con data_get.
      relacion      → data_get($row, campo) con fallback
      acciones      → Link a route() con ícono lápiz
      toggle        → Badge que alterna activo/inactivo (llama a un endpoint de status)
      personalizado → @include de una vista parcial, recibe $row

    Parámetros comunes por columna:
      label    string  — Texto del <th>
      centrado bool    — Agrega text-center al <th> y <td>
      ancho    string  — style="width: ..." en el <th>
      clase    string  — Clases adicionales al <td> (aplica a cualquier tipo)
--}}
@props([
    'columnas'          => [],
    'filas',
    'vacio'             => 'No se encontraron registros.',
    'tbodyId'           => null,
    'claseFilaInactiva' => 'text-muted bg-light-gray',
    'vacoIcono'         => 'fa-exclamation-circle',
    'soloCuerpo'        => false,
])

@if(!$soloCuerpo)
<thead class="table-light text-uppercase small text-secondary">
    <tr>
        @foreach($columnas as $col)
            <th class="{{ ($col['centrado'] ?? false) ? 'text-center' : '' }}"
                @if(!empty($col['ancho'])) style="width: {{ $col['ancho'] }};" @endif>
                {{ $col['label'] }}
            </th>
        @endforeach
    </tr>
</thead>

<tbody @if($tbodyId) id="{{ $tbodyId }}" @endif>
@endif
    @foreach($filas as $row)
        <tr class="{{ (isset($row->activo) && $row->activo == 0) ? $claseFilaInactiva : '' }}">
            @foreach($columnas as $col)
                <td class="{{ ($col['centrado'] ?? false) ? 'text-center' : '' }} {{ $col['clase'] ?? '' }}">
                    @switch($col['tipo'])

                        {{-- ── Índice de fila (paginado o campo directo) ── --}}
                        @case('indice')
                            {{ isset($col['campo'])
                                ? $row->{$col['campo']}
                                : ($filas->currentPage() - 1) * $filas->perPage() + $loop->parent->iteration }}
                            @break

                        {{-- ── Texto simple con fallback ── --}}
                        @case('texto')
                            {{ $row->{$col['campo']} ?: ($col['fallback'] ?? '—') }}
                            @break

                        {{-- ── Texto compuesto: principal + secundario ── --}}
                        @case('texto-combo')
                            @php
                                $tcPrincipal = data_get($row, $col['principal']) ?? ($col['fallback'] ?? '—');

                                // Secundario: múltiples campos con separador, o campo único
                                if (!empty($col['secundario_campos'])) {
                                    $tcSecundario = collect($col['secundario_campos'])
                                        ->map(fn($c) => data_get($row, $c))
                                        ->filter()          // descarta null / cadena vacía
                                        ->join($col['separador'] ?? ' · ');
                                } else {
                                    $tcSecundario = data_get($row, $col['secundario'] ?? '') ?? '';
                                }
                            @endphp
                            <div class="fw-semibold">{{ $tcPrincipal }}</div>
                            @if($tcSecundario)
                                <small class="text-muted">{{ $tcSecundario }}</small>
                            @endif
                            @break

                        {{-- ── Relación con dot-notation ── --}}
                        @case('relacion')
                            {{ data_get($row, $col['campo']) ?: ($col['fallback'] ?? 'N/A') }}
                            @break

                        {{-- ── Acciones: link a ruta ── --}}
                        @case('acciones')
                            <a href="{{ route($col['ruta'], $row->{$col['param']}) }}"
                               class="btn btn-sm btn-outline-dark border-0"
                               title="{{ $col['titulo'] ?? 'Editar' }}">
                                <i class="fa {{ $col['icono'] ?? 'fa-pencil-square-o' }}"></i>
                            </a>
                            @break

                        {{-- ── Toggle de status (badge activo/inactivo) ── --}}
                        @case('toggle')
                            @php $campoActivo = $col['campo']; $campoId = $col['campo_id'] ?? 'id'; @endphp
                            <a href="#"
                               class="btn-toggle-status badge {{ $row->{$campoActivo} == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
                               data-id="{{ $row->{$campoId} }}"
                               title="{{ $row->{$campoActivo} == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                                <i class="fa {{ $row->{$campoActivo} == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $row->{$campoActivo} == 1 ? ($col['texto_on'] ?? 'Activo') : ($col['texto_off'] ?? 'Inactivo') }}
                            </a>
                            @break

                        {{-- ── Escape hatch: vista parcial personalizada ── --}}
                        @case('personalizado')
                            @include($col['vista'], array_merge(['row' => $row], $col['vista_datos'] ?? []))
                            @break

                    @endswitch
                </td>
            @endforeach
        </tr>
    @endforeach

    {{-- Estado vacío --}}
    @if($filas->isEmpty())
        <tr>
            <td colspan="{{ count($columnas) }}" class="text-center py-5 text-muted">
                <i class="fa {{ $vacoIcono }} fa-2x mb-2 d-block opacity-25"></i>
                {{ $vacio }}
            </td>
        </tr>
    @endif
@if(!$soloCuerpo)
</tbody>
@endif
