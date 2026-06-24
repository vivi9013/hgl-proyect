@foreach($personas as $index => $row)
    @php
        $esInactivo = ($row->activo != 1);
        $claseFila  = $esInactivo ? 'text-muted bg-light-gray' : '';

        // Calcular edad
        $edad = $row->fecha_nac
            ? \Carbon\Carbon::parse($row->fecha_nac)->age
            : '—';
    @endphp
    <tr class="{{ $claseFila }}">
         <td class="ps-4 fw-bold index-cell">
             {{ ($personas->currentPage() - 1) * $personas->perPage() + $loop->iteration }}
         </td>

        {{-- Acciones --}}
        <td class="text-center">
            <a href="{{ route('personas.edit', $row->id) }}"
               class="btn btn-sm btn-outline-dark border-0" title="Editar Persona">
                <i class="fa fa-pencil-square-o"></i>
            </a>
        </td>

         <td class="text-center">
             <div class="fw-semibold">{{ $row->nombre }} {{ $row->ap_paterno }} {{ $row->ap_materno }}</div>
             <small class="text-muted">{{ $row->e_mail }}</small>
         </td>

        <td>
            @if($row->sexo === 'M')
                <span>M</span>
            @else
                <span>F</span>
            @endif
        </td>

        <td class="fw-semibold">{{ $edad }} años</td>

        <td class="text-center">
            <span class="small">{{ $row->estado }}</span><br>
            <small class="text-muted">{{ $row->municipio }}</small>
        </td>

        {{-- Toggle Estudiante --}}
        <td class="text-center">
            <button type="button" class="btn btn-link btn-sm btn-toggle-estudiante p-0" data-id="{{ $row->id }}">
                @if($row->estudiante == 1)
                    <i class="fa fa-graduation-cap text-primary fs-5" title="Es estudiante"></i>
                @else
                    <i class="fa fa-graduation-cap text-secondary icon-no-estudiante fs-5" title="No es estudiante"></i>
                @endif
            </button>
        </td>

        {{-- Toggle Status --}}
        <td class="text-center">
            <button type="button" class="btn btn-link btn-sm btn-toggle-status p-0" data-id="{{ $row->id }}">
                @if($row->activo == 1)
                    <i class="fa fa-check-square-o text-success fs-5" title="Activo"></i>
                @else
                    <i class="fa fa-square-o text-danger fs-5" title="Inactivo"></i>
                @endif
            </button>
        </td>
    </tr>
@endforeach

@if($personas->isEmpty())
    <tr>
        <td colspan="8" class="text-center py-4 text-muted">
            <i class="fa fa-exclamation-circle me-2"></i>No se encontraron personas registradas.
        </td>
    </tr>
@endif
