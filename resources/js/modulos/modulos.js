import Chart from 'chart.js/auto';

/**
 * modulos.js — Lógica del módulo de Gestión de Módulos (Estandarizado en Español).
 * Cubre: previsualización en tiempo real, paginación AJAX, alternar estado,
 * seleccionar todos en proyectos y perfiles, y carga de gráficas.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    // A. ALERTAS SWEETALERT2 DE SESIÓN
    // ─────────────────────────────────────────────────────────────────────────
    const alertaExitoGuardar = document.getElementById('alertaExitog');
    const alertaExitoActualizar  = document.getElementById('alertaExito');

    if (alertaExitoGuardar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoGuardar.dataset.message || 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExitoActualizar && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitoActualizar.dataset.message || 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // A.2 SECCIÓN DESTACADA DESDE URL Y COMPORTAMIENTO DE COLAPSABLES
    // ─────────────────────────────────────────────────────────────────────────
    // 1. Manejo de Chevrons al expandir/colapsar las tarjetas
    document.querySelectorAll('.collapse').forEach(colapsableEl => {
        colapsableEl.addEventListener('show.bs.collapse', function () {
            const tarjeta = this.closest('.card');
            if (tarjeta) {
                const chevron = tarjeta.querySelector('.collapse-chevron');
                if (chevron) {
                    chevron.classList.remove('fa-chevron-down');
                    chevron.classList.add('fa-chevron-up');
                }
            }
        });
        colapsableEl.addEventListener('hide.bs.collapse', function () {
            const tarjeta = this.closest('.card');
            if (tarjeta) {
                const chevron = tarjeta.querySelector('.collapse-chevron');
                if (chevron) {
                    chevron.classList.remove('fa-chevron-up');
                    chevron.classList.add('fa-chevron-down');
                }
            }
        });
    });

    // 2. Expandir sección según parámetro de consulta (seccion=proyectos, seccion=perfiles, etc.)
    const parametrosUrl = new URLSearchParams(window.location.search);
    const parametroSeccion = parametrosUrl.get('seccion');
    if (parametroSeccion) {
        const idColapsableDestino = 'collapse' + parametroSeccion.charAt(0).toUpperCase() + parametroSeccion.slice(1);
        const colapsableDestino = document.getElementById(idColapsableDestino);
        if (colapsableDestino) {
            // Cerrar colapsarDatos si está abierto y no es la sección solicitada
            if (parametroSeccion !== 'datos') {
                const colapsarDatos = document.getElementById('collapseDatos');
                if (colapsarDatos && colapsarDatos.classList.contains('show')) {
                    const disparadorDatos = document.querySelector('[data-bs-target="#collapseDatos"]');
                    if (disparadorDatos) {
                        disparadorDatos.click();
                    }
                }
            }
            // Abrir la sección deseada
            if (!colapsableDestino.classList.contains('show')) {
                const disparadorDestino = document.querySelector(`[data-bs-target="#${idColapsableDestino}"]`);
                if (disparadorDestino) {
                    disparadorDestino.click();
                    // Hacer scroll suave hacia la sección seleccionada
                    setTimeout(() => {
                        colapsableDestino.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                }
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. PREVISUALIZACIÓN EN TIEMPO REAL (Ficha de Registro y Edición)
    // ─────────────────────────────────────────────────────────────────────────
    const entradaNombre = document.getElementById('nombre');
    const seleccionColor = document.getElementById('color');
    const entradaIcono = document.getElementById('icono');
    const entradaDescripcion = document.getElementById('descripcion');
    const entradaCarpeta = document.getElementById('carpeta');
    const entradaCreador = document.getElementById('creador');

    // Elementos del widget de vista previa (Small Box)
    const vistaPreviaTarjeta = document.getElementById('vistaPreviaTarjeta');
    const vistaPreviaNombre = document.getElementById('vistaPreviaNombre');
    const vistaPreviaIcono = document.getElementById('vistaPreviaIcono');

    // Elementos de la lista informativa en la Edición
    const vistaPreviaCarpetaTexto = document.getElementById('vistaPreviaCarpetaTexto');
    const vistaPreviaColorTexto = document.getElementById('vistaPreviaColorTexto');
    const vistaPreviaCreadorTexto = document.getElementById('vistaPreviaCreadorTexto');

    function actualizarVistaPrevia() {
        const nombreValor = entradaNombre ? entradaNombre.value.trim() : '';
        const descripcionValor = entradaDescripcion ? entradaDescripcion.value.trim() : '';
        const colorValor = seleccionColor ? seleccionColor.value.trim() : 'blue';
        const iconoValor = entradaIcono ? entradaIcono.value.trim() : 'fa fa-cube';
        const carpetaValor = entradaCarpeta ? entradaCarpeta.value.trim() : '';
        const creadorValor = entradaCreador ? entradaCreador.value.trim() : '';

        // 1. Actualizar el color de la tarjeta (Small Box)
        if (vistaPreviaTarjeta) {
            vistaPreviaTarjeta.className.split(/\s+/).forEach(clase => {
                if (clase.startsWith('bg-')) {
                    vistaPreviaTarjeta.classList.remove(clase);
                }
            });
            vistaPreviaTarjeta.classList.add(`bg-${colorValor}`);
        }

        // 2. Actualizar el nombre en la vista previa
        if (vistaPreviaNombre) {
            vistaPreviaNombre.textContent = nombreValor || 'Nombre del módulo';
        }

        // 3. Actualizar el icono en la vista previa
        if (vistaPreviaIcono) {
            const clasesIcono = iconoValor.split(/\s+/).filter(Boolean);
            vistaPreviaIcono.className = '';
            if (clasesIcono.length > 0) {
                clasesIcono.forEach(c => vistaPreviaIcono.classList.add(c));
            } else {
                vistaPreviaIcono.classList.add('fa', 'fa-cube');
            }
        }

        // 4. Actualizar textos informativos inferiores (si existen en el DOM)
        if (vistaPreviaCarpetaTexto) {
            vistaPreviaCarpetaTexto.textContent = carpetaValor || 'nombre-carpeta';
        }
        if (vistaPreviaColorTexto) {
            vistaPreviaColorTexto.textContent = colorValor;
        }
        if (vistaPreviaCreadorTexto) {
            vistaPreviaCreadorTexto.textContent = creadorValor || 'Autor';
        }
    }

    if (entradaNombre) entradaNombre.addEventListener('input', actualizarVistaPrevia);
    if (entradaIcono) {
        entradaIcono.addEventListener('keyup', actualizarVistaPrevia);
        entradaIcono.addEventListener('change', actualizarVistaPrevia);
    }
    if (entradaDescripcion) entradaDescripcion.addEventListener('input', actualizarVistaPrevia);
    if (seleccionColor)      seleccionColor.addEventListener('change', actualizarVistaPrevia);
    if (entradaCarpeta)     entradaCarpeta.addEventListener('input', actualizarVistaPrevia);
    if (entradaCreador)     entradaCreador.addEventListener('input', actualizarVistaPrevia);

    // Inicializar previsualización al cargar
    actualizarVistaPrevia();

    // ─────────────────────────────────────────────────────────────────────────
    // B.2 CARGA DINÁMICA DE SUBMÓDULOS DE LA CATEGORÍA SELECCIONADA (Alta)
    // ─────────────────────────────────────────────────────────────────────────
    const seleccionCategoria = document.getElementById('id_CategoriaModulo');
    const contenedorVistaPreviaCategoria = document.getElementById('contenedorVistaPreviaCategoria');
    const contenidoVistaPreviaCategoria = document.getElementById('contenidoVistaPreviaCategoria');

    function cargarSubmodulosCategoria() {
        if (!seleccionCategoria || !contenedorVistaPreviaCategoria) return;

        const idCategoria = seleccionCategoria.value;
        if (!idCategoria) {
            contenedorVistaPreviaCategoria.classList.add('d-none');
            if (contenidoVistaPreviaCategoria) contenidoVistaPreviaCategoria.innerHTML = '';
            return;
        }

        contenedorVistaPreviaCategoria.classList.remove('d-none');
        if (contenidoVistaPreviaCategoria) {
            contenidoVistaPreviaCategoria.innerHTML = '<div class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin me-2"></i>Cargando submódulos...</div>';
        }

        fetch(`/modulos/categoria-preview?idCategoria=${idCategoria}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(respuesta => {
            if (!respuesta.ok) throw new Error('Error al obtener la vista previa');
            return respuesta.text();
        })
        .then(html => {
            if (contenidoVistaPreviaCategoria) {
                contenidoVistaPreviaCategoria.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('[modulos] Error al cargar preview de categoría:', error);
            if (contenidoVistaPreviaCategoria) {
                contenidoVistaPreviaCategoria.innerHTML = '<div class="text-danger py-2 text-center"><i class="fa fa-exclamation-triangle me-1"></i>Error al cargar los submódulos.</div>';
            }
        });
    }

    if (seleccionCategoria) {
        seleccionCategoria.addEventListener('change', cargarSubmodulosCategoria);
        cargarSubmodulosCategoria();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C. TABLA: PAGINACIÓN ASÍNCRONA Y BÚSQUEDA
    // ─────────────────────────────────────────────────────────────────────────
    const cuerpoTabla = document.getElementById('cuerpoTablaModulos');
    const infoPaginacionElemento = document.getElementById('infoPaginacion');
    const contenedorPaginas = document.getElementById('contenedorPaginacion');
    const entradaBusqueda = document.getElementById('busqueda-global');
    const etiquetaTotal = document.getElementById('totalModulos');

    function cargarPagina(numeroPagina = 1) {
        if (!cuerpoTabla) return;

        const textoBusqueda = entradaBusqueda ? entradaBusqueda.value.trim() : '';
        cuerpoTabla.style.opacity = '0.4';
        cuerpoTabla.style.transition = 'opacity 0.2s';

        fetch(`/modulos?buscar=${encodeURIComponent(textoBusqueda)}&page=${numeroPagina}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(respuesta => {
            if (!respuesta.ok) throw new Error('Error en el servidor');
            return respuesta.json();
        })
        .then(datos => {
            cuerpoTabla.style.opacity = '1';
            cuerpoTabla.innerHTML = datos.html;

            if (etiquetaTotal)            etiquetaTotal.textContent = `${datos.total} Registros`;
            if (infoPaginacionElemento)    infoPaginacionElemento.textContent = datos.info;
            if (contenedorPaginas)  {
                contenedorPaginas.innerHTML = datos.links;
                asignarEventosPaginacion();
            }

            enlazarAlternarEstado();
        })
        .catch(error => {
            cuerpoTabla.style.opacity = '1';
            console.error('[modulos] Error al paginar:', error);
        });
    }

    function asignarEventosPaginacion() {
        if (!contenedorPaginas) return;
        contenedorPaginas.querySelectorAll('a.page-link').forEach(enlace => {
            enlace.addEventListener('click', function (e) {
                e.preventDefault();
                const url = new URL(this.href);
                const pagina = url.searchParams.get('page');
                if (pagina) cargarPagina(pagina);
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. ALTERNAR ESTADO (botones .btn-alternar-estado en la tabla)
    // ─────────────────────────────────────────────────────────────────────────
    function enlazarAlternarEstado() {
        document.querySelectorAll('.btn-alternar-estado').forEach(boton => {
            const clon = boton.cloneNode(true);
            boton.parentNode.replaceChild(clon, boton);

            clon.addEventListener('click', function (e) {
                e.preventDefault();
                const idRegistro = this.dataset.id;
                const fila = this.closest('tr');
                const nombreModulo = fila?.querySelector('.col-nombre-modulo')?.textContent.trim() ?? '';
                const estaActivo = fila?.classList.contains('text-muted') ? 0 : 1;
                const accionTexto = estaActivo === 1 ? 'desactivar' : 'activar';

                const ejecutarAccion = () => {
                    const tokenCsrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    fetch(`/modulos/${idRegistro}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': tokenCsrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(respuesta => respuesta.json())
                    .then(datos => {
                        if (datos.success) {
                            const paginaActiva = contenedorPaginas
                                ?.querySelector('.page-item.active .page-link')
                                ?.textContent?.trim() ?? '1';
                            cargarPagina(paginaActiva);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: '¡Estado actualizado!',
                                    text: datos.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        }
                    })
                    .catch(() => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `¿${accionTexto.charAt(0).toUpperCase() + accionTexto.slice(1)} módulo?`,
                        text: `"${nombreModulo}" será ${accionTexto}do del sistema.`,
                        icon: estaActivo === 1 ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: `Sí, ${accionTexto}`,
                        cancelButtonText: 'Cancelar'
                    }).then(resultado => { if (resultado.isConfirmed) ejecutarAccion(); });
                } else {
                    if (confirm(`¿${accionTexto} el módulo "${nombreModulo}"?`)) ejecutarAccion();
                }
            });
        });
    }

    // Retardo para la entrada de búsqueda
    function demorarEjecucion(funcion, milisegundos) {
        let temporizador;
        return function (...argumentos) {
            clearTimeout(temporizador);
            temporizador = setTimeout(() => funcion.apply(this, argumentos), milisegundos);
        };
    }

    if (entradaBusqueda) {
        entradaBusqueda.addEventListener('input', demorarEjecucion(() => cargarPagina(1), 320));
    }

    // Inicialización: Solo si el cuerpo de la tabla existe en la vista
    if (cuerpoTabla) {
        const paginaInicial = contenedorPaginas
            ?.querySelector('.page-item.active .page-link')
            ?.textContent?.trim() ?? '1';
        cargarPagina(paginaInicial);
        asignarEventosPaginacion();
    } else {
        // En vistas donde no hay tabla (edición unificada), se enlazan los eventos alternar estado
        enlazarAlternarEstado();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. VISTA PROYECTOS: SELECCIONAR / DESELECCIONAR TODOS
    // ─────────────────────────────────────────────────────────────────────────
    const seleccionarTodosProyectos = document.getElementById('seleccionarTodosProyectos');
    const btnSeleccionarTodosProyectos = document.getElementById('btnSeleccionarTodosProyectos');
    const btnDeseleccionarTodosProyectos = document.getElementById('btnDeseleccionarTodosProyectos');
    const casillasProyecto = () => document.querySelectorAll('.casilla-proyecto');

    function actualizarFilasProyecto() {
        casillasProyecto().forEach(casilla => {
            const fila = casilla.closest('tr');
            if (!fila) return;
            if (casilla.checked) {
                fila.classList.add('table-success');
            } else {
                fila.classList.remove('table-success');
            }
        });
    }

    if (seleccionarTodosProyectos) {
        seleccionarTodosProyectos.addEventListener('change', function () {
            casillasProyecto().forEach(casilla => { casilla.checked = this.checked; });
            actualizarFilasProyecto();
        });
    }

    casillasProyecto().forEach(casilla => {
        casilla.addEventListener('change', actualizarFilasProyecto);
    });

    if (btnSeleccionarTodosProyectos) {
        btnSeleccionarTodosProyectos.addEventListener('click', () => {
            casillasProyecto().forEach(casilla => { casilla.checked = true; });
            if (seleccionarTodosProyectos) seleccionarTodosProyectos.checked = true;
            actualizarFilasProyecto();
        });
    }

    if (btnDeseleccionarTodosProyectos) {
        btnDeseleccionarTodosProyectos.addEventListener('click', () => {
            casillasProyecto().forEach(casilla => { casilla.checked = false; });
            if (seleccionarTodosProyectos) seleccionarTodosProyectos.checked = false;
            actualizarFilasProyecto();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // F. VISTA PERFILES: SELECCIONAR / DESELECCIONAR TODOS
    // ─────────────────────────────────────────────────────────────────────────
    const seleccionarTodosPerfiles = document.getElementById('seleccionarTodosPerfiles');
    const btnSeleccionarTodosPerfiles = document.getElementById('btnSeleccionarTodosPerfiles');
    const btnDeseleccionarTodosPerfiles = document.getElementById('btnDeseleccionarTodosPerfiles');
    const casillasPerfil = () => document.querySelectorAll('.casilla-perfil');

    function actualizarFilasPerfiles() {
        casillasPerfil().forEach(casilla => {
            const fila = casilla.closest('tr');
            if (!fila) return;
            if (casilla.checked) {
                fila.classList.add('table-success');
            } else {
                fila.classList.remove('table-success');
            }
        });
    }

    if (seleccionarTodosPerfiles) {
        seleccionarTodosPerfiles.addEventListener('change', function () {
            casillasPerfil().forEach(casilla => { casilla.checked = this.checked; });
            actualizarFilasPerfiles();
        });
    }

    casillasPerfil().forEach(casilla => {
        casilla.addEventListener('change', actualizarFilasPerfiles);
    });

    if (btnSeleccionarTodosPerfiles) {
        btnSeleccionarTodosPerfiles.addEventListener('click', () => {
            casillasPerfil().forEach(casilla => { casilla.checked = true; });
            if (seleccionarTodosPerfiles) seleccionarTodosPerfiles.checked = true;
            actualizarFilasPerfiles();
        });
    }

    if (btnDeseleccionarTodosPerfiles) {
        btnDeseleccionarTodosPerfiles.addEventListener('click', () => {
            casillasPerfil().forEach(casilla => { casilla.checked = false; });
            if (seleccionarTodosPerfiles) seleccionarTodosPerfiles.checked = false;
            actualizarFilasPerfiles();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // G. GRÁFICAS DE MÓDULOS (Chart.js)
    // ─────────────────────────────────────────────────────────────────────────
    const elementoDatos = document.getElementById('datos-graficas');
    if (elementoDatos) {
        const colores = [
            "#3498db", "#2ecc71", "#e74c3c", "#f1c40f", "#9b59b6",
            "#1abc9c", "#e67e22", "#34495e", "#16a085", "#27ae60",
            "#2980b9", "#8e44ad", "#d35400", "#c0392b", "#7f8c8d"
        ];

        // Parsear datos desde attributes
        const datosCategoriasObj = JSON.parse(elementoDatos.dataset.categorias || '{}');
        const datosProyectosObj = JSON.parse(elementoDatos.dataset.proyectos || '{}');
        const datosPerfilesObj = JSON.parse(elementoDatos.dataset.perfiles || '{}');

        const etiquetasCategorias = Object.keys(datosCategoriasObj);
        const valoresCategorias = Object.values(datosCategoriasObj);

        const etiquetasProyectos = Object.keys(datosProyectosObj);
        const valoresProyectos = Object.values(datosProyectosObj);

        const etiquetasPerfiles = Object.keys(datosPerfilesObj);
        const valoresPerfiles = Object.values(datosPerfilesObj);

        // 1. Dona Categorías
        const etiquetaDonaCategoria = document.getElementById('etiquetaDonaCategoria');
        const valorDonaCategoria = document.getElementById('valorDonaCategoria');
        if (etiquetasCategorias.length > 0) {
            if (etiquetaDonaCategoria) etiquetaDonaCategoria.textContent = etiquetasCategorias[0];
            if (valorDonaCategoria)    valorDonaCategoria.textContent = valoresCategorias[0];
        }

        const contextoDonaCategoria = document.getElementById('donaCategoria');
        if (contextoDonaCategoria) {
            new Chart(contextoDonaCategoria.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: etiquetasCategorias,
                    datasets: [{ data: valoresCategorias, backgroundColor: colores.slice(0, etiquetasCategorias.length), borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    onHover: function(event, elementosGrafico) {
                        if (elementosGrafico && elementosGrafico.length > 0 && etiquetaDonaCategoria && valorDonaCategoria) {
                            etiquetaDonaCategoria.textContent = etiquetasCategorias[elementosGrafico[0].index];
                            valorDonaCategoria.textContent = valoresCategorias[elementosGrafico[0].index];
                        }
                    }
                }
            });
        }

        // 2. Barras Categorías
        const contextoBarraCategoria = document.getElementById('barraCategoria');
        if (contextoBarraCategoria) {
            new Chart(contextoBarraCategoria.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: etiquetasCategorias,
                    datasets: [{
                        label: 'Módulos',
                        data: valoresCategorias,
                        backgroundColor: '#e67e22',
                        borderRadius: 4,
                        maxBarThickness: 35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                    }
                }
            });
        }

        // 3. Dona Proyectos
        const etiquetaDonaProyecto = document.getElementById('etiquetaDonaProyecto');
        const valorDonaProyecto = document.getElementById('valorDonaProyecto');
        if (etiquetasProyectos.length > 0) {
            if (etiquetaDonaProyecto) etiquetaDonaProyecto.textContent = etiquetasProyectos[0];
            if (valorDonaProyecto)    valorDonaProyecto.textContent = valoresProyectos[0];
        }

        const contextoDonaProyecto = document.getElementById('donaProyecto');
        if (contextoDonaProyecto) {
            new Chart(contextoDonaProyecto.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: etiquetasProyectos,
                    datasets: [{ data: valoresProyectos, backgroundColor: colores.slice(2, 2 + etiquetasProyectos.length), borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    onHover: function(event, elementosGrafico) {
                        if (elementosGrafico && elementosGrafico.length > 0 && etiquetaDonaProyecto && valorDonaProyecto) {
                            etiquetaDonaProyecto.textContent = etiquetasProyectos[elementosGrafico[0].index];
                            valorDonaProyecto.textContent = valoresProyectos[elementosGrafico[0].index];
                        }
                    }
                }
            });
        }

        // 4. Barras Proyectos
        const contextoBarraProyecto = document.getElementById('barraProyecto');
        if (contextoBarraProyecto) {
            new Chart(contextoBarraProyecto.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: etiquetasProyectos,
                    datasets: [{
                        label: 'Módulos',
                        data: valoresProyectos,
                        backgroundColor: '#3498db',
                        borderRadius: 4,
                        maxBarThickness: 35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                    }
                }
            });
        }

        // 5. Dona Perfiles
        const etiquetaDonaPerfil = document.getElementById('etiquetaDonaPerfil');
        const valorDonaPerfil = document.getElementById('valorDonaPerfil');
        if (etiquetasPerfiles.length > 0) {
            if (etiquetaDonaPerfil) etiquetaDonaPerfil.textContent = etiquetasPerfiles[0];
            if (valorDonaPerfil)    valorDonaPerfil.textContent = valoresPerfiles[0];
        }

        const contextoDonaPerfil = document.getElementById('donaPerfil');
        if (contextoDonaPerfil) {
            new Chart(contextoDonaPerfil.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: etiquetasPerfiles,
                    datasets: [{ data: valoresPerfiles, backgroundColor: colores.slice(4, 4 + etiquetasPerfiles.length), borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    onHover: function(event, elementosGrafico) {
                        if (elementosGrafico && elementosGrafico.length > 0 && etiquetaDonaPerfil && valorDonaPerfil) {
                            etiquetaDonaPerfil.textContent = etiquetasPerfiles[elementosGrafico[0].index];
                            valorDonaPerfil.textContent = valoresPerfiles[elementosGrafico[0].index];
                        }
                    }
                }
            });
        }

        // 6. Barras Perfiles
        const contextoBarraPerfil = document.getElementById('barraPerfil');
        if (contextoBarraPerfil) {
            new Chart(contextoBarraPerfil.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: etiquetasPerfiles,
                    datasets: [{
                        label: 'Módulos',
                        data: valoresPerfiles,
                        backgroundColor: '#2ecc71',
                        borderRadius: 4,
                        maxBarThickness: 35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf2f7' } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                    }
                }
            });
        }
    }

});
