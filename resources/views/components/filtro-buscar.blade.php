@props([
    'id' => 'filtro-buscar',
    'label' => 'Buscar',
    'placeholder' => 'Buscar...',
    'icono' => 'fa-search',
    'clase' => 'col-12 col-md-4'
])
<div class="{{ $clase }}">
    <label class="form-label small fw-semibold text-secondary mb-1" for="{{ $id }}">
        <i class="fa {{ $icono }} me-1"></i>{{ $label }}
    </label>
    <input type="search" 
           id="{{ $id }}"
           data-rol="buscar"
           class="form-control form-control-sm bg-light border-0"
           placeholder="{{ $placeholder }}"
           style="height: 31px; font-size: 0.875rem;">
</div>
