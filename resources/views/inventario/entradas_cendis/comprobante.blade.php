@extends('layouts.reporte_base')

@section('title', 'Comprobante – ENT-' . str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT))

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
    tfoot td { padding: 7px 8px; font-size: 10.5px; font-weight: bold; border-top: 2px solid #1d4ed8; background: #eff6ff; }
    .clave-badge { font-weight: bold; color: #1d4ed8; font-family: monospace; }
    .faltante-rojo { color: #dc2626; font-weight: bold; }
    .faltante-ok { color: #16a34a; font-weight: bold; }

    .firma-area { display: flex; justify-content: space-around; margin-top: 40px; }
    .firma-item { text-align: center; width: 200px; }
    .firma-linea { border-top: 1.5px solid #1a1a1a; margin-bottom: 4px; }
    .firma-etiqueta { font-size: 9.5px; color: #374151; }
</style>
@endpush

@section('content')

    {{-- Título --}}
    <div class="titulo">
        <h1>Comprobante de Entrada al CENDIS</h1>
        <div class="folio">ENT-{{ str_pad($entrada->id_entrada, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    {{-- Info de la entrada --}}
    <div class="info-grid">
        <div class="info-item">
            <div class="etiqueta">Área de Almacén</div>
            <div class="valor">{{ $entrada->areaAlmacen->nombre ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Área de Surtimiento</div>
            <div class="valor">{{ $entrada->areaSurtimiento->nombre ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Fecha de Entrada</div>
            <div class="valor">{{ $entrada->fecha_entrada ? \Carbon\Carbon::parse($entrada->fecha_entrada)->format('d/m/Y') : '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Hora</div>
            <div class="valor">{{ $entrada->hora_entrada ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Total de Insumos</div>
            <div class="valor">{{ $entrada->total_productos }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Cantidad Total Entregada</div>
            <div class="valor">{{ $entrada->total_cantidad }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Total Solicitado</div>
            <div class="valor">{{ $entrada->solicitado }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Faltante Total</div>
            <div class="valor {{ $entrada->faltante > 0 ? 'faltante-rojo' : 'faltante-ok' }}">{{ $entrada->faltante }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Status</div>
            <div class="valor">{{ $entrada->status }}</div>
        </div>
        <div class="info-item">
            <div class="etiqueta">Registrado por</div>
            <div class="valor">
                {{ $entrada->usuario && $entrada->usuario->persona
                   ? ($entrada->usuario->persona->nombre . ' ' . $entrada->usuario->persona->ap_paterno)
                   : ($entrada->usuario->nombre_usuario ?? '—') }}
            </div>
        </div>
    </div>

    {{-- Tabla de insumos --}}
    <h2 style="font-size:11.5px; font-weight:bold; margin-bottom:6px; color:#0f172a;">
        Insumos Recibidos ({{ $entrada->detalles->count() }})
    </h2>
    @if($entrada->detalles->isEmpty())
        <p style="color:#64748b; font-style:italic; padding:10px 0;">No hay insumos en esta entrada.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:35px;">#</th>
                    <th style="width:90px;">Clave</th>
                    <th>Descripción del Insumo</th>
                    <th style="width:70px; text-align:center;">Solicitado</th>
                    <th style="width:70px; text-align:center;">Entregado</th>
                    <th style="width:60px; text-align:center;">Faltante</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entrada->detalles as $i => $detalle)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="clave-badge">{{ $detalle->insumo->clave ?? '—' }}</span></td>
                        <td>{{ $detalle->insumo->descripcion ?? '—' }}</td>
                        <td style="text-align:center;">{{ $detalle->solicitado }}</td>
                        <td style="text-align:center; font-weight:bold;">{{ $detalle->cantidad }}</td>
                        <td style="text-align:center;" class="{{ $detalle->faltante > 0 ? 'faltante-rojo' : 'faltante-ok' }}">{{ $detalle->faltante }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;">Totales:</td>
                    <td style="text-align:center;">{{ $entrada->detalles->sum('solicitado') }}</td>
                    <td style="text-align:center;">{{ $entrada->detalles->sum('cantidad') }}</td>
                    <td style="text-align:center;" class="{{ $entrada->detalles->sum('faltante') > 0 ? 'faltante-rojo' : 'faltante-ok' }}">{{ $entrada->detalles->sum('faltante') }}</td>
                </tr>
            </tfoot>
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
            <div class="firma-etiqueta">Área de Surtimiento</div>
        </div>
    </div>

@endsection
