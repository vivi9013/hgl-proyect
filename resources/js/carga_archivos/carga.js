document.addEventListener('DOMContentLoaded', function () {
    // === ELEMENTOS FORMULARIO ===
    const inputNombre = document.getElementById('nombre');
    const selectTipo = document.getElementById('tipo');
    const feedbackDisponibilidad = document.getElementById('feedbackDisponibilidad');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnGuardar = document.getElementById('btnGuardar');

    // === ELEMENTOS PAGINACIÓN ===
    const tbody = document.getElementById('tbodyArchivos');
    const totalBadge = document.getElementById('totalArchivos');
    const infoPaginacion = document.getElementById('infoPaginacion');
    const contenedorPaginacion = document.getElementById('contenedorPaginacion');

    // ─────────────────────────────────────────────────────────
    // LÓGICA 1: VERIFICAR DISPONIBILIDAD DE NOMBRE (TU LOGICA)
    // ─────────────────────────────────────────────────────────
    if (inputNombre && selectTipo) {
        function verificarDisponibilidad() {
            const nombre = inputNombre.value.trim();
            const categoriaId = selectTipo.value;

            if (!nombre || !categoriaId) {
                feedbackDisponibilidad.innerHTML = '';
                if(btnGuardar) btnGuardar.disabled = false;
                return;
            }

            loadingSpinner.style.display = 'block';
            feedbackDisponibilidad.innerHTML = '';

            fetch(`/carga-archivos/verificar-nombre?nombre=${encodeURIComponent(nombre)}&id_catego=${encodeURIComponent(categoriaId)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Error en el servidor');
                return response.json();
            })
            .then(data => {
                loadingSpinner.style.display = 'none';
                if (data.disponible) {
                    feedbackDisponibilidad.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Nombre disponible</span>';
                    inputNombre.classList.remove('is-invalid');
                    inputNombre.classList.add('is-valid');
                    if(btnGuardar) btnGuardar.disabled = false;
                } else {
                    feedbackDisponibilidad.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> El nombre ya existe en esta categoría</span>';
                    inputNombre.classList.remove('is-valid');
                    inputNombre.classList.add('is-invalid');
                    if(btnGuardar) btnGuardar.disabled = true;
                }
            })
            .catch(error => {
                loadingSpinner.style.display = 'none';
                console.error('Error verificando disponibilidad:', error);
            });
        }

        inputNombre.addEventListener('blur', verificarDisponibilidad);
        selectTipo.addEventListener('change', verificarDisponibilidad);
    }

    // ─────────────────────────────────────────────────────────
    // LÓGICA 2: MOTOR DE PAGINACIÓN ASÍNCRONA (AJAX)
    // ─────────────────────────────────────────────────────────
    function cargarPagina(numeroPagina = 1) {
        if (!tbody) return;

        // Efecto visual de carga
        tbody.style.opacity = '0.5';

        fetch(`/carga-archivos?page=${numeroPagina}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text();
        })
        .then(html => {
            tbody.style.opacity = '1';
            tbody.innerHTML = html;

            // Sincronizar transporte de datos
            const elTransporte = document.getElementById('datosPaginacionTransporte');
            
            if (elTransporte) {
                const totalGlobal = parseInt(elTransporte.getAttribute('data-total'));
                const textoInfo = elTransporte.getAttribute('data-info');
                const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;

                if (totalBadge) totalBadge.textContent = `${totalGlobal} ${totalGlobal === 1 ? 'Registro' : 'Registros'}`;
                if (infoPaginacion) infoPaginacion.textContent = textoInfo;
                
                if (contenedorPaginacion) {
                    contenedorPaginacion.innerHTML = htmlLinks;
                    asignarEventosEnlaces();
                }
            }
        })
        .catch(err => {
            tbody.style.opacity = '1';
            console.error('Error paginando módulo de carga:', err);
        });
    }

    function asignarEventosEnlaces() {
        if (!contenedorPaginacion) return;
        const enlaces = contenedorPaginacion.querySelectorAll('a.page-link');
        
        enlaces.forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                
                // Extraemos dinámicamente la página de la URL nativa de Laravel
                const urlObj = new URL(this.href);
                const paginaDestino = urlObj.searchParams.get('page');
                
                if (paginaDestino) {
                    cargarPagina(paginaDestino);
                }
            });
        });
    }

    // Disparar render inicial de botones de paginación al entrar al módulo
    const elTransporteInicial = document.getElementById('datosPaginacionTransporte');
    if (elTransporteInicial && contenedorPaginacion) {
        const htmlLinks = document.getElementById('htmlLinksPaginacion').innerHTML;
        contenedorPaginacion.innerHTML = htmlLinks;
        asignarEventosEnlaces();
    }
});