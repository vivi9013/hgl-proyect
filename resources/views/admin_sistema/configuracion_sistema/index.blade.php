@extends('layouts.app')

@section('title', 'Configuración General - Hospital General')

@section('content')

{{-- Alertas de sesión por sección --}}
@foreach(['exito_institucion', 'exito_seguridad', 'exito_encabezado'] as $key)
    @if(session($key))
        <div id="alerta-{{ $key }}" data-message="{{ session($key) }}" style="display:none;"></div>
    @endif
@endforeach

<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-cog text-primary me-2"></i>Configuración General del Sistema
            </h1>
            <p class="text-muted mb-0">Parámetros globales de operación, seguridad e identidad del sistema</p>
        </div>
    </div>

    {{-- SECCIÓN 1: Información de la Institución --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-hospital-o text-secondary me-2"></i>Información de la Institución
                    </h5>
                    <hr class="mt-3 mb-0" style="border-color: #e0e0e0;">
                </div>

                <form action="{{ route('configuracion_sistema.update.institucion') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="card-body px-4 py-4">
                        <div class="row g-3">

                            <div class="col-12 col-md-4">
                                <label for="institucion" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-building-o me-1 text-dark"></i> Institución
                                </label>
                                <input type="text" name="institucion" id="institucion"
                                       class="form-control @error('institucion') is-invalid @enderror"
                                       value="{{ old('institucion', $config->institucion) }}"
                                       placeholder="Nombre completo de la institución" required>
                                @error('institucion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="director" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user-md me-1 text-dark"></i> Director
                                </label>
                                <input type="text" name="director" id="director"
                                       class="form-control @error('director') is-invalid @enderror"
                                       value="{{ old('director', $config->director) }}"
                                       placeholder="Nombre completo del director" required>
                                @error('director')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="administrador" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-user-circle-o me-1 text-dark"></i> Gerente / Administrador
                                </label>
                                <input type="text" name="administrador" id="administrador"
                                       class="form-control @error('administrador') is-invalid @enderror"
                                       value="{{ old('administrador', $config->administrador) }}"
                                       placeholder="Nombre completo del administrador" required>
                                @error('administrador')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 px-4 border-top d-flex justify-content-end">
                        <button type="submit" id="btnInstitucion" class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Guardar Institución
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SECCIONES 2 y 3: Seguridad + Encabezado --}}
    <div class="row g-4">

        {{-- SECCIÓN 2: Seguridad --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-shield text-secondary me-2"></i>Seguridad
                    </h5>
                    <hr class="mt-3 mb-0" style="border-color: #e0e0e0;">
                </div>

                <form action="{{ route('configuracion_sistema.update.seguridad') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="card-body px-4 py-4">
                        <div class="row g-3">

                            <div class="col-12 col-sm-6">
                                <label for="sesion" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-clock-o me-1 text-dark"></i> Duración de sesión
                                    <small class="text-muted fw-normal">(minutos)</small>
                                </label>
                                <input type="number" name="sesion" id="sesion"
                                       class="form-control @error('sesion') is-invalid @enderror"
                                       value="{{ old('sesion', $config->sesion) }}"
                                       min="1" max="1440" required>
                                @error('sesion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="contra" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-key me-1 text-dark"></i> Contraseña inicial
                                </label>
                                <div class="input-group">
                                    <input type="text" name="contra" id="contra"
                                           class="form-control @error('contra') is-invalid @enderror"
                                           value="{{ old('contra', $config->contra) }}"
                                           placeholder="Contraseña de reset" required>
                                    <button type="button" class="btn btn-outline-secondary border" id="btnToggleContra" title="Mostrar/ocultar">
                                        <i class="fa fa-eye" id="iconoContra"></i>
                                    </button>
                                </div>
                                @error('contra')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 px-4 border-top d-flex justify-content-end">
                        <button type="submit" id="btnSeguridad" class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
                            <i class="fa fa-save me-2"></i>Guardar Seguridad
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- SECCIÓN 3: Encabezado de Reportes --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-picture-o text-secondary me-2"></i>Encabezado de Reportes
                    </h5>
                    <hr class="mt-3 mb-0" style="border-color: #e0e0e0;">
                </div>

                <form action="{{ route('configuracion_sistema.update.encabezado') }}" method="POST"
                      enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <div class="card-body px-4 py-4">
                        <div class="row g-3 align-items-center">

                            {{-- Vista previa actual --}}
                            <div class="col-12 col-md-6 text-center">
                                <p class="text-muted small fw-bold mb-2">Encabezado actual:</p>
                                @if($tieneEncabezado)
                                    <img id="imgEncabezadoActual"
                                         src="{{ asset('images/encabezado.jpg') }}?v={{ $config->fecha }}"
                                         class="img-fluid img-thumbnail" style="max-height: 120px;" alt="Encabezado actual">
                                @else
                                    <div class="border rounded p-4 text-muted bg-light">
                                        <i class="fa fa-image fa-2x mb-2 d-block"></i>
                                        Sin encabezado configurado
                                    </div>
                                @endif
                            </div>

                            {{-- Selector de nuevo archivo --}}
                            <div class="col-12 col-md-6">
                                <p class="text-muted small fw-bold mb-2">Vista previa del nuevo:</p>
                                <img id="previewNuevoEncabezado" src="#" alt="Preview"
                                     class="img-fluid img-thumbnail mb-2" style="max-height: 120px; display: none;">
                                <label for="encabezado" class="form-label fw-bold text-secondary">
                                    <i class="fa fa-upload me-1 text-dark"></i> Seleccionar imagen (.jpg)
                                </label>
                                <input type="file" name="encabezado" id="encabezado"
                                       class="form-control @error('encabezado') is-invalid @enderror"
                                       accept=".jpg,image/jpeg">
                                <small class="text-muted">Máximo 50 MB. Solo formato JPG.</small>
                                @error('encabezado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 px-4 border-top d-flex justify-content-end">
                        <button type="submit" id="btnEncabezado" class="btn btn-primary py-2 px-4 rounded-pill shadow-sm">
                            <i class="fa fa-upload me-2"></i>Subir Encabezado
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@vite([
    'resources/css/configuracion_sistema/configuracion.css',
    'resources/js/configuracion_sistema/configuracion.js'
])
@endsection