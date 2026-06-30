@extends('layouts.app')

@section('title', 'Editar Área de Insumo')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="mb-4">
        <h1 class="h3 mb-0 fw-bold">
            <i class="fa fa-pencil text-primary me-2"></i> Reubicación de Insumo
        </h1>
        <p class="text-muted mb-0">Reasigne el insumo seleccionando a otra Área de Almacen activa.</p>
    </div>

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    <div class="justify-content-center">
            <div class=" card-premium">
                <div class="card-premium-header">
                    <h5 class="mb-2 fw-bold text-black">
                        <i class="fa fa-edit me-1"></i> Detalles de Asignación
                    </h5>
                </div>
                <div class="card-premium-body">
                    <form action="{{ route('insumos_area.update', $insumoArea->id_insumo_area) }}" method="POST" id="formEditarAreaInsumo">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Clave --}}
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold text-muted small">Clave:</label>
                                <div class="form-control bg-light fw-bold text-dark">{{ $insumoArea->insumo->clave ?? '—' }}</div>
                            </div>

                            {{-- Tipo --}}
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold text-muted small">Tipo de Insumo:</label>
                                <div class="form-control bg-light">{{ $insumoArea->insumo->tipo ?? '—' }}</div>
                            </div>

                            {{-- Descripción --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-muted small">Descripción:</label>
                                <textarea class="form-control bg-light" rows="2" readonly>{{ $insumoArea->insumo->descripcion ?? '—' }}</textarea>
                            </div>

                            {{-- Stock y Fondo Fijo --}}
                            <div class="col-12 col-sm-6">
                                <label for="stock" class="form-label fw-bold">Stock: <span class="text-danger">*</span></label>
                                <input type="number" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $insumoArea->stock) }}" required min="0">
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 col-sm-6">
                                <label for="fondo_fijo" class="form-label fw-bold">Fondo Fijo: <span class="text-danger">*</span></label>
                                <input type="number" name="fondo_fijo" id="fondo_fijo" class="form-control @error('fondo_fijo') is-invalid @enderror" value="{{ old('fondo_fijo', $insumoArea->fondo_fijo) }}" required min="1">
                                @error('fondo_fijo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Selección de Nueva Área --}}
                            <div class="col-12">
                                <label for="id_area_almacen" class="form-label fw-bold">
                                    <i class="fa fa-building text-secondary me-1"></i> Nueva Área de Almacén: <span class="text-danger">*</span>
                                </label>
                                <select name="id_area_almacen" id="id_area_almacen" class="form-select @error('id_area_almacen') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar Área --</option>
                                    @foreach($areasAlmacen as $area)
                                        <option value="{{ $area->id_area_almacen }}" {{ old('id_area_almacen', $insumoArea->id_area_almacen) == $area->id_area_almacen ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_area_almacen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('insumos_area.index') }}" class="btn btn-outline-secondary px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    </div>
</div>
@endsection
