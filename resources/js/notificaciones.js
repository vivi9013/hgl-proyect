/**
 * Lógica Javascript para la campanita de Notificaciones del Header.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const NOTIF_URL = '/notificaciones';

    const badge       = document.getElementById('notif-badge');
    const headerCount = document.getElementById('notif-header-count');
    const loading     = document.getElementById('notif-loading');
    const emptyState  = document.getElementById('notif-empty');
    const list        = document.getElementById('notif-list');
    const footer      = document.getElementById('notif-footer');
    const bellIcon    = document.getElementById('notif-bell-icon');
    const toggle      = document.getElementById('notifDropdownToggle');

    if (!toggle && !badge) return;

    function setBadge(n) {
        if (!badge) return;
        if (n > 0) {
            const txt = n > 99 ? '99+' : n;
            badge.textContent = txt;
            if (headerCount) headerCount.textContent = txt;
            badge.classList.remove('d-none');
            if (headerCount) headerCount.classList.remove('d-none');
            if (bellIcon) bellIcon.classList.add('text-danger');
        } else {
            badge.classList.add('d-none');
            if (headerCount) headerCount.classList.add('d-none');
            if (bellIcon) bellIcon.classList.remove('text-danger');
        }
    }

    function colorClass(color) {
        const map = { warning: '#ffc107', info: '#0dcaf0', danger: '#dc3545', success: '#198754' };
        return map[color] || '#6c757d';
    }

    function buildItem(n) {
        const c  = colorClass(n.color);
        const li = document.createElement('li');
        li.style.cssText = 'border-bottom: 1px solid rgba(0,0,0,0.08);';

        const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        li.innerHTML = `
            <a href="${esc(n.enlace)}"
               class="d-flex align-items-start text-decoration-none notif-item"
               style="padding: 12px 14px; gap: 12px; transition: background 0.18s;">

              <div style="flex-shrink:0; width:40px; height:40px; border-radius:50%;
                          background:${c}1a; color:${c}; font-size:1.2rem;
                          display:flex; align-items:center; justify-content:center;
                          margin-top:2px; border: 1.5px solid ${c}33;">
                <i class="bi ${esc(n.icono)}"></i>
              </div>

              <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:6px; margin-bottom:3px;">
                  <p style="margin:0; font-size:0.8rem; font-weight:600;
                             color:var(--theme-text, #222); line-height:1.3;
                             word-break:break-word;">${esc(n.titulo)}</p>
                  <span style="flex-shrink:0; width:8px; height:8px; border-radius:50%;
                               background:${c}; margin-top:4px; opacity:0.85;"></span>
                </div>
                <p style="margin:0 0 5px 0; font-size:0.8rem; color:#555;
                           line-height:1.45; word-break:break-word;
                           white-space:normal;">${esc(n.cuerpo)}</p>
                <div style="display:flex; align-items:center; gap:5px;">
                  <i class="bi bi-clock" style="font-size:0.65rem; color:#999;"></i>
                  <small style="font-size:0.72rem; color:#999;">${esc(n.fecha)}</small>
                </div>
              </div>
            </a>`;

        const link = li.querySelector('.notif-item');
        if (link) {
            link.addEventListener('mouseenter', function () {
                this.style.background = 'var(--theme-hover-bg, #f5f5f5)';
            });
            link.addEventListener('mouseleave', function () {
                this.style.background = '';
            });
        }

        return li;
    }

    function renderNotifications(data) {
        if (loading) loading.style.display = 'none';
        if (list) list.innerHTML = '';

        if (!data.notificaciones || data.notificaciones.length === 0) {
            if (emptyState) emptyState.classList.remove('d-none');
            if (list) list.classList.add('d-none');
            if (footer) footer.classList.add('d-none');
            setBadge(0);
            return;
        }

        if (emptyState) emptyState.classList.add('d-none');
        if (list) list.classList.remove('d-none');
        if (footer) footer.classList.remove('d-none');

        data.notificaciones.forEach(n => {
            if (list) list.appendChild(buildItem(n));
        });

        setBadge(data.pendientes || 0);
    }

    function loadNotifications() {
        if (loading) loading.style.display = '';
        if (emptyState) emptyState.classList.add('d-none');
        if (list) list.classList.add('d-none');
        if (footer) footer.classList.add('d-none');

        fetch(NOTIF_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(renderNotifications)
        .catch(() => {
            if (loading) loading.style.display = 'none';
            if (emptyState) emptyState.classList.remove('d-none');
        });
    }

    function refreshBadge() {
        fetch(NOTIF_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => setBadge(data.pendientes || 0))
        .catch(() => {});
    }

    if (toggle) {
        toggle.addEventListener('show.bs.dropdown', loadNotifications);
        toggle.addEventListener('hide.bs.dropdown', refreshBadge);
    }

    window.actualizarNotificaciones = function () {
        refreshBadge();
        if (toggle && toggle.classList.contains('show')) {
            loadNotifications();
        }
    };

    document.addEventListener('notificaciones:refresh', function () {
        window.actualizarNotificaciones();
    });

    window.addEventListener('focus', refreshBadge);

    refreshBadge();
    setInterval(refreshBadge, 15000);
});
