/**
 * tabla-interactiva.js — JS compartido para módulos de tabla con paginación AJAX.
 *
 * Uso: marcar el contenedor con data-tabla-interactiva y los data-* necesarios.
 *
 * Atributos requeridos en el contenedor:
 *   data-tabla-interactiva        → activa el autobootstrap
 *   data-endpoint="<url>"         → URL del controlador (ruta index)
 *   data-tbody-target="<id>"      → id del <tbody> a reemplazar
 *
 * Atributos opcionales:
 *   data-info-target="<id>"       → id del elemento de info de paginación
 *   data-paginacion-target="<id>" → id del contenedor de links de paginación
 *
 * Roles en elementos hijos:
 *   data-rol="buscar"             → input de búsqueda (dispara con debounce)
 *   data-rol="aplicar-filtros"    → botón que aplica checkboxes marcados
 *
 * Filtros por checkbox:
 *   data-filtro="<nombre_param>"  → checkbox; value = valor a enviar
 *   Múltiples checkboxes del mismo nombre se concatenan con coma.
 */
/**
 * tabla-interactiva.js — JS compartido para módulos de tabla con paginación AJAX.
 *
 * Contiene además la auto-inicialización global de los componentes de filtro:
 *   - [data-rol="fecha-rango"]    -> Inicializa Flatpickr en español con accesos rápidos.
 *   - [data-rol="filtro-dropdown"] -> Controla el dropdown de checkboxes y contadores.
 */
document.addEventListener('DOMContentLoaded', () => {
    // ─────────────────────────────────────────────────────────────────────────
    // 1. AUTO-INICIALIZACIÓN DE FLATPICKR (fecha-rango)
    // ─────────────────────────────────────────────────────────────────────────
    document.querySelectorAll('input[data-rol="fecha-rango"]').forEach(input => {
        if (typeof flatpickr === 'undefined') return;

        const fpInstancia = flatpickr(input, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            locale: flatpickr.l10ns.es ?? 'default',
            allowInput: false,
            disableMobile: true,
            showMonths: 1,
            onChange: function (selectedDates) {
                if (selectedDates.length === 2 || selectedDates.length === 0) {
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            },
            onReady: function (selectedDates, dateStr, fp) {
                fp.calendarContainer.classList.add('has-sidebar');

                const sidebar = document.createElement('div');
                sidebar.className = 'flatpickr-sidebar';
                sidebar.style.cssText = 'display:flex;flex-direction:column;justify-content:space-between;box-sizing:border-box;';

                const topGroup = document.createElement('div');
                topGroup.style.cssText = 'display:flex;flex-direction:column;gap:4px;';

                const shortcuts = [
                    { label: 'Hoy',             fn: fp => { const h = new Date(); fp.setDate([h, h], true); } },
                    { label: 'Ayer',             fn: fp => { const a = new Date(); a.setDate(a.getDate()-1); fp.setDate([a, a], true); } },
                    { label: 'Semana pasada',    fn: fp => { const h = new Date(), i = new Date(); i.setDate(h.getDate()-7); fp.setDate([i, h], true); } },
                    { label: 'Mes pasado',       fn: fp => { const h = new Date(); fp.setDate([new Date(h.getFullYear(), h.getMonth()-1, 1), new Date(h.getFullYear(), h.getMonth(), 0)], true); } },
                    { label: 'Último trimestre', fn: fp => { const h = new Date(), i = new Date(); i.setDate(h.getDate()-90); fp.setDate([i, h], true); } },
                ];

                const btnStyle = 'background:none;border:none;padding:6px 8px;text-align:left;font-size:0.8rem;color:#475569;cursor:pointer;border-radius:6px;transition:background 0.15s,color 0.15s;font-weight:500;font-family:inherit;width:100%;';

                shortcuts.forEach(s => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = s.label;
                    btn.style.cssText = btnStyle;
                    btn.addEventListener('mouseover', () => { btn.style.background = '#f1f5f9'; btn.style.color = '#0f172a'; });
                    btn.addEventListener('mouseout',  () => { btn.style.background = 'none';    btn.style.color = '#475569'; });
                    btn.addEventListener('click',     () => { s.fn(fp); fp.close(); });
                    topGroup.appendChild(btn);
                });

                const resetBtn = document.createElement('button');
                resetBtn.type = 'button';
                resetBtn.textContent = 'Restablecer';
                resetBtn.style.cssText = 'background:none;border:none;padding:6px 8px;text-align:left;font-size:0.8rem;color:#2563eb;cursor:pointer;border-radius:6px;transition:background 0.15s,color 0.15s;font-weight:600;font-family:inherit;width:100%;margin-top:12px;';
                resetBtn.addEventListener('mouseover', () => { resetBtn.style.background = '#eff6ff'; resetBtn.style.color = '#1d4ed8'; });
                resetBtn.addEventListener('mouseout',  () => { resetBtn.style.background = 'none';    resetBtn.style.color = '#2563eb'; });
                resetBtn.addEventListener('click',     () => { fp.clear(); fp.close(); });

                sidebar.appendChild(topGroup);
                sidebar.appendChild(resetBtn);
                fp.calendarContainer.insertBefore(sidebar, fp.calendarContainer.firstChild);
            },
        });

        // Guardar referencia en el input por si los scripts de módulo necesitan acceder
        input._flatpickr = fpInstancia;
    });

    // Cierre seguro de calendarios al hacer clic afuera
    document.addEventListener('mousedown', function (e) {
        document.querySelectorAll('input[data-rol="fecha-rango"]').forEach(input => {
            const fp = input._flatpickr;
            if (fp && fp.isOpen) {
                const clicEnInput      = input.contains(e.target);
                const clicEnCalendario = fp.calendarContainer && fp.calendarContainer.contains(e.target);
                if (!clicEnInput && !clicEnCalendario) fp.close();
            }
        });
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 2. AUTO-INICIALIZACIÓN DE DROPDOWNS DE FILTRO (filtro-dropdown)
    // ─────────────────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-rol="filtro-dropdown"]').forEach(dropdown => {
        const btnToggle  = dropdown.querySelector('.dropdown-toggle');
        const labelText  = dropdown.querySelector('.dropdown-label');
        const btnApply   = dropdown.querySelector('.btn-dropdown-apply');
        const btnCancel  = dropdown.querySelector('.btn-dropdown-cancel');
        const linkClear  = dropdown.querySelector('.dropdown-clear-all');
        const defaultLabel = labelText ? labelText.textContent : 'Todos los registros';

        function actualizarContador() {
            if (!labelText) return;
            const checkedCount = dropdown.querySelectorAll('.dropdown-body input[type="checkbox"]:checked').length;
            labelText.textContent = checkedCount === 0 ? defaultLabel : `Filtros activos (${checkedCount})`;
        }

        // Registrar cambios en checkboxes
        dropdown.querySelectorAll('.dropdown-body input[type="checkbox"]').forEach(chk => {
            chk.addEventListener('change', actualizarContador);
        });

        // Botón: Aplicar
        btnApply?.addEventListener('click', () => {
            actualizarContador();
            dropdown.dispatchEvent(new CustomEvent('filtros:aplicar', { bubbles: true }));
            
            // Cerrar dropdown usando Bootstrap API de forma segura
            if (btnToggle && window.bootstrap && window.bootstrap.Dropdown) {
                const bsDropdown = window.bootstrap.Dropdown.getOrCreateInstance(btnToggle);
                bsDropdown.hide();
            } else if (btnToggle) {
                btnToggle.click(); // fallback rústico
            }
        });

        // Botón: Cancelar
        btnCancel?.addEventListener('click', () => {
            if (btnToggle && window.bootstrap && window.bootstrap.Dropdown) {
                const bsDropdown = window.bootstrap.Dropdown.getOrCreateInstance(btnToggle);
                bsDropdown.hide();
            } else if (btnToggle) {
                btnToggle.click();
            }
        });

        // Botón: Limpiar todo
        linkClear?.addEventListener('click', e => {
            e.preventDefault();
            dropdown.querySelectorAll('.dropdown-body input[type="checkbox"]').forEach(chk => {
                chk.checked = false;
            });
            actualizarContador();
            dropdown.dispatchEvent(new CustomEvent('filtros:limpiar', { bubbles: true }));
        });

        // Inicializar contador al cargar
        actualizarContador();
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 3. TABLA INTERACTIVA (BÚSQUEDA + PAGINACIÓN AJAX GENÉRICA)
    // ─────────────────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-tabla-interactiva]').forEach(cont => {
        const tbodyId    = cont.dataset.tbodyTarget;
        const infoId     = cont.dataset.infoTarget;
        const paginId    = cont.dataset.paginacionTarget;
        const printSel   = cont.dataset.btnImprimir;

        const tbody      = tbodyId  ? document.getElementById(tbodyId)  : null;
        const info       = infoId   ? document.getElementById(infoId)   : null;
        const paginacion = paginId  ? document.getElementById(paginId)  : null;
        const buscar     = cont.querySelector('[data-rol="buscar"]');
        const btnImprimir = printSel ? document.querySelector(printSel) : null;

        let debounce;

        function recolectarFiltros() {
            const f = {};
            cont.querySelectorAll('[data-filtro]:checked').forEach(chk => {
                const k = chk.dataset.filtro;
                f[k] = f[k] ? `${f[k]},${chk.value}` : chk.value;
            });
            return f;
        }

        function actualizarEnlaceImpresion() {
            if (!btnImprimir) return;
            const params = new URLSearchParams();
            const q = buscar?.value?.trim() ?? '';
            if (q) params.set('buscar', q);

            const filtros = recolectarFiltros();
            Object.entries(filtros).forEach(([k, v]) => {
                // Para consistencia con Laravel, si el valor contiene comas,
                // enviamos cada uno como elemento de array (ej: tipo[]=A&tipo[]=B)
                if (v.includes(',')) {
                    v.split(',').forEach(val => params.append(`${k}[]`, val));
                } else {
                    params.append(k, v);
                }
            });

            const baseUrl = btnImprimir.href.split('?')[0];
            btnImprimir.href = params.toString() ? `${baseUrl}?${params}` : baseUrl;
        }

        function cargar(extra = {}) {
            if (!tbody) return;

            const url    = new URL(cont.dataset.endpoint, location.origin);
            const params = { buscar: buscar?.value?.trim() ?? '', ...recolectarFiltros(), ...extra };
            Object.entries(params).forEach(([k, v]) => v && url.searchParams.set(k, v));

            tbody.style.opacity    = '0.4';
            tbody.style.transition = 'opacity 0.2s';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json',
                },
            })
                .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
                .then(d => {
                    tbody.innerHTML    = d.html;
                    tbody.style.opacity = '1';
                    if (info)       info.textContent     = d.info;
                    if (paginacion) paginacion.innerHTML = d.links;

                    // Re-vincular links de paginación después de actualizar
                    vincularPaginacion();
                    actualizarEnlaceImpresion();
                })
                .catch(() => { tbody.style.opacity = '1'; });
        }

        function vincularPaginacion() {
            if (!paginacion) return;
            paginacion.querySelectorAll('a.page-link').forEach(a => {
                a.addEventListener('click', e => {
                    e.preventDefault();
                    const p = new URL(a.href).searchParams.get('page');
                    if (p) cargar({ page: p });
                });
            });
        }

        // Búsqueda con debounce
        buscar?.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => cargar(), 400);
        });

        // Botón "Aplicar filtros"
        cont.querySelector('[data-rol="aplicar-filtros"]')
            ?.addEventListener('click', () => cargar());

        // Escuchar eventos personalizados de los filtros dentro de este contenedor
        cont.addEventListener('filtros:aplicar', () => {
            cargar();
        });

        cont.addEventListener('filtros:limpiar', () => {
            if (buscar) buscar.value = '';
            cargar();
        });

        // Paginación e impresión inicial (SSR)
        vincularPaginacion();
        actualizarEnlaceImpresion();
    });
});

