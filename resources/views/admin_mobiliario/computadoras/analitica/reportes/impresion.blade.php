@extends('layouts.reporte_base')

@section('title', 'Reporte de Computadoras')

@section('report_title', 'Reporte General de Equipos de Cómputo')

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
                <th style="width: 110px;">Inventario</th>
                <th>Nombre Equipo</th>
                <th>Marca / Modelo</th>
                <th style="width: 90px;">S.O.</th>
                <th style="width: 60px;">RAM</th>
                <th style="width: 100px;">Dirección IP</th>
                <th>Persona Responsable</th>
                <th>Área / Depto.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($computadoras as $index => $comp)
                <tr>
                    <td class="num">{{ $index + 1 }}</td>
                    <td><strong>{{ $comp->inventario }}</strong></td>
                    <td>{{ $comp->nombre_equipo ?: 'N/A' }}</td>
                    <td>
                        @if($comp->mobiliario)
                            {{ $comp->mobiliario->marca }} / {{ $comp->mobiliario->modelo }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $comp->so ?: 'N/A' }}</td>
                    <td class="center">{{ $comp->ram ? $comp->ram . ' MB' : 'N/A' }}</td>
                    <td><code>{{ $comp->ip ?: 'N/A' }}</code></td>
                    <td>
                        @if($comp->mobiliario && $comp->mobiliario->persona)
                            {{ $comp->mobiliario->persona->nombre }} {{ $comp->mobiliario->persona->ap_paterno }}
                        @else
                            Sin Asignar
                        @endif
                    </td>
                    <td>
                        @if($comp->mobiliario)
                            {{ $comp->mobiliario->area ? $comp->mobiliario->area->area : 'N/A' }}
                            @if($comp->mobiliario->departamento)
                                <br><small style="color: #666;">({{ $comp->mobiliario->departamento->departamento }})</small>
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center" style="padding: 20px; font-style: italic;">
                        No se encontraron registros de computadoras para mostrar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
