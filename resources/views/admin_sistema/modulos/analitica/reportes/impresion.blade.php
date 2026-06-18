@extends('layouts.reporte_base')

@section('title', 'Reporte de Módulos - Hospital General')

@section('report_title', 'LISTA COMPLETA DE MODULOS')

@section('content')
    <table>
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">No</th>
                <th style="width: 200px;">Modulo</th>
                <th>Descripcion del proyecto</th>
                <th style="width: 220px;">Programador</th>
            </tr>
        </thead>
        <tbody>
            @forelse($modulos as $i => $modulo)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $i + 1 }}</td>
                    <td><strong>{{ $modulo->nombre }}</strong></td>
                    <td>{{ $modulo->descripcion }}</td>
                    <td>{{ $modulo->creador }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #888; padding: 20px;">
                        No hay registros para mostrar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
