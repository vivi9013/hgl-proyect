@extends('layouts.app')

@section('title', 'Editar Impresora - Hospital General')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-print text-dark me-2"></i>Impresoras
            </h1>
            <p class="text-muted mb-0">Modificación de impresora y especificaciones técnicas</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('inicio') }}"><i class="fa fa-dashboard"></i> Panel de Control</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('impresoras.index') }}">Impresoras</a>
                </li>
                <li class="breadcrumb-item active">Edición</li>
            </ol>
        </nav>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- Formulario de Edición --}}
    <div class="row">
        <div class="col-12 col-lg-10">
            <div class="card shadow-sm border-0 rounded-3 bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-semibold text-white">
                        <i class="fa fa-pencil-square-o me-2 text-white"></i>Actualizar Datos de la Impresora: {{ $impresora->inventario }}
                    </h5>
                </div>
                <form action="{{ route('impresoras.update', $impresora->id_impresora) }}" method="POST" autocomplete="off" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body p-4">
                        
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            {{-- Sección 1: Datos de Inventario --}}
                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">1. Información del Activo (Mobiliario)</h6>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="inventario" class="form-label fw-bold text-secondary">No. de Inventario (Solo Lectura)</label>
                                <input type="text" id="inventario" class="form-control bg-light" value="{{ $impresora->inventario }}" readonly>
                                <span class="text-muted small">El número de inventario no se puede modificar.</span>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="marca" class="form-label fw-bold text-secondary">Marca *</label>
                                <input type="text" name="marca" id="marca" class="form-control @error('marca') is-invalid @enderror" 
                                       value="{{ old('marca', $impresora->marca) }}" placeholder="Ej: HP, Epson, Zebra" required>
                                @error('marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold text-secondary">Modelo</label>
                                <input type="text" name="modelo" id="modelo" class="form-control @error('modelo') is-invalid @enderror" 
                                       value="{{ old('modelo', $impresora->modelo) }}" placeholder="Ej: LaserJet Pro M404dn">
                                @error('modelo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="serie" class="form-label fw-bold text-secondary">No. de Serie *</label>
                                <input type="text" name="serie" id="serie" class="form-control @error('serie') is-invalid @enderror" 
                                       value="{{ old('serie', $impresora->serie) }}" placeholder="Número de serie físico" required>
                                @error('serie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Sección 2: Especificaciones Técnicas --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">2. Especificaciones Técnicas de Impresión</h6>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tipo" class="form-label fw-bold text-secondary">Tipo *</label>
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

                            <div class="col-12 col-md-4">
                                <label for="tecnologia" class="form-label fw-bold text-secondary">Tecnología / Color</label>
                                <input type="text" name="tecnologia" id="tecnologia" class="form-control @error('tecnologia') is-invalid @enderror" 
                                       value="{{ old('tecnologia', $impresora->tecnologia) }}" placeholder="Ej: Monocromática, Color">
                                @error('tecnologia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="consumible" class="form-label fw-bold text-secondary">Consumible *</label>
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

                            <div class="col-12 col-md-4">
                                <label for="red" class="form-label fw-bold text-secondary">¿Es de Red? *</label>
                                <select name="red" id="red" class="form-select @error('red') is-invalid @enderror" required>
                                    @foreach($redOpts as $r)
                                        <option value="{{ $r }}" {{ old('red', $impresora->red) == $r ? 'selected' : '' }}>{{ $r }}</option>
                                    @endforeach
                                </select>
                                @error('red')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="ip" class="form-label fw-bold text-secondary">Dirección IP</label>
                                <input type="text" name="ip" id="ip" class="form-control @error('ip') is-invalid @enderror" 
                                       value="{{ old('ip', $impresora->ip) }}" placeholder="Ej: 192.168.1.50" data-excluir="{{ $impresora->id_impresora }}">
                                <div id="feedbackIp" class="mt-1"></div>
                                @error('ip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="comodato" class="form-label fw-bold text-secondary">¿Es Comodato? *</label>
                                <select name="comodato" id="comodato" class="form-select @error('comodato') is-invalid @enderror" required>
                                    @foreach($comodatoOpts as $c)
                                        <option value="{{ $c }}" {{ old('comodato', $impresora->comodato) == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('comodato')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-bold text-secondary">Descripción / Observaciones</label>
                                <textarea name="descripcion" id="descripcion" rows="2" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Área donde se ubica, estado físico, etc.">{{ old('descripcion', $impresora->descripcion) }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                    
                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('impresoras.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                            <i class="fa fa-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" id="btnGuardarImpresora" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" style="border: 1.5px solid #000;">
                            <i class="fa fa-save me-1 text-white"></i> Actualizar Información
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @vite(['resources/css/control_insumos/impresoras/impresoras.css', 'resources/js/control_insumos/impresoras/impresoras.js'])
@endpush
