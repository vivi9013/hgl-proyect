@extends('layouts.reporte_base')

@section('title', 'Reporte de Stock por Área')

@section('report_title', 'REPORTE DE INSUMOS POR ÁREA — STOCK Y FONDO FIJO')

@section('report_subheader')
    <div style="margin-bottom: 20px; font-size: 11px;">
        <p><strong>Área:</strong> {{ $area ? $area->nombre : 'Todas las Áreas' }}</p>
        <p><strong>Niveles de stock incluidos:</strong> 
            @if(empty($niveles))
                Todos los niveles
            @else
                @php
                    $txtNiveles = [];
                    foreach($niveles as $nivel) {
                        $txtNiveles[] = match($nivel) {
                            'muy_bajo'   => 'Muy Bajo (0-24%)',
                            'bajo'       => 'Bajo (25-49%)',
                            'regular'    => 'Regular (50-74%)',
                            'suficiente' => 'Suficiente (75-100%)',
                            'excedido'   => 'Excedido (>100%)',
                            default      => $nivel
                        };
                    }
                    echo implode(', ', $txtNiveles);
                @endphp
            @endif
        </p>
    </div>
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th class="center" style="width: 50px;">#</th>
                <th class="center" style="width: 100px;">Clave</th>
                <th>Descripción</th>
                <th class="center" style="width: 120px;">Tipo</th>
                @if(!$area)
                    <th>Área</th>
                @endif
                <th class="center" style="width: 80px;">Stock</th>
                <th class="center" style="width: 80px;">Fondo Fijo</th>
                <th class="center" style="width: 80px;">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse($insumos as $ia)
                @php
                    $stockVal = (int) $ia->stock;
                    $ffVal = (int) $ia->fondo_fijo;
                    $porcentaje = $ffVal > 0 ? round(($stockVal * 100) / $ffVal, 1) : 0;
                @endphp
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td class="center" style="font-weight: bold;">{{ $ia->insumo->clave ?? '—' }}</td>
                    <td>{{ $ia->insumo->descripcion ?? '—' }}</td>
                    <td class="center">{{ $ia->insumo->tipo ?? '—' }}</td>
                    @if(!$area)
                        <td>{{ $ia->areaAlmacen->nombre ?? '—' }}</td>
                    @endif
                    <td class="center" style="font-weight: bold;">{{ $ia->stock }}</td>
                    <td class="center">{{ $ia->fondo_fijo }}</td>
                    <td class="center" style="font-weight: bold;">{{ $porcentaje }} %</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ !$area ? 8 : 7 }}" class="center" style="padding: 20px; color: #555;">
                        No se encontraron insumos que coincidan con los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="text-align: right; margin-top: 15px; font-size: 12px; font-weight: bold;">
        Total de stock general listado: {{ $insumos->sum(fn($ia) => (int)$ia->stock) }}
    </div>
@endsection
