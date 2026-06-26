@props([
    'inputId' => 'buscarInsumo',
    'panelId' => 'panelClaves',
    'endpoint',
    'areaInputId' => null,
    'columnaExtra' => 'none', // 'stock', 'tipo', 'none'
])

<div id="{{ $panelId }}" class="panel-claves-container" style="display:none; position:absolute; left:0; top:calc(100% + 6px); z-index:1070; width:580px; max-width:92vw; background:#fff; border:1px solid #cbd5e1; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.13); overflow:hidden;">
    <div style="background:#1d4ed8; padding:8px 14px; display:flex; justify-content:space-between; align-items:center;">
        <span style="color:#fff; font-weight:700; font-size:0.82rem;">
            <i class="fa fa-list-alt me-1"></i> Claves disponibles
        </span>
        <button type="button" class="cerrar-panel-claves" data-panel="{{ $panelId }}" style="background:transparent; border:none; color:#fff; font-size:1rem; cursor:pointer; line-height:1;" title="Cerrar">
            <i class="fa fa-times"></i>
        </button>
    </div>
    <div style="padding:8px 10px; border-bottom:1px solid #e5e7eb;">
        <input type="text" class="filtro-panel-claves" data-panel="{{ $panelId }}" placeholder="Filtrar claves…" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:5px 10px; font-size:0.8rem; outline:none;">
    </div>
    
    @if($areaInputId)
        <div class="aviso-sin-area" data-panel="{{ $panelId }}" style="display:none; align-items:center; gap:8px; padding:7px 14px; background:#fffbeb; border-bottom:1px solid #fde68a; font-size:0.78rem; color:#92400e;">
            <i class="fa fa-info-circle" style="color:#d97706;"></i>
            <span>Selecciona primero un <strong>Área de Almacén</strong> para continuar.</span>
        </div>
    @endif

    <div class="cuerpo-panel-claves" style="max-height:260px; overflow-y:auto; overflow-x:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:0.8rem; table-layout:fixed;">
            <colgroup>
                <col style="width: 25%;">
                <col style="width: {{ $columnaExtra !== 'none' ? '55%' : '75%' }};">
                @if($columnaExtra !== 'none')
                    <col style="width: 20%;">
                @endif
            </colgroup>
            <thead>
                <tr style="background:#f8fafc; position:sticky; top:0; z-index:1;">
                    <th style="padding:6px 10px; text-align:left; color:#374151; font-weight:700; border-bottom:1px solid #e5e7eb;">Clave</th>
                    <th style="padding:6px 10px; text-align:left; color:#374151; font-weight:700; border-bottom:1px solid #e5e7eb;">Descripción</th>
                    @if($columnaExtra === 'stock')
                        <th style="padding:6px 10px; text-align:center; color:#374151; font-weight:700; border-bottom:1px solid #e5e7eb;">Stock</th>
                    @elseif($columnaExtra === 'tipo')
                        <th style="padding:6px 10px; text-align:center; color:#374151; font-weight:700; border-bottom:1px solid #e5e7eb;">Tipo</th>
                    @endif
                </tr>
            </thead>
            <tbody class="filas-claves" data-panel="{{ $panelId }}"></tbody>
        </table>
        <div class="panel-claves-loading" data-panel="{{ $panelId }}" style="text-align:center; padding:18px; color:#6b7280; font-size:0.82rem;">
            <i class="fa fa-circle-o-notch fa-spin me-1"></i> Cargando claves…
        </div>
        <div class="panel-claves-vacio" data-panel="{{ $panelId }}" style="display:none; text-align:center; padding:18px; color:#9ca3af; font-size:0.82rem;">
            <i class="fa fa-search me-1"></i> Sin resultados
        </div>
    </div>
</div>
