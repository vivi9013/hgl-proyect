/**
 * HGL - Funciones auxiliares globales de Javascript
 */

window.renderPaginacion = function (links, elementId, callback) {
    const paginador = document.getElementById(elementId);
    if (!paginador) return;

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

    // Registrar eventos para cambiar de página
    paginador.querySelectorAll('.page-link').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const page = this.getAttribute('data-page');
            if (page && page !== 'null') {
                callback(parseInt(page));
            }
        });
    });
};

/**
 * Listener global para confirmación de cierre de sesión con SweetAlert2
 */
document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        const logoutLink = e.target.closest('a[href*="logout"]');
        if (logoutLink) {
            e.preventDefault();
            const logoutUrl = logoutLink.getAttribute('href');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Cerrar sesión?',
                    text: '¿Estás seguro de que deseas salir del sistema?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Sí, cerrar sesión',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-4 shadow-lg'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = logoutUrl;
                    }
                });
            } else {
                if (confirm('¿Estás seguro de que deseas salir del sistema?')) {
                    window.location.href = logoutUrl;
                }
            }
        }
    });
});
