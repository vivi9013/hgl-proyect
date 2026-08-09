@extends('layouts.reporte_base')

@section('title', 'Comprobante – DEV-' . str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT))

@push('styles')
<style>
    .titulo { text-align: center; margin-bottom: 16px; }
    .titulo h1 { font-size: 14px; font-weight: bold; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; }
    .folio { display: inline-block; background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8; font-size: 13px; font-weight: bold; padding: 3px 14px; border-radius: 6px; margin-top: 4px; }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
    .info-item .etiqueta { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 0.05em; }
    .info-item .valor { font-size: 11.5px; color: #0f172a; font-weight: 600; margin-top: 1px; }

    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    thead tr { background-color: #1d4ed8; color: #fff; }
    thead th { padding: 7px 8px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
    tbody tr { border-bottom: 1px solid #e2e8f0; }
    tbody tr:nth-child(even) { background-color: #f8fafc; }
    tbody td { padding: 6px 8px; font-size: 10.5px; vertical-align: top; }
    .clave-badge { font-weight: bold; color: #1d4ed8; font-family: monospace; }

    .firma-area { display: flex; justify-content: space-around; margin-top: 40px; }
    .firma-item { text-align: center; width: 200px; }
    .firma-linea { border-top: 1.5px solid #1a1a1a; margin-bottom: 4px; }
    .firma-etiqueta { font-size: 9.5px; color: #374151; }
</style>
@endpush

@section('content')

    {{-- Título --}}
    <div class="titulo">
        <h1>Comprobante de Devolución</h1>
        <div class="folio">DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    {{-- Info de la devolución --}}
    <div class="info-grid">
        <div class="info-item">
            <div class="etiqueta">Área de Almacén</div>
            <div class="valor">{{ $devolucion->areaAlmacen->nombre ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Área de Abastecimiento</div>
            <div class="valor">{{ $devolucion->areaAbastecimiento->nombre ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Motivo de Devolución</div>
            <div class="valor">{{ $devolucion->motivo->descripcion ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Fecha de Registro</div>
            <div class="valor">{{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Hora</div>
            <div class="valor">{{ $devolucion->hora_devolucion ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Status</div>
            <div class="valor">{{ $devolucion->status }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Registrado por</div>
            <div class="valor">
                {{ $devolucion->usuario && $devolucion->usuario->persona 
                   ? ($devolucion->usuario->persona->nombre . ' ' . $devolucion->usuario->persona->ap_paterno) 
                   : ($devolucion->usuario->nombre_usuario ?? '—') }}
            </div>
        </div>
    </div>

    {{-- Tabla de insumos --}}
    <h2 style="font-size:11.5px; font-weight:bold; margin-bottom:6px; color:#0f172a;">
        Insumos Devueltos ({{ $devolucion->detalles->count() }})
    </h2>
    @if($devolucion->detalles->isEmpty())
        <p style="color:#64748b; font-style:italic; padding:10px 0;">No hay insumos en esta devolución.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:35px;">#</th>
                    <th style="width:90px;">Clave</th>
                    <th>Descripción del Insumo</th>
                    <th style="width:60px; text-align:center;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devolucion->detalles as $i => $detalle)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="clave-badge">{{ $detalle->insumo->clave ?? '—' }}</span></td>
                        <td>{{ $detalle->insumo->descripcion ?? '—' }}</td>
                        <td style="text-align:center; font-weight:bold;">{{ $detalle->cantidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Firmas --}}
    <div class="firma-area">
        <div class="firma-item">
            <div class="firma-linea"></div>
            <div class="firma-etiqueta">Responsable de Almacén</div>
        </div>
        <div class="firma-item">
            <div class="firma-linea"></div>
            <div class="firma-etiqueta">Área de Abastecimiento</div>
        </div>
    </div>

@endsection
