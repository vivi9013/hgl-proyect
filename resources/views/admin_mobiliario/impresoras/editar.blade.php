@extends('layouts.app')
@section('title', 'Editar Impresora - Hospital General')
@section('content')

<div class="container-fluid py-4" id="modulo-impresoras">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-print me-2"></i>Editar Impresora
            </h1>
            <p class="text-muted mb-0">Modificar especificaciones de la impresora registrada</p>
        </div>
        <div>
            <a href="{{ route('impresoras.index') }}" class="btn btn-light border rounded-pill px-4 py-2">
                <i class="fa fa-arrow-left me-2"></i>Volver al Catálogo
            </a>
        </div>
    </div>

    {{-- Formulario principal --}}
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card p-4 shadow-sm border-0">
                <form action="{{ route('impresoras.update', $impresora->id_impresora) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        {{-- Inventario (Solo Lectura) --}}
                        <div class="col-12 col-md-6">
                            <label for="inventario" class="form-label fw-bold text-secondary">No. de Inventario (Mobiliario):</label>
                            <input type="text" id="inventario" class="form-control bg-light" value="{{ $impresora->inventario }}" readonly>
                            <span class="text-muted small">El número de inventario no se puede modificar.</span>
                        </div>

                        {{-- Marca --}}
                        <div class="col-12 col-md-6">
                            <label for="marca" class="form-label fw-bold text-secondary">Marca:</label>
                            <input type="text" name="marca" id="marca" class="form-control @error('marca') is-invalid @enderror" value="{{ old('marca', $impresora->marca) }}" placeholder="Ej: HP, Epson, Zebra" required>
                            @error('marca')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Modelo --}}
                        <div class="col-12 col-md-6">
                            <label for="modelo" class="form-label fw-bold text-secondary">Modelo:</label>
                            <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror" value="{{ old('modelo', $impresora->modelo) }}" placeholder="Ej: LaserJet Pro M404dn">
                            @error('modelo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Serie --}}
                        <div class="col-12 col-md-6">
                            <label for="serie" class="form-label fw-bold text-secondary">No. de Serie:</label>
                            <input type="text" name="serie" id="serie" class="form-control @error('serie') is-invalid @enderror" value="{{ old('serie', $impresora->serie) }}" placeholder="Número de serie físico" required>
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
                                    <option value="{{ $t }}" {{ old('tipo', $impresora->tipo) == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tecnología --}}
                        <div class="col-12 col-md-4">
                            <label for="tecnologia" class="form-label fw-bold text-secondary">Tecnología / Color:</label>
                            <input type="text" name="tecnologia" id="tecnologia" class="form-control @error('tecnologia') is-invalid @enderror" value="{{ old('tecnologia', $impresora->tecnologia) }}" placeholder="Ej: Monocromática, Color">
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
                                    <option value="{{ $c }}" {{ old('consumible', $impresora->consumible) == $c ? 'selected' : '' }}>{{ $c }}</option>
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
                                    <option value="{{ $r }}" {{ old('red', $impresora->red) == $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                            @error('red')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- IP --}}
                        <div class="col-12 col-md-6">
                            <label for="ip" class="form-label fw-bold text-secondary">Dirección IP:</label>
                            <input type="text" name="ip" id="ip" class="form-control @error('ip') is-invalid @enderror" value="{{ old('ip', $impresora->ip) }}" placeholder="Ej: 192.168.1.50" data-excluir="{{ $impresora->id_impresora }}">
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
                                    <option value="{{ $c }}" {{ old('comodato', $impresora->comodato) == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                            @error('comodato')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-bold text-secondary">Descripción / Observaciones:</label>
                            <textarea name="descripcion" id="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Área donde se ubica, estado físico, etc.">{{ old('descripcion', $impresora->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('impresoras.index') }}" class="btn btn-light px-4 py-2 border">
                            <i class="fa fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" id="btnGuardarImpresora" class="btn btn-primary px-4 py-2">
                            <i class="fa fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Panel lateral: Información y Estatus --}}
        <div class="col-12 col-lg-4">
            <div class="card p-4 shadow-sm border-0 h-100 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="fa fa-info-circle me-2"></i>Información Adicional
                    </h5>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2">
                            <strong>Registrado el:</strong> {{ \Carbon\Carbon::parse($impresora->fecha)->format('d/m/Y') }} a las {{ $impresora->hora }}
                        </li>
                        <li class="mb-2">
                            <strong>Usuario auditor:</strong> <span class="badge bg-secondary">{{ $impresora->usuario }}</span>
                        </li>
                        <li class="mb-2">
                            <strong>Estatus actual:</strong>
                            @if($impresora->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </li>
                    </ul>
                </div>

                {{-- Formulario para cambiar status --}}
                <div class="border-top pt-3">
                    <form id="formToggleEstado" action="{{ route('impresoras.status', $impresora->id_impresora) }}" method="POST"
                          data-marca-modelo="{{ $impresora->marca }} {{ $impresora->modelo }}" data-activo="{{ $impresora->activo }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-{{ $impresora->activo ? 'danger' : 'success' }} rounded-pill px-4 py-2 w-100">
                            <i class="fa {{ $impresora->activo ? 'fa-ban' : 'fa-check' }} me-2"></i>
                            {{ $impresora->activo ? 'Desactivar Impresora' : 'Activar Impresora' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@vite(['resources/css/control_insumos/impresoras/impresoras.css', 'resources/js/control_insumos/impresoras/impresoras.js'])
@endsection
