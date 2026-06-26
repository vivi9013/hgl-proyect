/**
 * Compartido: Inicialización del Panel de Claves Flotante para buscadores de insumos
 */
export function initPanelClaves(config) {
    const {
        panelId = 'panelClaves',
        inputBuscarId = 'buscarInsumo',
        inputHiddenId = 'id_insumo',
        sugerenciasId = 'sugerenciasInsumo',
        areaInputId = null,
        endpoint = '',
        columnaExtra = 'none', // 'stock', 'tipo', 'none'
        onSelect = null
    } = config;

    const panel = document.getElementById(panelId);
    if (!panel) return;

    const inputBuscar = document.getElementById(inputBuscarId);
    const inputHidden = inputHiddenId ? document.getElementById(inputHiddenId) : null;
    const sugerencias = sugerenciasId ? document.getElementById(sugerenciasId) : null;
    const areaInput = areaInputId ? document.getElementById(areaInputId) : null;

    const filasClaves = panel.querySelector('.filas-claves');
    const panelClavesLoading = panel.querySelector('.panel-claves-loading');
    const panelClavesVacio = panel.querySelector('.panel-claves-vacio');
    const filtroPanelClaves = panel.querySelector('.filtro-panel-claves');
    const cerrarPanelClaves = panel.querySelector('.cerrar-panel-claves');
    const avisoSinArea = panel.querySelector('.aviso-sin-area');

    let clavesCache = [];

    const renderizarFilas = (filtro = '') => {
        if (!filasClaves) return;
        const texto = filtro.toLowerCase().trim();
        const datos = texto
            ? clavesCache.filter(i =>
                i.clave.toLowerCase().includes(texto) ||
                i.descripcion.toLowerCase().includes(texto))
            : clavesCache;

        filasClaves.innerHTML = '';

        if (datos.length === 0) {
            if (panelClavesVacio) panelClavesVacio.style.display = 'block';
            return;
        }
        if (panelClavesVacio) panelClavesVacio.style.display = 'none';

        datos.forEach((insumo, idx) => {
            const tr = document.createElement('tr');
            tr.style.cssText = `cursor:pointer; transition:background 0.15s;`;
            
            let extraTd = '';
            if (columnaExtra === 'stock') {
                const stockVal = insumo.stock !== undefined ? insumo.stock : '—';
                const badgeBg = insumo.stock > 0 ? '#dcfce7' : '#fee2e2';
                const badgeColor = insumo.stock > 0 ? '#15803d' : '#dc2626';
                extraTd = `
                    <td style="padding:6px 10px; border-bottom:1px solid #f3f4f6; text-align:center; white-space:nowrap;">
                        <span style="background:${badgeBg}; color:${badgeColor}; border-radius:12px; padding:2px 8px; font-size:0.75rem; font-weight:700;">${stockVal}</span>
                    </td>
                `;
            } else if (columnaExtra === 'tipo') {
                const tipoVal = insumo.tipo || 'Insumo';
                extraTd = `
                    <td style="padding:6px 10px; border-bottom:1px solid #f3f4f6; text-align:center; white-space:nowrap;">
                        <span class="badge bg-secondary">${tipoVal}</span>
                    </td>
                `;
            }

            tr.innerHTML = `
                <td style="padding:6px 10px; border-bottom:1px solid #f3f4f6; font-family:Arial,sans-serif; font-weight:600; color:#1d4ed8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${insumo.clave}</td>
                <td style="padding:6px 10px; border-bottom:1px solid #f3f4f6; color:#374151; word-wrap:break-word; overflow-wrap:break-word; white-space:normal;">${insumo.descripcion}</td>
                ${extraTd}
            `;

            tr.addEventListener('mouseenter', () => tr.style.background = '#eff6ff');
            tr.addEventListener('mouseleave', () => tr.style.background = idx % 2 === 0 ? '#fff' : '#f9fafb');
            tr.style.background = idx % 2 === 0 ? '#fff' : '#f9fafb';

            tr.addEventListener('click', () => {
                if (inputBuscar) {
                    inputBuscar.value = `[${insumo.clave}] ${insumo.descripcion}`;
                }
                if (inputHidden) {
                    inputHidden.value = insumo.id_insumo;
                }

                panel.style.display = 'none';
                
                if (onSelect) {
                    onSelect(insumo);
                }
                
                if (inputBuscar) {
                    inputBuscar.focus();
                }
            });

            filasClaves.appendChild(tr);
        });
    };

    const abrirPanelClaves = () => {
        if (sugerencias) sugerencias.style.display = 'none';

        panel.style.display = 'block';
        if (filasClaves) filasClaves.innerHTML = '';
        if (filtroPanelClaves) {
            filtroPanelClaves.value = '';
            filtroPanelClaves.focus();
        }
        if (panelClavesLoading) panelClavesLoading.style.display = 'block';
        if (panelClavesVacio) panelClavesVacio.style.display = 'none';

        const areaId = areaInput ? areaInput.value : '';
        if (avisoSinArea) {
            avisoSinArea.style.display = areaId ? 'none' : 'flex';
        }

        if (areaInput && !areaId) {
            if (panelClavesLoading) panelClavesLoading.style.display = 'none';
            if (panelClavesVacio) panelClavesVacio.style.display = 'none';
            clavesCache = [];
            renderizarFilas();
            return;
        }

        let url = `${endpoint}?all=1`;
        if (areaId) {
            url += `&id_area_almacen=${encodeURIComponent(areaId)}`;
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => { if (!res.ok) throw new Error('Error'); return res.json(); })
        .then(data => {
            if (panelClavesLoading) panelClavesLoading.style.display = 'none';
            clavesCache = data || [];
            renderizarFilas();
            if (filtroPanelClaves) filtroPanelClaves.focus();
        })
        .catch(() => {
            if (panelClavesLoading) panelClavesLoading.style.display = 'none';
            if (filasClaves) {
                filasClaves.innerHTML = `<tr><td colspan="${columnaExtra !== 'none' ? 3 : 2}" style="text-align:center;padding:14px;color:#dc2626;font-size:0.8rem;">Error al cargar las claves.</td></tr>`;
            }
        });
    };

    if (inputBuscar) {
        inputBuscar.addEventListener('dblclick', (e) => {
            e.stopPropagation();
            abrirPanelClaves();
        });
    }

    if (filtroPanelClaves) {
        filtroPanelClaves.addEventListener('input', () => {
            renderizarFilas(filtroPanelClaves.value);
        });
        filtroPanelClaves.addEventListener('keydown', e => {
            if (e.key === 'Enter') e.preventDefault();
            if (e.key === 'Escape') panel.style.display = 'none';
        });
    }

    if (cerrarPanelClaves) {
        cerrarPanelClaves.addEventListener('click', () => {
            panel.style.display = 'none';
        });
    }

    document.addEventListener('click', (e) => {
        if (panel.style.display === 'block') {
            if (!panel.contains(e.target) && e.target !== inputBuscar) {
                panel.style.display = 'none';
            }
        }
    });
}
