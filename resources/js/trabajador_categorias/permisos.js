/**
 * Lógica Javascript para el módulo de Permisos de Archivos con Persistencia entre Paginación
 */

document.addEventListener('DOMContentLoaded', function () {
    // === SELECTORES GLOBAL DE ESTADOS (MEMORIA) ===
    const categoriasSeleccionadas = new Set();

    // El formulario y contenedor de inputs
    const formAsignar = document.getElementById('formAsignarCategorias');
    const inputsDestinoOcultos = document.getElementById('inputsDestinoOcultos');

    // Nodos de la tabla asíncrona (solo una de las dos existe en cada página)
    const tbody = document.getElementById('tbodyAsignacion');
    const tbodyTrabajadores = document.getElementById('tbodyTrabajadores');
    const totalTrabajadoresBadge = document.getElementById('totalTrabajadores');
    const filtroBuscar = document.getElementById('filtro-buscar');
    const contador = document.getElementById('contadorSeleccionados');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');

    const btnMarcarTodos = document.getElementById('btnMarcarTodos');
    const btnDesmarcarTodos = document.getElementById('btnDesmarcarTodos');

    // SweetAlert2 Inicializadores estándar del sistema
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({ title: '¡Operación Satisfactoria!', text: alertaExitog.getAttribute('data-mensaje') || 'El registro se ha guardado correctamente.', icon: 'success', confirmButtonColor: '#3085d6', confirmButtonText: 'Aceptar' });
    }
    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({ title: '¡Operación Satisfactoria!', text: alertaExito.getAttribute('data-mensaje') || 'El registro se ha actualizado correctamente.', icon: 'success', confirmButtonColor: '#3085d6', confirmButtonText: 'Aceptar' });
    }

    // ─────────────────────────────────────────────────────────
    // HELPER — debounce
    // ─────────────────────────────────────────────────────────
    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    // ─────────────────────────────────────────────────────────
    // GESTIÓN DE MEMORIA VIRTUAL DE CHECKBOXES (solo vista Asignar)
    // ─────────────────────────────────────────────────────────
    function inicializarMemoriaDesdeDOM() {
        if (!tbody) return;
        // Captura el estado cargado inicialmente por Laravel
        tbody.querySelectorAll('.chk-permiso').forEach(chk => {
            if (chk.checked) categoriasSeleccionadas.add(chk.value);
        });
        actualizarContadorInterfaz();
    }

    function actualizarContadorInterfaz() {
        if (contador) contador.textContent = categoriasSeleccionadas.size;
    }

    function sincronizarCheckboxesVisuales() {
        if (!tbody) return;
        tbody.querySelectorAll('.chk-permiso').forEach(chk => {
            const row = chk.closest('.fila-categoria');
            if (categoriasSeleccionadas.has(chk.value)) {
                chk.checked = true;
                if (row) row.classList.add('table-success-soft');
            } else {
                chk.checked = false;
                if (row) row.classList.remove('table-success-soft');
            }
        });
    }

    function enlazarEventosCheckboxes() {
        if (!tbody) return;
        tbody.querySelectorAll('.chk-permiso').forEach(chk => {
            chk.addEventListener('change', function () {
                const row = this.closest('.fila-categoria');
                if (this.checked) {
                    categoriasSeleccionadas.add(this.value);
                    if (row) row.classList.add('table-success-soft');
                } else {
                    categoriasSeleccionadas.delete(this.value);
                    if (row) row.classList.remove('table-success-soft');
                }
                actualizarContadorInterfaz();
            });
        });
    }

    // ─────────────────────────────────────────────────────────
    // ACCIONES EN MASA (MARCAR / DESMARCAR POR PÁGINA)
    // ─────────────────────────────────────────────────────────
    if (btnMarcarTodos) {
        btnMarcarTodos.addEventListener('click', function (e) {
            e.preventDefault();
            if (!tbody) return;
            tbody.querySelectorAll('.chk-permiso').forEach(chk => {
                chk.checked = true;
                categoriasSeleccionadas.add(chk.value);
                const row = chk.closest('.fila-categoria');
                if (row) row.classList.add('table-success-soft');
            });
            actualizarContadorInterfaz();
        });
    }

    if (btnDesmarcarTodos) {
        btnDesmarcarTodos.addEventListener('click', function (e) {
            e.preventDefault();
            if (!tbody) return;
            tbody.querySelectorAll('.chk-permiso').forEach(chk => {
                chk.checked = false;
                categoriasSeleccionadas.delete(chk.value);
                const row = chk.closest('.fila-categoria');
                if (row) row.classList.remove('table-success-soft');
            });
            actualizarContadorInterfaz();
        });
    }

    // ─────────────────────────────────────────────────────────
    // MOTOR DE PAGINACIÓN + FILTROS AJAX (respuesta JSON estándar)
    // ─────────────────────────────────────────────────────────
    function obtenerFiltros() {
        return { buscar: filtroBuscar?.value.trim() ?? '' };
    }

    function asignarEventosEnlaces(cargarPagina) {
        if (!contenedorPaginacion) return;
        contenedorPaginacion.querySelectorAll('a.page-link').forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                const p = new URL(this.href).searchParams.get('page');
                if (p) cargarPagina(p);
            });
        });
    }

    // Vista Asignar (matriz de categorías de un trabajador)
    function cargarPaginaAsignacion(pagina = 1) {
        if (!tbody) return;
        const f = obtenerFiltros();
        const params = new URLSearchParams({ page: pagina });
        if (f.buscar) params.set('buscar', f.buscar);

        tbody.style.opacity = '0.4';
        tbody.style.transition = 'opacity 0.2s';

        fetch(`${window.location.pathname}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(datos => {
            tbody.style.opacity = '1';
            tbody.innerHTML = datos.html;
            if (infoPaginacion) infoPaginacion.textContent = datos.info;
            if (contenedorPaginacion) {
                contenedorPaginacion.innerHTML = datos.links;
                asignarEventosEnlaces(cargarPaginaAsignacion);
            }
            // Sincronizar UI con la memoria virtual y volver a registrar listeners
            sincronizarCheckboxesVisuales();
            enlazarEventosCheckboxes();
        })
        .catch(() => { tbody.style.opacity = '1'; });
    }

    // Vista Index (lista de trabajadores)
    function cargarPaginaTrabajadores(pagina = 1) {
        if (!tbodyTrabajadores) return;
        const f = obtenerFiltros();
        const params = new URLSearchParams({ page: pagina });
        if (f.buscar) params.set('buscar', f.buscar);

        tbodyTrabajadores.style.opacity = '0.4';
        tbodyTrabajadores.style.transition = 'opacity 0.2s';

        fetch(`/permisos-archivo?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(datos => {
            tbodyTrabajadores.style.opacity = '1';
            tbodyTrabajadores.innerHTML = datos.html;
            if (totalTrabajadoresBadge) totalTrabajadoresBadge.textContent = `${datos.total} ${datos.total === 1 ? 'Registro' : 'Registros'}`;
            if (infoPaginacion) infoPaginacion.textContent = datos.info;
            if (contenedorPaginacion) {
                contenedorPaginacion.innerHTML = datos.links;
                asignarEventosEnlaces(cargarPaginaTrabajadores);
            }
        })
        .catch(() => { tbodyTrabajadores.style.opacity = '1'; });
    }

    // Listener de filtro (debounce) — dispara la carga que corresponda a la página actual
    if (filtroBuscar) {
        filtroBuscar.addEventListener('input', demorarEjecucion(() => {
            if (tbodyTrabajadores) cargarPaginaTrabajadores(1);
            else if (tbody) cargarPaginaAsignacion(1);
        }, 320));
    }

    // Paginación inicial (SSR): los enlaces ya vienen del render de Laravel al cargar la página
    if (contenedorPaginacion) {
        if (tbodyTrabajadores) asignarEventosEnlaces(cargarPaginaTrabajadores);
        else if (tbody) asignarEventosEnlaces(cargarPaginaAsignacion);
    }

    // ─────────────────────────────────────────────────────────
    // PREPARACIÓN DE ENVÍO DE DATOS POST (INYECCIÓN DE CHECKS)
    // ─────────────────────────────────────────────────────────
    if (formAsignar) {
        formAsignar.addEventListener('submit', function () {
            if (inputsDestinoOcultos) inputsDestinoOcultos.innerHTML = '';
            categoriasSeleccionadas.forEach(idCategoria => {
                const inputHidden = document.createElement('input');
                inputHidden.type = 'hidden';
                inputHidden.name = 'categorias[]';
                inputHidden.value = idCategoria;
                if (inputsDestinoOcultos) inputsDestinoOcultos.appendChild(inputHidden);
            });
        });
    }

    // Inicialización al cargar la página
    inicializarMemoriaDesdeDOM();
    enlazarEventosCheckboxes();
});