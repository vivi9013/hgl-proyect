@props([
    'id' => 'filtro-fecha-rango',
    'label' => 'Rango de fechas',
    'placeholder' => 'Seleccionar rango...',
    'icono' => 'fa-calendar',
    'clase' => 'col-12 col-md-3'
])
<div class="{{ $clase }}">
    <label class="form-label small fw-semibold text-secondary mb-1" for="{{ $id }}">
        <i class="fa {{ $icono }} me-1"></i>{{ $label }}
    </label>
    <input type="text" 
           id="{{ $id }}"
           data-rol="fecha-rango"
           class="form-control form-control-sm bg-light border-0"
           placeholder="{{ $placeholder }}"
           readonly>
</div>
