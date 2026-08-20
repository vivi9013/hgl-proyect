@extends('layouts.app')
@section('title', 'Movimientos de Insumos - Hospital General')
@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif
@if($errors->any())
    <div id="alertaError" data-message="{{ $errors->first() }}" style="display:none;"></div>
@endif



<div class="container-fluid py-4" id="modulo-movimientos-insumos">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-exchange me-2"></i>Movimientos de Insumos
            </h1>
            <p class="text-muted mb-0">Control de Insumos / Entradas y Salidas de Tóneres, Cartuchos y Cintas</p>
        </div>
    </div>


    {{-- ─── Modal: ENTRADA ──────────────────────────────────────────────────── --}}
    <div class="modal fade" id="modalEntrada" tabindex="-1" aria-labelledby="modalEntradaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px; overflow:hidden;">
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalEntradaLabel">
                        <i class="fa fa-arrow-circle-down me-2"></i>Registrar Entrada de Insumo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('movimientos_insumos.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="tipo" value="Entrada">
                    <div class="modal-body p-4">
                        <div class="row g-3">

                            {{-- Insumo --}}
                            <div class="col-12">
                                <label for="id_insumo_impresora_entrada" class="form-label fw-bold text-secondary">Insumo *:</label>
                                <select name="id_insumo_impresora" id="id_insumo_impresora_entrada"
                                        class="form-select" required>
                                    <option value="">Seleccione un insumo del catálogo...</option>
                                    @foreach($insumos as $ins)
                                        <option value="{{ $ins->id_insumo_impresora }}">
                                            {{ $ins->familia }} — {{ $ins->modelo }} ({{ $ins->color }}) | Stock: {{ $ins->stock }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Concepto --}}
                            <div class="col-12 col-md-4">
                                <label for="concepto_entrada" class="form-label fw-bold text-secondary">Concepto *:</label>
                                <select name="concepto" id="concepto_entrada" class="form-select" required disabled>
                                    <option value="">Seleccione...</option>
                                    @foreach($conceptosEntrada as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Cantidad --}}
                            <div class="col-12 col-md-4">
                                <label for="cantidad_entrada" class="form-label fw-bold text-secondary">Cantidad *:</label>
                                <input type="number" name="cantidad" id="cantidad_entrada"
                                       class="form-control" min="1" required placeholder="Ej: 10" disabled>
                            </div>

                            {{-- Fecha --}}
                            <div class="col-12 col-md-4">
                                <label for="fecha_entrada" class="form-label fw-bold text-secondary">Fecha *:</label>
                                <input type="date" name="fecha_movimiento" id="fecha_entrada"
                                       class="form-control" value="{{ now()->toDateString() }}" required disabled>
                            </div>

                            {{-- Proveedor --}}
                            <div class="col-12">
                                <label for="select_proveedor_entrada" class="form-label fw-bold text-secondary">Proveedor / Fuente:</label>
                                <select id="select_proveedor_entrada" class="form-select mb-2" disabled>
                                    <option value="">Seleccione un proveedor...</option>
                                    <option value="Tigre">Tigre</option>
                                    <option value="Premium">Premium</option>
                                    <option value="PREMIUM Cartridge">PREMIUM Cartridge</option>
                                    <option value="Generico">Genérico</option>
                                    <option value="Otro">Otro (especificar)</option>
                                </select>
                                {{-- Campo de texto libre, visible sólo al elegir "Otro" --}}
                                <input type="text" name="proveedor" id="proveedor_entrada"
                                       class="form-control d-none"
                                       placeholder="Escribe el nombre del proveedor..." disabled>
                            </div>



                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 border rounded-pill" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success px-4 py-2 rounded-pill">
                            <i class="fa fa-save me-2"></i>Guardar Entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ─── Modal: SALIDA ───────────────────────────────────────────────────── --}}
    <div class="modal fade" id="modalSalida" tabindex="-1" aria-labelledby="modalSalidaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px; overflow:hidden;">
                <div class="modal-header bg-danger text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalSalidaLabel">
                        <i class="fa fa-arrow-circle-up me-2"></i>Registrar Salida de Insumo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSalida" action="{{ route('movimientos_insumos.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <input type="hidden" name="tipo" value="Salida">
                    <div class="modal-body p-4">
                        <div class="row g-3">

                            {{-- Insumo --}}
                            <div class="col-12">
                                <label for="id_insumo_impresora" class="form-label fw-bold text-secondary">Insumo *:</label>
                                <select name="id_insumo_impresora" id="id_insumo_impresora"
                                        class="form-select" required>
                                    <option value="">Seleccione un insumo del catálogo...</option>
                                    @foreach($insumos as $ins)
                                        <option value="{{ $ins->id_insumo_impresora }}"
                                                data-modelo="{{ $ins->modelo }}"
                                                data-color="{{ $ins->color }}"
                                                data-familia="{{ $ins->familia }}"
                                                data-hojas="{{ $ins->hojas_uso_total }}"
                                                data-tiempo="{{ $ins->tiempo_uso }}"
                                                data-stock="{{ $ins->stock }}"
                                                data-compatibles="{{ $ins->modelos_compatibles }}">
                                            {{ $ins->familia }} &mdash; {{ $ins->modelo }} ({{ $ins->color }}) | Stock: {{ $ins->stock }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Concepto --}}
                            <div class="col-12 col-md-4">
                                <label for="concepto_salida" class="form-label fw-bold text-secondary">Concepto *:</label>
                                <select name="concepto" id="concepto_salida" class="form-select" required disabled>
                                    <option value="">Seleccione...</option>
                                    @foreach($conceptosSalida as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Cantidad --}}
                            <div class="col-12 col-md-4">
                                <label for="cantidad_salida" class="form-label fw-bold text-secondary">Cantidad *:</label>
                                <input type="number" name="cantidad" id="cantidad_salida"
                                       class="form-control" min="1" required placeholder="Ej: 1" disabled>
                                <div class="invalid-feedback" id="error_cantidad_salida">
                                    La cantidad no puede superar el stock disponible.
                                </div>
                            </div>

                            {{-- Fecha --}}
                            <div class="col-12 col-md-4">
                                <label for="fecha_salida" class="form-label fw-bold text-secondary">Fecha *:</label>
                                <input type="date" name="fecha_movimiento" id="fecha_salida"
                                       class="form-control" value="{{ now()->toDateString() }}" required disabled>
                            </div>

                            {{-- Panel de información del insumo --}}
                            <div class="col-12" id="panelInfoInsumo" style="display:none;">
                                <div class="p-3 rounded-3" style="background:#eef6ff; border:1px solid #bcd8ff; font-size:0.85rem;">
                                    <div class="fw-semibold text-primary mb-1">
                                        <i class="fa fa-info-circle me-1"></i>
                                        Compatibilidad: <span id="infoCompatibles" class="fw-normal">—</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 text-muted">
                                        <span>Rendimiento: <strong id="infoHojas">—</strong></span>
                                        <span>Tiempo uso: <strong id="infoTiempo">—</strong></span>
                                        <span class="text-success fw-bold">Stock disponible: <strong id="infoStock">—</strong></span>
                                    </div>
                                </div>
                            </div>



                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 border rounded-pill" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill">
                            <i class="fa fa-save me-2"></i>Guardar Salida
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ─── Modal: EDITAR MOVIMIENTO ────────────────────────────────────────── --}}
    <div class="modal fade" id="modalEditarMovimiento" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius:15px; overflow:hidden;">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalEditarLabel">
                        <i class="fa fa-pencil-square-o me-2"></i>Editar Movimiento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarMovimiento" autocomplete="off">
                    <div class="modal-body p-4">
                        <div class="row g-3">

                            {{-- Insumo (solo lectura) --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary">Insumo:</label>
                                <input type="text" id="editar_insumo_nombre" class="form-control bg-light" readonly>
                            </div>

                            {{-- Tipo --}}
                            <div class="col-12 col-md-6">
                                <label for="editar_tipo" class="form-label fw-bold text-secondary">Tipo *:</label>
                                <select name="tipo" id="editar_tipo" class="form-select" required>
                                    <option value="Entrada">Entrada</option>
                                    <option value="Salida">Salida</option>
                                </select>
                            </div>

                            {{-- Cantidad --}}
                            <div class="col-12 col-md-6">
                                <label for="editar_cantidad" class="form-label fw-bold text-secondary">Cantidad *:</label>
                                <input type="number" name="cantidad" id="editar_cantidad" class="form-control" min="1" required>
                            </div>

                            {{-- Concepto --}}
                            <div class="col-12 col-md-6">
                                <label for="editar_concepto" class="form-label fw-bold text-secondary">Concepto *:</label>
                                <select name="concepto" id="editar_concepto" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>

                            {{-- Fecha --}}
                            <div class="col-12 col-md-6">
                                <label for="editar_fecha_movimiento" class="form-label fw-bold text-secondary">Fecha *:</label>
                                <input type="date" name="fecha_movimiento" id="editar_fecha_movimiento"
                                       class="form-control" required>
                            </div>

                            {{-- Proveedor (solo Entradas) --}}
                            <div class="col-12" id="editar_campo_proveedor">
                                <label for="editar_select_proveedor" class="form-label fw-bold text-secondary">Proveedor / Fuente:</label>
                                <select id="editar_select_proveedor" class="form-select mb-2">
                                    <option value="">Seleccione un proveedor...</option>
                                    <option value="Tigre">Tigre</option>
                                    <option value="Premium">Premium</option>
                                    <option value="PREMIUM Cartridge">PREMIUM Cartridge</option>
                                    <option value="Generico">Genérico</option>
                                    <option value="Otro">Otro (especificar)</option>
                                </select>
                                <input type="text" name="proveedor" id="editar_proveedor"
                                       class="form-control d-none"
                                       placeholder="Escribe el nombre del proveedor...">
                            </div>

                            {{-- Panel info insumo (solo Salidas) --}}
                            <div class="col-12" id="editar_panel_info_insumo" style="display:none;">
                                <div class="p-3 rounded-3" style="background:#eef6ff; border:1px solid #bcd8ff; font-size:0.85rem;">
                                    <div class="fw-semibold text-primary mb-1">
                                        <i class="fa fa-info-circle me-1"></i>
                                        Compatibilidad: <span id="editar_info_compatibles" class="fw-normal">—</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 text-muted">
                                        <span>Rendimiento: <strong id="editar_info_hojas">—</strong></span>
                                        <span>Tiempo uso: <strong id="editar_info_tiempo">—</strong></span>
                                        <span class="text-success fw-bold">Stock disponible: <strong id="editar_info_stock">—</strong></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 border rounded-pill" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-dark px-4 py-2 rounded-pill">
                            <i class="fa fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ─── Tabla unificada de Movimientos ─────────────────────────────────── --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                {{-- Cabecera tarjeta --}}
                <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fa fa-list-ul me-2"></i>Historial de Movimientos
                        </h5>
                    </div>

                    {{-- ── Panel de filtros avanzados ──────────────────────────── --}}
                    <div class="row g-2 align-items-end" id="panelFiltros">

                        {{-- Búsqueda por insumo --}}
                        <x-filtro-buscar id="filtro-buscar" label="Buscar insumo" placeholder="Modelo del insumo..." />

                        {{-- Filtro Desplegable Premium --}}
                        <x-filtro-dropdown id="dropdownFiltros" label="Filtrar por categoría" labelDefault="Todos los movimientos">
                            <!-- Grupo: Tipo -->
                            <div class="mb-2">
                                <span class="text-muted fw-bold d-block mb-1 small text-uppercase" style="font-size:0.7rem; letter-spacing: 0.5px;">Tipo</span>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-tipo" type="checkbox" value="Entrada" id="chkTipoEntrada">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkTipoEntrada">Entradas</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-tipo" type="checkbox" value="Salida" id="chkTipoSalida">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkTipoSalida">Salidas</label>
                                </div>
                            </div>
                            
                            <!-- Grupo: Concepto -->
                            <div class="mb-2">
                                <span class="text-muted fw-bold d-block mb-1 small text-uppercase" style="font-size:0.7rem; letter-spacing: 0.5px;">Concepto</span>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-concepto" type="checkbox" value="Compra" id="chkConceptoCompra">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkConceptoCompra">Compra</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-concepto" type="checkbox" value="Donación" id="chkConceptoDonacion">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkConceptoDonacion">Donación</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-concepto" type="checkbox" value="Uso" id="chkConceptoUso">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkConceptoUso">Uso</label>
                                </div>
                                <div class="form-check py-1">
                                    <input class="form-check-input chk-concepto" type="checkbox" value="Por daño" id="chkConceptoDano">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkConceptoDano">Por daño</label>
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
                                    <input class="form-check-input chk-status" type="checkbox" value="0" id="chkStatusCancelado">
                                    <label class="form-check-label text-dark cursor-pointer" for="chkStatusCancelado">Cancelados</label>
                                </div>
                            </div>
                        </x-filtro-dropdown>

                        {{-- Rango de fechas (Flatpickr) --}}
                        <x-filtro-fecha-rango id="filtro-fecha-rango" />

                    </div>
                    {{-- /panelFiltros --}}

                    {{-- Acciones secundarias (Registros e Reporte) --}}
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-success rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalEntrada">
                                <i class="fa fa-arrow-circle-down me-1"></i>Registrar Entrada
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#modalSalida">
                                <i class="fa fa-arrow-circle-up me-1"></i>Registrar Salida
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('movimientos_insumos.imprimir') }}" target="_blank"
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-pdf-o me-1 text-danger"></i>Imprimir
                            </a>
                            <a href="{{ route('movimientos_insumos.exportar_excel') }}" target="_blank"
                               class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm text-nowrap">
                                <i class="fa fa-file-excel-o me-1 text-success"></i>Exportar Excel
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Tabla de movimientos --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        @include('control_insumos.movimientos_insumos.partials.tabla')
                    </table>
                </div>

                {{-- Pie: info + paginación --}}
                <div class="px-4 py-3 d-flex justify-content-between align-items-center border-top">
                    <div class="text-muted small" id="infoPaginacionMovimientos">
                        Mostrando {{ $movimientos->firstItem() ?? 0 }} a {{ $movimientos->lastItem() ?? 0 }}
                        de {{ $movimientos->total() }} registros
                    </div>
                    <nav>
                        <div id="paginacionMovimientos">
                            {{ $movimientos->links('pagination::bootstrap-4') }}
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
    @vite(['resources/css/control_insumos/movimientos_insumos/movimientos_insumos.css',
            'resources/js/control_insumos/movimientos_insumos/movimientos_insumos.js'])
@endpush
@endsection
