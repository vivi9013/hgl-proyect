@extends('layouts.app')

@section('title', 'Acceso a Categorías de Archivos')

@section('content')
<div class="container-fluid py-4">

    {{-- ── 1. Encabezado del Módulo (Patrón unificado del Hospital) ──────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-key text-primary me-2"></i>Permisos de Archivos
            </h1>
            <p class="text-muted mb-0">Gestión y control de acceso a categorías por trabajador</p>
        </div>
    </div>

    {{-- ── 2. Navegación Interna y Contenedor con los Submódulos ── --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-secondary fw-bold me-2"><i class="fa fa-arrow-circle-right text-primary"></i> Submódulos:</span>
                    
                    {{-- Enlace al catálogo de categorías (el index de referencia que me pasaste) --}}
                    <a href="{{ route('categoria_archivos.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fa fa-folder-open me-1"></i> Catálogo de Categorías
                    </a>
                    
                    {{-- Botón activo actual --}}
                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" disabled>
                        <i class="fa fa-user-plus me-1"></i> Asignar Permisos a Trabajadores
                    </button>
                </div>
            </div>
        </div>
        <!-- Contenedor con los Submódulos -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 rounded-3 bg-white h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-md-end">
                    <a href="{{ route('categoria_archivos.reportes') }}"
                       class="btn btn-sm btn-outline-secondary px-4 py-2 rounded-pill shadow-sm">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i> Reportes 
                    </a>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── 3. Alertas de Operación (SweetAlert2 o Notificaciones) ──────── --}}
    @if(session('exitog') || request('var') == 'exitog')
        <div id="alertaExitog" data-mensaje="El registro se ha guardado correctamente."></div>
    @endif
    @if(session('exito') || request('var') == 'exito')
        <div id="alertaExito" data-mensaje="El registro se ha actualizado correctamente."></div>
    @endif

    {{-- ── 4. Listado de Trabajadores y Permisos Asignados ────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-users text-secondary me-2"></i>Lista de Trabajadores del Hospital
                    </h5>
                    {{-- Conteo dinámico que se alimentará desde el servidor/paginador --}}
                    <span class="badge bg-primary rounded-pill px-4 py-2 shadow-sm fw-bold" id="totalTrabajadores">
                        {{ $trabajadores->total() }} Registros
                    </span>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaTrabajadoresPermisos" class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase font-size-xs text-secondary letter-spacing-1">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">#</th>
                                    <th class="text-center" style="width: 150px;">Asignar</th>
                                    <th>Nombre Completo del Trabajador</th>
                                    <th class="text-center pe-4" style="width: 220px;">Categorías Asignadas</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTrabajadores">
                                @forelse($trabajadores as $index => $trabajador)
                                    <tr>
                                        {{-- Cálculo del índice continuo respetando la paginación de 10 --}}
                                        <td class="ps-4 fw-medium text-secondary">
                                            {{ ($trabajadores->currentPage() - 1) * $trabajadores->perPage() + $loop->iteration }}
                                        </td>
                                        
                                        {{-- Botón de Acción para agregar categorías --}}
                                        <td class="text-center">
                                            <a href="{{ route('trabajador_categorias.create', $trabajador->id) }}" 
                                               class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm text-primary fw-bold d-inline-flex align-items-center gap-1">
                                                <i class="fa fa-plus-circle"></i> Asignar
                                            </a>
                                        </td>
                                        
                                        {{-- Nombre Completo sanitizado --}}
                                        <td>
                                            <div class="fw-bold text-dark fs-6">
                                                {{ $trabajador->ap_paterno }} {{ $trabajador->ap_materno }} {{ $trabajador->nombre }}
                                            </div>
                                            <small class="text-muted"><i class="fa fa-building-o me-1"></i>Sede: {{ $trabajador->sede_nombre }}</small>
                                        </td>
                                        
                                        {{-- Contador de categorías asignadas (equivalente a $row4[0]) --}}
                                        <td class="text-center pe-4">
                                            @if($trabajador->categorias_count > 0)
                                                <span class="badge bg-success px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                                                    <i class="fa fa-check me-1"></i>{{ $trabajador->categorias_count }} Asignadas
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill">
                                                    <i class="fa fa-lock me-1"></i>Ninguna
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="fa fa-user-times fa-3x mb-3 text-secondary opacity-40"></i>
                                                <p class="mb-0 fw-medium text-secondary">No se encontraron trabajadores activos en el sistema.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── 5. Footer con Controles de Paginación Asíncrona (10 registros) ── --}}
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $trabajadores->firstItem() ?? 0 }} a {{ $trabajadores->lastItem() ?? 0 }} de {{ $trabajadores->total() }} trabajadores
                    </div>
                    <nav aria-label="Paginacion de trabajadores">
                        <ul class="pagination mb-0" id="contenedorPaginacion">
                            {{ $trabajadores->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    {{-- Inyección modular compilada mediante Vite cumpliendo la norma de Desacoplamiento --}}
    @vite(['resources/css/trabajador_categorias/permisos.css', 'resources/js/trabajador_categorias/permisos.js'])
@endpush