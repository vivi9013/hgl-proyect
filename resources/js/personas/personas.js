/**
 * personas.js — Lógica AJAX para el catálogo de Personas
 * Incluye: toggle status, toggle estudiante (delegación de eventos),
 * carga dinámica de municipios, modal de edición y SweetAlert2.
 * La paginación y búsqueda AJAX son manejadas por tabla-interactiva.js.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Alertas de sesión (SweetAlert2) ─────────────────────────────────────
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito  = document.getElementById('alertaExito');

    if (alertaExitog && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExitog.dataset.message || 'El registro se ha guardado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    if (alertaExito && typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: alertaExito.dataset.message || 'El registro se ha actualizado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // ── Carga dinámica de Estados en el modal de alta ─────────────────────────
    const estadoSel    = document.getElementById('estado_sel');
    const municipioSel = document.getElementById('municipio_sel');

    if (estadoSel) {
        const modalEl = document.getElementById('modalAltaPersona');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function () {
                if (estadoSel.options.length <= 1) {
                    cargarEstados(estadoSel, municipioSel);
                }
            });
        }

        estadoSel.addEventListener('change', function () {
            cargarMunicipios(this.value, municipioSel);
        });
    }

    function cargarEstados(selectEstado, selectMunicipio) {
        fetch('/personas/municipios?estado=__estados__', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            selectEstado.innerHTML = '<option value="">-- Seleccionar --</option>';
            Object.keys(data).forEach(key => {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = key;
                selectEstado.appendChild(opt);
            });
        })
        .catch(() => {});
    }

    function cargarMunicipios(estado, selectMunicipio) {
        if (!estado || !selectMunicipio) return;
        selectMunicipio.innerHTML = '<option value="">Cargando...</option>';

        fetch(`/personas/municipios?estado=${encodeURIComponent(estado)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            selectMunicipio.innerHTML = '<option value="">-- Seleccionar --</option>';
            Object.entries(data).forEach(([key, val]) => {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val;
                selectMunicipio.appendChild(opt);
            });
        })
        .catch(() => {
            selectMunicipio.innerHTML = '<option value="">Error al cargar</option>';
        });
    }

    // ── D. TOGGLE STATUS — delegación de eventos ─────────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-toggle-status');
        if (!boton) return;

        const id     = boton.dataset.id;
        const nombre = boton.dataset.nombre ?? '';
        const icono  = boton.querySelector('i');
        const esActivo = icono && icono.classList.contains('text-success');
        const accion = esActivo ? 'desactivar' : 'activar';

        const runFetch = () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(`/personas/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ title: 'Actualizado', text: data.message, icon: 'success', timer: 1400, showConfirmButton: false });
                    }
                    document.querySelector('[data-tabla-interactiva]')
                        ?.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));
                }
            })
            .catch(err => console.error(err));
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} persona?`,
                text: `"${nombre}" será ${accion}da en el sistema.`,
                icon: esActivo ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) runFetch(); });
        } else {
            if (confirm(`¿${accion} a "${nombre}"?`)) runFetch();
        }
    });

    // ── D.2 TOGGLE ESTUDIANTE — delegación de eventos ─────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-toggle-estudiante');
        if (!boton) return;

        const id     = boton.dataset.id;
        const nombre = boton.dataset.nombre ?? '';
        const icono  = boton.querySelector('i');
        const esEstudiante = icono && icono.classList.contains('text-primary');
        const accion = esEstudiante ? 'quitar el rol de estudiante a' : 'asignar el rol de estudiante a';

        const runFetch = () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(`/personas/${id}/estudiante`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ title: 'Actualizado', text: data.message, icon: 'success', timer: 1400, showConfirmButton: false });
                    }
                    document.querySelector('[data-tabla-interactiva]')
                        ?.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));
                }
            })
            .catch(err => console.error(err));
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Cambiar rol de estudiante?',
                text: `Se va a ${accion} "${nombre}".`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then(result => { if (result.isConfirmed) runFetch(); });
        } else {
            if (confirm(`¿${accion} "${nombre}"?`)) runFetch();
        }
    });

    // ── Modal Editar Persona — delegación de eventos ─────────────────────────
    document.addEventListener('click', function (e) {
        const boton = e.target.closest('.btn-editar-persona');
        if (!boton) return;

        const id = boton.dataset.id;
        const formEdit = document.getElementById('formEditarPersona');
        if (!formEdit) return;

        formEdit.action = `/personas/${id}`;

        fetch(`/personas/${id}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const p = data.persona;
                document.getElementById('edit_nombre').value     = p.nombre || '';
                document.getElementById('edit_ap_paterno').value = p.ap_paterno || '';
                document.getElementById('edit_ap_materno').value = p.ap_materno || '';
                document.getElementById('edit_fecha_nac').value  = p.fecha_nac || '';
                document.getElementById('edit_sexo').value       = p.sexo || 'M';
                document.getElementById('edit_ecivil').value     = p.ecivil || 'Soltero(a)';
                document.getElementById('edit_telefono').value   = p.telefono || '';
                document.getElementById('edit_rfc').value        = p.rfc || '';
                document.getElementById('edit_curp').value       = p.curp || '';
                document.getElementById('edit_e_mail').value     = p.e_mail || '';
                document.getElementById('edit_colonia').value    = p.colonia || '';
                document.getElementById('edit_calle').value      = p.calle || '';
                document.getElementById('edit_numero').value     = p.numero || '';

                const editEstado    = document.getElementById('edit_estado');
                const editMunicipio = document.getElementById('edit_municipio');

                if (editEstado) {
                    editEstado.innerHTML = '<option value="">-- Seleccionar --</option>';
                    Object.keys(data.estados).forEach(st => {
                        const opt = document.createElement('option');
                        opt.value = st;
                        opt.textContent = st;
                        if (st === p.estado) opt.selected = true;
                        editEstado.appendChild(opt);
                    });
                }

                if (editMunicipio) {
                    editMunicipio.innerHTML = '<option value="">-- Seleccionar --</option>';
                    Object.keys(data.municipios).forEach(mun => {
                        const opt = document.createElement('option');
                        opt.value = mun;
                        opt.textContent = mun;
                        if (mun === p.municipio) opt.selected = true;
                        editMunicipio.appendChild(opt);
                    });
                }

                const modalEl = document.getElementById('modalEditarPersona');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            }
        })
        .catch(err => console.error('Error al cargar persona:', err));
    });

    // ── Cambio de estado en el modal de edición ──
    const editEstadoSel    = document.getElementById('edit_estado');
    const editMunicipioSel = document.getElementById('edit_municipio');
    if (editEstadoSel) {
        editEstadoSel.addEventListener('change', function () {
            cargarMunicipios(this.value, editMunicipioSel);
        });
    }
    // ── Carga dinámica de municipios en la vista de edición estática ─────────
    const estadoEdit    = document.getElementById('estado_edit');
    const municipioEdit = document.getElementById('municipio_edit');

    if (estadoEdit && municipioEdit) {
        estadoEdit.addEventListener('change', function () {
            const estado = this.value;
            if (!estado) return;

            const valorActualMunicipio = municipioEdit.value;
            municipioEdit.innerHTML = '<option value="">Cargando...</option>';

            fetch(`/personas/municipios?estado=${encodeURIComponent(estado)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                municipioEdit.innerHTML = '<option value="">-- Seleccionar --</option>';
                Object.entries(data).forEach(([key, val]) => {
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = val;
                    if (val === valorActualMunicipio) opt.selected = true;
                    municipioEdit.appendChild(opt);
                });
            })
            .catch(() => {
                municipioEdit.innerHTML = '<option value="">Error al cargar municipios</option>';
            });
        });
    }
});