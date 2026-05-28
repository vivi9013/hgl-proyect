@extends('layouts.app')

@section('title', 'Cambiar Fotografía - Hospital System')

@section('content')
<style>
    .avatar-preview-container {
        position: relative;
        width: 180px;
        height: 180px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        transition: all 0.3s ease;
    }
    
    .avatar-preview-container:hover {
        transform: scale(1.03);
        box-shadow: 0 12px 32px rgba(13, 110, 253, 0.18);
    }
    
    .avatar-preview-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .drop-zone {
        border: 2px dashed #dee2e6;
        border-radius: 16px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        background-color: #f8fafc;
        transition: all 0.25s ease-in-out;
        cursor: pointer;
        position: relative;
    }

    .drop-zone:hover, .drop-zone.dragover {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.03);
    }

    .drop-zone i {
        font-size: 2.5rem;
        color: #6c757d;
        transition: color 0.2s;
    }

    .drop-zone:hover i, .drop-zone.dragover i {
        color: #0d6efd;
    }

    .upload-btn-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }

    .preview-thumbnail-container {
        display: none;
        margin-top: 1.5rem;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .pulse-avatar {
        animation: pulseBorder 2s infinite;
    }

    @keyframes pulseBorder {
        0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
        70% { box-shadow: 0 0 0 15px rgba(13, 110, 253, 0); }
        100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }
</style>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Cambiar Fotografía</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Carga y actualización de la fotografía de perfil del trabajador</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-light p-2 rounded-3 border-0">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}" class="text-decoration-none"><i class="fa fa-dashboard me-1"></i>Panel de Control</a></li>
                <li class="breadcrumb-item active" aria-current="page">Subir archivo</li>
            </ol>
        </nav>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 p-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">¡Operación Satisfactoria!</h6>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 p-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Error de Carga</h6>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 p-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill fs-4 me-3 text-warning"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Validación de Imagen</h6>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Card Main Container -->
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                            <i class="bi bi-image fs-5"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-0 text-dark">Modificar Fotografía Base</h5>
                    </div>
                    <span class="badge bg-light text-muted border py-2 px-3 rounded-pill fw-normal">Formato JPG únicamente</span>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4 align-items-center">
                        <!-- Left Side: Current Profile Picture -->
                        <div class="col-md-4 text-center py-3" style="border-right: 1px solid #dee2e6;">
                            <h6 class="text-muted fw-bold mb-3 uppercase tracking-wider" style="font-size: 0.8rem; letter-spacing: 1px;">FOTOGRAFÍA ACTUAL</h6>
                            
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
                                    <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center flex-column text-muted">
                                        <span class="fw-bold fs-1">{{ $user->initials }}</span>
                                    </div>
                                @endif
                            </div>
                            <p class="mb-0 text-dark fw-bold" style="font-size: 0.9rem;">
                                {{ $user->persona ? $user->persona->nombre . ' ' . $user->persona->ap_paterno : $user->nombre_usuario }}
                            </p>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                ID Persona: <span class="fw-bold text-primary">{{ $user->id_persona }}</span>
                            </small>
                        </div>
                        
                        <!-- Right Side: Upload Form -->
                        <div class="col-md-8">
                            <div class="ps-md-4">
                                <h6 class="text-muted fw-bold mb-3 uppercase tracking-wider" style="font-size: 0.8rem; letter-spacing: 1px;">SUBIR NUEVA IMAGEN</h6>
                                
                                <form action="{{ route('cambiar_foto.store') }}" method="POST" enctype="multipart/form-data" id="photo-upload-form">
                                    @csrf
                                    
                                    <input type="file" name="archivo-a-subir" id="file-upload-input" accept=".jpg,.jpeg" style="display: none;">
                                    
                                    <!-- Interactive Drop Zone -->
                                    <div class="drop-zone mb-4" id="upload-drop-zone">
                                        <i class="bi bi-cloud-arrow-up-fill mb-2 d-inline-block text-secondary"></i>
                                        <h5 class="fw-bold mb-1 fs-6 text-dark">Arrastra tu fotografía aquí</h5>
                                        <p class="text-muted mb-3" style="font-size: 0.8rem;">O si lo prefieres, haz clic para buscar en tus archivos</p>
                                        <button type="button" class="btn btn-outline-primary btn-sm px-4 rounded-pill fw-bold" id="browse-files-btn">
                                            Seleccionar archivo
                                        </button>
                                        
                                        <!-- Selected File Indicator -->
                                        <div class="preview-thumbnail-container" id="preview-container">
                                            <hr class="my-3">
                                            <div class="d-flex align-items-center justify-content-center bg-white p-3 rounded-3 border">
                                                <img src="" id="preview-image" class="rounded-circle me-3 object-fit-cover" style="width: 50px; height: 50px; border: 2px solid #198754;" alt="Preview">
                                                <div class="text-start">
                                                    <p class="mb-0 fw-bold text-dark text-truncate" id="selected-file-name" style="max-width: 250px; font-size: 0.85rem;"></p>
                                                    <small class="text-muted" id="selected-file-size" style="font-size: 0.75rem;"></small>
                                                </div>
                                                <button type="button" class="btn btn-link text-danger ms-auto p-0" id="remove-file-btn">
                                                    <i class="bi bi-trash-fill fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Requirements info -->
                                    <div class="bg-light p-3 rounded-3 mb-4" style="font-size: 0.8rem;">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-info-circle text-primary me-2 mt-0.5"></i>
                                            <div>
                                                <span class="fw-bold text-dark d-block mb-1">Requisitos de la imagen:</span>
                                                <span class="text-muted d-block">• Únicamente imágenes con extensión <strong>.jpg</strong> o <strong>.jpeg</strong></span>
                                                <span class="text-muted d-block">• El tamaño máximo del archivo no debe exceder <strong>5 MB</strong> (5,120 KB)</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Submit buttons -->
                                    <div class="d-grid d-md-flex gap-2 justify-content-md-end">
                                        <a href="{{ route('inicio') }}" class="btn btn-light px-4 rounded-pill text-dark fw-bold border" id="cancel-btn">Cancelar</a>
                                        <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold" id="submit-upload-btn" disabled>
                                            <span id="btn-default-state">
                                                <i class="bi bi-cloud-upload me-1"></i> Guardar Cambios
                                            </span>
                                            <span id="btn-loading-state" style="display: none;">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Subiendo imagen...
                                            </span>
                                        </button>
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('upload-drop-zone');
    const fileInput = document.getElementById('file-upload-input');
    const browseBtn = document.getElementById('browse-files-btn');
    const submitBtn = document.getElementById('submit-upload-btn');
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const removeFileBtn = document.getElementById('remove-file-btn');
    
    // Open file selector when clicking dropzone elements
    browseBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.click();
    });
    
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    // Drag and drop events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    // File input change event
    fileInput.addEventListener('change', function() {
        if (this.files.length) {
            handleFileSelect(this.files[0]);
        }
    });

    // Handle the selected file
    function handleFileSelect(file) {
        // Validate extension
        const allowedExtensions = /(\.jpg|\.jpeg)$/i;
        if (!allowedExtensions.exec(file.name)) {
            alert('La imagen cargada no pertenece a la extensión jpg o jpeg.');
            resetFileInput();
            return;
        }

        // Validate size (5MB = 5242880 bytes)
        const maxSize = 5242880;
        if (file.size > maxSize) {
            alert('La imagen sobrepasa los 5MB permitidos.');
            resetFileInput();
            return;
        }

        // Display info & enable submit
        selectedFileName.textContent = file.name;
        selectedFileSize.textContent = formatBytes(file.size);
        
        // Image preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
            submitBtn.removeAttribute('disabled');
        }
        reader.readAsDataURL(file);
    }

    // Remove file selection
    removeFileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        resetFileInput();
    });

    function resetFileInput() {
        fileInput.value = '';
        previewContainer.style.display = 'none';
        previewImage.src = '';
        submitBtn.setAttribute('disabled', 'true');
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    // Submit form loading state
    const form = document.getElementById('photo-upload-form');
    const cancelBtn = document.getElementById('cancel-btn');
    const btnDefaultState = document.getElementById('btn-default-state');
    const btnLoadingState = document.getElementById('btn-loading-state');

    form.addEventListener('submit', function() {
        // Mostrar estado de carga
        submitBtn.setAttribute('disabled', 'true');
        btnDefaultState.style.display = 'none';
        btnLoadingState.style.display = 'inline-flex';
        btnLoadingState.style.alignItems = 'center';
        // Deshabilitar cancelar mientras sube
        cancelBtn.classList.add('disabled');
        cancelBtn.setAttribute('aria-disabled', 'true');
    });
});
</script>
@endsection