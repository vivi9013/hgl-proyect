<div class="d-flex gap-2 mb-2 flex-wrap">
    <a href="{{ route('entradas_cendis.index') }}" 
       class="btn btn-sm {{ ($activo === 'pendientes') ? 'btn-primary' : 'btn-outline-dark bg-white' }} py-2 px-3 fw-bold shadow-sm" 
       style="border: 1.5px solid #000; border-radius: 8px;">
        <i class="fa fa-hourglass-half me-1 {{ ($activo === 'pendientes') ? '' : 'text-dark' }}"></i>Pendientes
    </a>
    <a href="{{ route('entradas_cendis.terminadas') }}" 
       class="btn btn-sm {{ ($activo === 'terminadas') ? 'btn-primary' : 'btn-outline-dark bg-white' }} py-2 px-3 fw-bold shadow-sm" 
       style="border: 1.5px solid #000; border-radius: 8px;">
        <i class="fa fa-check-circle me-1 {{ ($activo === 'terminadas') ? '' : 'text-dark' }}"></i>Terminadas
    </a>
    <a href="{{ route('entradas_cendis.reportes') }}" 
       class="btn btn-sm {{ ($activo === 'reportes') ? 'btn-primary' : 'btn-outline-dark bg-white' }} py-2 px-3 fw-bold shadow-sm" 
       style="border: 1.5px solid #000; border-radius: 8px;">
        <i class="fa fa-bar-chart me-1 {{ ($activo === 'reportes') ? '' : 'text-dark' }}"></i>Reportes
    </a>
</div>
