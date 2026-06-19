{{-- @extends indica que esta plantilla hereda del diseño de reporte base layouts/reporte_base.blade.php. --}}
{{-- Utiliza un diseño minimalista optimizado para impresión (con CSS de media @print y márgenes limpios). --}}
{{-- En este caso, provee la estructura de cabecera y estilos de tabla específicos para reportes imprimibles. --}}
@extends('layouts.reporte_base')

{{-- @section envía el título de la página al marcador @yield('title') de la plantilla de impresión. --}}
{{-- Establece el título de la pestaña del navegador para el documento de impresión. --}}
@section('title', 'Reporte - Áreas de Almacén')

{{-- @section define el título principal que se renderizará dentro del cuerpo del reporte. --}}
{{-- Pasa un string en mayúsculas al marcador de cabecera del reporte de la plantilla base. --}}
@section('report_title', 'LISTA COMPLETA DE ÁREAS DE ALMACÉN')

{{-- @section con cierre @endsection define botones de acciones rápidas sobre el reporte. --}}
{{-- Provee botones en pantalla para regresar al listado o cerrar la ventana emergente de impresión. --}}
@section('extra_actions')
    {{-- route() obtiene dinámicamente la URL del listado general. --}}
    {{-- Permite al usuario regresar de manera directa al panel administrativo. --}}
    <a href="{{ route('areas_almacen.index') }}" class="btn-close-win" style="text-decoration:none; margin-right:8px;">
        ← Regresar al módulo
    </a>
    {{-- onclick="window.close()" ejecuta el método JavaScript nativo de la ventana del navegador. --}}
    {{-- Cierra la pestaña de impresión actual que fue abierta de forma emergente. --}}
    <button class="btn-close-win" onclick="window.close()">✕ Cerrar</button>
@endsection

{{-- @section con cierre @endsection inserta información adicional en el encabezado secundario del reporte. --}}
{{-- Muestra metadatos generales del reporte antes de la tabla principal de datos. --}}
@section('report_subheader')
<div style="margin-bottom: 12px; font-size: 11px; color: #444;">
    {{-- $areas->count() ejecuta el método count de la colección de Eloquent de Laravel. --}}
    {{-- Obtiene y despliega de forma dinámica el número de registros cargados en el listado del reporte. --}}
    {{-- Permite verificar rápidamente cuántos registros en total están impresos en el documento. --}}
    <strong>Total de registros:</strong> {{ $areas->count() }}
</div>
@endsection

{{-- @section agrupa el contenido principal del reporte para ser inyectado en el marcador de la plantilla base. --}}
{{-- Contiene la tabla principal con el listado de las áreas de almacén activas e inactivas. --}}
@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">#</th>
            <th>Nombre del Área</th>
            <th>Fecha de Registro</th>
            <th>Hora</th>
            <th class="center" style="width:100px;">Status</th>
        </tr>
    </thead>
    <tbody>
        {{-- @forelse es una estructura de bucle que itera sobre la colección de áreas. --}}
        {{-- Recorre los registros uno a uno y cae en el bloque @empty si la colección no contiene elementos. --}}
        {{-- Dibuja dinámicamente una fila de la tabla para cada área registrada en el sistema. --}}
        @forelse ($areas as $index => $area)
            <tr>
                {{-- $index representa la clave de la iteración actual en el bucle (inicia en 0). --}}
                {{-- Suma 1 a la clave para mostrar un consecutivo de registros legible para humanos en el reporte. --}}
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $area->nombre }}</td>
                {{-- El operador ternario ?: evalúa si el campo fecha_registro contiene datos en base de datos. --}}
                {{-- Carbon::parse() convierte el string de la fecha en una instancia de Carbon para aplicar format('d/m/Y'). --}}
                {{-- Asegura que la fecha de registro se muestre con formato estándar latino (día/mes/año) o una raya en su defecto. --}}
                <td>{{ $area->fecha_registro ? \Carbon\Carbon::parse($area->fecha_registro)->format('d/m/Y') : '—' }}</td>
                {{-- El operador coalescente ?? evalúa si el campo hora_registro es nulo. --}}
                {{-- Imprime el valor del campo si existe, o dibuja una raya si está ausente en la base de datos. --}}
                <td>{{ $area->hora_registro ?? '—' }}</td>
                <td class="center">
                    {{-- @if evalúa el valor numérico binario de activo en el registro del área. --}}
                    {{-- Si el valor es igual a 1, imprime el texto Activo; de lo contrario, imprime Inactivo. --}}
                    {{-- Indica el estado actual del área de almacén en el documento impreso. --}}
                    @if ($area->activo == 1)
                        Activo
                    @else
                        Inactivo
                    @endif
                </td>
            </tr>
        {{-- @empty captura el escenario donde la consulta no retornó registros de áreas de almacén. --}}
        {{-- Renderiza una fila única con un mensaje descriptivo indicando la ausencia de datos en el sistema. --}}
        @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:12px; color:#666;">
                    No hay áreas de almacén registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
