@extends('layouts.app')

@section('title', 'Cambiar Fotografía - Hospital System')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/layouts/CambiarFoto/cambiar_foto.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">

    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold"><i class="bi bi-image"></i> Cambiar Fotografía</h1>
            <p class="text-muted mb-0 subtitle">Carga y actualización de la fotografía de perfil del trabajador</p>
        </div>
    </div>


    <hr class="divider">
    <!-- Tarjeta principal -->
    <div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card">

                <!-- Header -->
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title fw-bold mb-0"><i class="bi bi-images"></i> Modificar Fotografía Actual</h5>
                    </div>
                    <span class="badge format-badge">¡Revisa los formatos disponibles!</span>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <div class="row g-4 align-items-center">

                        <!-- Panel izquierdo: foto actual -->
                        <div class="col-md-4 text-center photo-panel">
                            <h6 class="section-title">Fotografía Actual</h6>

                            @php
                                $hasPhoto = false;
                                if ($user && $user->id_persona) {
                                    $hasPhoto = \Illuminate\Support\Facades\Storage::disk('fotos')->exists($user->id_persona . '.jpg');
                                }
                            @endphp

                            <div class="avatar-preview-container mb-3 {{ $hasPhoto ? '' : 'pulse-avatar' }}">
                                @if($hasPhoto)
                                    <img src="{{ $user->foto_url }}" alt="Imagen de Perfil" id="current-photo">
                                @else
                                    <div class="no-photo">
                                        <span class="fw-bold fs-1">{{ $user->initials }}</span>
                                    </div>
                                @endif
                            </div>

                            <p class="user-name">
                                {{ $user->persona ? $user->persona->nombre . ' ' . $user->persona->ap_paterno : $user->nombre_usuario }}
                            </p>
                            <small class="user-id">
                                ID Persona: <span class="fw-bold text-primary">{{ $user->id_persona }}</span>
                            </small>
                        </div>

                        <!-- Panel derecho: formulario -->
                        <div class="col-md-8">
                            <div class="ps-md-4">
                                <h6 class="section-title">Subir Nueva Imagen</h6>

                                <form action="{{ route('cambiar_foto.store') }}" method="POST" enctype="multipart/form-data" id="photo-upload-form">
                                    @csrf
                                    <input type="file" name="archivo-a-subir" id="file-upload-input" accept="image/*" hidden>

                                    <!-- Drop Zone -->
                                    <div class="drop-zone mb-4" id="upload-drop-zone">
                                        <i class="bi bi-cloud-arrow-up-fill mb-2 d-inline-block"></i>
                                        <h5 class="fw-bold mb-1">Arrastra tu fotografía aquí</h5>
                                        <p class="text-muted mb-3">O si lo prefieres, haz clic para buscar en tus archivos</p>
                                        <button type="button" class="btn btn-outline-primary btn-sm px-4 fw-bold" id="browse-files-btn">
                                            Seleccionar archivo
                                        </button>

                                        <!-- Preview -->
                                        <div class="preview-thumbnail-container" id="preview-container">
                                            <hr class="my-3">
                                            <div class="preview-box">
                                                <img src="" id="preview-image" alt="Preview">
                                                <div class="text-start">
                                                    <p class="file-name" id="selected-file-name"></p>
                                                    <small class="file-size" id="selected-file-size"></small>
                                                </div>
                                                <button type="button" class="btn btn-link text-danger ms-auto p-0" id="remove-file-btn">
                                                    <i class="bi bi-trash-fill fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Requisitos -->
                                    <div class="requirements mb-4">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-info-circle text-primary me-2"></i>
                                            <div>
                                                <span class="fw-bold d-block mb-1">Requisitos de la imagen:</span>
                                                <span class="d-block">• Se aceptan todos los formatos: <strong>JPG, PNG, WEBP, GIF, BMP</strong></span>
                                                <span class="d-block">• El archivo se convertirá automáticamente a <strong>JPG</strong> al guardarse</span>
                                                <span class="d-block">• El tamaño máximo del archivo no debe exceder <strong>5 MB</strong> (5,120 KB)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botones -->
                                    <div class="d-grid d-md-flex gap-2 justify-content-md-end">
                                        <button type="button" id="cancel-btn" onclick="window.location='{{ route('inicio') }}'">
                                            <i class="bi bi-arrow-left"></i> Cancelar
                                        </button>

                                        <button type="submit" id="submit-upload-btn" disabled>
                                            <span id="btn-default-state">
                                                <i class="bi bi-cloud-upload me-1"></i> Guardar Cambios
                                            </span>
                                            <span id="btn-loading-state" hidden>
                                                <span class="spinner-border spinner-border-sm me-2"></span>
                                                Subiendo imagen...
                                            </span>
                                            </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <hr class="divider">
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone       = document.getElementById('upload-drop-zone');
    const fileInput      = document.getElementById('file-upload-input');
    const browseBtn      = document.getElementById('browse-files-btn');
    const submitBtn      = document.getElementById('submit-upload-btn');
    const previewContainer = document.getElementById('preview-container');
    const previewImage   = document.getElementById('preview-image');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const removeFileBtn  = document.getElementById('remove-file-btn');

    browseBtn.addEventListener('click', (e) => { e.stopPropagation(); fileInput.click(); });
    dropZone.addEventListener('click', () => { fileInput.click(); });

    ['dragenter', 'dragover'].forEach(ev => {
        dropZone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.add('dragover'); }, false);
    });
    ['dragleave', 'drop'].forEach(ev => {
        dropZone.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('dragover'); }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length) { fileInput.files = files; handleFileSelect(files[0]); }
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length) handleFileSelect(this.files[0]);
    });

    function handleFileSelect(file) {
        // Solo verificar que sea una imagen (cualquier formato)
        if (!file.type.startsWith('image/')) {
            alert('El archivo seleccionado no es una imagen válida.');
            resetFileInput(); return;
        }
        if (file.size > 5242880) {
            alert('La imagen sobrepasa los 5MB permitidos.');
            resetFileInput(); return;
        }
        selectedFileName.textContent = file.name;
        selectedFileSize.textContent = formatBytes(file.size);
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
            submitBtn.removeAttribute('disabled');
        };
        reader.readAsDataURL(file);
    }

    removeFileBtn.addEventListener('click', (e) => { e.stopPropagation(); resetFileInput(); });

    function resetFileInput() {
        fileInput.value = '';
        previewContainer.style.display = 'none';
        previewImage.src = '';
        submitBtn.setAttribute('disabled', 'true');
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024, sizes = ['Bytes','KB','MB','GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
    }

    const form = document.getElementById('photo-upload-form');
    const cancelBtn = document.getElementById('cancel-btn');
    const btnDefaultState = document.getElementById('btn-default-state');
    const btnLoadingState = document.getElementById('btn-loading-state');

    form.addEventListener('submit', function() {
        submitBtn.setAttribute('disabled', 'true');
        btnDefaultState.style.display = 'none';

        // 🔑 quitar el atributo hidden para que se muestre correctamente
        btnLoadingState.removeAttribute('hidden');
        btnLoadingState.style.display = 'inline-flex';
        btnLoadingState.style.alignItems = 'center';

        cancelBtn.classList.add('disabled');
        cancelBtn.setAttribute('aria-disabled', 'true');
    });
});
</script>
@endsection
