@extends('layouts.reporte_base')
@section('title', 'Tipos de Servicio Técnico – Reporte')
@section('report_title', 'Catálogo Oficial de Tipos de Servicio Técnico')
@section('content')

@foreach($servicios as $nombreArea => $grupo)
    <h3 style="font-size:11pt; margin:14px 0 6px; color:#1a1a2e; border-bottom:1px solid #ccc; padding-bottom:3px;">
        Área: {{ $nombreArea }}
        <span style="font-size:9pt; font-weight:normal; color:#555;">
            ({{ $grupo->count() }} tipo(s) —
            activos: {{ $grupo->where('activo',1)->count() }},
            inactivos: {{ $grupo->where('activo',0)->count() }})
        </span>
    </h3>

    <table>
        <thead>
            <tr>
                <th class="num" style="width:36px;">#</th>
                <th>Descripción del Tipo de Servicio</th>
                <th class="center" style="width:80px;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grupo as $i => $row)
                <tr>
                    <td class="num">{{ $i + 1 }}</td>
                    <td>{{ $row->servicio }}</td>
                    <td class="center">{{ $row->activo ? 'Activo' : 'Inactivo' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

<div class="footer-info" style="margin-top:15px;">
    <p><strong>Total de Tipos de Servicio:</strong>
        {{ $servicios->flatten()->count() }}
        (activos: {{ $servicios->flatten()->where('activo',1)->count() }})
    </p>
</div>

@endsection
