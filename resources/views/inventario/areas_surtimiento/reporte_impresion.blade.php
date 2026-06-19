{{-- @extends indica que esta plantilla hereda del diseño de reporte base layouts/reporte_base.blade.php. --}}
{{-- Utiliza un diseño de impresión limpio optimizado para papel o formato PDF. --}}
{{-- En este caso, establece el marco estructural de tablas y cabeceras para el reporte. --}}
@extends('layouts.reporte_base')

{{-- @section inyecta una cadena de texto en la directiva @yield('title') de la plantilla base. --}}
{{-- Configura el título que se mostrará en la pestaña del navegador durante la vista de impresión. --}}
@section('title', 'Reporte - Áreas de Surtimiento')

{{-- @section define el título de cabecera visible en el reporte físico o virtual. --}}
{{-- Muestra la etiqueta del reporte "LISTA COMPLETA DE ÁREAS DE SURTIMIENTO". --}}
@section('report_title', 'LISTA COMPLETA DE ÁREAS DE SURTIMIENTO')

{{-- @section y @endsection definen la sección de controles adicionales sobre la barra del reporte. --}}
{{-- Provee botones en pantalla para interactuar antes de ejecutar la impresión del documento. --}}
@section('extra_actions')
    {{-- route() obtiene de manera dinámica la ruta del listado principal. --}}
    {{-- Crea un enlace de navegación para regresar al módulo administrativo principal. --}}
    <a href="{{ route('areas_surtimiento.index') }}" class="btn-close-win" style="text-decoration:none; margin-right:8px;">
        ← Regresar al módulo
    </a>
    {{-- onclick="window.close()" ejecuta el método JavaScript nativo de la ventana del navegador. --}}
    {{-- Cierra la pestaña de impresión actual que fue abierta de forma emergente. --}}
    <button class="btn-close-win" onclick="window.close()">✕ Cerrar</button>
@endsection

{{-- @section y @endsection definen un contenedor para datos secundarios del reporte. --}}
{{-- Renderiza información de cabecera antes del listado tabular. --}}
@section('report_subheader')
<div style="margin-bottom: 12px; font-size: 11px; color: #444;">
    {{-- $areas->count() calcula el número total de elementos en la colección de Laravel. --}}
    {{-- Imprime dinámicamente cuántos registros componen este documento impreso. --}}
    <strong>Total de registros:</strong> {{ $areas->count() }}
</div>
@endsection

{{-- @section agrupa el cuerpo principal del reporte para inyectarlo en el marcador de la plantilla base. --}}
{{-- Aglutina la tabla con las columnas y renglones de las áreas de surtimiento. --}}
@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">#</th>
            <th>Nombre del Área de Surtimiento</th>
            <th>Tipo</th>
            <th>Fecha de Registro</th>
            <th>Hora</th>
            <th class="center" style="width:100px;">Status</th>
        </tr>
    </thead>
    <tbody>
        {{-- @forelse es la directiva Blade que itera sobre la colección de áreas y ofrece una sección de escape si no existen. --}}
        {{-- Recorre cada registro de área de surtimiento para renderizarlo en una fila de la tabla. --}}
        @forelse ($areas as $index => $area)
            <tr>
                {{-- $index representa la clave de la iteración actual (comenzando en 0). --}}
                {{-- Suma 1 para formatear una numeración secuencial de lectura humana en la tabla. --}}
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $area->nombre }}</td>
                <td>{{ $area->tipo }}</td>
                {{-- El operador ternario condicional evalúa si existe la fecha de registro. --}}
                {{-- Carbon::parse() convierte el string de la fecha a un objeto Carbon y format('d/m/Y') le da formato estándar latino. --}}
                <td>{{ $area->fecha_registro ? \Carbon\Carbon::parse($area->fecha_registro)->format('d/m/Y') : '—' }}</td>
                {{-- El operador de coalescencia nula ?? evalúa si hora_registro es nulo. --}}
                {{-- Retorna el valor si existe, u otorga una raya de reemplazo si el valor está ausente en la base de datos. --}}
                <td>{{ $area->hora_registro ?? '—' }}</td>
                <td class="center">
                    {{-- @if evalúa el campo de estado booleano activo en la base de datos. --}}
                    {{-- Dibuja 'Activo' si es igual a 1, o 'Inactivo' si está deshabilitado en el sistema. --}}
                    @if ($area->activo == 1)
                        Activo
                    @else
                        Inactivo
                    @endif
                </td>
            </tr>
        {{-- @empty se activa si la colección $areas no contiene registros de áreas de surtimiento. --}}
        {{-- Renderiza una celda descriptiva única alertando que no hay información disponible. --}}
        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:12px; color:#666;">
                    No hay áreas de surtimiento registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
