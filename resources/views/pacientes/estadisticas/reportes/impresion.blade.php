@extends('layouts.reporte_base')

@section('title', 'Reporte Radiológico Diario - Hospital General')

@section('report_title', 'REPORTE RADIOLÓGICO DIARIO')

@section('report_subheader')
    <div style="text-align: center; margin-bottom: 15px; font-size: 13px; font-weight: bold; color: #374151;">
        Periodo: {{ \Carbon\Carbon::parse($fi)->format('d/m/Y') }} @if($fi !== $ff) al {{ \Carbon\Carbon::parse($ff)->format('d/m/Y') }} @endif
    </div>
@endsection

@section('content')
<style>
    /* Estilos personalizados para ajustar la gran matriz de 27 columnas */
    body {
        font-family: Arial, sans-serif;
        font-size: 8px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        border: 1px solid #000000 !important;
        padding: 3px 2px !important;
        font-size: 8px !important;
        text-align: center;
    }
    th {
        background-color: #f3f4f6 !important;
        font-weight: bold;
    }
    .text-left {
        text-align: left !important;
        padding-left: 4px !important;
    }
    .bg-strip {
        background-color: #f9fafb;
    }
    .fw-bold {
        font-weight: bold;
    }
</style>

@php
    $sumaAmb  = 0;
    $sumaHosp = 0;
    $sumaUrg  = 0;
    $sumaOU   = 0;
    $sumM     = 0;
    $sumF     = 0;

    $sCraneo    = 0;
    $sTX        = 0;
    $sABD       = 0;
    $sCOL       = 0;
    $sMSUP      = 0;
    $sMINF      = 0;
    $sContraste = 0;
    $sCD        = 0;
@endphp

<table>
    <thead>
        <!-- Fila de Cabecera Principal (Spans) -->
        <tr>
            <th rowspan="2" style="width: 2%;">No.</th>
            <th rowspan="2" style="width: 8%;">Elaboró</th>
            <th colspan="3" style="width: 9%;">Fecha</th>
            <th rowspan="2" style="width: 12%;">Paciente</th>
            <th rowspan="2" style="width: 4%;">NHC</th>
            <th colspan="2" style="width: 4%;">Seg. Pop.</th>
            <th colspan="4" style="width: 8%;">HGL</th>
            <th colspan="8" style="width: 32%;">ESTUDIOS</th>
            <th rowspan="2" style="width: 3%;">Total</th>
            <th rowspan="2" style="width: 3%;">Edad</th>
            <th colspan="2" style="width: 4%;">Sexo</th>
            <th rowspan="2" style="width: 3%;">CD</th>
            <th rowspan="2" style="width: 6%;">Seg. Pop. No.</th>
        </tr>
        <!-- Fila de Cabecera Secundaria -->
        <tr>
            <th>Año</th>
            <th>Mes</th>
            <th>Día</th>
            <th>Sí</th>
            <th>No</th>
            <th>AMB.</th>
            <th>HOSP.</th>
            <th>URG.</th>
            <th>O.U.</th>
            <th>Cráneo</th>
            <th>TX</th>
            <th>ABD</th>
            <th>COL</th>
            <th>M.SUP</th>
            <th>M.INF</th>
            <th>Contra.</th>
            <th>Especificado</th>
            <th>M</th>
            <th>F</th>
        </tr>
    </thead>
    <tbody>
        @forelse($estudios as $index => $estudio)
            @php
                $hasSp = $estudio->sp && $estudio->sp !== '0' && $estudio->sp !== '';
                
                // Mapeo Origen (HGL)
                $vAmb = '';
                $vHosp = '';
                $vUrg = '';
                $vOU = '';
                if ($estudio->hgl === 'Consulta Externa' || $estudio->hgl === 'Ambulatorio') {
                    $vAmb = 'X';
                    $sumaAmb++;
                } elseif ($estudio->hgl === 'Hospitalización' || $estudio->hgl === 'Hospital') {
                    $vHosp = 'X';
                    $sumaHosp++;
                } elseif ($estudio->hgl === 'Urgencias') {
                    $vUrg = 'X';
                    $sumaUrg++;
                } else {
                    $vOU = 'X';
                    $sumaOU++;
                }

                // Mapeo Sexo
                $vM = '';
                $vF = '';
                if ($estudio->sexo === 'M') {
                    $vM = 'X';
                    $sumM++;
                } elseif ($estudio->sexo === 'F') {
                    $vF = 'X';
                    $sumF++;
                }

                // Sumatorias por Regiones
                if ($estudio->craneo) $sCraneo += $estudio->craneo;
                if ($estudio->tx) $sTX += $estudio->tx;
                if ($estudio->abd) $sABD += $estudio->abd;
                if ($estudio->col) $sCOL += $estudio->col;
                if ($estudio->m_sup) $sMSUP += $estudio->m_sup;
                if ($estudio->m_inf) $sMINF += $estudio->m_inf;
                if ($estudio->contraste) $sContraste += $estudio->contraste;

                $sCD += $estudio->total_cds;

                $fecha = \Carbon\Carbon::parse($estudio->fecha_estudio);
            @endphp
            <tr class="{{ $index % 2 === 0 ? '' : 'bg-strip' }}">
                <td>{{ $index + 1 }}</td>
                <td class="text-left">
                    @if($estudio->creador)
                        {{ $estudio->creador->persona ? $estudio->creador->persona->nombre . ' ' . substr($estudio->creador->persona->ap_paterno, 0, 1) . '.' : $estudio->creador->nombre_usuario }}
                    @else
                        ---
                    @endif
                </td>
                <td>{{ $fecha->format('Y') }}</td>
                <td>{{ $fecha->format('m') }}</td>
                <td>{{ $fecha->format('d') }}</td>
                <td class="text-left fw-bold">{{ $estudio->nombre }} {{ $estudio->ap_paterno }} {{ $estudio->ap_materno }}</td>
                <td>{{ $estudio->nhc && $estudio->nhc !== '0' ? $estudio->nhc : '---' }}</td>
                <td>{{ $hasSp ? 'X' : '' }}</td>
                <td>{{ !$hasSp ? 'X' : '' }}</td>
                <td>{{ $vAmb }}</td>
                <td>{{ $vHosp }}</td>
                <td>{{ $vUrg }}</td>
                <td>{{ $vOU }}</td>
                <td>{{ $estudio->craneo ? '1' : '' }}</td>
                <td>{{ $estudio->tx ? '1' : '' }}</td>
                <td>{{ $estudio->abd ? '1' : '' }}</td>
                <td>{{ $estudio->col ? '1' : '' }}</td>
                <td>{{ $estudio->m_sup ? '1' : '' }}</td>
                <td>{{ $estudio->m_inf ? '1' : '' }}</td>
                <td>{{ $estudio->contraste ? '1' : '' }}</td>
                <td class="text-left" style="font-size: 7px !important;">{{ $estudio->especificado ?: '---' }}</td>
                <td>{{ $estudio->total_estudios ?: 0 }}</td>
                <td>{{ $estudio->edad }}</td>
                <td>{{ $vM }}</td>
                <td>{{ $vF }}</td>
                <td>{{ $estudio->total_cds ?: 0 }}</td>
                <td>{{ $hasSp ? $estudio->sp : '---' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="27" style="padding: 10px; font-weight: bold; color: #4b5563;">
                    No se encontraron registros de estudios en el rango de fechas seleccionado.
                </td>
            </tr>
        @endforelse

        <!-- Fila de Sumatorias Totales (Pie) -->
        @if($estudios->count() > 0)
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <td colspan="9" style="text-align: right; padding-right: 6px;">TOTALES:</td>
                <td>{{ $sumaAmb }}</td>
                <td>{{ $sumaHosp }}</td>
                <td>{{ $sumaUrg }}</td>
                <td>{{ $sumaOU }}</td>
                <td>{{ $sCraneo }}</td>
                <td>{{ $sTX }}</td>
                <td>{{ $sABD }}</td>
                <td>{{ $sCOL }}</td>
                <td>{{ $sMSUP }}</td>
                <td>{{ $sMINF }}</td>
                <td>{{ $sContraste }}</td>
                <td></td>
                <td>{{ $sCraneo + $sTX + $sABD + $sCOL + $sMSUP + $sMINF + $sContraste }}</td>
                <td></td>
                <td>{{ $sumM }}</td>
                <td>{{ $sumF }}</td>
                <td>{{ $sCD }}</td>
                <td></td>
            </tr>
        @endif
    </tbody>
</table>
@endsection
