@extends('layouts.app')

@section('title', 'Mis Datos Personales - Hospital General')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado del Módulo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="bi bi-person-arms-up"></i> Mis Datos Personales
            </h1>
            <p class="text-muted mb-0">Visualiza y mantén actualizada tu información personal</p>
        </div>
    </div>

    <!-- Alertas de Éxito / Errores del Servidor -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle me-2 fs-4"></i>
                <div>
                    <strong>¡Operación Satisfactoria!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-triangle me-2 fs-4"></i>
                <div>
                    <strong>¡Atención!</strong> Por favor corrige los errores señalados en el formulario.
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <!-- Card Principal -->
            <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
                <div class="card-body p-4">
                    <form id="formMisDatos" action="{{ route('mis_datos.update') }}" method="POST" class="needs-validation" novalidate autocomplete="off">
                        @csrf

                        <!-- Sección 1: Datos de Identificación -->
                        <div class="mb-4">
                            <h5 class="section-title text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-person-rolodex"></i> Información Personal y de Contacto
                            </h5>
                            
                            <div class="row g-3">
                                <!-- Nombre -->
                                <div class="col-12 col-md-4">
                                    <label for="nombre" class="form-label fw-semibold text-secondary">Nombre(s):</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-user-o"></i></span>
                                        <input type="text" name="nombre" id="nombre" 
                                               class="form-control border-start-0 @error('nombre') is-invalid @enderror" 
                                               value="{{ old('nombre', $persona->nombre) }}" 
                                               placeholder="Ingresa tu nombre" required>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Apellido Paterno -->
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="ap_paterno" class="form-label fw-semibold text-secondary">Apellido Paterno:</label>
                                    <input type="text" name="ap_paterno" id="ap_paterno" 
                                           class="form-control @error('ap_paterno') is-invalid @enderror" 
                                           value="{{ old('ap_paterno', $persona->ap_paterno) }}" 
                                           placeholder="Primer apellido" required>
                                    @error('ap_paterno')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Apellido Materno -->
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="ap_materno" class="form-label fw-semibold text-secondary">Apellido Materno:</label>
                                    <input type="text" name="ap_materno" id="ap_materno" 
                                           class="form-control @error('ap_materno') is-invalid @enderror" 
                                           value="{{ old('ap_materno', $persona->ap_materno) }}" 
                                           placeholder="Segundo apellido" required>
                                    @error('ap_materno')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="fecha_nac" class="form-label fw-semibold text-secondary">Fecha de Nacimiento:</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-calendar"></i></span>
                                        <input type="date" name="fecha_nac" id="fecha_nac" 
                                               class="form-control border-start-0 @error('fecha_nac') is-invalid @enderror" 
                                               value="{{ old('fecha_nac', $persona->fecha_nac) }}" required>
                                        @error('fecha_nac')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Sexo -->
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="sexo" class="form-label fw-semibold text-secondary">Sexo:</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-venus-mars"></i></span>
                                        <select name="sexo" id="sexo" class="form-select border-start-0 @error('sexo') is-invalid @enderror" required>
                                            <option value="" disabled>Selecciona tu sexo</option>
                                            <option value="M" {{ old('sexo', $persona->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                                            <option value="F" {{ old('sexo', $persona->sexo) == 'F' ? 'selected' : '' }}>Femenino</option>
                                        </select>
                                        @error('sexo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Estado Civil -->
                                <div class="col-12 col-sm-6 col-md-4">
                                    <label for="ecivil" class="form-label fw-semibold text-secondary">Estado Civil:</label>
                                    <select name="ecivil" id="ecivil" class="form-select @error('ecivil') is-invalid @enderror" required>
                                        <option value="" disabled>Selecciona tu estado civil</option>
                                        <option value="Soltero(a)" {{ old('ecivil', $persona->ecivil) == 'Soltero(a)' ? 'selected' : '' }}>Soltero(a)</option>
                                        <option value="Casado(a)" {{ old('ecivil', $persona->ecivil) == 'Casado(a)' ? 'selected' : '' }}>Casado(a)</option>
                                        <option value="Viudo(a)" {{ old('ecivil', $persona->ecivil) == 'Viudo(a)' ? 'selected' : '' }}>Viudo(a)</option>
                                        <option value="Divorciado(a)" {{ old('ecivil', $persona->ecivil) == 'Divorciado(a)' ? 'selected' : '' }}>Divorciado(a)</option>
                                        <option value="Union Libre" {{ old('ecivil', $persona->ecivil) == 'Union Libre' ? 'selected' : '' }}>Union Libre</option>
                                        <option value="No especificado" {{ old('ecivil', $persona->ecivil) == 'No especificado' ? 'selected' : '' }}>No especificado</option>
                                    </select>
                                    @error('ecivil')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Documentos y Contacto Oficial -->
                        <div class="mb-4">
                            <h5 class="section-title text-primary border-bottom pb-2 mb-3">
                                <i class="fa fa-address-card-o me-2"></i>Documentación Oficial y Contacto
                            </h5>

                            <div class="row g-3">
                                <!-- Teléfono -->
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="tel" class="form-label fw-semibold text-secondary">Teléfono:</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-phone"></i></span>
                                        <input type="text" name="tel" id="tel" 
                                               class="form-control border-start-0 @error('tel') is-invalid @enderror" 
                                               value="{{ old('tel', $persona->telefono) }}" 
                                               placeholder="Casa o Celular" required>
                                        @error('tel')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- RFC -->
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="rfc" class="form-label fw-semibold text-secondary">RFC:</label>
                                    <input type="text" name="rfc" id="rfc" 
                                           class="form-control text-uppercase @error('rfc') is-invalid @enderror" 
                                           value="{{ old('rfc', $persona->rfc) }}" 
                                           maxlength="13" placeholder="RFC de 10 a 13 caracteres" required>
                                    @error('rfc')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- CURP -->
                                <div class="col-12 col-md-3">
                                    <label for="curp" class="form-label fw-semibold text-secondary">CURP:</label>
                                    <input type="text" name="curp" id="curp" 
                                           class="form-control text-uppercase @error('curp') is-invalid @enderror" 
                                           value="{{ old('curp', $persona->curp) }}" 
                                           maxlength="18" placeholder="CURP de 18 caracteres" required>
                                    @error('curp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-12 col-md-3">
                                    <label for="email" class="form-label fw-semibold text-secondary">Email:</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa fa-envelope-o"></i></span>
                                        <input type="email" name="email" id="email" 
                                               class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                               value="{{ old('email', $persona->e_mail) }}" 
                                               placeholder="correo@ejemplo.com" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Domicilio -->
                        <div class="mb-4">
                            <h5 class="section-title text-primary border-bottom pb-2 mb-3">
                                <i class="fa fa-map-marker me-2"></i>Domicilio Particular
                            </h5>

                            <div class="row g-3">
                                <!-- Colonia -->
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="colonia" class="form-label fw-semibold text-secondary">Colonia:</label>
                                    <input type="text" name="colonia" id="colonia" 
                                           class="form-control @error('colonia') is-invalid @enderror" 
                                           value="{{ old('colonia', $persona->colonia) }}" 
                                           placeholder="Ingresa tu colonia" required>
                                    @error('colonia')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Calle -->
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="calle" class="form-label fw-semibold text-secondary">Calle:</label>
                                    <input type="text" name="calle" id="calle" 
                                           class="form-control @error('calle') is-invalid @enderror" 
                                           value="{{ old('calle', $persona->calle) }}" 
                                           placeholder="Nombre de la calle" required>
                                    @error('calle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Número -->
                                <div class="col-12 col-sm-4 col-md-2">
                                    <label for="numero" class="form-label fw-semibold text-secondary">Número:</label>
                                    <input type="text" name="numero" id="numero" 
                                           class="form-control @error('numero') is-invalid @enderror" 
                                           value="{{ old('numero', $persona->numero) }}" 
                                           placeholder="Exterior/Interior" required>
                                    @error('numero')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Estado -->
                                <div class="col-12 col-sm-4 col-md-2">
                                    <label for="estado" class="form-label fw-semibold text-secondary">Estado:</label>
                                    <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                        <option value="" disabled>Selecciona tu estado</option>
                                        @foreach($estados as $edo)
                                            <option value="{{ $edo }}" {{ old('estado', $persona->estado) == $edo ? 'selected' : '' }}>
                                                {{ $edo }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Municipio -->
                                <div class="col-12 col-sm-4 col-md-2">
                                    <label for="municipio" class="form-label fw-semibold text-secondary">Municipio:</label>
                                    <input type="text" name="municipio" id="municipio" 
                                           class="form-control @error('municipio') is-invalid @enderror" 
                                           value="{{ old('municipio', $persona->municipio) }}" 
                                           placeholder="Municipio o Delegación" required>
                                    @error('municipio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <a href="{{ route('inicio') }}" class="btn btn-light rounded-pill px-4">
                                <i class="fa fa-arrow-left me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnActualizar">
                                <i class="fa fa-save me-2"></i>Actualizar Información
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@vite(['resources/css/mis_datos/mis_datos.css', 'resources/js/mis_datos/mis_datos.js'])
@endsection
