/**
 * Radiología RX – Controladores e Interacciones Frontend (Pacientes y Estudios)
 */

document.addEventListener('DOMContentLoaded', function () {
    // ── CONFIGURACIONES DE PAGINACIÓN Y BÚSQUEDA ──────────
    let paginaPaciente = 1;
    let buscarPacienteTerm = '';
    let paginaEstudio = 1;
    let buscarEstudioTerm = '';

    // Elementos de la interfaz
    const tablaPacientesBody = document.querySelector('#tabla-pacientes tbody');
    const tablaEstudiosBody = document.querySelector('#tabla-estudios tbody');
    const buscarPacienteInput = document.getElementById('buscar-paciente');
    const buscarEstudioInput = document.getElementById('buscar-estudio');

    // Modales Bootstrap
    const modalPacienteEl = document.getElementById('modalPaciente');
    const modalPaciente = new bootstrap.Modal(modalPacienteEl);
    const modalEstudioEl = document.getElementById('modalEstudio');
    const modalEstudio = new bootstrap.Modal(modalEstudioEl);
    const modalDetalleEl = document.getElementById('modalDetalleEstudio');
    const modalDetalle = new bootstrap.Modal(modalDetalleEl);

    // Switches de Pacientes (NHC / SP)
    const tieneNhcSwitch = document.getElementById('tiene_nhc');
    const tieneSpSwitch = document.getElementById('tiene_sp');
    const nhcWrapper = document.querySelector('.input-nhc-wrapper');
    const spWrapper = document.querySelector('.input-sp-wrapper');

    // ── EVENTOS DE FORMULARIO DE INTERACTIVIDAD DE NHC/SP ─────
    tieneNhcSwitch.addEventListener('change', function () {
        if (this.checked) {
            nhcWrapper.classList.remove('d-none');
            document.getElementById('paciente-nhc-hgl').setAttribute('required', 'true');
        } else {
            nhcWrapper.classList.add('d-none');
            document.getElementById('paciente-nhc-hgl').removeAttribute('required');
            document.getElementById('paciente-nhc-hgl').value = '';
        }
    });

    tieneSpSwitch.addEventListener('change', function () {
        if (this.checked) {
            spWrapper.classList.remove('d-none');
            document.getElementById('paciente-sp').setAttribute('required', 'true');
        } else {
            spWrapper.classList.add('d-none');
            document.getElementById('paciente-sp').removeAttribute('required');
            document.getElementById('paciente-sp').value = '';
        }
    });

    // ── CALCULAR TOTAL DE ESTUDIOS SELECCIONADOS ──────────
    const checkboxesEstudio = document.querySelectorAll('.checkbox-estudio');
    const badgeTotalEstudios = document.getElementById('badge-total-estudios');

    function actualizarTotalEstudios() {
        let total = 0;
        checkboxesEstudio.forEach(cb => {
            if (cb.checked) total++;
        });
        badgeTotalEstudios.textContent = total;
    }

    checkboxesEstudio.forEach(cb => {
        cb.addEventListener('change', actualizarTotalEstudios);
    });

    // ── CARGA ASÍNCRONA DE PACIENTES ─────────────────────
    function cargarPacientes(pagina = 1) {
        paginaPaciente = pagina;
        tablaPacientesBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(`/mRXestudios?q=${encodeURIComponent(buscarPacienteTerm)}&page=${pagina}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(res => {
            document.getElementById('total-pacientes-badge').textContent = `Total: ${res.total} pacientes`;
            
            if (res.data.length === 0) {
                tablaPacientesBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle-fill me-1"></i> No se encontraron pacientes registrados.
                        </td>
                    </tr>
                `;
                document.getElementById('paginacion-pacientes').innerHTML = '';
                return;
            }

            tablaPacientesBody.innerHTML = '';
            res.data.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-3 fw-bold text-dark">${p.nhc_hgl || '<span class="text-muted">Ninguno</span>'}</td>
                    <td class="fw-semibold">${p.nombre} ${p.ap_paterno} ${p.ap_materno || ''}</td>
                    <td><span class="badge ${p.sexo === 'M' ? 'bg-primary-subtle text-primary' : (p.sexo === 'F' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary')} px-2.5 py-1 rounded-pill">${p.sexo === 'M' ? 'Masculino' : (p.sexo === 'F' ? 'Femenino' : 'Otro')}</span></td>
                    <td>${p.fecha_nacimiento || '<span class="text-muted">No registrada</span>'}</td>
                    <td class="font-monospace small">${p.rfc ? p.rfc + (p.homoclave || '') : '<span class="text-muted">Ninguno</span>'}</td>
                    <td><span class="badge ${p.sp ? 'bg-success' : 'bg-secondary'}">${p.sp || 'Ninguno'}</span></td>
                    <td>${p.telefono && p.telefono !== '0' ? p.telefono : '<span class="text-muted">---</span>'}</td>
                    <td class="text-center pe-3">
                        <div class="d-flex justify-content-center gap-1.5">
                            <button class="btn btn-outline-primary btn-sm px-2.5 py-1 btn-estudio" data-id="${p.id_paciente}" title="Registrar Estudio/Cita">
                                <i class="bi bi-file-medical-fill"></i> Estudio
                            </button>
                            <button class="btn btn-outline-dark btn-sm px-2 py-1 btn-edit-paciente" data-id="${p.id_paciente}" title="Editar Expediente">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm px-2 py-1 btn-del-paciente" data-id="${p.id_paciente}" title="Eliminar Paciente">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </td>
                `;
                tablaPacientesBody.appendChild(tr);
            });

            renderPaginacion(res.links, 'paginacion-pacientes', cargarPacientes);
        })
        .catch(err => {
            console.error('Error al cargar pacientes:', err);
            tablaPacientesBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Ocurrió un error al cargar la lista de pacientes.
                    </td>
                </tr>
            `;
        });
    }

    // ── CARGA ASÍNCRONA DE ESTUDIOS ──────────────────────
    function cargarEstudios(pagina = 1) {
        paginaEstudio = pagina;
        tablaEstudiosBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(`/mRXestudios/estudios?q=${encodeURIComponent(buscarEstudioTerm)}&page=${pagina}`)
        .then(res => res.json())
        .then(res => {
            document.getElementById('total-estudios-badge').textContent = `Total: ${res.total} estudios`;
            
            if (res.data.length === 0) {
                tablaEstudiosBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle-fill me-1"></i> No se encontraron estudios registrados.
                        </td>
                    </tr>
                `;
                document.getElementById('paginacion-estudios').innerHTML = '';
                return;
            }

            tablaEstudiosBody.innerHTML = '';
            res.data.forEach(e => {
                // Generar lista de regiones
                let regiones = [];
                if (e.craneo) regiones.push('Cráneo');
                if (e.tx) regiones.push('Tórax');
                if (e.abd) regiones.push('Abdomen');
                if (e.col) regiones.push('Columna');
                if (e.m_sup) regiones.push('Miembro Sup.');
                if (e.m_inf) regiones.push('Miembro Inf.');
                if (e.contraste) regiones.push('Contraste');

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-3 fw-bold text-dark">${e.fecha_estudio}</td>
                    <td class="font-monospace small fw-semibold">${e.nhc || '---'}</td>
                    <td class="fw-semibold">${e.nombre} ${e.ap_paterno} ${e.ap_materno || ''}</td>
                    <td><span class="badge bg-info text-dark rounded-pill px-2.5 py-1">${e.hgl}</span></td>
                    <td>
                        <span class="small font-monospace text-muted" style="font-size:0.8rem;">
                            ${regiones.join(', ') || 'Sin región'}
                        </span>
                    </td>
                    <td class="text-truncate fw-medium text-primary" style="max-width:180px;" title="${e.especificado}">${e.especificado || '---'}</td>
                    <td><span class="badge bg-secondary rounded-circle px-2 py-1">${e.total_cds || 0}</span></td>
                    <td class="small fw-semibold text-muted">${e.medico_rx ? e.medico_rx.nombre : 'No especificado'}</td>
                    <td class="text-center pe-3">
                        <div class="d-flex justify-content-center gap-1.5">
                            <button class="btn btn-outline-success btn-sm px-2 py-1 btn-ver-estudio" data-id="${e.id_estudios}" title="Ver detalles del Estudio">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                            <button class="btn btn-outline-dark btn-sm px-2 py-1 btn-edit-estudio" data-id="${e.id_estudios}" title="Editar Datos de Estudio">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm px-2 py-1 btn-del-estudio" data-id="${e.id_estudios}" title="Eliminar Registro de Estudio">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </td>
                `;
                tablaEstudiosBody.appendChild(tr);
            });

            renderPaginacion(res.links, 'paginacion-estudios', cargarEstudios);
        })
        .catch(err => {
            console.error('Error al cargar estudios:', err);
            tablaEstudiosBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Ocurrió un error al cargar la lista de estudios.
                    </td>
                </tr>
            `;
        });
    }

    // ── RENDERIZAR BOTONES DE PAGINACIÓN DINÁMICOS ────────
    function renderPaginacion(links, elementId, callback) {
        const paginador = document.getElementById(elementId);
        if (!links || links.length <= 3) {
            paginador.innerHTML = '';
            return;
        }

        let html = '<nav aria-label="Navegación"><ul class="pagination pagination-sm mb-0">';
        links.forEach(l => {
            const pageNum = l.url ? new URL(l.url).searchParams.get('page') : null;
            const activeClass = l.active ? 'active' : '';
            const disabledClass = !l.url ? 'disabled' : '';

            // Traducir "Previous" y "Next"
            let label = l.label;
            if (label.includes('Previous')) label = '&laquo;';
            if (label.includes('Next')) label = '&raquo;';

            html += `
                <li class="page-item ${activeClass} ${disabledClass}">
                    <a class="page-link py-1.5 px-3 rounded-2 shadow-none border-0 ms-1" href="#" data-page="${pageNum}">${label}</a>
                </li>
            `;
        });
        html += '</ul></nav>';
        paginador.innerHTML = html;

        // Añadir listeners
        paginador.querySelectorAll('.page-link').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                if (page && page !== 'null') {
                    callback(parseInt(page));
                }
            });
        });
    }

    // ── EVENTOS DE BÚSQUEDA EN TIEMPO REAL ─────────────────
    let debouncePaciente = null;
    buscarPacienteInput.addEventListener('input', function () {
        clearTimeout(debouncePaciente);
        buscarPacienteTerm = this.value;
        debouncePaciente = setTimeout(() => {
            cargarPacientes(1);
        }, 350);
    });

    let debounceEstudio = null;
    buscarEstudioInput.addEventListener('input', function () {
        clearTimeout(debounceEstudio);
        buscarEstudioTerm = this.value;
        debounceEstudio = setTimeout(() => {
            cargarEstudios(1);
        }, 350);
    });

    // ── CRUD PACIENTES (CREAR Y EDITAR) ────────────────────
    const formPaciente = document.getElementById('form-paciente');
    const btnGuardarPaciente = document.getElementById('btn-guardar-paciente');
    const spinnerPaciente = btnGuardarPaciente.querySelector('.spinner-border');
    const textPaciente = document.getElementById('text-guardar-paciente');

    // Resetear formulario para registrar nuevo
    document.getElementById('btn-nuevo-paciente').addEventListener('click', function () {
        formPaciente.reset();
        document.getElementById('paciente-id').value = '';
        document.getElementById('modalPacienteLabel').innerHTML = '<i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Paciente';
        nhcWrapper.classList.add('d-none');
        document.getElementById('paciente-nhc-hgl').removeAttribute('required');
        spWrapper.classList.add('d-none');
        document.getElementById('paciente-sp').removeAttribute('required');
    });

    // Enviar Formulario
    formPaciente.addEventListener('submit', function (e) {
        e.preventDefault();
        
        btnGuardarPaciente.setAttribute('disabled', 'true');
        spinnerPaciente.classList.remove('d-none');
        textPaciente.textContent = 'Guardando...';

        const id = document.getElementById('paciente-id').value;
        const url = id ? `/mRXestudios/pacientes/actualizar/${id}` : '/mRXestudios/pacientes/guardar';

        const formData = new FormData(formPaciente);
        if (id) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            btnGuardarPaciente.removeAttribute('disabled');
            spinnerPaciente.classList.add('d-none');
            textPaciente.textContent = 'Guardar Paciente';

            if (res.errors) {
                // Capturar validaciones del backend
                const errors = Object.values(res.errors).flat().join('\n');
                alert('Errores de validación:\n' + errors);
                return;
            }

            if (res.success) {
                modalPaciente.hide();
                alert(res.message);
                cargarPacientes(paginaPaciente);
            }
        })
        .catch(err => {
            console.error('Error al guardar paciente:', err);
            btnGuardarPaciente.removeAttribute('disabled');
            spinnerPaciente.classList.add('d-none');
            textPaciente.textContent = 'Guardar Paciente';
            alert('Ocurrió un error inesperado al procesar la solicitud.');
        });
    });

    // Abrir Modal de Edición de Paciente
    tablaPacientesBody.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-edit-paciente');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id');
            fetch(`/mRXestudios/pacientes/ver/${id}`)
            .then(res => res.json())
            .then(p => {
                document.getElementById('paciente-id').value = p.id_paciente;
                document.getElementById('paciente-nombre').value = p.nombre;
                document.getElementById('paciente-ap-paterno').value = p.ap_paterno;
                document.getElementById('paciente-ap-materno').value = p.ap_materno || '';
                document.getElementById('paciente-sexo').value = p.sexo;
                document.getElementById('paciente-fecha-nacimiento').value = p.fecha_nacimiento || '';
                document.getElementById('paciente-telefono').value = p.telefono || '';
                document.getElementById('paciente-domicilio').value = p.domicilio || '';
                document.getElementById('paciente-rfc').value = p.rfc || '';
                document.getElementById('paciente-homoclave').value = p.homoclave || '';

                // NHC Switch
                if (p.tiene_nhc || p.nhc_hgl) {
                    tieneNhcSwitch.checked = true;
                    nhcWrapper.classList.remove('d-none');
                    document.getElementById('paciente-nhc-hgl').value = p.nhc_hgl || '';
                    document.getElementById('paciente-nhc-hgl').setAttribute('required', 'true');
                } else {
                    tieneNhcSwitch.checked = false;
                    nhcWrapper.classList.add('d-none');
                    document.getElementById('paciente-nhc-hgl').value = '';
                    document.getElementById('paciente-nhc-hgl').removeAttribute('required');
                }

                // SP Switch
                if (p.tiene_sp || p.sp) {
                    tieneSpSwitch.checked = true;
                    spWrapper.classList.remove('d-none');
                    document.getElementById('paciente-sp').value = p.sp || '';
                    document.getElementById('paciente-sp').setAttribute('required', 'true');
                } else {
                    tieneSpSwitch.checked = false;
                    spWrapper.classList.add('d-none');
                    document.getElementById('paciente-sp').value = '';
                    document.getElementById('paciente-sp').removeAttribute('required');
                }

                document.getElementById('modalPacienteLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Datos del Paciente';
                modalPaciente.show();
            });
        }
    });

    // Eliminar Paciente (Lógico)
    tablaPacientesBody.addEventListener('click', function (e) {
        const btnDel = e.target.closest('.btn-del-paciente');
        if (btnDel) {
            const id = btnDel.getAttribute('data-id');
            if (confirm('¿Estás totalmente seguro de que deseas eliminar este paciente de los expedientes?')) {
                fetch(`/mRXestudios/pacientes/eliminar/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        alert(res.message);
                        cargarPacientes(paginaPaciente);
                    }
                });
            }
        }
    });

    // ── CRUD ESTUDIOS (REGISTRAR DESDE PACIENTE O EDITAR) ──────
    const formEstudio = document.getElementById('form-estudio');
    const btnGuardarEstudio = document.getElementById('btn-guardar-estudio');
    const spinnerEstudio = btnGuardarEstudio.querySelector('.spinner-border');
    const textEstudio = document.getElementById('text-guardar-estudio');

    // Registrar Estudio para Paciente Específico
    tablaPacientesBody.addEventListener('click', function (e) {
        const btnEstudio = e.target.closest('.btn-estudio');
        if (btnEstudio) {
            const id = btnEstudio.getAttribute('data-id');
            fetch(`/mRXestudios/pacientes/ver/${id}`)
            .then(res => res.json())
            .then(p => {
                formEstudio.reset();
                document.getElementById('estudio-id').value = '';
                
                // Mostrar datos informativos
                const fullNombre = `${p.nombre} ${p.ap_paterno} ${p.ap_materno || ''}`;
                document.getElementById('estudio-paciente-display').textContent = fullNombre;
                document.getElementById('estudio-nhc-display').textContent = p.nhc_hgl || 'Ninguno';
                document.getElementById('estudio-nacimiento-display').textContent = p.fecha_nacimiento || 'No registrada';
                document.getElementById('estudio-sexo-display').textContent = p.sexo === 'M' ? 'Masculino' : (p.sexo === 'F' ? 'Femenino' : 'Otro');

                // Llenar campos ocultos sincronizados
                document.getElementById('estudio-nhc').value = p.nhc_hgl || '0';
                document.getElementById('estudio-nombre').value = p.nombre;
                document.getElementById('estudio-ap-paterno').value = p.ap_paterno;
                document.getElementById('estudio-ap-materno').value = p.ap_materno || '';
                document.getElementById('estudio-nacimiento').value = p.fecha_nacimiento || '';
                document.getElementById('estudio-sexo').value = p.sexo;
                document.getElementById('estudio-sp').value = p.sp || '0';

                // Resetear checkboxes y total
                checkboxesEstudio.forEach(cb => cb.checked = false);
                badgeTotalEstudios.textContent = '0';

                document.getElementById('modalEstudioLabel').innerHTML = '<i class="bi bi-journal-plus me-2"></i>Registrar Cita / Estudio de Radiología';
                modalEstudio.show();
            });
        }
    });

    // Guardar Estudio (Crear/Editar)
    formEstudio.addEventListener('submit', function (e) {
        e.preventDefault();

        // Validar que se haya seleccionado al menos una región anatómica
        let totalChecked = 0;
        checkboxesEstudio.forEach(cb => {
            if (cb.checked) totalChecked++;
        });
        if (totalChecked === 0) {
            alert('Debes seleccionar al menos un estudio / región anatómica.');
            return;
        }

        btnGuardarEstudio.setAttribute('disabled', 'true');
        spinnerEstudio.classList.remove('d-none');
        textEstudio.textContent = 'Guardando...';

        const id = document.getElementById('estudio-id').value;
        const url = id ? `/mRXestudios/estudios/actualizar/${id}` : '/mRXestudios/estudios/guardar';

        const formData = new FormData(formEstudio);
        if (id) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            btnGuardarEstudio.removeAttribute('disabled');
            spinnerEstudio.classList.add('d-none');
            textEstudio.textContent = 'Guardar Estudio';

            if (res.errors) {
                const errors = Object.values(res.errors).flat().join('\n');
                alert('Errores de validación:\n' + errors);
                return;
            }

            if (res.success) {
                modalEstudio.hide();
                alert(res.message);
                
                // Mover a la pestaña de Estudios para ver el registro
                const estudioTabBtn = document.getElementById('estudios-tab');
                const triggerEl = bootstrap.Tab.getInstance(estudioTabBtn) || new bootstrap.Tab(estudioTabBtn);
                triggerEl.show();

                cargarEstudios(paginaEstudio);
            }
        })
        .catch(err => {
            console.error('Error al guardar estudio:', err);
            btnGuardarEstudio.removeAttribute('disabled');
            spinnerEstudio.classList.add('d-none');
            textEstudio.textContent = 'Guardar Estudio';
            alert('Ocurrió un error inesperado.');
        });
    });

    // Abrir Modal de Edición de Estudio
    tablaEstudiosBody.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-edit-estudio');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id');
            fetch(`/mRXestudios/estudios/ver/${id}`)
            .then(res => res.json())
            .then(est => {
                formEstudio.reset();
                document.getElementById('estudio-id').value = est.id_estudios;

                // Datos del paciente al estudio
                const fullNombre = `${est.nombre} ${est.ap_paterno} ${est.ap_materno || ''}`;
                document.getElementById('estudio-paciente-display').textContent = fullNombre;
                document.getElementById('estudio-nhc-display').textContent = est.nhc || 'Ninguno';
                document.getElementById('estudio-nacimiento-display').textContent = est.nacimiento || 'No registrada';
                document.getElementById('estudio-sexo-display').textContent = est.sexo === 'M' ? 'Masculino' : (est.sexo === 'F' ? 'Femenino' : 'Otro');

                // Llenar campos ocultos
                document.getElementById('estudio-nhc').value = est.nhc || '0';
                document.getElementById('estudio-nombre').value = est.nombre;
                document.getElementById('estudio-ap-paterno').value = est.ap_paterno;
                document.getElementById('estudio-ap-materno').value = est.ap_materno || '';
                document.getElementById('estudio-nacimiento').value = est.nacimiento || '';
                document.getElementById('estudio-sexo').value = est.sexo;
                document.getElementById('estudio-sp').value = est.sp || '0';

                // Llenar campos propios del estudio
                document.getElementById('estudio-fecha-estudio').value = est.fecha_estudio;
                document.getElementById('estudio-hgl').value = est.hgl;
                document.getElementById('estudio-total-cds').value = est.total_cds || 0;
                document.getElementById('estudio-especificado').value = est.especificado || '';
                document.getElementById('estudio-especialidad').value = est.especialidad;
                document.getElementById('estudio-medico').value = est.medico;
                document.getElementById('estudio-otros-datos').value = est.otros_datos || '';

                // Activar checkboxes correspondientes
                document.getElementById('est-craneo').checked = !!est.craneo;
                document.getElementById('est-tx').checked = !!est.tx;
                document.getElementById('est-abd').checked = !!est.abd;
                document.getElementById('est-col').checked = !!est.col;
                document.getElementById('est-m-sup').checked = !!est.m_sup;
                document.getElementById('est-m-inf').checked = !!est.m_inf;
                document.getElementById('est-contraste').checked = !!est.contraste;

                actualizarTotalEstudios();

                document.getElementById('modalEstudioLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Datos del Estudio';
                modalEstudio.show();
            });
        }
    });

    // Eliminar Estudio (Lógico)
    tablaEstudiosBody.addEventListener('click', function (e) {
        const btnDel = e.target.closest('.btn-del-estudio');
        if (btnDel) {
            const id = btnDel.getAttribute('data-id');
            if (confirm('¿Estás totalmente seguro de que deseas eliminar este registro de estudio de radiología?')) {
                fetch(`/mRXestudios/estudios/eliminar/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value
                    }
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        alert(res.message);
                        cargarEstudios(paginaEstudio);
                    }
                });
            }
        }
    });

    // ── VER DETALLES DE ESTUDIO ───────────────────────────
    tablaEstudiosBody.addEventListener('click', function (e) {
        const btnVer = e.target.closest('.btn-ver-estudio');
        if (btnVer) {
            const id = btnVer.getAttribute('data-id');
            fetch(`/mRXestudios/estudios/ver/${id}`)
            .then(res => res.json())
            .then(est => {
                // Llenar campos del modal de detalle
                document.getElementById('det-paciente').textContent = `${est.nombre} ${est.ap_paterno} ${est.ap_materno || ''}`;
                document.getElementById('det-nhc').textContent = est.nhc || 'Ninguno';
                document.getElementById('det-edad').textContent = est.edad || '---';
                document.getElementById('det-sexo').textContent = est.sexo === 'M' ? 'Masculino' : (est.sexo === 'F' ? 'Femenino' : 'Otro');
                document.getElementById('det-sp').textContent = est.sp && est.sp !== '0' ? est.sp : 'Ninguno';

                document.getElementById('det-fecha').textContent = est.fecha_estudio;
                document.getElementById('det-origen').textContent = est.hgl;
                document.getElementById('det-cds').textContent = est.total_cds || 0;
                document.getElementById('det-especificado').textContent = est.especificado || 'Ninguno';

                document.getElementById('det-especialidad').textContent = est.especialidad_rx ? est.especialidad_rx.nombre : 'No especificado';
                document.getElementById('det-medico').textContent = est.medico_rx ? est.medico_rx.nombre : 'No especificado';
                document.getElementById('det-usuario').textContent = est.creador ? est.creador.nombre_usuario : '---';
                document.getElementById('det-fecha-registro').textContent = est.fecha_registro || '---';
                document.getElementById('det-hora-registro').textContent = est.hora_registro || '';

                document.getElementById('det-total-estudios').textContent = est.total_estudios || 0;

                // Crear badges de regiones anatómicas
                const badgesContainer = document.getElementById('det-badges-regiones');
                badgesContainer.innerHTML = '';
                
                const regiones = [
                    { key: 'craneo', name: 'Cráneo', color: 'bg-primary' },
                    { key: 'tx', name: 'Tórax (TX)', color: 'bg-success' },
                    { key: 'abd', name: 'Abdomen (ABD)', color: 'bg-info text-dark' },
                    { key: 'col', name: 'Columna', color: 'bg-warning text-dark' },
                    { key: 'm_sup', name: 'Miembro Superior', color: 'bg-danger' },
                    { key: 'm_inf', name: 'Miembro Inferior', color: 'bg-secondary' },
                    { key: 'contraste', name: 'Con Contraste', color: 'bg-dark' },
                ];

                let algunaSeleccionada = false;
                regiones.forEach(r => {
                    if (est[r.key]) {
                        algunaSeleccionada = true;
                        const span = document.createElement('span');
                        span.className = `badge ${r.color} px-3 py-2 rounded-2`;
                        span.textContent = r.name;
                        badgesContainer.appendChild(span);
                    }
                });

                if (!algunaSeleccionada) {
                    badgesContainer.innerHTML = '<span class="text-muted small">Ninguna región anatómica registrada.</span>';
                }

                document.getElementById('det-notas').textContent = est.otros_datos || 'Sin observaciones registradas.';

                modalDetalle.show();
            });
        }
    });

    // ── LISTENERS DE PESTAÑAS (TABS) PARA RECARGAR LISTAS ──
    const pacientesTabBtn = document.getElementById('pacientes-tab');
    const estudiosTabBtn = document.getElementById('estudios-tab');

    pacientesTabBtn.addEventListener('shown.bs.tab', function () {
        cargarPacientes(1);
    });

    estudiosTabBtn.addEventListener('shown.bs.tab', function () {
        cargarEstudios(1);
    });

    // ── INICIALIZACIÓN POR DEFECTO ─────────────────────────
    cargarPacientes(1);
});
