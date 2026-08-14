@extends('layouts.app')

@section('title', 'Dashboard - Hospital System')

@push('styles')
@vite(['resources/css/indexstyle.css'])
@endpush

@section('content')

<div class="container-fluid py-2">
    <!-- Encabezado Principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">
            PANEL DE CONTROL
            <small class="text-muted fs-6"> Bienvenido/a, {{ Auth::user()->persona ? Auth::user()->persona->nombre . ' ' . Auth::user()->persona->ap_paterno : Auth::user()->nombre_usuario }}</small>
        </h1>

        <!-- Buscador alineado a la derecha -->
        <div class="input-group" style="width: 280px; border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
            <input type="text" id="global-search" class="form-control bg-light border-0" placeholder="Buscar módulo..." style="font-size: 0.9rem; box-shadow: none;">
            <span class="input-group-text bg-light border-0">
                <i class="bi bi-search text-dark"></i>
            </span>
        </div>
    </div>

    


    <!-- Contenedor del Acordeón -->
    <div class="accordion" id="dashboardAccordion">
        @forelse ($categorias as $index => $categoria)
            @php
                // Se expande según lo guardado en el campo "colapsado" de la DB (colapsado == 'no')
                $isOpen = ($categoria->colapsado == 'no');
            @endphp
            <div class="accordion-item category-item shadow-sm mb-3 rounded-3 overflow-hidden">
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
                                    @php
                                        $routeMap = [
                                            'mBuscaArchivos' => 'busca_archivos.index',
                                            'mCargaArchivos' => 'carga_archivos.index',
                                            'mCategoArchivos' => 'categoria_archivos.index',
                                            'mPermisosArchivo' => 'trabajador_categorias.index',
                                            'mRXestudios' => 'rx.index',
                                            'mRXespecialidad' => 'rx_especialidades.index',
                                            'mRXmedicos' => 'rx_medicos.index',
                                            'mEstReportesRX' => 'rx_estadisticas.index',
                                            'mAreasAlmacen' => 'areas_almacen.index',
                                            'mComputadoras' => 'computadoras.index',
                                            'mMobiliario' => 'mobiliario.index',
                                            'mAreaSurtimiento' => 'areas_surtimiento.index',
                                            'mCumpleanos' => 'cumpleanos.index',
                                            'mBajasInsumos' => 'bajas_insumos.index',
                                            'mDevoluciones' => 'devoluciones.index',
                                            'mEntradaInsumosCendis' => 'entradas_cendis.index',
                                            'mInsumos' => 'insumos.index',
                                            'mInsumosArea' => 'insumos_area.index',
                                            'mMotivos' => 'motivos.index',
                                            'mReportes' => 'reportes_inventario.index',
                                            'mPedidosRecibidos' => 'pedidos_recibidos.index',
                                            'mCategoModulos' => 'categoria_modulos.index',
                                            'mUsuarios' => 'usuarios.index',
                                            'mConfiguracion' => 'configuracion_sistema.index',
                                            'mModulos' => 'modulos.index',
                                            'mPerfiles' => 'perfiles.index',
                                            'mPersonas' => 'personas.index',
                                            'mProyectos' => 'proyectos.index',
                                            'mImpresoras' => 'impresoras.index',
                                            'mRegActividades' => 'actividades.index',
                                            'mMonitores' => 'monitores.index',
                                            'mTipoMobiliario' => 'tipo_mobiliario.index',
                                            'mDepartamentos' => 'departamentos.index',
                                            'mPuestos' => 'puestos.index',
                                            'mSedes' => 'sedes.index',
                                            'mTipoTrabajador' => 'tipo_trabajador.index',
                                            'mTrabajadores' => 'trabajadores.index',
                                            'mAlmacenSubAreas' => 'almacen_subareas.index',
                                            'mAlmacenSubarea' => 'almacen_subareas.index',
                                            'mAreaAbastecimiento' => 'areas_abastecimiento.index',
                                            'mAreasAbastecimiento' => 'areas_abastecimiento.index',
                                            'mSubareaAbastecimiento' => 'subareas_abastecimiento.index',
                                            'mSubareasAbastecimiento' => 'subareas_abastecimiento.index',
                                            'mInsumosImpresoras' => 'insumos_impresoras.index',
                                            'mMovimientosInsumos' => 'movimientos_insumos.index',
                                            'mSoporteArea'       => 'soporte_area.index',
                                            'mSolicitarServicio' => 'solicitar_servicio.index',
                                            'mPlantillasPedidos' => 'plantillas_pedido.index',
                                            'mPlantillasPedido'  => 'plantillas_pedido.index',
                                            'mPlantillaPedido'   => 'plantillas_pedido.index',
                                            'mPedidoInsumos'     => 'pedido_insumos.index',
                                            'mPedidoInsumosDif'  => 'pedido_insumos_dif.index',
                                        ];
                                        $carpeta = trim($modulo->carpeta);
                                        $href = isset($routeMap[$carpeta]) && Route::has($routeMap[$carpeta])
                                            ? route($routeMap[$carpeta])
                                            : url($modulo->carpeta);
                                    @endphp
                                    <a href="{{ $href }}" class="text-decoration-none d-block h-100 module-card-link">
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
                                                    <p class="mb-0 text-white module-desc" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                        {{ $modulo->descripcion }}
                                                    </p>
                                                </div>

                                                <!-- Pie de tarjeta -->
                                                <div class="mt-auto pt-3">
                                                    <span class="module-footer-label">
                                                        Ingresar al Módulo &nbsp;<i class="fa fa-arrow-circle-right"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
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
