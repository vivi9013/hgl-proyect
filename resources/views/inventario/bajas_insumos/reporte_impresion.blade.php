{{-- @extends indica que esta plantilla hereda del diseño de reporte base layouts/reporte_base.blade.php. --}}
{{-- Carga una estructura HTML limpia, sin barras de navegación del portal, optimizada para impresiones. --}}
@extends('layouts.reporte_base')

{{-- @section inyecta una cadena de texto en la directiva @yield('title') de la plantilla de impresión base. --}}
{{-- Define el título visible de la pestaña en el navegador web para el usuario. --}}
@section('title', 'Reporte - Bajas de Insumos')

{{-- @section define el título de cabecera que se renderizará de forma centralizada en el reporte. --}}
@section('report_title', 'BAJAS DE INSUMOS')

{{-- @section y @endsection definen la sección de controles adicionales sobre la barra del reporte. --}}
@section('extra_actions')
    {{-- route() obtiene de manera dinámica la ruta del listado principal. --}}
    {{-- Crea un enlace de navegación para regresar al módulo administrativo principal. --}}
    <a href="{{ route('bajas_insumos.index') }}" class="btn-close-win" style="text-decoration:none; margin-right:8px;">
        ← Regresar al módulo
    </a>
    {{-- onclick="window.close()" ejecuta el método JavaScript nativo de la ventana del navegador. --}}
    {{-- Cierra la pestaña de impresión actual que fue abierta de forma emergente. --}}
    <button class="btn-close-win" onclick="window.close()">✕ Cerrar</button>
@endsection

{{-- @push agrega los estilos CSS específicos al final de la cabecera del reporte base. --}}
{{-- Permite apilar hojas de estilo adicionales y clases de diseño CSS específicas para la impresión del listado. --}}
@push('styles')
    .filtros-activos {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 14px;
        font-size: 11px;
        color: #4b5563;
    }
    .badge-activa {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }
    .badge-cancelada {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .alerta-limite {
        background-color: #fffbeb; 
        border: 1px solid #fef3c7; 
        color: #92400e; 
        padding: 8px 12px; 
        margin-bottom: 14px; 
        border-radius: 6px; 
        font-size: 11px;
    }
@endpush

{{-- @section y @endsection definen la cabecera secundaria del reporte. --}}
{{-- Agrega el subencabezado con el conteo de elementos y filtros utilizados. --}}
@section('report_subheader')
<div style="margin-bottom: 12px; font-size: 11px; color: #444;">
    {{-- $bajas->count() calcula el número total de registros presentes en la colección de bajas a imprimir. --}}
    <strong>Total de registros:</strong> {{ $bajas->count() }}
</div>

{{-- @if evalúa si el usuario especificó algún filtro de búsqueda o rango de fechas en la pantalla previa. --}}
{{-- Si se cumple, renderiza un div con el detalle de los parámetros activos para dejar constancia en el papel. --}}
@if($buscar || $fechaInit || $fechaFin || !empty($areaFiltrada))
    <div class="filtros-activos">
        <strong>Filtros aplicados:</strong>
        @if(!empty($areaFiltrada)) &nbsp;Área Asignada: <strong>{{ $areaFiltrada->nombre }}</strong> @endif
        @if($buscar) &nbsp;| Búsqueda: "{{ $buscar }}" @endif
        {{-- Carbon::parse() convierte los strings de fecha fechaInit y fechaFin a instancias Carbon para formatearlas a d/m/Y. --}}
        @if($fechaInit) &nbsp;| Desde: {{ \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') }} @endif
        @if($fechaFin) &nbsp;| Hasta: {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }} @endif
    </div>
@endif

{{-- @if comprueba si el conteo de registros es mayor o igual a 500 para mostrar un aviso preventivo. --}}
{{-- Utiliza la clase no-print para que este cuadro de diálogo de aviso solo se muestre en pantalla y no salga impreso. --}}
@if($bajas->count() >= 500)
    <div class="alerta-limite no-print">
        <strong>Aviso:</strong> El reporte se ha limitado a los 500 registros más recientes para optimizar el rendimiento y la impresión. Por favor, utilice los filtros de fecha o búsqueda en la pantalla del listado para delimitar los resultados si requiere registros anteriores.
    </div>
@endif
@endsection

{{-- @section define el cuerpo del reporte para ser inyectado en el marcador de la plantilla base. --}}
{{-- Agrupa la tabla de datos con el historial de bajas. --}}
@section('content')
<table>
    <thead>
        <tr>
            <th class="center" style="width:50px;">#</th>
            <th>Insumo</th>
            <th>Clave</th>
            <th>Área de Almacén</th>
            <th>Motivo</th>
            <th>Cantidad</th>
            <th>Fecha Baja</th>
            <th>Hora</th>
            <th class="center" style="width:90px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        {{-- @forelse es la directiva Blade para iterar sobre la colección con una cláusula de escape @empty si no existen elementos. --}}
        {{-- Recorre la lista de bajas para dibujar cada renglón en el documento de impresión. --}}
        @forelse($bajas as $index => $baja)
            <tr>
                {{-- $index representa la clave de la iteración actual en el bucle (inicia en 0). --}}
                {{-- Suma 1 para mostrar una numeración secuencial de lectura humana en la tabla. --}}
                <td class="num">{{ $index + 1 }}</td>
                <td>{{ $baja->insumo->descripcion ?? '—' }}</td>
                <td>{{ $baja->insumo->clave ?? '—' }}</td>
                <td>{{ $baja->areaAlmacen->nombre ?? '—' }}</td>
                <td>{{ $baja->motivo }}</td>
                <td class="center">{{ $baja->cantidad }}</td>
                {{-- Carbon::parse() convierte la fecha de la baja en objeto Carbon para darle el formato d/m/Y legible. --}}
                <td class="center">{{ $baja->fecha_baja ? \Carbon\Carbon::parse($baja->fecha_baja)->format('d/m/Y') : '—' }}</td>
                {{-- El operador coalescente ?? provee una raya en el reporte si la hora está vacía. --}}
                <td class="center">{{ $baja->hora_baja ?? '—' }}</td>
                <td class="center">
                    {{-- @if evalúa el string cancelado en el registro de la baja de insumos. --}}
                    {{-- Dibuja un badge de estilo rojo si está cancelada, u verde si está activa. --}}
                    @if($baja->cancelado === 'Si')
                        <span class="badge-cancelada">Cancelada</span>
                    @else
                        <span class="badge-activa">Activa</span>
                    @endif
                </td>
            </tr>
        {{-- @empty se activa si la colección $bajas no contiene registros en la consulta. --}}
        {{-- Dibuja un aviso indicando que no hay bajas de insumos en el sistema. --}}
        @empty
            <tr>
                <td colspan="9" style="text-align:center; padding: 20px; color:#9ca3af;">
                    No hay bajas de insumos registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
