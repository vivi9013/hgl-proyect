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

    const alertaError = document.getElementById('alertaError');
    if (alertaError && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Error!',
            text: alertaError.dataset.message || 'Hubo un error al procesar el archivo.',
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. REFERENCIAS AL DOM
    // ─────────────────────────────────────────────────────────────────────────
    const inputNombre  = document.getElementById('nombre');
    const inputVersion = document.getElementById('version');
    const selectTipo    = document.getElementById('tipo');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar     = document.getElementById('btnGuardar');

    const cuerpoTabla     = document.getElementById('cuerpoTablaArchivos');
    const infoPaginacion  = document.getElementById('infoPaginacionArchivos');
    const paginacionDiv   = document.getElementById('paginacionArchivos');
    const filtroBuscar    = document.getElementById('filtro-buscar');
    const dropdownFiltros = document.getElementById('dropdownFiltros');

    // ─────────────────────────────────────────────────────────────────────────
    // C. HELPER — debounce
    // ─────────────────────────────────────────────────────────────────────────
    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. VERIFICAR DISPONIBILIDAD DE NOMBRE + VERSIÓN (POR CATEGORÍA) — AJAX
    // ─────────────────────────────────────────────────────────────────────────
    if (inputNombre && inputVersion) {
        function verificarDisponibilidad() {
            const nombre  = inputNombre.value.trim();
            const version = inputVersion.value.trim();
            const tipo    = selectTipo ? selectTipo.value.trim() : '';

            if (!nombre || !version || !tipo) {
                feedbackDisponibilidad.innerHTML = '';
                inputNombre.classList.remove('is-valid', 'is-invalid');
                if (btnGuardar) btnGuardar.disabled = false;
                return;
            }

            loadingSpinner.style.display = 'block';
            feedbackDisponibilidad.innerHTML = '';

            fetch(`/carga-archivos/verificar-nombre?nombre=${encodeURIComponent(nombre)}&version=${encodeURIComponent(version)}&tipo=${encodeURIComponent(tipo)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => { if (!r.ok) throw new Error(); return r.json(); })
            .then(datos => {
                loadingSpinner.style.display = 'none';
                if (datos.disponible) {
                    feedbackDisponibilidad.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Nombre y versión disponibles</span>';
                    inputNombre.classList.remove('is-invalid');
                    inputNombre.classList.add('is-valid');
                    if (btnGuardar) btnGuardar.disabled = false;
                } else {
                    feedbackDisponibilidad.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> Este nombre y versión ya existen en esta categoría</span>';
                    inputNombre.classList.remove('is-valid');
                    inputNombre.classList.add('is-invalid');
                    if (btnGuardar) btnGuardar.disabled = true;
                }
            })
            .catch(() => { loadingSpinner.style.display = 'none'; });
        }

        const verificarConDebounce = demorarEjecucion(verificarDisponibilidad, 300);

        inputNombre.addEventListener('blur', verificarDisponibilidad);
        inputNombre.addEventListener('input', verificarConDebounce);
        inputVersion.addEventListener('change', verificarDisponibilidad);
        inputVersion.addEventListener('input', verificarConDebounce);
        if (selectTipo) selectTipo.addEventListener('change', verificarDisponibilidad);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. TABLA — filtros (buscar + categorías) + paginación AJAX
    // ─────────────────────────────────────────────────────────────────────────
    function obtenerFiltros() {
        return {
            buscar:    filtroBuscar?.value.trim() ?? '',
            categoria: Array.from(document.querySelectorAll('.chk-categoria:checked')).map(el => el.value),
        };
    }

    function cargarArchivos(pagina = 1) {
        if (!cuerpoTabla) return;
        const f = obtenerFiltros();
        const params = new URLSearchParams({ page: pagina });
        if (f.buscar) params.set('buscar', f.buscar);
        f.categoria.forEach(v => params.append('categoria[]', v));

        cuerpoTabla.style.opacity    = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/carga-archivos?${params}`, {
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
                        if (p) cargarArchivos(p);
                    });
                });
            }
        })
        .catch(() => { cuerpoTabla.style.opacity = '1'; });
    }

    if (filtroBuscar) {
        filtroBuscar.addEventListener('input', demorarEjecucion(() => cargarArchivos(1), 320));
    }

    if (dropdownFiltros) {
        dropdownFiltros.addEventListener('filtros:aplicar', () => {
            cargarArchivos(1);
        });
        dropdownFiltros.addEventListener('filtros:limpiar', () => {
            if (filtroBuscar) filtroBuscar.value = '';
            cargarArchivos(1);
        });
    }

    // Paginación carga inicial (SSR)
    if (paginacionDiv) {
        paginacionDiv.querySelectorAll('a.page-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const p = new URL(a.href).searchParams.get('page');
                if (p) cargarArchivos(p);
            });
        });
    }
});