@extends('layouts.reporte_base')

@section('title', 'Reporte Departamentos - Hospital General de Linares')

@section('report_title')
    Catálogo de Departamentos
@endsection

@section('report_subheader')
    @if(!empty($buscar))
        <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
            <strong>Filtro aplicado:</strong> "{{ $buscar }}"
        </p>
    @endif
    <p style="font-size: 11px; color: #555; margin-bottom: 10px;">
        <strong>Total de registros:</strong> {{ $departamentos->count() }}
    </p>
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width: 40px;">#</th>
            <th>Nombre</th>
            <th style="width: 80px;">Abreviatura</th>
            <th style="width: 80px;">Extensión</th>
            <th>Responsable</th>
            <th class="center" style="width: 100px;">Fecha</th>
            <th class="center" style="width: 70px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($departamentos as $index => $dep)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $dep->nombre }}</td>
                <td>{{ $dep->abreviatura }}</td>
                <td>{{ $dep->extension ?? '—' }}</td>
                <td>
                    @if($dep->responsable)
                        {{ $dep->responsable->nombre }} {{ $dep->responsable->ap_paterno }}
                    @else
                        —
                    @endif
                </td>
                <td class="center">
                    {{ $dep->fecha ? \Carbon\Carbon::parse($dep->fecha)->format('d/m/Y') : 'N/A' }}
                </td>
                <td class="center">
                    {{ $dep->activo == 1 ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #555;">
                    No hay registros para mostrar.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
