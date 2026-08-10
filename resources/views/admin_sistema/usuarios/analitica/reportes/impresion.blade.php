@extends('layouts.reporte_base')

@section('title', 'Reporte - Lista de Usuarios')

@section('report_title', 'LISTA COMPLETA DE USUARIOS DEL SISTEMA')

@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">#</th>
            <th>Persona / Trabajador</th>
            <th>Nombre de Usuario</th>
            <th>Perfil asignado</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($usuarios as $index => $user)
            <tr>
                <td class="num">{{ $index + 1 }}</td>
                <td>
                    @if($user->persona)
                        {{ $user->persona->ap_paterno }} {{ $user->persona->ap_materno }} {{ $user->persona->nombre }}
                    @else
                        <span style="color:#c53030;">Sin persona vinculada</span>
                    @endif
                </td>
                <td>{{ $user->nombre_usuario }}</td>
                <td>
                    @if($user->perfil)
                        {{ $user->perfil->nombre }}
                    @else
                        <span>Sin perfil asignado</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align:center; padding:12px; color:#666;">
                    No hay usuarios registrados actualmente en el sistema.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
