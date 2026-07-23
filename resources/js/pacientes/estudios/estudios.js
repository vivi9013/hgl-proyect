/**
 * Radiología RX – Controladores e Interacciones Frontend (Pacientes y Estudios)
 * Modernizado para usar paginador global y SweetAlert2.
 */

import * as bootstrap from 'bootstrap';

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

    // Modales Bootstrap (Lazy init)
    let _modalPaciente = null;
    function getModalPaciente() {
        if (!_modalPaciente) {
            const el = document.getElementById('modalPaciente');
            if (el) _modalPaciente = new bootstrap.Modal(el);
        }
        return _modalPaciente;
    }

    let _modalEstudio = null;
    function getModalEstudio() {
        if (!_modalEstudio) {
            const el = document.getElementById('modalEstudio');
            if (el) _modalEstudio = new bootstrap.Modal(el);
        }
        return _modalEstudio;
    }

    let _modalDetalle = null;
    function getModalDetalle() {
        if (!_modalDetalle) {
            const el = document.getElementById('modalDetalleEstudio');
            if (el) _modalDetalle = new bootstrap.Modal(el);
        }
        return _modalDetalle;
    }

    // Switches de Pacientes (NHC / SP)
    const tieneNhcSwitch = document.getElementById('tiene_nhc');
    const tieneSpSwitch = document.getElementById('tiene_sp');
    const nhcWrapper = document.querySelector('.input-nhc-wrapper');
    const spWrapper = document.querySelector('.input-sp-wrapper');

    // ── EVENTOS DE FORMULARIO DE INTERACTIVIDAD DE NHC/SP ─────
    if (tieneNhcSwitch) {
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
    }

    if (tieneSpSwitch) {
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
    }

    // ── CALCULAR TOTAL DE ESTUDIOS SELECCIONADOS ──────────
    const checkboxesEstudio = document.querySelectorAll('.checkbox-estudio');
    const badgeTotalEstudios = document.getElementById('badge-total-estudios');

    function actualizarTotalEstudios() {
        let total = 0;
        checkboxesEstudio.forEach(cb => {
            if (cb.checked) total++;
        });
        if (badgeTotalEstudios) {
            badgeTotalEstudios.textContent = total;
        }
    }

    checkboxesEstudio.forEach(cb => {
        cb.addEventListener('change', actualizarTotalEstudios);
    });

    // ── CARGA ASÍNCRONA DE PACIENTES ─────────────────────
    function cargarPacientes(pagina = 1) {
        paginaPaciente = pagina;
        if (!tablaPacientesBody) return;

        tablaPacientesBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(`/rx-estudios?q=${encodeURIComponent(buscarPacienteTerm)}&page=${pagina}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(res => {
            const badge = document.getElementById('total-pacientes-badge');
            if (badge) badge.textContent = `Total: ${res.total} pacientes`;
            
            if (!res.data || res.data.length === 0) {
                tablaPacientesBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle-fill me-1"></i> No se encontraron pacientes registrados.
                        </td>
                    </tr>
                `;
                const pag = document.getElementById('paginacion-pacientes');
                if (pag) pag.innerHTML = '';
                return;
            }

            tablaPacientesBody.innerHTML = '';
            res.data.forEach(p => {
                const tr = document.createElement('tr');
                const sexoBadge = p.sexo === 'M'
                    ? `<span style="background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;">M</span>`
                    : (p.sexo === 'F'
                        ? `<span style="background:#fce7f3;color:#be185d;border:1px solid #f9a8d4;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;">F</span>`
                        : `<span style="background:#f3f4f6;color:#6b7280;border:1px solid #d1d5db;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;">O</span>`);
                const rfcFull = p.rfc ? (p.rfc + (p.homoclave || '')) : null;
                const spText  = p.sp && p.sp !== '0' ? p.sp : null;
                const telText = p.telefono && p.telefono !== '0' ? p.telefono : null;

                tr.innerHTML = `
                    <td class="ps-3" style="overflow:hidden;">
                        <div style="font-weight:600;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${p.nombre} ${p.ap_paterno} ${p.ap_materno || ''}">${p.nombre} ${p.ap_paterno} ${p.ap_materno || ''}</div>
                        <div style="font-size:0.72rem;color:#6b7280;font-family:monospace;margin-top:1px;">NHC: ${p.nhc_hgl || '—'}</div>
                    </td>
                    <td>${sexoBadge}</td>
                    <td style="font-size:0.82rem;color:#374151;white-space:nowrap;">${p.fecha_nacimiento || '<span style="color:#9ca3af;">—</span>'}</td>
                    <td style="overflow:hidden;">
                        ${rfcFull
                            ? `<span style="font-family:monospace;font-size:0.78rem;color:#374151;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${rfcFull}">${rfcFull}</span>`
                            : `<span style="color:#9ca3af;font-size:0.78rem;">—</span>`}
                    </td>
                    <td style="overflow:hidden;">
                        ${spText ? `<div style="font-size:0.78rem;font-weight:600;color:#059669;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${spText}">${spText}</div>` : ''}
                        <div style="font-size:0.78rem;color:#6b7280;white-space:nowrap;">${telText || '<span style="color:#9ca3af;">—</span>'}</div>
                    </td>
                    <td class="text-center pe-2">
                        <div class="d-flex justify-content-center align-items-center gap-3">
                            <button class="btn btn-link text-decoration-none text-primary p-0 btn-estudio" data-id="${p.id_paciente}" title="Registrar Estudio/Cita" style="font-weight:600;font-size:0.78rem;white-space:nowrap;">
                                <i class="bi bi-file-medical-fill me-1"></i>Estudio
                            </button>
                            <button class="btn btn-link text-dark p-0 btn-edit-paciente" data-id="${p.id_paciente}" title="Editar Expediente">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-link text-danger p-0 btn-del-paciente" data-id="${p.id_paciente}" title="Eliminar Paciente">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </td>
                `;
                tablaPacientesBody.appendChild(tr);
            });

            // Usar paginador global
            if (typeof window.renderPaginacion === 'function') {
                window.renderPaginacion(res.links, 'paginacion-pacientes', cargarPacientes);
            }
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
        if (!tablaEstudiosBody) return;

        tablaEstudiosBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch(`/rx-estudios/estudios?q=${encodeURIComponent(buscarEstudioTerm)}&page=${pagina}`)
        .then(res => res.json())
        .then(res => {
            const badge = document.getElementById('total-estudios-badge');
            if (badge) badge.textContent = `Total: ${res.total} estudios`;
            
            if (!res.data || res.data.length === 0) {
                tablaEstudiosBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle-fill me-1"></i> No se encontraron estudios registrados.
                        </td>
                    </tr>
                `;
                const pag = document.getElementById('paginacion-estudios');
                if (pag) pag.innerHTML = '';
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

                // Chips de regiones (máx 4 visibles, resto como +N)
                const regionDefs = [
                    { key: 'craneo',    label: 'Cráneo',    color: '#1d4ed8' },
                    { key: 'tx',        label: 'Tórax',     color: '#047857' },
                    { key: 'abd',       label: 'Abd.',      color: '#7c3aed' },
                    { key: 'col',       label: 'Columna',   color: '#b45309' },
                    { key: 'm_sup',     label: 'M.Sup.',    color: '#be185d' },
                    { key: 'm_inf',     label: 'M.Inf.',    color: '#0e7490' },
                    { key: 'contraste', label: 'Contraste', color: '#374151' },
                ];
                const activas = regionDefs.filter(r => e[r.key]);
                const visible = activas.slice(0, 4);
                const resto   = activas.length - visible.length;

                const chipsHtml = visible.map(r =>
                    `<span style="display:inline-block;background:${r.color}18;color:${r.color};border:1px solid ${r.color}55;font-size:0.7rem;font-weight:600;padding:1px 6px;border-radius:4px;white-space:nowrap;">${r.label}</span>`
                ).join(' ');
                const restoHtml = resto > 0
                    ? `<span style="font-size:0.7rem;color:#6b7280;font-weight:600;">+${resto}</span>`
                    : '';
                const especificadoHtml = e.especificado
                    ? `<div style="font-size:0.72rem;color:#6b7280;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px;" title="${e.especificado}">${e.especificado}</div>`
                    : '';

                tr.innerHTML = `
                    <td class="ps-3">
                        <div style="font-size:0.82rem;font-weight:700;color:#111827;">${e.fecha_estudio}</div>
                        <div style="font-size:0.72rem;color:#6b7280;margin-top:1px;">${e.hgl}</div>
                    </td>
                    <td>
                        <div style="font-size:0.85rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${e.nombre} ${e.ap_paterno} ${e.ap_materno || ''}</div>
                        <div style="font-size:0.72rem;color:#6b7280;font-family:monospace;">NHC: ${e.nhc && e.nhc !== '0' ? e.nhc : '—'}</div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">${chipsHtml} ${restoHtml}</div>
                        ${especificadoHtml}
                    </td>
                    <td style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:0.82rem;color:#374151;">
                        ${e.medico_rx ? e.medico_rx.nombre : '<span style="color:#9ca3af;">—</span>'}
                    </td>
                    <td class="text-center">
                        <span style="font-size:0.8rem;font-weight:700;color:#374151;">${e.total_cds || 0}</span>
                    </td>
                    <td class="text-center pe-3">
                        <div class="d-flex justify-content-center align-items-center gap-3">
                            <button class="btn btn-link text-success p-0 btn-ver-estudio" data-id="${e.id_estudios}" title="Ver detalles del Estudio">
                                <i class="bi bi-eye-fill fs-5"></i>
                            </button>
                            <button class="btn btn-link text-dark p-0 btn-edit-estudio" data-id="${e.id_estudios}" title="Editar Datos de Estudio">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </button>
                            <button class="btn btn-link text-danger p-0 btn-del-estudio" data-id="${e.id_estudios}" title="Eliminar Registro de Estudio">
                                <i class="bi bi-trash-fill fs-5"></i>
                            </button>
                        </div>
                    </td>
                `;
                tablaEstudiosBody.appendChild(tr);
            });

            // Usar paginador global
            if (typeof window.renderPaginacion === 'function') {
                window.renderPaginacion(res.links, 'paginacion-estudios', cargarEstudios);
            }
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

    // ── EVENTOS DE BÚSQUEDA EN TIEMPO REAL ─────────────────
    if (buscarPacienteInput) {
        let debouncePaciente = null;
        buscarPacienteInput.addEventListener('input', function () {
            clearTimeout(debouncePaciente);
            buscarPacienteTerm = this.value;
            debouncePaciente = setTimeout(() => {
                cargarPacientes(1);
            }, 350);
        });
    }

    if (buscarEstudioInput) {
        let debounceEstudio = null;
        buscarEstudioInput.addEventListener('input', function () {
            clearTimeout(debounceEstudio);
            buscarEstudioTerm = this.value;
            debounceEstudio = setTimeout(() => {
                cargarEstudios(1);
            }, 350);
        });
    }

    // ── CRUD PACIENTES (CREAR Y EDITAR) ────────────────────
    const formPaciente = document.getElementById('form-paciente');
    const btnGuardarPaciente = document.getElementById('btn-guardar-paciente');
    const spinnerPaciente = btnGuardarPaciente ? btnGuardarPaciente.querySelector('.spinner-border') : null;
    const textPaciente = document.getElementById('text-guardar-paciente');

    // Resetear formulario para registrar nuevo
    const btnNuevoPaciente = document.getElementById('btn-nuevo-paciente');
    if (btnNuevoPaciente) {
        btnNuevoPaciente.addEventListener('click', function () {
            if (formPaciente) formPaciente.reset();
            document.getElementById('paciente-id').value = '';
            document.getElementById('modalPacienteLabel').innerHTML = '<i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Paciente';
            if (nhcWrapper) nhcWrapper.classList.add('d-none');
            document.getElementById('paciente-nhc-hgl').removeAttribute('required');
            if (spWrapper) spWrapper.classList.add('d-none');
            document.getElementById('paciente-sp').removeAttribute('required');
        });
    }

    // Enviar Formulario Paciente
    if (formPaciente) {
        formPaciente.addEventListener('submit', function (e) {
            e.preventDefault();
            
            if (btnGuardarPaciente) btnGuardarPaciente.setAttribute('disabled', 'true');
            if (spinnerPaciente) spinnerPaciente.classList.remove('d-none');
            if (textPaciente) textPaciente.textContent = 'Guardando...';

            const id = document.getElementById('paciente-id').value;
            const url = id ? `/rx-estudios/pacientes/actualizar/${id}` : '/rx-estudios/pacientes/guardar';

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
                if (btnGuardarPaciente) btnGuardarPaciente.removeAttribute('disabled');
                if (spinnerPaciente) spinnerPaciente.classList.add('d-none');
                if (textPaciente) textPaciente.textContent = 'Guardar Paciente';

                if (res.errors) {
                    const errors = Object.values(res.errors).flat().join('<br>');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Errores de validación', html: errors });
                    } else {
                        alert('Errores de validación:\n' + errors.replace(/<br>/g, '\n'));
                    }
                    return;
                }

                if (res.success) {
                    getModalPaciente().hide();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Operación Satisfactoria!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                    cargarPacientes(paginaPaciente);
                }
            })
            .catch(err => {
                console.error('Error al guardar paciente:', err);
                if (btnGuardarPaciente) btnGuardarPaciente.removeAttribute('disabled');
                if (spinnerPaciente) spinnerPaciente.classList.add('d-none');
                if (textPaciente) textPaciente.textContent = 'Guardar Paciente';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error inesperado al procesar la solicitud.' });
                } else {
                    alert('Ocurrió un error inesperado al procesar la solicitud.');
                }
            });
        });
    }

    // Abrir Modal de Edición de Paciente
    if (tablaPacientesBody) {
        tablaPacientesBody.addEventListener('click', function (e) {
            const btnEdit = e.target.closest('.btn-edit-paciente');
            if (btnEdit) {
                const id = btnEdit.getAttribute('data-id');
                fetch(`/rx-estudios/pacientes/ver/${id}`)
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
                        if (tieneNhcSwitch) tieneNhcSwitch.checked = true;
                        if (nhcWrapper) nhcWrapper.classList.remove('d-none');
                        document.getElementById('paciente-nhc-hgl').value = p.nhc_hgl || '';
                        document.getElementById('paciente-nhc-hgl').setAttribute('required', 'true');
                    } else {
                        if (tieneNhcSwitch) tieneNhcSwitch.checked = false;
                        if (nhcWrapper) nhcWrapper.classList.add('d-none');
                        document.getElementById('paciente-nhc-hgl').value = '';
                        document.getElementById('paciente-nhc-hgl').removeAttribute('required');
                    }

                    // SP Switch
                    if (p.tiene_sp || p.sp) {
                        if (tieneSpSwitch) tieneSpSwitch.checked = true;
                        if (spWrapper) spWrapper.classList.remove('d-none');
                        document.getElementById('paciente-sp').value = p.sp || '';
                        document.getElementById('paciente-sp').setAttribute('required', 'true');
                    } else {
                        if (tieneSpSwitch) tieneSpSwitch.checked = false;
                        if (spWrapper) spWrapper.classList.add('d-none');
                        document.getElementById('paciente-sp').value = '';
                        document.getElementById('paciente-sp').removeAttribute('required');
                    }

                    document.getElementById('modalPacienteLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Datos del Paciente';
                    getModalPaciente().show();
                });
            }
        });
    }

    // Eliminar Paciente (Lógico)
    if (tablaPacientesBody) {
        tablaPacientesBody.addEventListener('click', function (e) {
            const btnDel = e.target.closest('.btn-del-paciente');
            if (btnDel) {
                const id = btnDel.getAttribute('data-id');

                const ejecutarEliminar = () => {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                        || document.querySelector('input[name="_token"]')?.value;

                    fetch(`/rx-estudios/pacientes/eliminar/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                alert(res.message);
                            }
                            cargarPacientes(paginaPaciente);
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Estás totalmente seguro?',
                        text: "Esta acción dará de baja al paciente de los expedientes oficiales de radiología.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            ejecutarEliminar();
                        }
                    });
                } else {
                    if (confirm('¿Estás totalmente seguro de que deseas eliminar este paciente de los expedientes?')) {
                        ejecutarEliminar();
                    }
                }
            }
        });
    }

    // ── CRUD ESTUDIOS (REGISTRAR DESDE PACIENTE O EDITAR) ──────
    const formEstudio = document.getElementById('form-estudio');
    const btnGuardarEstudio = document.getElementById('btn-guardar-estudio');
    const spinnerEstudio = btnGuardarEstudio ? btnGuardarEstudio.querySelector('.spinner-border') : null;
    const textEstudio = document.getElementById('text-guardar-estudio');

    // Registrar Estudio para Paciente Específico
    if (tablaPacientesBody) {
        tablaPacientesBody.addEventListener('click', function (e) {
            const btnEstudio = e.target.closest('.btn-estudio');
            if (btnEstudio) {
                const id = btnEstudio.getAttribute('data-id');
                fetch(`/rx-estudios/pacientes/ver/${id}`)
                .then(res => res.json())
                .then(p => {
                    if (formEstudio) formEstudio.reset();
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
                    if (badgeTotalEstudios) badgeTotalEstudios.textContent = '0';

                    document.getElementById('modalEstudioLabel').innerHTML = '<i class="bi bi-journal-plus me-2"></i>Registrar Cita / Estudio de Radiología';
                    getModalEstudio().show();
                });
            }
        });
    }

    // Guardar Estudio (Crear/Editar)
    if (formEstudio) {
        formEstudio.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validar que se haya seleccionado al menos una región anatómica
            let totalChecked = 0;
            checkboxesEstudio.forEach(cb => {
                if (cb.checked) totalChecked++;
            });
            if (totalChecked === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debes seleccionar al menos un estudio / región anatómica.' });
                } else {
                    alert('Debes seleccionar al menos un estudio / región anatómica.');
                }
                return;
            }

            if (btnGuardarEstudio) btnGuardarEstudio.setAttribute('disabled', 'true');
            if (spinnerEstudio) spinnerEstudio.classList.remove('d-none');
            if (textEstudio) textEstudio.textContent = 'Guardando...';

            const id = document.getElementById('estudio-id').value;
            const url = id ? `/rx-estudios/estudios/actualizar/${id}` : '/rx-estudios/estudios/guardar';

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
                if (btnGuardarEstudio) btnGuardarEstudio.removeAttribute('disabled');
                if (spinnerEstudio) spinnerEstudio.classList.add('d-none');
                if (textEstudio) textEstudio.textContent = 'Guardar Estudio';

                if (res.errors) {
                    const errors = Object.values(res.errors).flat().join('<br>');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Errores de validación', html: errors });
                    } else {
                        alert('Errores de validación:\n' + errors.replace(/<br>/g, '\n'));
                    }
                    return;
                }

                if (res.success) {
                    getModalEstudio().hide();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Operación Satisfactoria!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                    
                    // Mover a la pestaña de Estudios para ver el registro
                    const estudioTabBtn = document.getElementById('estudios-tab');
                    if (estudioTabBtn) {
                        const triggerEl = bootstrap.Tab.getInstance(estudioTabBtn) || new bootstrap.Tab(estudioTabBtn);
                        triggerEl.show();
                    }

                    cargarEstudios(paginaEstudio);
                }
            })
            .catch(err => {
                console.error('Error al guardar estudio:', err);
                if (btnGuardarEstudio) btnGuardarEstudio.removeAttribute('disabled');
                if (spinnerEstudio) spinnerEstudio.classList.add('d-none');
                if (textEstudio) textEstudio.textContent = 'Guardar Estudio';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error inesperado.' });
                } else {
                    alert('Ocurrió un error inesperado.');
                }
            });
        });
    }

    // Abrir Modal de Edición de Estudio
    if (tablaEstudiosBody) {
        tablaEstudiosBody.addEventListener('click', function (e) {
            const btnEdit = e.target.closest('.btn-edit-estudio');
            if (btnEdit) {
                const id = btnEdit.getAttribute('data-id');
                fetch(`/rx-estudios/estudios/ver/${id}`)
                .then(res => res.json())
                .then(est => {
                    if (formEstudio) formEstudio.reset();
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
                    getModalEstudio().show();
                });
            }
        });
    }

    // Eliminar Estudio (Lógico)
    if (tablaEstudiosBody) {
        tablaEstudiosBody.addEventListener('click', function (e) {
            const btnDel = e.target.closest('.btn-del-estudio');
            if (btnDel) {
                const id = btnDel.getAttribute('data-id');

                const ejecutarEliminar = () => {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                        || document.querySelector('input[name="_token"]')?.value;

                    fetch(`/rx-estudios/estudios/eliminar/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                alert(res.message);
                            }
                            cargarEstudios(paginaEstudio);
                        }
                    });
                };

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Estás totalmente seguro?',
                        text: "Esta acción eliminará de forma lógica el estudio de radiografía seleccionado.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            ejecutarEliminar();
                        }
                    });
                } else {
                    if (confirm('¿Estás totalmente seguro de que deseas eliminar este registro de estudio de radiología?')) {
                        ejecutarEliminar();
                    }
                }
            }
        });
    }

    // ── VER DETALLES DE ESTUDIO ───────────────────────────
    if (tablaEstudiosBody) {
        tablaEstudiosBody.addEventListener('click', function (e) {
            const btnVer = e.target.closest('.btn-ver-estudio');
            if (btnVer) {
                const id = btnVer.getAttribute('data-id');
                fetch(`/rx-estudios/estudios/ver/${id}`)
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
                    if (badgesContainer) {
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
                    }

                    document.getElementById('det-notas').textContent = est.otros_datos || 'Sin observaciones registradas.';

                    getModalDetalle().show();
                });
            }
        });
    }

    // ── LISTENERS DE PESTAÑAS (TABS) PARA RECARGAR LISTAS ──
    const pacientesTabBtn = document.getElementById('pacientes-tab');
    const estudiosTabBtn = document.getElementById('estudios-tab');

    if (pacientesTabBtn) {
        pacientesTabBtn.addEventListener('shown.bs.tab', function () {
            cargarPacientes(1);
        });
    }

    if (estudiosTabBtn) {
        estudiosTabBtn.addEventListener('shown.bs.tab', function () {
            cargarEstudios(1);
        });
    }

    // ── INICIALIZACIÓN POR DEFECTO ─────────────────────────
    cargarPacientes(1);
});
