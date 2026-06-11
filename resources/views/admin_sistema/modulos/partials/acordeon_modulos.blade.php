<div class="accordion" id="accordionPreview">
    @forelse ($categorias as $index => $row)
        @php
            $collapseId = "collapse_" . ($index + 1);
            $headingId = "heading_" . ($index + 1);
            $isOpen = ($index == 0) ? "show" : "";
        @endphp
        <div class="accordion-item border mb-3 rounded shadow-sm overflow-hidden">
            <h2 class="accordion-header" id="{{ $headingId }}">
                <button class="accordion-button bg-light fw-bold text-dark @if($index > 0) collapsed @endif" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#{{ $collapseId }}" 
                        aria-expanded="@if($index == 0) true @else false @endif" 
                        aria-controls="{{ $collapseId }}">
                    <i class="fa fa-folder-open text-primary me-2"></i> {{ $row->categoria }}
                </button>
            </h2>
            <div id="{{ $collapseId }}" 
                 class="accordion-collapse collapse {{ $isOpen }}" 
                 aria-labelledby="{{ $headingId }}" 
                 data-bs-parent="#accordionPreview">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        @forelse ($row->modulos as $mod)
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="small-box bg-{{ $mod->color }}">
                                    <div class="inner">
                                        <h3>&nbsp;</h3>
                                        <span class="progress-description fw-bold">{{ $mod->nombre }}</span>
                                    </div>
                                    <div class="icon">
                                        @if($mod->icono)
                                            <i class="{{ $mod->icono }}"></i>
                                        @else
                                            <i class="fa fa-puzzle-piece"></i>
                                        @endif
                                    </div>
                                    <a href="#" class="small-box-footer py-2">
                                        Ingresar al Módulo <i class="fa fa-arrow-circle-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted text-center py-3">
                                <i class="fa fa-info-circle me-1"></i> No hay submódulos activos para tu perfil en esta categoría.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-muted text-center py-4 bg-light rounded border border-dashed">
            <i class="fa fa-info-circle me-1"></i> No se encontraron categorías con módulos activos asignados para tu perfil.
        </div>
    @endforelse
</div>
