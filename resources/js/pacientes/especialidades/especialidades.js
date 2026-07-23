/**
 * Especialidad RX - Interacciones Frontend (AJAX CRUD)
 * HGL - Estudios Radiológicos
 */

document.addEventListener('DOMContentLoaded', function () {
    let paginaActual = 1;
    let buscarTerm = '';

    // Elementos de la interfaz
    const tablaBody = document.querySelector('#tablaEspecialidades tbody');
    const buscarInput = document.getElementById('buscar-especialidad');
    const totalBadge = document.getElementById('total-registros-badge');
    const infoPaginacion = document.getElementById('info-paginacion');

    // Modales Bootstrap
    const modalEditarEl = document.getElementById('modalEditar');
    const modalEditar = new bootstrap.Modal(modalEditarEl);

    // Formularios
    const formAlta = document.getElementById('frmAltaEspecialidad');
    const formEditar = document.getElementById('frmEditarEspecialidad');

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Carga inicial
    cargarEspecialidades(1);

    /**
     * ── 1. CARGA ASÍNCRONA (GET) ──
     */
    function cargarEspecialidades(pagina = 1) {
        paginaActual = pagina;
        tablaBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(`/rx-especialidades?q=${encodeURIComponent(buscarTerm)}&page=${pagina}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(res => {
            totalBadge.textContent = `${res.total} ${res.total === 1 ? 'registro' : 'registros'}`;
            
            if (res.data.length === 0) {
                tablaBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fa fa-info-circle me-1"></i> No se encontraron especialidades registradas.
                        </td>
                    </tr>
                `;
                infoPaginacion.textContent = 'Mostrando 0 a 0 de 0 registros';
                document.getElementById('paginador-especialidades').innerHTML = '';
                return;
            }

            tablaBody.innerHTML = '';
            res.data.forEach((item, index) => {
                const filaIndex = (res.current_page - 1) * res.per_page + (index + 1);
                const tr = document.createElement('tr');
                const rowClass = item.activo == 1 ? '' : 'text-muted table-light';
                const iconActivo = item.activo == 1 
                    ? '<i class="fa fa-check-square-o text-success fa-lg" style="cursor:pointer;" title="Desactivar"></i>' 
                    : '<i class="fa fa-square-o text-secondary fa-lg" style="cursor:pointer;" title="Activar"></i>';

                tr.className = rowClass;
                tr.innerHTML = `
                    <td class="ps-4 fw-bold">${filaIndex}</td>
                    <td class="text-center">
                        <button class="btn btn-link btn-sm text-dark p-0 btn-edit" data-id="${item.id_especialidad}" title="Editar Especialidad">
                            <i class="fa fa-pencil-square-o fa-lg text-dark"></i>
                        </button>
                    </td>
                    <td>${item.nombre}</td>
                    <td class="font-monospace fw-bold text-primary">${item.abreviatura}</td>
                    <td>${item.fecha_registro || '—'}</td>
                    <td>${item.hora_registro || '—'}</td>
                    <td class="text-center pe-4">
                        <button class="btn btn-link p-0 btn-toggle-status" data-id="${item.id_especialidad}" data-activo="${item.activo}">
                            ${iconActivo}
                        </button>
                    </td>
                `;
                tablaBody.appendChild(tr);
            });

            // Actualizar texto informativo
            infoPaginacion.textContent = `Mostrando ${res.from} a ${res.to} de ${res.total} registros`;

            // Invocar renderizador de paginador general (helpers.js)
            if (typeof window.renderPaginacion === 'function') {
                window.renderPaginacion(res.links, 'paginador-especialidades', cargarEspecialidades);
            }
        })
        .catch(err => {
            console.error('Error al cargar especialidades:', err);
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        <i class="fa fa-exclamation-triangle me-1"></i> Ocurrió un error al cargar la lista.
                    </td>
                </tr>
            `;
        });
    }

    /**
     * ── 2. BÚSQUEDA CON DEBOUNCE ──
     */
    let debounceTimer = null;
    buscarInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        buscarTerm = this.value;
        debounceTimer = setTimeout(() => {
            cargarEspecialidades(1);
        }, 350);
    });

    /**
     * ── 3. GUARDAR NUEVO REGISTRO (POST) ──
     */
    formAlta.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarErrores(formAlta);

        const nombre = document.getElementById('nombreEsp').value.trim();
        const abreviatura = document.getElementById('abre').value.trim();

        fetch('/rx-especialidades/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nombre, abreviatura })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                if (data.errors) {
                    mostrarErrores(formAlta, data.errors);
                }
                throw new Error(data.message || 'Error al guardar');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Operación Satisfactoria!',
                        text: 'El registro se ha guardado correctamente.',
                        confirmButtonColor: '#000',
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
                formAlta.reset();
                cargarEspecialidades(1);
                document.getElementById('nombreEsp').focus();
            }
        })
        .catch(err => {
            console.error(err);
        });
    });

    /**
     * ── 4. VER Y CARGAR EN EL MODAL DE EDICIÓN (GET) ──
     */
    tablaBody.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-edit');
        if (!btnEdit) return;

        const id = btnEdit.dataset.id;
        limpiarErrores(formEditar);

        fetch(`/rx-especialidades/${id}/edit`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('idRegistro').value = data.id_especialidad;
            document.getElementById('EnombreEsp').value = data.nombre;
            document.getElementById('Eabre').value = data.abreviatura;

            modalEditar.show();
        })
        .catch(err => {
            console.error('Error al cargar datos:', err);
        });
    });

    /**
     * ── 5. ACTUALIZAR REGISTRO (PUT) ──
     */
    formEditar.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarErrores(formEditar);

        const id = document.getElementById('idRegistro').value;
        const nombre = document.getElementById('EnombreEsp').value.trim();
        const abreviatura = document.getElementById('Eabre').value.trim();

        fetch(`/rx-especialidades/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nombre, abreviatura })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                if (data.errors) {
                    mostrarErrores(formEditar, data.errors, true);
                }
                throw new Error(data.message || 'Error al actualizar');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                modalEditar.hide();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Operación Satisfactoria!',
                        text: 'El registro se ha editado correctamente.',
                        confirmButtonColor: '#16a34a',
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
                cargarEspecialidades(paginaActual);
            }
        })
        .catch(err => {
            console.error(err);
        });
    });

    /**
     * ── 6. CAMBIO DE ESTATUS (PATCH) ──
     */
    tablaBody.addEventListener('click', function (e) {
        const btnStatus = e.target.closest('.btn-toggle-status');
        if (!btnStatus) return;

        const id = btnStatus.dataset.id;
        const activo = btnStatus.dataset.activo;

        if (typeof Swal === 'undefined') {
            procederCambioEstatus(id);
            return;
        }

        Swal.fire({
            title: 'Cambiar estatus',
            text: '¿Está seguro de querer cambiar el estatus del registro?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡cambiar!',
            cancelButtonText: 'No, cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                procederCambioEstatus(id);
            }
        });
    });

    function procederCambioEstatus(id) {
        fetch(`/rx-especialidades/${id}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Estatus Actualizado!',
                        text: data.message,
                        confirmButtonColor: '#000',
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
                cargarEspecialidades(paginaActual);
            }
        })
        .catch(err => {
            console.error('Error al cambiar estatus:', err);
        });
    }

    /**
     * ── HELPERS: MANEJO DE ERRORES DE VALIDACIÓN ──
     */
    function mostrarErrores(form, errors, esEditar = false) {
        const prefijo = esEditar ? 'feedback-editar-' : 'feedback-';
        
        if (errors.nombre) {
            const inputNombre = form.querySelector(esEditar ? '#EnombreEsp' : '#nombreEsp');
            const feedNombre = form.querySelector(`#${prefijo}nombre`);
            if (inputNombre && feedNombre) {
                inputNombre.classList.add('is-invalid');
                feedNombre.textContent = errors.nombre[0];
            }
        }

        if (errors.abreviatura) {
            const inputAbre = form.querySelector(esEditar ? '#Eabre' : '#abre');
            const feedAbre = form.querySelector(`#${prefijo}abreviatura`);
            if (inputAbre && feedAbre) {
                inputAbre.classList.add('is-invalid');
                feedAbre.textContent = errors.abreviatura[0];
            }
        }
    }

    function limpiarErrores(form) {
        form.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(div => {
            div.textContent = '';
        });
    }
});
