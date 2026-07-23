@extends('layouts.reporte_base')

@section('title', 'Reporte de Mobiliario General')

@section('report_title', 'Reporte General de Mobiliario')

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
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Marca / Modelo</th>
                <th>Serie</th>
                <th>Persona Responsable</th>
                <th>Área / Depto.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mobiliarios as $index => $mob)
                <tr>
                    <td class="num">{{ $index + 1 }}</td>
                    <td><strong>{{ $mob->inventario }}</strong></td>
                    <td>
                        @if($mob->tipoMobiliario)
                            {{ $mob->tipoMobiliario->tipo }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $mob->descripcion }}</td>
                    <td>{{ $mob->marca }} / {{ $mob->modelo }}</td>
                    <td>{{ $mob->serie ?: 'N/A' }}</td>
                    <td>
                        @if($mob->persona)
                            {{ $mob->persona->nombre }} {{ $mob->persona->ap_paterno }}
                        @else
                            Sin Asignar
                        @endif
                    </td>
                    <td>
                        {{ $mob->area ? $mob->area->area : 'N/A' }}
                        @if($mob->departamento)
                            <br><small style="color: #666;">({{ $mob->departamento->nombre }})</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center" style="padding: 20px; font-style: italic;">
                        No se encontraron registros de mobiliario para mostrar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
