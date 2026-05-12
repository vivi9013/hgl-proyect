<aside class="app-sidebar bg-white shadow-sm border-end" style="width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000;">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand bg-white border-bottom d-flex align-items-center justify-content-center" style="height: var(--header-height);">
    <a href="/" class="brand-link text-center text-decoration-none">
      <div class="text-primary fs-4 fw-bold">
        <i class="bi bi-heart-pulse-fill"></i> Estadias 
      </div>
    </a>
  </div>
  <!--end::Sidebar Brand-->

  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper overflow-auto" style="height: calc(100vh - var(--header-height) - 80px);">
    <nav class="mt-3">
      <ul class="nav sidebar-menu flex-column px-3">
        <li class="nav-item mb-2">
          <a href="/" class="nav-link d-flex align-items-center p-3 {{ request()->is('/') ? 'active-menu-item' : 'text-dark' }}">
            <i class="bi bi-speedometer2 me-3 fs-5"></i>
            <span class="fw-medium">Dashboard</span>
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" class="nav-link d-flex align-items-center p-3 text-dark">
            <i class="bi bi-calendar-check me-3 fs-5"></i>
            <span class="fw-medium">Citas</span>
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" class="nav-link d-flex align-items-center p-3 text-dark">
            <i class="bi bi-people me-3 fs-5"></i>
            <span class="fw-medium">Pacientes</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->

  <div class="sidebar-logout p-3 border-top bg-white" style="position: absolute; bottom: 0; width: 100%;">
    <a href="{{ route('login') }}" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center py-2">
      <i class="bi bi-box-arrow-right me-2"></i>
      <span>Cerrar Sesión</span>
    </a>
  </div>
</aside>