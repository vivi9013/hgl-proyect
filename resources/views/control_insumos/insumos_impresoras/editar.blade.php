@extends('layouts.app')
@section('title', 'Editar Insumo - Hospital General')
@section('content')

<div class="container-fluid py-4" id="modulo-insumos-impresoras">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-th-large text-dark me-2"></i>Catálogo de Insumos
            </h1>
            <p class="text-muted mb-0">Modificación de datos del insumo</p>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="col-12 col-lg-10">
            <div class="card shadow-sm border-0 rounded-3 bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-semibold text-white">
                        <i class="fa fa-pencil-square-o me-2"></i>
                        Actualizar: {{ $insumo->modelo }}
                    </h5>
                </div>
                <form action="{{ route('insumos_impresoras.update', $insumo->id_insumo_impresora) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="card-body p-4">

                        @if($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">

                            <div class="col-12">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">1. Identificación del Insumo</h6>
                            </div>

                            {{-- Familia --}}
                            <div class="col-12 col-md-6">
                                <label for="familia" class="form-label fw-bold text-secondary">Tipo de insumo *</label>
                                <select name="familia" id="familia" class="form-select @error('familia') is-invalid @enderror" required>
                                    @foreach($familias as $f)
                                        <option value="{{ $f }}" {{ old('familia', $insumo->familia) == $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                                @error('familia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Modelo --}}
                            <div class="col-12 col-md-6">
                                <label for="modelo" class="form-label fw-bold text-secondary">Modelo *</label>
                                <input type="text" name="modelo" id="modelo"
                                       class="form-control @error('modelo') is-invalid @enderror"
                                       value="{{ old('modelo', $insumo->modelo) }}" placeholder="Ej: CE285A, 664" required>
                                @error('modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Color --}}
                            <div class="col-12 col-md-4">
                                <label for="color" class="form-label fw-bold text-secondary">Color *</label>
                                <select name="color" id="color" class="form-select @error('color') is-invalid @enderror" required>
                                    @foreach($colores as $c)
                                        <option value="{{ $c }}" {{ old('color', $insumo->color) == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="fw-bold border-bottom pb-2 text-secondary">2. Rendimiento y Compatibilidad</h6>
                            </div>

                            {{-- Hojas --}}
                            <div class="col-12 col-md-4">
                                <label for="hojas_uso_total" class="form-label fw-bold text-secondary">Rendimiento (hojas)</label>
                                <input type="number" name="hojas_uso_total" id="hojas_uso_total" min="1"
                                       class="form-control @error('hojas_uso_total') is-invalid @enderror"
                                       value="{{ old('hojas_uso_total', $insumo->hojas_uso_total) }}" placeholder="Ej: 1500">
                                @error('hojas_uso_total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tiempo uso --}}
                            <div class="col-12 col-md-4">
                                <label for="tiempo_uso" class="form-label fw-bold text-secondary">Tiempo de uso estimado</label>
                                <input type="text" name="tiempo_uso" id="tiempo_uso"
                                       class="form-control @error('tiempo_uso') is-invalid @enderror"
                                       value="{{ old('tiempo_uso', $insumo->tiempo_uso) }}" placeholder="Ej: 2 semanas, 1 mes">
                                @error('tiempo_uso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Stock (editable) --}}
                            <div class="col-12 col-md-4">
                                <label for="stock" class="form-label fw-bold text-secondary">Stock actual</label>
                                <input type="number" name="stock" id="stock" min="0"
                                       class="form-control @error('stock') is-invalid @enderror"
                                       value="{{ old('stock', $insumo->stock) }}">
                                <span class="text-muted small">Ajusta la existencia física directamente.</span>
                                @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Modelos compatibles --}}
                            <div class="col-12">
                                <label for="modelos_compatibles" class="form-label fw-bold text-secondary">Compatibilidad (modelos de impresora)</label>
                                <input type="text" name="modelos_compatibles" id="modelos_compatibles"
                                       class="form-control @error('modelos_compatibles') is-invalid @enderror"
                                       value="{{ old('modelos_compatibles', $insumo->modelos_compatibles) }}"
                                       placeholder="Ej: HP LaserJet Pro M404dn, M402n">
                                @error('modelos_compatibles') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>



                        </div>
                    </div>

                    <div class="card-footer bg-light p-3 d-flex justify-content-between">
                        <a href="{{ route('insumos_impresoras.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                            <i class="fa fa-arrow-left me-1"></i> Regresar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                            <i class="fa fa-save me-1 text-white"></i> Actualizar Insumo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    @vite(['resources/css/control_insumos/insumos_impresoras/insumos_impresoras.css',
            'resources/js/control_insumos/insumos_impresoras/insumos_impresoras.js'])
@endpush
@endsection
