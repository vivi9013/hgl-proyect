@extends('layouts.app')
@section('title', 'Catálogo de Insumos de Impresoras - Hospital General')
@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-insumos-impresoras">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-th-large me-2"></i>Catálogo de Insumos
            </h1>
            <p class="text-muted mb-0">Control de Insumos / Tóneres, Cartuchos y Cintas</p>
        </div>
    </div>

    {{-- Fila de acciones --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card p-4 h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa fa-info-circle fa-lg text-dark"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Registra un insumo</h6>
                        <p class="text-muted small mb-0">Agrega tóneres, cartuchos o cintas al catálogo con su modelo, color, compatibilidad y rendimiento estimado.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card p-4 h-100 d-flex justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <button type="button"
                            class="btn btn-primary px-3 py-2 rounded-pill shadow-sm text-nowrap"
                            data-bs-toggle="modal" data-bs-target="#modalAltaInsumo">
                        <i class="fa fa-plus-circle me-2"></i>Registrar Insumo
                    </button>
                    <a href="{{ route('insumos_impresoras.imprimir') }}" target="_blank"
                       class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm text-nowrap">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i>Imprimir Catálogo
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de alta --}}
    <div class="modal fade" id="modalAltaInsumo" tabindex="-1" aria-labelledby="modalAltaInsumoLabel" aria-hidden="true"
         @if($errors->any()) data-auto-open="true" @endif>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px; overflow:hidden;">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalAltaInsumoLabel">
                        <i class="fa fa-th-large me-2"></i>Registrar Nuevo Insumo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formularioAltaInsumo" action="{{ route('insumos_impresoras.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">

                            {{-- Familia --}}
                            <div class="col-12 col-md-6">
                                <label for="familia" class="form-label fw-bold text-secondary">Tipo de insumo *:</label>
                                <select name="familia" id="familia" class="form-select @error('familia') is-invalid @enderror" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($familias as $f)
                                        <option value="{{ $f }}" {{ old('familia') == $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                                @error('familia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Modelo --}}
                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold text-secondary">Modelo *:</label>
                                <input type="text" name="modelo" id="modelo"
                                       class="form-control @error('modelo') is-invalid @enderror"
                                       value="{{ old('modelo') }}" placeholder="Ej: CE285A, 664, ZD500" required>
                                @error('modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Color --}}
                            <div class="col-12 col-md-4">
                                <label for="color" class="form-label fw-bold text-secondary">Color *:</label>
                                <select name="color" id="color" class="form-select @error('color') is-invalid @enderror" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($colores as $c)
                                        <option value="{{ $c }}" {{ old('color') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Hojas de uso total --}}
                            <div class="col-12 col-md-4">
                                <label for="hojas_uso_total" class="form-label fw-bold text-secondary">Rendimiento (hojas):</label>
                                <input type="number" name="hojas_uso_total" id="hojas_uso_total" min="1"
                                       class="form-control @error('hojas_uso_total') is-invalid @enderror"
                                       value="{{ old('hojas_uso_total') }}" placeholder="Ej: 1500">
                                @error('hojas_uso_total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tiempo de uso estimado --}}
                            <div class="col-12 col-md-4">
                                <label for="tiempo_uso" class="form-label fw-bold text-secondary">Tiempo de uso estimado:</label>
                                <input type="text" name="tiempo_uso" id="tiempo_uso"
                                       class="form-control @error('tiempo_uso') is-invalid @enderror"
                                       value="{{ old('tiempo_uso') }}" placeholder="Ej: 2 semanas, 1 mes">
                                @error('tiempo_uso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Modelos compatibles --}}
                            <div class="col-12">
                                <label for="modelos_compatibles" class="form-label fw-bold text-secondary">Compatibilidad (modelos de impresora):</label>
                                <input type="text" name="modelos_compatibles" id="modelos_compatibles"
                                       class="form-control @error('modelos_compatibles') is-invalid @enderror"
                                       value="{{ old('modelos_compatibles') }}"
                                       placeholder="Ej: HP LaserJet Pro M404dn, M402n, M403d">
                                @error('modelos_compatibles') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>



                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 border rounded-pill" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardarInsumo" class="btn btn-primary px-4 py-2">
                            <i class="fa fa-save me-2"></i>Guardar Insumo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tabla principal --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fa fa-list-ul me-2"></i>Catálogo de Insumos
                            </h5>
                            <span class="badge bg-dark rounded-pill px-3 py-2" id="totalInsumos">
                                {{ $insumos->total() }} Registros
                            </span>
                        </div>
                        <div class="input-group search-group">
                            <input type="search" id="busqueda-global" class="form-control bg-light border-0"
                                   placeholder="Buscar insumo...">
                            <span class="input-group-text bg-light border-0">
                                <i class="fa fa-search text-dark"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase">
                                <tr>
                                    <th class="text-center" style="width:50px;">#</th>
                                    <th class="text-center" style="width:80px;">Editar</th>
                                    <th>Tipo</th>
                                    <th>Modelo</th>
                                    <th>Color</th>
                                    <th>Compatibilidad</th>
                                    <th class="text-center">Rendimiento</th>
                                    <th class="text-center">Tiempo Uso</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center" style="width:100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaInsumos">
                                @include('control_insumos.insumos_impresoras.partials.tabla')
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $insumos->firstItem() ?? 0 }} a {{ $insumos->lastItem() ?? 0 }}
                        de {{ $insumos->total() }} registros
                    </div>
                    <nav>
                        <div id="contenedorPaginacion">
                            {{ $insumos->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>

</div>

@vite(['resources/css/control_insumos/insumos_impresoras/insumos_impresoras.css',
        'resources/js/control_insumos/insumos_impresoras/insumos_impresoras.js'])
@endsection
