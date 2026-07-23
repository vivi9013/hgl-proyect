@extends('layouts.app')

@section('title', 'Asociar Módulos a Perfil - Hospital General')

@section('content')
{{-- Alertas de Sesión renderizadas por SweetAlert2 --}}
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display: none;"></div>
@endif

<div class="container-fluid py-4">
    {{-- Encabezado de Navegación y Título --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-folder-open text-primary me-2"></i>Asociación de Módulos
            </h1>
            <p class="text-muted mb-0">Seleccione qué módulos estarán accesibles para los usuarios con el perfil: <strong>{{ $perfil->nombre }}</strong></p>
        </div> 
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fa fa-cubes text-secondary me-2"></i>Módulos Disponibles
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" id="btnMarcarTodos" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="fa fa-check-square-o me-1"></i>Marcar todos
                            </button>
                            <button type="button" id="btnDesmarcarTodos" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="fa fa-square-o me-1"></i>Desmarcar todos
                            </button>
                        </div>
                    </div>
                </div>

                <form id="formAsociarModulos" action="{{ route('perfiles.modulos.sync', $perfil->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body px-4 py-3">
                        <div class="table-responsive" style="border: 1px solid #e5e7eb; border-radius: 8px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                    <tr>
                                        <th class="ps-4" style="width: 80px;">#</th>
                                        <th>Categoría</th>
                                        <th>Módulo</th>
                                        <th class="text-center" style="width: 120px;">Icono</th>
                                        <th class="text-center pe-4" style="width: 150px;">Agregar/Quitar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($modulos as $index => $modulo)
                                        @php
                                            $estaAsignado = $asignados->contains($modulo->id);
                                        @endphp
                                        <tr id="filaModulo{{ $modulo->id }}" class="{{ $estaAsignado ? 'table-success-custom' : '' }}">
                                            <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                                            <td class="text-secondary small">{{ $modulo->categoria ? $modulo->categoria->categoria : 'Sin categoría' }}</td>
                                            <td class="fw-semibold text-dark">{{ $modulo->nombre }}</td>
                                            <td class="text-center">
                                                <i class="{{ $modulo->icono }} fs-5"></i>
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="form-check d-inline-block">
                                                    <input type="checkbox" 
                                                           name="modulos[]" 
                                                           value="{{ $modulo->id }}" 
                                                           id="check_modulo{{ $modulo->id }}" 
                                                           class="form-check-input casilla-modulo"
                                                           {{ $estaAsignado ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fa fa-exclamation-circle me-2"></i>No hay módulos activos en el sistema.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer con Paginación Homologada --}}
                    <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center border-top">
                        <div class="text-muted small" id="infoPaginacion">
                            Mostrando 0 a 0 de 0 registros
                        </div>
                        <nav aria-label="Paginacion de modulos">
                            <ul class="pagination mb-0" id="contenedorPaginacion">
                                {{-- Llenado dinámicamente por JS --}}
                            </ul>
                        </nav>
                    </div>

                    <div class="card-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-between align-items-center border-top">
                        <a href="{{ route('perfiles.index') }}" class="btn btn-light py-2 rounded-pill shadow-sm">
                            <i class="fa fa-arrow-left me-2"></i>Volver al Catálogo
                        </a>
                        <button type="submit" id="btnGuardarAsignacion" class="btn btn-primary py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Guardar Asignación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/perfiles/perfiles.css', 'resources/js/perfiles/perfiles_modulos.js'])
@endsection
