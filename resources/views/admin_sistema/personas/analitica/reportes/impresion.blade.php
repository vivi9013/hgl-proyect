@extends('layouts.reporte_base')

@php
    // Construir título dinámico según filtros activos
    $mapasSexo = ['M' => 'Masculino', 'F' => 'Femenino'];
    $partesSexo = collect($sexoFiltro ?? [])
        ->map(fn($s) => $mapasSexo[$s] ?? $s)
        ->filter()
        ->values();

    $partesEstado = collect($estadoFiltro ?? [])
        ->filter()
        ->values();

    $mapasStatus = ['1' => 'Activo', '0' => 'Inactivo'];
    $partesStatus = collect($statusFiltro ?? [])
        ->map(fn($st) => $mapasStatus[$st] ?? $st)
        ->filter()
        ->values();

    $partesTitulo = $partesSexo->merge($partesEstado)->merge($partesStatus);

    $tituloDinamico = $partesTitulo->isNotEmpty()
        ? 'LISTADO DE PERSONAS — ' . $partesTitulo->map(fn($p) => strtoupper($p))->implode(' — ')
        : 'LISTA COMPLETA DE PERSONAS';
@endphp

@section('title', 'Reporte - Listado de Personas')

@section('report_title', $tituloDinamico)

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">No</th>
            <th>Persona</th>
            <th>Nacimiento</th>
            <th>Telefono</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($personas as $index => $row)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">
                    {{ $row->ap_paterno }} {{ $row->ap_materno }} {{ $row->nombre }}
                </td>
                <td>{{ $row->fecha_nac ?? '—' }}</td>
                <td>{{ $row->telefono ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align:center; padding:12px; color:#666;">
                    No se encontraron personas registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
