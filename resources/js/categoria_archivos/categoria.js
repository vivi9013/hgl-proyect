/**
 * Lógica Javascript para el módulo de Categoría de Archivos
 */
document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitog.dataset.message || 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    const alertaExito = document.getElementById('alertaExito');
    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.dataset.message || 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    const alertaError = document.getElementById('alertaError');
    if (alertaError && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Error!',
            text: alertaError.dataset.message || 'Hubo un error al procesar la categoría.',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. REFERENCIAS AL DOM
    // ─────────────────────────────────────────────────────────────────────────
    const inputCategoria         = document.getElementById('categoria');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner         = document.getElementById('loadingSpinner');
    const btnGuardar             = document.getElementById('btnGuardar');

    const cuerpoTabla    = document.getElementById('cuerpoTablaCategorias');
    const infoPaginacion = document.getElementById('infoPaginacionCategorias');
    const paginacionDiv  = document.getElementById('paginacionCategorias');
    const filtroBuscar   = document.getElementById('filtro-buscar');

    // ─────────────────────────────────────────────────────────────────────────
    // C. HELPER — debounce
    // ─────────────────────────────────────────────────────────────────────────
    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. VERIFICACIÓN DE DISPONIBILIDAD DE NOMBRE EN TIEMPO REAL (AJAX)
    // ─────────────────────────────────────────────────────────────────────────
    if (inputCategoria && feedbackDisponibilidad && loadingSpinner && btnGuardar) {
        inputCategoria.addEventListener('input', demorarEjecucion(function () {
            const nombre = this.value.trim();

            if (!nombre) {
                feedbackDisponibilidad.innerHTML = '';
                inputCategoria.classList.remove('is-valid', 'is-invalid');
                btnGuardar.disabled = false;
                return;
            }

            loadingSpinner.style.display = 'block';
            feedbackDisponibilidad.innerHTML = '';

            fetch(`/categoria-archivos/verificar?categoria=${encodeURIComponent(nombre)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => { if (!r.ok) throw new Error(); return r.json(); })
            .then(datos => {
                loadingSpinner.style.display = 'none';
                if (datos.disponible) {
                    feedbackDisponibilidad.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Categoría disponible</span>';
                    inputCategoria.classList.remove('is-invalid');
                    inputCategoria.classList.add('is-valid');
                    btnGuardar.disabled = false;
                } else {
                    feedbackDisponibilidad.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> Esta categoría ya existe</span>';
                    inputCategoria.classList.remove('is-valid');
                    inputCategoria.classList.add('is-invalid');
                    btnGuardar.disabled = true;
                }
            })
            .catch(() => { loadingSpinner.style.display = 'none'; });
        }, 300));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. TABLA — filtro de búsqueda + paginación AJAX
    // ─────────────────────────────────────────────────────────────────────────
    function obtenerFiltros() {
        return {
            buscar: filtroBuscar?.value.trim() ?? '',
        };
    }

    function cargarCategorias(pagina = 1) {
        if (!cuerpoTabla) return;
        const f = obtenerFiltros();
        const params = new URLSearchParams({ page: pagina });
        if (f.buscar) params.set('buscar', f.buscar);

        cuerpoTabla.style.opacity    = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/categoria-archivos?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(datos => {
            cuerpoTabla.style.opacity = '1';
            cuerpoTabla.innerHTML     = datos.html;
            if (infoPaginacion) infoPaginacion.textContent = datos.info;
            if (paginacionDiv) {
                paginacionDiv.innerHTML = datos.links;
                paginacionDiv.querySelectorAll('a.page-link').forEach(a => {
                    a.addEventListener('click', e => {
                        e.preventDefault();
                        const p = new URL(a.href).searchParams.get('page');
                        if (p) cargarCategorias(p);
                    });
                });
            }
            enlazarEventosStatus();
        })
        .catch(() => { cuerpoTabla.style.opacity = '1'; });
    }

    if (filtroBuscar) {
        filtroBuscar.addEventListener('input', demorarEjecucion(() => cargarCategorias(1), 320));
    }

    // Paginación carga inicial (SSR)
    if (paginacionDiv) {
        paginacionDiv.querySelectorAll('a.page-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const p = new URL(a.href).searchParams.get('page');
                if (p) cargarCategorias(p);
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // F. CAMBIO DE ESTATUS (activar / desactivar) con confirmación
    // ─────────────────────────────────────────────────────────────────────────
    function enlazarEventosStatus() {
        document.querySelectorAll('.btn-toggle-status').forEach(link => {
            const nuevoLink = link.cloneNode(true);
            link.parentNode.replaceChild(nuevoLink, link);

            nuevoLink.addEventListener('click', function (e) {
                e.preventDefault();
                const url    = this.getAttribute('data-url');
                const nombre = this.getAttribute('data-nombre');
                const activo = parseInt(this.getAttribute('data-activo'));

                const accion         = (activo === 1) ? 'desactivar' : 'activar';
                const iconType       = (activo === 1) ? 'warning' : 'question';
                const confirmBtnText = (activo === 1) ? 'Sí, desactivar' : 'Sí, activar';

                const submitStatusForm = () => {
                    const form = document.createElement('form');
                    form.action = url;
                    form.method = 'POST';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
                    const csrfInput = document.createElement('input');
                    csrfInput.type  = 'hidden';
                    csrfInput.name  = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type  = 'hidden';
                    methodInput.name  = '_method';
                    methodInput.value = 'PATCH';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿Desea ${accion} la categoría?`,
                        text: `La categoría "${nombre}" será ${activo === 1 ? 'desactivada' : 'activada'} en el sistema.`,
                        icon: iconType,
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) submitStatusForm();
                    });
                } else if (confirm(`¿Está seguro de que desea ${accion} la categoría "${nombre}"?`)) {
                    submitStatusForm();
                }
            });
        });
    }

    enlazarEventosStatus();
});