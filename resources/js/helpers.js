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
