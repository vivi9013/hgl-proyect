/**
 * Médicos RX - Interacciones Frontend (AJAX CRUD)
 * HGL - Estudios Radiológicos
 */

import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    let paginaActual = 1;
    let buscarTerm = '';

    // Elementos de la interfaz
    const tablaBody = document.querySelector('#tablaMedicos tbody');
    const buscarInput = document.getElementById('buscar-medico');
    const totalBadge = document.getElementById('total-registros-badge');
    const infoPaginacion = document.getElementById('info-paginacion');

    // ── Modal lazy init para evitar crash si bootstrap no está listo ──
    let _modalEditar = null;
    function getModalEditar() {
        if (!_modalEditar) {
            const el = document.getElementById('modalEditar');
            if (el) _modalEditar = new bootstrap.Modal(el);
        }
        return _modalEditar;
    }

    // Formularios
    const formAlta = document.getElementById('frmAltaMedico');
    const formEditar = document.getElementById('frmEditarMedico');

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Carga inicial
    cargarMedicos(1);

    /**
     * ── 1. CARGA ASÍNCRONA (GET) ──
     */
    function cargarMedicos(pagina = 1) {
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

        fetch(`/rx-medicos?q=${encodeURIComponent(buscarTerm)}&page=${pagina}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(res => {
            totalBadge.textContent = `${res.total} ${res.total === 1 ? 'registro' : 'registros'}`;

            if (!res.data || res.data.length === 0) {
                tablaBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fa fa-info-circle me-1"></i> No se encontraron médicos registrados.
                        </td>
                    </tr>
                `;
                infoPaginacion.textContent = 'Mostrando 0 a 0 de 0 registros';
                document.getElementById('paginador-medicos').innerHTML = '';
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
                        <button class="btn btn-link btn-sm text-dark p-0 btn-edit" data-id="${item.id_medicos}" title="Editar Médico">
                            <i class="fa fa-pencil-square-o fa-lg text-dark"></i>
                        </button>
                    </td>
                    <td>${item.nombre}</td>
                    <td class="font-monospace fw-bold text-primary">${item.abreviatura}</td>
                    <td>${item.fecha_registro || '—'}</td>
                    <td>${item.hora_registro || '—'}</td>
                    <td class="text-center pe-4">
                        <button class="btn btn-link p-0 btn-toggle-status" data-id="${item.id_medicos}" data-activo="${item.activo}">
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
                window.renderPaginacion(res.links, 'paginador-medicos', cargarMedicos);
            }
        })
        .catch(err => {
            console.error('Error al cargar médicos:', err);
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        Error al cargar la lista. <small class="d-block text-muted mt-1">${err.message}</small>
                    </td>
                </tr>
            `;
            totalBadge.textContent = 'Error';
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
            cargarMedicos(1);
        }, 350);
    });

    /**
     * ── 3. GUARDAR NUEVO REGISTRO (POST) ──
     */
    formAlta.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarErrores(formAlta);

        const nombre = document.getElementById('nombreMed').value.trim();
        const abreviatura = document.getElementById('abre').value.trim();

        fetch('/rx-medicos/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
                formAlta.reset();
                limpiarErrores(formAlta);
                cargarMedicos(1);
            }
        })
        .catch(err => {
            console.error('Error al guardar médico:', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            }
        });
    });

    /**
     * ── 4. EDITAR REGISTRO (GET datos → modal) ──
     */
    tablaBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit');
        if (!btn) return;

        const id = btn.dataset.id;

        fetch(`/rx-medicos/${id}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(medico => {
            document.getElementById('idRegistro').value = medico.id_medicos;
            document.getElementById('EnombreMed').value = medico.nombre;
            document.getElementById('Eabre').value = medico.abreviatura;
            limpiarErrores(formEditar);
            getModalEditar().show();
        })
        .catch(err => {
            console.error('Error al cargar médico para editar:', err);
            alert('No se pudo cargar la información del médico.');
        });
    });

    /**
     * ── 5. ACTUALIZAR REGISTRO (PUT) ──
     */
    formEditar.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarErrores(formEditar);

        const id = document.getElementById('idRegistro').value;
        const nombre = document.getElementById('EnombreMed').value.trim();
        const abreviatura = document.getElementById('Eabre').value.trim();

        fetch(`/rx-medicos/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ nombre, abreviatura })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                if (data.errors) {
                    mostrarErrores(formEditar, data.errors);
                }
                throw new Error(data.message || 'Error al actualizar');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                getModalEditar().hide();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
                cargarMedicos(paginaActual);
            }
        })
        .catch(err => {
            console.error('Error al actualizar médico:', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            }
        });
    });

    /**
     * ── 6. TOGGLE STATUS (PATCH) ──
     */
    tablaBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-toggle-status');
        if (!btn) return;

        const id = btn.dataset.id;
        const activo = btn.dataset.activo;

        fetch(`/rx-medicos/${id}/status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                cargarMedicos(paginaActual);
            }
        })
        .catch(err => console.error('Error al cambiar estado:', err));
    });

    /**
     * ── UTILIDADES ──
     */
    function mostrarErrores(form, errors) {
        Object.keys(errors).forEach(campo => {
            const feedback = form.querySelector(`[id^="feedback-"][id$="${campo}"]`)
                || form.querySelector(`#feedback-${campo}`)
                || form.querySelector(`#feedback-editar-${campo}`);
            if (feedback) {
                feedback.textContent = errors[campo][0];
                feedback.style.display = 'block';
                const input = form.querySelector(`[name="${campo}"]`);
                if (input) input.classList.add('is-invalid');
            }
        });
    }

    function limpiarErrores(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('[id^="feedback-"]').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }
});
