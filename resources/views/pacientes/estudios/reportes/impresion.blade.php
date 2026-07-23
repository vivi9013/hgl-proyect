@extends('layouts.reporte_base')

@section('title', 'Reporte de Estudios RX - Hospital General')

@section('report_title', 'Reporte de Estudios de Radiología (RX)')

@section('report_subheader')
    @if($fi || $ff)
        <div style="text-align: center; margin-bottom: 15px; font-size: 12px; font-weight: bold; color: #555;">
            Rango de Fechas: 
            @if($fi) Desde: {{ \Carbon\Carbon::parse($fi)->format('d/m/Y') }} @endif
            @if($ff) Hasta: {{ \Carbon\Carbon::parse($ff)->format('d/m/Y') }} @endif
        </div>
    @else
        <div style="text-align: center; margin-bottom: 15px; font-size: 12px; font-weight: bold; color: #555;">
            Listado General (Todos los Registros)
        </div>
    @endif
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 40px;">#</th>
            <th style="width: 140px;">Técnico Radiólogo</th>
            <th style="width: 90px;">Fecha</th>
            <th>Paciente</th>
            <th style="width: 90px;">NHC</th>
            <th style="width: 90px;">Seguro Popular</th>
            <th style="width: 100px;">Origen / Servicio</th>
            <th>Regiones Estudiadas</th>
            <th class="center" style="width: 50px;">Edad</th>
            <th class="center" style="width: 40px;">CDs</th>
        </tr>
    </thead>
    <tbody>
        @forelse($estudios as $index => $estudio)
            @php
                // Generar lista de regiones estudias
                $regiones = [];
                if ($estudio->craneo) $regiones[] = 'Cráneo';
                if ($estudio->tx) $regiones[] = 'Tórax';
                if ($estudio->abd) $regiones[] = 'Abdomen';
                if ($estudio->col) $regiones[] = 'Columna';
                if ($estudio->m_sup) $regiones[] = 'Miembro Sup.';
                if ($estudio->m_inf) $regiones[] = 'Miembro Inf.';
                if ($estudio->contraste) $regiones[] = 'Contraste';
            @endphp
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>
                    @if($estudio->creador)
                        {{ $estudio->creador->persona ? $estudio->creador->persona->nombre . ' ' . $estudio->creador->persona->ap_paterno : $estudio->creador->nombre_usuario }}
                    @else
                        ---
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($estudio->fecha_estudio)->format('d/m/Y') }}</td>
                <td>{{ $estudio->nombre }} {{ $estudio->ap_paterno }} {{ $estudio->ap_materno }}</td>
                <td>{{ $estudio->nhc && $estudio->nhc !== '0' ? $estudio->nhc : '---' }}</td>
                <td>{{ $estudio->sp && $estudio->sp !== '0' ? $estudio->sp : '---' }}</td>
                <td>{{ $estudio->hgl }}</td>
                <td style="font-size: 10px; font-family: monospace;">
                    {{ implode(', ', $regiones) ?: 'Ninguna' }}
                    @if($estudio->especificado)
                        <br><span style="color: #666; font-style: italic;">Obs: {{ $estudio->especificado }}</span>
                    @endif
                </td>
                <td class="center">{{ $estudio->edad }}</td>
                <td class="center">{{ $estudio->total_cds ?: 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 15px; color: #777;">
                    No se encontraron estudios registrados en el periodo seleccionado.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
