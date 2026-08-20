@forelse($solicitudes as $s)
    <div class="border rounded-3 p-3 mb-3" data-solicitud-id="{{ $s->id }}">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <strong>Usuario:</strong> {{ $s->nombre_usuario }}<br>
                <strong>Declaró:</strong> {{ $s->nombre_declarado }}
                @if($s->dato_adicional)
                    <br><strong>Dato adicional:</strong> {{ $s->dato_adicional }}
                @endif
            </div>
            <div class="text-end small text-muted">
                {{ $s->fecha }} {{ $s->hora }}<br>IP: {{ $s->ip }}
            </div>
        </div>

        @if($s->usuario && $s->usuario->persona)
            <div class="alert alert-light border small mb-2">
                <strong>Registrado en el sistema:</strong>
                {{ $s->usuario->persona->nombre }} {{ $s->usuario->persona->ap_paterno }} {{ $s->usuario->persona->ap_materno }}
                @if($s->usuario->persona->curp) — CURP: {{ $s->usuario->persona->curp }} @endif
                @if($s->usuario->persona->rfc) — RFC: {{ $s->usuario->persona->rfc }} @endif
                @if($s->usuario->persona->telefono) — Tel: {{ $s->usuario->persona->telefono }} @endif
            </div>
        @endif

        <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-sm btn-outline-danger btn-rechazar-solicitud" data-id="{{ $s->id }}">
                <i class="fa fa-times me-1"></i>Rechazar
            </button>
            <button type="button" class="btn btn-sm btn-success btn-aprobar-solicitud" data-id="{{ $s->id }}">
                <i class="fa fa-check me-1"></i>Aprobar y Restablecer
            </button>
        </div>
    </div>
@empty
    <p class="text-center text-muted py-4 mb-0">No hay solicitudes pendientes.</p>
@endforelse
