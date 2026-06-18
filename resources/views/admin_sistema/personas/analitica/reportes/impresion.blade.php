@extends('layouts.reporte_base')

@section('title', 'Reporte - Padrón de Personas')

@section('report_title', 'PADRÓN DE PERSONAS REGISTRADAS')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:40px;">No</th>
            <th>Nombre Completo</th>
            <th class="center" style="width:50px;">Sexo</th>
            <th class="center" style="width:80px;">F. Nacimiento</th>
            <th style="width:100px;">RFC</th>
            <th style="width:120px;">CURP</th>
            <th>Estado / Municipio</th>
            <th class="center" style="width:70px;">Estudiante</th>
            <th class="center" style="width:60px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($personas as $index => $row)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">
                    {{ $row->ap_paterno }} {{ $row->ap_materno }}, {{ $row->nombre }}
                </td>
                <td class="center">{{ $row->sexo === 'M' ? 'Masc.' : 'Fem.' }}</td>
                <td class="center">{{ $row->fecha_nac ? \Carbon\Carbon::parse($row->fecha_nac)->format('d/m/Y') : '—' }}</td>
                <td>{{ $row->rfc }}</td>
                <td>{{ $row->curp }}</td>
                <td>{{ $row->estado }} / {{ $row->municipio }}</td>
                <td class="center">{{ $row->estudiante == 1 ? 'Sí' : 'No' }}</td>
                <td class="center">{{ $row->activo == 1 ? 'Activo' : 'Inactivo' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron personas con los filtros seleccionados.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
