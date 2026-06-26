@extends('layouts.app')
@section('title', 'Catálogo de Impresoras - Hospital General')
@section('content')

@if(session('exitog'))
    <div id="alertaExitog" data-message="{{ session('exitog') }}" style="display:none;"></div>
@endif
@if(session('exito'))
    <div id="alertaExito" data-message="{{ session('exito') }}" style="display:none;"></div>
@endif

<div class="container-fluid py-4" id="modulo-impresoras">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-print me-2"></i>Impresoras
            </h1>
            <p class="text-muted mb-0">Control de Insumos / Catálogo de Impresoras</p>
        </div>
    </div>

    {{-- Fila de acciones --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card p-4 h-100 d-flex justify-content-center">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa fa-info-circle fa-lg text-dark"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Registra una impresora</h6>
                        <p class="text-muted small mb-0">Asigna un número de inventario de mobiliario disponible como impresora y registra sus especificaciones de red y consumible.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card p-4 h-100 d-flex justify-content-center">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <button type="button"
                            class="btn btn-primary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap"
                            data-bs-toggle="modal" data-bs-target="#modalAltaImpresora">
                        <i class="fa fa-plus-circle me-2"></i>Registrar Impresora
                    </button>
                    <a href="{{ route('impresoras.reportes') }}" class="btn btn-outline-secondary px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-file-pdf-o me-2 text-danger"></i>Reportes
                    </a>
                    <a href="{{ route('impresoras.graficas') }}" class="btn btn-outline-success px-3 py-2 rounded-pill shadow-sm w-100 w-sm-auto text-nowrap">
                        <i class="fa fa-bar-chart me-2 text-success"></i>Gráficas
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de alta --}}
    <div class="modal fade" id="modalAltaImpresora" tabindex="-1" aria-labelledby="modalAltaImpresoraLabel" aria-hidden="true" @if($errors->any()) data-auto-open="true" @endif>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg modal-alta-impresora">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalAltaImpresoraLabel">
                        <i class="fa fa-print me-2"></i>Registrar Nueva Impresora
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formularioAltaImpresora" action="{{ route('impresoras.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            {{-- Inventario (Mobiliario) --}}
                            <div class="col-12 col-md-6">
                                <label for="inventario" class="form-label fw-bold text-secondary">No. de Inventario (Mobiliario):</label>
                                <select name="inventario" id="inventario" class="form-select @error('inventario') is-invalid @enderror" required>
                                    <option value="">Seleccione un inventario...</option>
                                    @foreach($inventario as $inv)
                                        <option value="{{ $inv }}" {{ old('inventario') == $inv ? 'selected' : '' }}>{{ $inv }}</option>
                                    @endforeach
                                </select>
                                @error('inventario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Marca --}}
                            <div class="col-12 col-md-6">
                                <label for="marca" class="form-label fw-bold text-secondary">Marca:</label>
                                <input type="text" name="marca" id="marca" class="form-control @error('marca') is-invalid @enderror" value="{{ old('marca') }}" placeholder="Ej: HP, Epson, Zebra" required>
                                @error('marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Modelo --}}
                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold text-secondary">Modelo:</label>
                                <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror" value="{{ old('modelo') }}" placeholder="Ej: LaserJet Pro M404dn">
                                @error('modelo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Serie --}}
                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label fw-bold text-secondary">No. de Serie:</label>
                                <input type="text" name="serie" id="serie" class="form-control @error('serie') is-invalid @enderror" value="{{ old('serie') }}" placeholder="Número de serie físico" required>
                                @error('serie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tipo --}}
                            <div class="col-12 col-md-4">
                                <label for="tipo" class="form-label fw-bold text-secondary">Tipo:</label>
                                <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                    <option value="">Seleccione tipo...</option>
                                    @foreach($tipos as $t)
                                        <option value="{{ $t }}" {{ old('tipo') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tecnología --}}
                            <div class="col-12 col-md-4">
                                <label for="tecnologia" class="form-label fw-bold text-secondary">Tecnología / Color:</label>
                                <input type="text" name="tecnologia" id="tecnologia" class="form-control @error('tecnologia') is-invalid @enderror" value="{{ old('tecnologia') }}" placeholder="Ej: Monocromática, Color">
                                @error('tecnologia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Consumible --}}
                            <div class="col-12 col-md-4">
                                <label for="consumible" class="form-label fw-bold text-secondary">Consumible:</label>
                                <select name="consumible" id="consumible" class="form-select @error('consumible') is-invalid @enderror" required>
                                    <option value="">Seleccione consumible...</option>
                                    @foreach($consumibles as $c)
                                        <option value="{{ $c }}" {{ old('consumible') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('consumible')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Red --}}
                            <div class="col-12 col-md-3">
                                <label for="red" class="form-label fw-bold text-secondary">¿Es de Red?:</label>
                                <select name="red" id="red" class="form-select @error('red') is-invalid @enderror" required>
                                    @foreach($redOpts as $r)
                                        <option value="{{ $r }}" {{ old('red', 'No') == $r ? 'selected' : '' }}>{{ $r }}</option>
                                    @endforeach
                                </select>
                                @error('red')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- IP --}}
                            <div class="col-12 col-md-6">
                                <label for="ip" class="form-label fw-bold text-secondary">Dirección IP:</label>
                                <input type="text" name="ip" id="ip" class="form-control @error('ip') is-invalid @enderror" value="{{ old('ip') }}" placeholder="Ej: 192.168.1.50">
                                <div id="feedbackIp" class="mt-1"></div>
                                @error('ip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Comodato --}}
                            <div class="col-12 col-md-3">
                                <label for="comodato" class="form-label fw-bold text-secondary">¿Es Comodato?:</label>
                                <select name="comodato" id="comodato" class="form-select @error('comodato') is-invalid @enderror" required>
                                    @foreach($comodatoOpts as $c)
                                        <option value="{{ $c }}" {{ old('comodato', 'No') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('comodato')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Descripción --}}
                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-bold text-secondary">Descripción / Observaciones:</label>
                                <textarea name="descripcion" id="descripcion" rows="2" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Área donde se ubica, estado físico, etc.">{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 border" data-bs-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" id="btnGuardarImpresora" class="btn btn-primary px-4 py-2">
                            <i class="fa fa-save me-2"></i>Guardar Impresora
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
                                <i class="fa fa-list-ul me-2"></i>Lista de Impresoras
                            </h5>
                            <span class="badge bg-dark rounded-pill px-3 py-2" id="totalImpresoras">
                                {{ $impresoras->total() }} Registros
                            </span>
                        </div>
                        <div class="input-group search-group">
                            <input type="search" id="busqueda-global" class="form-control bg-light border-0"
                                   placeholder="Buscar impresora...">
                            <span class="input-group-text bg-light border-0">
                                <i class="fa fa-search text-dark"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase thead-impresoras">
                                <tr>
                                    <th class="text-center" style="width:50px;">#</th>
                                    <th class="text-center" style="width:80px;">Editar</th>
                                    <th>Inventario</th>
                                    <th>Marca</th>
                                    <th>Modelo</th>
                                    <th>Tipo</th>
                                    <th>Serie</th>
                                    <th>Tecnología</th>
                                    <th class="text-center">Consumible</th>
                                    <th class="text-center">Red</th>
                                    <th>IP</th>
                                    <th class="text-center">Comodato</th>
                                    <th class="text-center" style="width:100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaImpresoras">
                                @include('control_insumos.impresoras.partials.tabla')
                            </tbody>
                            <tfoot class="table-light text-uppercase tfoot-impresoras">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Editar</th>
                                    <th>Inventario</th>
                                    <th>Marca</th>
                                    <th>Modelo</th>
                                    <th>Tipo</th>
                                    <th>Serie</th>
                                    <th>Tecnología</th>
                                    <th class="text-center">Consumible</th>
                                    <th class="text-center">Red</th>
                                    <th>IP</th>
                                    <th class="text-center">Comodato</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted small" id="infoPaginacion">
                        Mostrando {{ $impresoras->firstItem() ?? 0 }} a {{ $impresoras->lastItem() ?? 0 }} de {{ $impresoras->total() }} registros
                    </div>
                    <nav aria-label="Paginacion de impresoras">
                        <div id="contenedorPaginacion">
                            {{ $impresoras->links('pagination::bootstrap-4') }}
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>

</div>

@vite(['resources/css/control_insumos/impresoras/impresoras.css', 'resources/js/control_insumos/impresoras/impresoras.js'])
@endsection
