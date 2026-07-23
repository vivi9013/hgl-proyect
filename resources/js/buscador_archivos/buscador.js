document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. REFERENCIAS AL DOM
    // ─────────────────────────────────────────────────────────────────────────
    const cuerpoTabla     = document.getElementById('cuerpoTablaArchivos');
    const infoPaginacion  = document.getElementById('infoPaginacionArchivos');
    const paginacionDiv   = document.getElementById('paginacionArchivos');
    const filtroBuscar    = document.getElementById('filtro-buscar');
    const dropdownFiltros = document.getElementById('dropdownFiltros');

    // ─────────────────────────────────────────────────────────────────────────
    // B. HELPER — debounce
    // ─────────────────────────────────────────────────────────────────────────
    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C. TABLA — filtros (buscar + categorías) + paginación AJAX
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

        fetch(`/buscador-archivos/filtrar?${params}`, {
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