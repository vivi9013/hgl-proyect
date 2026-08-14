@props([
    'id' => 'filtro-dropdown',
    'label' => 'Filtrar por categoría',
    'titulo' => 'Categorías',
    'labelDefault' => 'Todos los registros',
    'clase' => 'col-12 col-md-4'
])
<div class="{{ $clase }}">
    <label class="form-label small fw-semibold text-secondary mb-1">
        <i class="fa fa-filter me-1"></i>{{ $label }}
    </label>
    <div class="dropdown w-100" id="{{ $id }}" data-rol="filtro-dropdown">
        <button class="btn btn-sm btn-light border w-100 text-start d-flex justify-content-between align-items-center dropdown-toggle" 
                type="button" 
                id="btn-{{ $id }}" 
                data-bs-toggle="dropdown" 
                data-bs-auto-close="outside" 
                aria-expanded="false"
                style="height: 31px; font-size: 0.875rem;">
            <span class="dropdown-label">{{ $labelDefault }}</span>
        </button>
        
        <div class="dropdown-menu p-3 shadow-lg border-0" aria-labelledby="btn-{{ $id }}" style="width: 290px; border-radius: 12px; font-size: 0.85rem;">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="fw-bold text-dark">{{ $titulo }}</span>
                <a href="#" class="dropdown-clear-all text-decoration-none text-primary small fw-semibold">Limpiar todo</a>
            </div>
            
            <div class="dropdown-body" style="max-height: 220px; overflow-y: auto;">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            <div class="d-flex gap-2 pt-2 border-top mt-2">
                <button type="button" class="btn-dropdown-cancel btn btn-sm btn-outline-secondary w-50 rounded-pill">Cancelar</button>
                <button type="button" class="btn-dropdown-apply btn btn-sm btn-dark w-50 rounded-pill text-white">Aplicar</button>
            </div>
        </div>
    </div>
</div>
