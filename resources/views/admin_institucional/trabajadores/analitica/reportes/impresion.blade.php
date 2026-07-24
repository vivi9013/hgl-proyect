@extends('layouts.reporte_base')

@section('title', 'Reporte Oficial de Trabajadores - Hospital General de Linares')

@section('report_title')
    Relación de Trabajadores e Expedientes Laborales
@endsection

@section('report_subheader')
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de registros expedidos:</strong> {{ $trabajadores->count() }}
    </p>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 35px;">#</th>
            <th class="center" style="width: 80px;">No. Emp.</th>
            <th>Nombre del Trabajador / RFC</th>
            <th>Sede</th>
            <th>Departamento</th>
            <th>Puesto</th>
            <th>Tipo</th>
            <th class="center" style="width: 80px;">Ingreso</th>
            <th class="center" style="width: 60px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($trabajadores as $index => $t)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td class="center font-monospace"><strong>{{ $t->num_empleado }}</strong></td>
                <td>
                    @if($t->persona)
                        <strong>{{ $t->persona->nombre }} {{ $t->persona->ap_paterno }} {{ $t->persona->ap_materno }}</strong>
                        @if($t->persona->rfc)
                            <br><small style="color: #666;">RFC: {{ $t->persona->rfc }}</small>
                        @endif
                    @else
                        <em>Sin asignación de persona</em>
                    @endif
                </td>
                <td>{{ $t->sede ? $t->sede->nombre : 'N/A' }}</td>
                <td>{{ $t->departamento ? $t->departamento->nombre : 'N/A' }}</td>
                <td>{{ $t->puesto ? $t->puesto->puesto : 'N/A' }}</td>
                <td>{{ $t->tipoTrabajador ? $t->tipoTrabajador->tipo : 'N/A' }}</td>
                <td class="center">
                    {{ $t->fecha_ingreso ? \Carbon\Carbon::parse($t->fecha_ingreso)->format('d/m/Y') : 'N/A' }}
                </td>
                <td class="center">
                    {{ $t->activo == 1 ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; color: #555;">
                    No hay trabajadores registrados en la consulta actual.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
