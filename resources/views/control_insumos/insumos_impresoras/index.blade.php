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
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fa fa-list-ul me-2"></i>Catálogo de Insumos
                            </h5>
                            <span class="badge bg-dark rounded-pill px-3 py-2" id="totalInsumos">
                                {{ $insumos->total() }} Registros
                            </span>
                        </div>
                    </div>

                    {{-- ── Panel de filtros avanzados ──────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">

                        {{-- Búsqueda por insumo --}}
                        <x-filtro-buscar id="filtro-buscar" label="Buscar insumo" placeholder="Modelo, color o compatibilidad..." />

                        {{-- Filtro Desplegable Premium --}}
                        <x-filtro-dropdown id="dropdownFiltros" label="Filtrar por categoría" labelDefault="Todos los insumos">
                            <!-- Grupo: Familia -->
                            <div class="mb-2">
                                <span class="text-muted fw-bold d-block mb-1 small text-uppercase" style="font-size:0.7rem; letter-spacing: 0.5px;">Familia</span>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-familia" type="checkbox" value="Tóner" id="chkFamiliaToner">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkFamiliaToner">Tóner</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-familia" type="checkbox" value="Cartucho" id="chkFamiliaCartucho">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkFamiliaCartucho">Cartucho</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-familia" type="checkbox" value="Cinta" id="chkFamiliaCinta">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkFamiliaCinta">Cinta</label>
                                </div>
                            </div>
                            
                            <!-- Grupo: Estado -->
                            <div class="mb-3">
                                <span class="text-muted fw-bold d-block mb-1 small text-uppercase" style="font-size:0.7rem; letter-spacing: 0.5px;">Estado</span>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-status" type="checkbox" value="1" id="chkStatusActivo">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkStatusActivo">Activos</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-status" type="checkbox" value="0" id="chkStatusInactivo">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkStatusInactivo">Inactivos</label>
                                </div>
                            </div>
                        </x-filtro-dropdown>

                        {{-- Rango de fechas (Flatpickr) --}}
                        <x-filtro-fecha-rango id="filtro-fecha-rango" label="Fecha de registro" />

                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalAltaInsumo">
                                <i class="fa fa-plus-circle me-1"></i>Registrar Insumo
                            </button>
                        </div>
                        <div>
                            <a href="{{ route('insumos_impresoras.imprimir') }}" target="_blank"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Imprimir Catálogo
                            </a>
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

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    @vite(['resources/css/control_insumos/insumos_impresoras/insumos_impresoras.css',
            'resources/js/control_insumos/insumos_impresoras/insumos_impresoras.js'])
@endpush
@endsection
