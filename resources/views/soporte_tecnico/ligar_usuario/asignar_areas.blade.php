@extends('layouts.app')
@section('title', 'Asignar Áreas – ' . $persona->nombre_completo ?? '')
@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-asignar-areas">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-map-marker me-2"></i>Asignar Áreas de Atención
            </h1>
            <p class="text-muted mb-0">
                Técnico:
                <strong>
                    {{ $persona->ap_paterno }} {{ $persona->ap_materno }} {{ $persona->nombre }}
                </strong>
            </p>
        </div>
        <a href="{{ route('soporte_area.index') }}"
           class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i>Regresar
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fa fa-list me-2"></i>Lista de Áreas Disponibles
                    </h5>
                    <p class="text-muted small mt-1 mb-0">
                        Selecciona las áreas que este técnico debe atender. Las áreas marcadas en
                        <span class="text-primary fw-semibold">azul</span> ya están asignadas.
                    </p>
                </div>

                <form id="formAsignarAreas"
                      action="{{ route('soporte_area.sincronizar', $persona->id) }}"
                      method="POST">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaAreas">
                            <thead class="table-light text-uppercase small text-secondary">
                                <tr>
                                    <th style="width:48px;"  class="text-center">#</th>
                                    <th>Área</th>
                                    <th style="width:120px;" class="text-center">Abreviatura</th>
                                    <th style="width:80px;"  class="text-center">Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($areas as $i => $area)
                                    <tr id="fila-area-{{ $area->id }}"
                                        class="{{ $area->asignada ? 'table-primary' : '' }}">

                                        <td class="text-center">{{ $i + 1 }}</td>

                                        <td class="fw-semibold">{{ $area->area }}</td>
                                        <td class="text-center text-muted small">{{ $area->abreviatura }}</td>

                                        {{-- Checkbox de selección --}}
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   class="form-check-input chk-area"
                                                   name="areas[]"
                                                   value="{{ $area->id }}"
                                                   id="chkArea{{ $area->id }}"
                                                   {{ $area->asignada ? 'checked' : '' }}
                                                   data-fila="fila-area-{{ $area->id }}">
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                            No hay áreas activas registradas en el sistema.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer: Marcar todos / Desmarcar todos / Guardar --}}
                    <div class="card-footer bg-white border-top px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex gap-3 text-muted small">
                            <a href="#" id="btnMarcarTodos" class="text-decoration-none">
                                <i class="fa fa-check-square me-1"></i>Marcar todos
                            </a>
                            <span class="text-muted">/</span>
                            <a href="#" id="btnDesmarcarTodos" class="text-decoration-none">
                                <i class="fa fa-minus-square me-1"></i>Desmarcar todos
                            </a>
                        </div>
                        <button type="submit"
                                id="btnGuardar"
                                class="btn btn-primary rounded-pill px-4"
                                disabled>
                            <i class="fa fa-save me-2"></i>Guardar Asignación
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/css/soporte_tecnico/soporte_area/soporte_area.css',
            'resources/js/soporte_tecnico/soporte_area/soporte_area.js'])
@endpush
@endsection
