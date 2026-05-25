@extends('layouts.app')

@section('title', 'Dashboard - Hospital System')

@section('content')

<style>
  /* Mapeo seguro de colores de AdminLTE original */
  .bg-blue, .bg-blue-active { background-color: #0073b7 !important; color: #fff !important; }
  .bg-green, .bg-green-active { background-color: #00a65a !important; color: #fff !important; }
  .bg-yellow, .bg-yellow-active { background-color: #f39c12 !important; color: #fff !important; }
  .bg-red, .bg-red-active { background-color: #dd4b39 !important; color: #fff !important; }
  .bg-aqua, .bg-aqua-active { background-color: #00c0ef !important; color: #fff !important; }
  .bg-purple, .bg-purple-active { background-color: #605ca8 !important; color: #fff !important; }
  .bg-navy, .bg-navy-active { background-color: #001f3f !important; color: #fff !important; }
  .bg-teal, .bg-teal-active { background-color: #39cccc !important; color: #fff !important; }
  .bg-olive, .bg-olive-active { background-color: #3d9970 !important; color: #fff !important; }
  .bg-orange, .bg-orange-active { background-color: #ff851b !important; color: #fff !important; }
  .bg-fuchsia, .bg-fuchsia-active { background-color: #f012be !important; color: #fff !important; }
  .bg-maroon, .bg-maroon-active { background-color: #d81b60 !important; color: #fff !important; }
  .bg-light-blue, .bg-light-blue-active { background-color: #3c8dbc !important; color: #fff !important; }

  /* Estilos específicos para la visualización responsiva del Dashboard */
  .module-card {
    transition: transform 0.15s ease-in-out;
  }
  .module-card:hover {
    transform: translateY(-2px);
  }
  .icon-opacity {
    opacity: 0.25;
    font-size: 2.8rem;
    position: absolute;
    right: 15px;
    top: 15px;
  }
</style>

<div class="container-fluid py-2">
    <!-- Encabezado Principal -->
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Panel de Control <small class="text-muted fs-6">Inicio</small></h1>
    </div>

    <!-- Contenedor del Acordeón -->
    <div class="accordion" id="dashboardAccordion">
        @forelse ($categorias as $index => $categoria)
            @php
                // Se expande la primera categoría por defecto, o según lo guardado en el campo "colapsado" de la DB
                $isOpen = ($index == 0 || $categoria->colapsado == 'no');
            @endphp
            <div class="accordion-item category-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                <h2 class="accordion-header" id="heading-{{ $categoria->id_CategoriaModulo }}">
                    <button class="accordion-button fw-bold bg-dark text-white {{ $isOpen ? '' : 'collapsed' }}" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapse-{{ $categoria->id_CategoriaModulo }}" 
                            aria-expanded="{{ $isOpen ? 'true' : 'false' }}" 
                            aria-controls="collapse-{{ $categoria->id_CategoriaModulo }}"
                            style="font-size: 0.95rem;">
                        <i class="fa fa-folder-open-o me-2 text-warning"></i> {{ $categoria->categoria }}
                    </button>
                </h2>
                <div id="collapse-{{ $categoria->id_CategoriaModulo }}" 
                     class="accordion-collapse collapse {{ $isOpen ? 'show' : '' }}" 
                     aria-labelledby="heading-{{ $categoria->id_CategoriaModulo }}" 
                     data-bs-parent="#dashboardAccordion">
                    <div class="accordion-body bg-light p-4">
                        <div class="row g-3">
                            @foreach ($categoria->modulos as $modulo)
                                @php
                                    $colorClass = trim($modulo->color);
                                    // Si no incluye 'bg-', lo agregamos por seguridad
                                    if (!str_starts_with($colorClass, 'bg-')) {
                                        $colorClass = 'bg-' . $colorClass;
                                    }
                                @endphp
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 module-container">
                                    <div class="card {{ $colorClass }} text-white h-100 border-0 shadow-sm position-relative overflow-hidden module-card" style="min-height: 120px;">
                                        <div class="card-body d-flex flex-column justify-content-between p-3">
                                            <!-- Ícono decorativo de fondo -->
                                            <div class="icon-opacity">
                                                <i class="{{ $modulo->icono }}"></i>
                                            </div>
                                            
                                            <!-- Contenido del texto -->
                                            <div class="pe-5">
                                                <h6 class="fw-bold mb-1 text-white module-title" style="font-size: 0.95rem; line-height: 1.3;">
                                                    {{ $modulo->nombre }}
                                                </h6>
                                                <p class="mb-0 text-white-50 module-desc" style="font-size: 0.75rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                    {{ $modulo->descripcion }}
                                                </p>
                                            </div>
                                            
                                            <!-- Botón de ingreso -->
                                            <div class="mt-3">
                                                <a href="{{ url($modulo->carpeta) }}" class="text-white text-decoration-none d-flex align-items-center justify-content-between py-1 px-2 rounded bg-black bg-opacity-20 border border-white border-opacity-10" style="font-size: 0.8rem; transition: background 0.15s;">
                                                    <span>Ingresar al Módulo</span>
                                                    <i class="fa fa-arrow-circle-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-warning border-0 shadow-sm">
                <i class="fa fa-exclamation-triangle me-2"></i> No tienes ningún módulo asignado a tu perfil de usuario actualmente.
            </div>
        @endforelse
    </div>

    <!-- Mensaje cuando no hay resultados de búsqueda -->
    <div id="no-results-message" class="alert alert-info border-0 shadow-sm text-center d-none my-4">
        <i class="bi bi-search me-2"></i> No se encontraron resultados para "<strong id="search-term-placeholder"></strong>"
    </div>
</div>

@endsection
