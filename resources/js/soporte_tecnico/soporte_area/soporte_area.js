document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS DE SESIÓN (SweetAlert2)
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text:  alertaExitog.dataset.message || 'Los cambios se han guardado correctamente.',
            icon:  'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar',
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. MÓDULO INDEX — Paginación AJAX + Buscador + Filtros dropdown
    // ─────────────────────────────────────────────────────────────────────────
    const cuerpoTabla    = document.getElementById('cuerpoTablaSoporte');
    const infoPaginacion = document.getElementById('infoPaginacionSoporte');
    const paginacionDiv  = document.getElementById('paginacionSoporte');
    const filtroBuscar   = document.getElementById('filtro-buscar');
    const dropdownFiltros= document.getElementById('dropdownFiltros');

    function demorarEjecucion(fn, ms) {
        let timer;
        return function (...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), ms); };
    }

    function obtenerFiltros() {
        return {
            buscar:  filtroBuscar?.value.trim() ?? '',
            estatus: Array.from(document.querySelectorAll('.chk-estatus:checked')).map(el => el.value),
            areas:   Array.from(document.querySelectorAll('.chk-areas:checked')).map(el => el.value),
        };
    }

    function cargarTrabajadores(pagina = 1) {
        if (!cuerpoTabla) return;
        const f = obtenerFiltros();
        const params = new URLSearchParams({ page: pagina });
        if (f.buscar) params.set('buscar', f.buscar);
        f.estatus.forEach(v => params.append('estatus[]', v));
        f.areas.forEach(v   => params.append('areas[]', v));

        cuerpoTabla.style.opacity    = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/soporte-tecnico/areas?${params}`, {
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
                        if (p) cargarTrabajadores(p);
                    });
                });
            }
        })
        .catch(() => { cuerpoTabla.style.opacity = '1'; });
    }

    // Listener buscador con debounce
    if (filtroBuscar) {
        filtroBuscar.addEventListener('input', demorarEjecucion(() => cargarTrabajadores(1), 320));
    }

    // Listeners del dropdown
    if (dropdownFiltros) {
        dropdownFiltros.addEventListener('filtros:aplicar', () => cargarTrabajadores(1));
        dropdownFiltros.addEventListener('filtros:limpiar', () => {
            if (filtroBuscar) filtroBuscar.value = '';
            cargarTrabajadores(1);
        });
    }

    // Paginación inicial SSR
    if (paginacionDiv) {
        paginacionDiv.querySelectorAll('a.page-link').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const p = new URL(a.href).searchParams.get('page');
                if (p) cargarTrabajadores(p);
            });
        });
    }

    // Listener para cambiar status del trabajador
    if (cuerpoTabla) {
        cuerpoTabla.addEventListener('click', function (e) {
            const btnStatus = e.target.closest('.btn-toggle-status');
            if (!btnStatus) return;

            e.preventDefault();
            const id = btnStatus.dataset.id;
            if (!id) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            fetch(`/soporte-tecnico/areas/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(datos => {
                if (datos.success) {
                    cargarTrabajadores();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Estado Actualizado!',
                            text: datos.message,
                            icon: 'success',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    }
                }
            })
            .catch(() => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'No se pudo cambiar el estado.', 'error');
                }
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C. MÓDULO ASIGNAR ÁREAS
    //    - Marcar / Desmarcar todos
    //    - Resaltar fila al hacer clic en checkbox
    //    - Habilitar / deshabilitar botón Guardar según cambios
    // ─────────────────────────────────────────────────────────────────────────
    const btnMarcarTodos   = document.getElementById('btnMarcarTodos');
    const btnDesmarcarTodos= document.getElementById('btnDesmarcarTodos');
    const btnGuardar       = document.getElementById('btnGuardar');
    const formAsignar      = document.getElementById('formAsignarAreas');

    // Estado inicial de checkboxes (para detectar cambios)
    let estadoInicial = {};

    function registrarEstadoInicial() {
        document.querySelectorAll('.chk-area').forEach(chk => {
            estadoInicial[chk.value] = chk.checked;
        });
    }

    function hayModificaciones() {
        let cambio = false;
        document.querySelectorAll('.chk-area').forEach(chk => {
            if (chk.checked !== estadoInicial[chk.value]) cambio = true;
        });
        return cambio;
    }

    function actualizarEstadoBtnGuardar() {
        if (!btnGuardar) return;
        if (hayModificaciones()) {
            btnGuardar.removeAttribute('disabled');
        } else {
            btnGuardar.setAttribute('disabled', '');
        }
    }

    function actualizarFilaVisual(chk) {
        const filaId = chk.dataset.fila;
        const fila   = filaId ? document.getElementById(filaId) : null;
        if (!fila) return;
        fila.classList.toggle('table-primary', chk.checked);
    }

    // Registrar estado inicial
    registrarEstadoInicial();

    // Listener individual por checkbox
    document.querySelectorAll('.chk-area').forEach(chk => {
        chk.addEventListener('change', function () {
            actualizarFilaVisual(this);
            actualizarEstadoBtnGuardar();
        });
    });

    // Marcar todos
    if (btnMarcarTodos) {
        btnMarcarTodos.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.chk-area').forEach(chk => {
                chk.checked = true;
                actualizarFilaVisual(chk);
            });
            actualizarEstadoBtnGuardar();
        });
    }

    // Desmarcar todos
    if (btnDesmarcarTodos) {
        btnDesmarcarTodos.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.chk-area').forEach(chk => {
                chk.checked = false;
                actualizarFilaVisual(chk);
            });
            actualizarEstadoBtnGuardar();
        });
    }

    // Confirmación antes de guardar
    if (formAsignar) {
        formAsignar.addEventListener('submit', function (e) {
            e.preventDefault();
            const seleccionadas = document.querySelectorAll('.chk-area:checked').length;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Confirmar cambios?',
                    html:  `Se guardarán <strong>${seleccionadas}</strong> área(s) asignada(s) al técnico.`,
                    icon:  'question',
                    showCancelButton:   true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor:  '#6c757d',
                    confirmButtonText:  'Sí, guardar',
                    cancelButtonText:   'Cancelar',
                }).then(res => {
                    if (res.isConfirmed) this.submit();
                });
            } else {
                if (confirm(`¿Confirmar? Se asignarán ${seleccionadas} área(s).`)) this.submit();
            }
        });
    }

});
