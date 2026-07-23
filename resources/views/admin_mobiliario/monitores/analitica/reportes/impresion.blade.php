@extends('layouts.reporte_base')

@section('title', 'Reporte de Monitores')

@section('report_title', 'Reporte General de Monitores')

@section('report_subheader')
    @if(!empty($buscar))
        <div style="margin-bottom: 10px; font-size: 11px; font-style: italic; color: #555;">
            Filtro de búsqueda aplicado: "{{ $buscar }}"
        </div>
    @endif
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th class="center" style="width: 40px;">#</th>
                <th style="width: 120px;">Inventario</th>
                <th>Marca / Modelo</th>
                <th style="width: 90px;">Tipo</th>
                <th>No. de Serie</th>
                <th>Descripción</th>
                <th>Área / Depto.</th>
                <th>Persona Responsable</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monitores as $index => $mon)
                <tr>
                    <td class="num">{{ $index + 1 }}</td>
                    <td><strong>{{ $mon->inventario }}</strong></td>
                    <td>{{ $mon->marca }} / {{ $mon->modelo }}</td>
                    <td class="center">{{ $mon->tipo }}</td>
                    <td>{{ $mon->serie ?: 'N/A' }}</td>
                    <td>{{ $mon->descripcion ?: 'N/A' }}</td>
                    <td>
                        @if($mon->mobiliario)
                            {{ $mon->mobiliario->area ? $mon->mobiliario->area->area : 'N/A' }}
                            @if($mon->mobiliario->departamento)
                                <br><small style="color: #666;">({{ $mon->mobiliario->departamento->departamento }})</small>
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($mon->mobiliario && $mon->mobiliario->persona)
                            {{ $mon->mobiliario->persona->nombre }} {{ $mon->mobiliario->persona->ap_paterno }}
                        @else
                            Sin Asignar
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center" style="padding: 20px; font-style: italic;">
                        No se encontraron registros de monitores para mostrar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
