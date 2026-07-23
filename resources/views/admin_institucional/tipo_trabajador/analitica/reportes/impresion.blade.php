@extends('layouts.reporte_base')

@section('title', 'Reporte Tipos de Trabajador - Hospital General de Linares')

@section('report_title')
    Catálogo de Tipos de Trabajador
@endsection

@section('report_subheader')
    @if(!empty($buscar))
        <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
            <strong>Filtro aplicado:</strong> "{{ $buscar }}"
        </p>
    @endif
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de registros:</strong> {{ $tipos->count() }}
    </p>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 50px;">#</th>
            <th>Nombre del Tipo</th>
            <th class="center" style="width: 120px;">Fecha de Registro</th>
            <th class="center" style="width: 90px;">Hora</th>
            <th class="center" style="width: 80px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tipos as $index => $t)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $t->tipo }}</td>
                <td class="center">
                    {{ $t->fecha ? \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') : 'N/A' }}
                </td>
                <td class="center">{{ $t->hora ?? 'N/A' }}</td>
                <td class="center">
                    {{ $t->activo == 1 ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #555;">
                    No hay registros para mostrar.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
