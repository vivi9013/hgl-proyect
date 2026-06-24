@extends('layouts.app')

@section('title', 'Editar Persona - Hospital General de Linares')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado y Breadcrumb --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa fa-pencil-square-o text-primary me-2"></i>Actualización de Persona
            </h1>
            <p class="text-muted mb-0">Modifique los datos personales, identificación y domicilio de la persona</p>
        </div>
    </div>

    <form id="formEditarPersona" action="{{ route('personas.update', $persona->id) }}" method="POST" autocomplete="off">
        @csrf
        @method('PUT')

        {{-- Sección: Datos Personales --}}
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fa fa-user text-secondary me-2"></i>Datos Personales
                </h5>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="nombre" class="form-label fw-semibold small text-secondary">Nombre(s) <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre"
                               class="form-control shadow-sm @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $persona->nombre) }}" required>
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="ap_paterno" class="form-label fw-semibold small text-secondary">Apellido Paterno <span class="text-danger">*</span></label>
                        <input type="text" name="ap_paterno" id="ap_paterno"
                               class="form-control shadow-sm @error('ap_paterno') is-invalid @enderror"
                               value="{{ old('ap_paterno', $persona->ap_paterno) }}" required>
                        @error('ap_paterno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="ap_materno" class="form-label fw-semibold small text-secondary">Apellido Materno <span class="text-danger">*</span></label>
                        <input type="text" name="ap_materno" id="ap_materno"
                               class="form-control shadow-sm @error('ap_materno') is-invalid @enderror"
                               value="{{ old('ap_materno', $persona->ap_materno) }}" required>
                        @error('ap_materno') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="fecha_nac" class="form-label fw-semibold small text-secondary">Fecha de Nacimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_nac" id="fecha_nac"
                               class="form-control shadow-sm @error('fecha_nac') is-invalid @enderror"
                               value="{{ old('fecha_nac', $persona->fecha_nac) }}" required>
                        @error('fecha_nac') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="sexo" class="form-label fw-semibold small text-secondary">Sexo <span class="text-danger">*</span></label>
                        <select name="sexo" id="sexo" class="form-select shadow-sm @error('sexo') is-invalid @enderror" required>
                            <option value="M" {{ old('sexo', $persona->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('sexo', $persona->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                        </select>
                        @error('sexo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="ecivil" class="form-label fw-semibold small text-secondary">Estado Civil <span class="text-danger">*</span></label>
                        <select name="ecivil" id="ecivil" class="form-select shadow-sm @error('ecivil') is-invalid @enderror" required>
                            @foreach(['Soltero(a)','Casado(a)','Viudo(a)','Divorciado(a)','Union Libre','No especificado'] as $ec)
                                <option value="{{ $ec }}" {{ old('ecivil', $persona->ecivil) === $ec ? 'selected' : '' }}>{{ $ec }}</option>
                            @endforeach
                        </select>
                        @error('ecivil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="telefono" class="form-label fw-semibold small text-secondary">Teléfono <span class="text-danger">*</span></label>
                        <input type="text" name="telefono" id="telefono"
                               class="form-control shadow-sm @error('telefono') is-invalid @enderror"
                               value="{{ old('telefono', $persona->telefono) }}" required>
                        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección: Identificación --}}
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fa fa-id-card-o text-secondary me-2"></i>Identificación
                </h5>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="rfc" class="form-label fw-semibold small text-secondary">RFC <span class="text-danger">*</span></label>
                        <input type="text" name="rfc" id="rfc"
                               class="form-control shadow-sm text-uppercase @error('rfc') is-invalid @enderror"
                               value="{{ old('rfc', $persona->rfc) }}" maxlength="13" required>
                        @error('rfc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="curp" class="form-label fw-semibold small text-secondary">CURP <span class="text-danger">*</span></label>
                        <input type="text" name="curp" id="curp"
                               class="form-control shadow-sm text-uppercase @error('curp') is-invalid @enderror"
                               value="{{ old('curp', $persona->curp) }}" maxlength="18" required>
                        @error('curp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="e_mail" class="form-label fw-semibold small text-secondary">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" name="e_mail" id="e_mail"
                               class="form-control shadow-sm @error('e_mail') is-invalid @enderror"
                               value="{{ old('e_mail', $persona->e_mail) }}" required>
                        @error('e_mail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección: Domicilio --}}
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="card-title mb-0 fw-bold text-dark">
                    <i class="fa fa-map-marker text-secondary me-2"></i>Domicilio
                </h5>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label for="colonia" class="form-label fw-semibold small text-secondary">Colonia <span class="text-danger">*</span></label>
                        <input type="text" name="colonia" id="colonia"
                               class="form-control shadow-sm @error('colonia') is-invalid @enderror"
                               value="{{ old('colonia', $persona->colonia) }}" required>
                        @error('colonia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="calle" class="form-label fw-semibold small text-secondary">Calle <span class="text-danger">*</span></label>
                        <input type="text" name="calle" id="calle"
                               class="form-control shadow-sm @error('calle') is-invalid @enderror"
                               value="{{ old('calle', $persona->calle) }}" required>
                        @error('calle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-1">
                        <label for="numero" class="form-label fw-semibold small text-secondary">No. <span class="text-danger">*</span></label>
                        <input type="text" name="numero" id="numero"
                               class="form-control shadow-sm @error('numero') is-invalid @enderror"
                               value="{{ old('numero', $persona->numero) }}" required>
                        @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="estado_edit" class="form-label fw-semibold small text-secondary">Estado <span class="text-danger">*</span></label>
                        <select name="estado" id="estado_edit" class="form-select shadow-sm" required>
                            @foreach($estados as $est)
                                <option value="{{ $est }}" {{ old('estado', $persona->estado) === $est ? 'selected' : '' }}>{{ $est }}</option>
                            @endforeach
                        </select>
                        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="municipio_edit" class="form-label fw-semibold small text-secondary">Municipio <span class="text-danger">*</span></label>
                        <select name="municipio" id="municipio_edit" class="form-select shadow-sm" required>
                            @foreach($municipios as $mun)
                                <option value="{{ $mun }}" {{ old('municipio', $persona->municipio) === $mun ? 'selected' : '' }}>{{ $mun }}</option>
                            @endforeach
                        </select>
                        @error('municipio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer de acciones --}}
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('personas.index') }}" class="btn btn-light py-2 rounded-pill shadow-sm">
                <i class="fa fa-times me-2"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary py-2 rounded-pill shadow-sm">
                <i class="fa fa-save me-2"></i>Actualizar Información
            </button>
        </div>

    </form>
</div>

@vite(['resources/css/personas/personas.css', 'resources/js/personas/personas_edit.js'])
@endsection
