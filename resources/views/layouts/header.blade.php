<style>
/* ── Profile Dropdown Card ───────────────────────────────── */
.profile-dropdown-menu {
    width: 300px;
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    overflow: hidden;
    padding: 0;
    margin-top: 10px !important;
}

.profile-card-top {
    background: linear-gradient(160deg, #1a1a2e 0%, #2d2d44 100%);
    padding: 24px 20px 20px;
    text-align: center;
}

.profile-card-avatar {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.25);
    object-fit: cover;
    margin-bottom: 12px;
}

.profile-card-avatar-initials {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.25);
    background: rgba(255,255,255,0.12);
    color: #fff;
    font-size: 1.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
}

.profile-card-name {
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 2px;
    line-height: 1.3;
}

.profile-card-role {
    color: rgba(255,255,255,0.6);
    font-size: 0.78rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.profile-card-meta {
    color: rgba(255,255,255,0.45);
    font-size: 0.73rem;
}

.profile-card-bottom {
    background: #fff;
    padding: 16px 20px;
}

.profile-card-desc {
    font-size: 0.82rem;
    color: #666;
    text-align: center;
    margin-bottom: 14px;
    line-height: 1.4;
}

.profile-card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.profile-card-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    border: 1.5px solid #ddd;
    color: #444;
    background: #f8f8f8;
    transition: all 0.18s ease;
}

.profile-card-btn:hover {
    background: #f0f0f0;
    border-color: #bbb;
    color: #222;
    text-decoration: none;
}

.profile-card-btn.btn-danger-soft {
    border-color: #fbb;
    color: #c0392b;
    background: #fff5f5;
}

.profile-card-btn.btn-danger-soft:hover {
    background: #ffe0e0;
    border-color: #e74c3c;
    color: #c0392b;
}

/* ── Trigger hover en el header ──────────────────────────── */
.profile-trigger {
    cursor: pointer;
    border-radius: 10px;
    padding: 6px 10px;
    transition: background 0.15s;
}
.profile-trigger:hover {
    background: rgba(0,0,0,0.06);
}
</style>

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
        $metaTexto = 'Miembro desde ' . $miembroDesde . ' - ' . $edad . ' anos de edad';
    } elseif ($miembroDesde) {
        $metaTexto = 'Miembro desde ' . $miembroDesde;
    } elseif ($edad) {
        $metaTexto = $edad . ' anos de edad';
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
            <p class="mb-0 fw-bold" style="font-size: 0.85rem; line-height: 1;">
              {{ $nombreCorto }}
            </p>
            <small class="text-muted" style="font-size: 0.75rem;">
              {{ $rolNombre }}
            </small>
          </div>
        </a>

        <ul class="dropdown-menu dropdown-menu-end profile-dropdown-menu" aria-labelledby="profileDropdown">
          <li>
            <div class="profile-card-top">
              @if($hasPhoto)
                <img src="{{ $user->foto_url }}" class="profile-card-avatar" alt="Foto de perfil">
              @else
                <div class="profile-card-avatar-initials">
                  {{ $iniciales }}
                </div>
              @endif

              <div class="profile-card-name">{{ $nombreCompleto }}</div>
              <div class="profile-card-role">{{ $rolNombre }}</div>

              @if($metaTexto)
                <div class="profile-card-meta">{{ $metaTexto }}</div>
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