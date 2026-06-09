
@php
    $user = Auth::user();
    $hasPhoto = false;
    if ($user && $user->id_persona) {
        $hasPhoto = \Illuminate\Support\Facades\Storage::disk('fotos')->exists($user->id_persona . '.jpg');
    }

    // Nombre completo (Dropdown interno)
    $nombreCompleto = $user && $user->persona
        ? trim($user->persona->nombre . ' ' . $user->persona->ap_paterno . ' ' . ($user->persona->ap_materno ?? ''))
        : ($user ? $user->nombre_usuario : 'Usuario');

    // Nombre corto (Para la barra superior estática)
    $nombreCorto = $user && $user->persona 
        ? trim($user->persona->nombre . ' ' . $user->persona->ap_paterno)
        : ($user ? $user->nombre_usuario : 'Usuario');

    // Perfil/Rol
    $rolNombre = $user && $user->perfil ? $user->perfil->perfil : 'Colaborador';

    // Descripción del perfil
    $rolDesc = $user && $user->perfil && !empty($user->perfil->descripcion)
        ? $user->perfil->descripcion
        : 'Perfil de acceso al sistema de gestión hospitalaria.';

    // Antigüedad — se usa fecha de la persona (fecha de registro en el sistema)
    $miembroDesde = null;
    if ($user && $user->persona && $user->persona->fecha) {
        try {
            $miembroDesde = \Carbon\Carbon::parse($user->persona->fecha)->locale('es')->isoFormat('MMM YYYY');
        } catch (\Exception $e) {}
    }

    // Edad
    $edad = null;
    if ($user && $user->persona && $user->persona->fecha_nac) {
        try {
            $edad = \Carbon\Carbon::parse($user->persona->fecha_nac)->age;
        } catch (\Exception $e) {}
    }

    // Texto meta unificado para evitar chars especiales en directivas Blade
    $metaTexto = '';
    if ($miembroDesde && $edad) {
        $metaTexto = 'Miembro desde ' . $miembroDesde . ' - ' . $edad . ' años de edad';
    } elseif ($miembroDesde) {
        $metaTexto = 'Miembro desde ' . $miembroDesde;
    } elseif ($edad) {
        $metaTexto = $edad . ' años de edad';
    }
    $iniciales = $user ? $user->initials : 'US';
@endphp

<nav class="app-header navbar navbar-expand shadow-sm px-4" style="height: var(--header-height);">
  <div class="container-fluid">

    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link text-dark" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list fs-4"></i>
        </a>
      </li>
    </ul>

    <ul class="navbar-nav ms-auto align-items-center">

      <li class="nav-item me-3">
        <a class="nav-link text-secondary position-relative" href="#">
          <i class="bi bi-bell fs-5"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">3</span>
        </a>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link d-flex align-items-center profile-trigger dropdown-toggle"
           href="#"
           id="profileDropdown"
           role="button"
           data-bs-toggle="dropdown"
           aria-expanded="false"
           style="text-decoration: none;">

          @if($hasPhoto)
            <img src="{{ $user->foto_url }}" class="rounded-circle me-2 object-fit-cover" style="width: 35px; height: 35px; border: 2px solid #9e9e9e;" alt="Foto de perfil">
          @else
            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 35px; height: 35px; font-size: 0.85rem; border: 2px solid #9e9e9e;">
              {{ $iniciales }}
            </div>
          @endif

          <div class="d-none d-sm-block">
            <p class="mb-0 fw-bold" style="font-size: 0.85rem; line-height: 1; color: var(--theme-text);">
              {{ $nombreCorto }}
            </p>
            <small class="text-muted" style="font-size: 0.75rem;">
              {{ $rolNombre }}
            </small>
          </div>
        </a>

        <ul class="dropdown-menu dropdown-menu-end profile-dropdown-menu" style="border: 2px solid #000000;" aria-labelledby="profileDropdown">
          <li>
            <div class="profile-card-top" style="background: var(--theme-primary);">
              @if($hasPhoto)
                <img src="{{ $user->foto_url }}" class="profile-card-avatar" alt="Foto de perfil" style="width: 78px; height: 78px; border-radius: 50%; border: 3px solid rgba(0, 0, 0, 0.35); object-fit: cover; margin-bottom: 12px;">
              @else
                <div class="profile-card-avatar-initials" style="background: var(--theme-hover-bg); color: var(--theme-text); border: 3px solid rgba(0,0,0,0.3);">
                  {{ $iniciales }}
                </div>
              @endif

              <div class="profile-card-name" style="color: var(--theme-text);">{{ $nombreCompleto }}</div>
              <div class="profile-card-role" style="color: var(--theme-text-muted);">{{ $rolNombre }}</div>

              @if($metaTexto)
                <div class="profile-card-meta" style="color: var(--theme-text-muted);">{{ $metaTexto }}</div>
              @endif
            </div>

            <div class="profile-card-bottom">
              <p class="profile-card-desc">{{ $rolDesc }}</p>

              <div class="profile-card-actions">
                <a href="{{ route('mis_datos.index') }}" class="profile-card-btn">
                  <i class="bi bi-person-lines-fill"></i>
                  Mis Datos
                </a>
                <a href="{{ route('logout') }}" class="profile-card-btn btn-danger-soft">
                  <i class="bi bi-power"></i>
                  Salir
                </a>
              </div>
            </div>
          </li>
        </ul>

      </li>
    </ul>

  </div>
</nav>