<nav class="app-header navbar navbar-expand bg-white border-bottom shadow-sm px-4" style="height: var(--header-height);">
  <div class="container-fluid">
    
    <!-- Botón para colapsar sidebar -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link text-dark" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list fs-4"></i>
        </a>
      </li>
    </ul>

    <!-- Buscador y Perfil -->
    <ul class="navbar-nav ms-auto align-items-center">
      <!-- Buscador -->
      <li class="nav-item d-none d-md-block me-3">
        <div class="input-group input-group-sm">
          <input type="text" id="global-search" class="form-control border-0 bg-light" placeholder="Buscar..." style="width: 200px; border-radius: 20px;">
          <span class="input-group-text border-0 bg-light" style="border-radius: 20px;"><i class="bi bi-search"></i></span>
        </div>
      </li>

      <!-- Notificaciones -->
      <li class="nav-item me-3">
        <a class="nav-link text-secondary position-relative" href="#">
          <i class="bi bi-bell fs-5"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">3</span>
        </a>
      </li>

      <!-- Perfil Usuario -->
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center" href="#">
          @php
            $user = Auth::user();
            $hasPhoto = false;
            if ($user && $user->id_persona) {
                $hasPhoto = \Illuminate\Support\Facades\Storage::disk('public')->exists('fotos/' . $user->id_persona . '.jpg');
            }
          @endphp
          @if($hasPhoto)
            <img src="{{ $user->foto_url }}" class="rounded-circle me-2 object-fit-cover" style="width: 35px; height: 35px; border: 1.5px solid #0d6efd;" alt="User Image">
          @else
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 35px; height: 35px; font-size: 0.85rem;">
              {{ $user ? $user->initials : 'US' }}
            </div>
          @endif
          <div class="d-none d-sm-block">
            <p class="mb-0 fw-bold" style="font-size: 0.85rem; line-height: 1;">
              {{ $user && $user->persona ? $user->persona->nombre . ' ' . $user->persona->ap_paterno : ($user ? $user->nombre_usuario : 'Usuario') }}
            </p>
            <small class="text-muted" style="font-size: 0.75rem;">
              {{ $user && $user->perfil ? $user->perfil->perfil : 'Colaborador' }}
            </small>
          </div>
        </a>
      </li>
    </ul>
  </div>
</nav>