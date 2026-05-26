document.addEventListener('DOMContentLoaded', function () {
    const filtro = document.getElementById('filtroCategoria');
    const tbody = document.getElementById('tbodyArchivos');
    const totalBadge = document.getElementById('totalArchivos');

    function cargarArchivos(categoria) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted mb-0">Cargando formatos...</p>
                </td>
            </tr>
        `;

        fetch(`/mBuscaArchivos/filtrar?categoria=${encodeURIComponent(categoria)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Respuesta no satisfactoria del servidor');
            }
            return response.text();
        })
        .then(html => {
            tbody.innerHTML = html;
            // Calcular total de filas válidas
            const filas = tbody.querySelectorAll('tr:not(.text-center)');
            const total = filas.length;
            totalBadge.textContent = `${total} ${total === 1 ? 'formato' : 'formatos'}`;
        })
        .catch(err => {
            console.error('Error al cargar formatos:', err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-danger">
                        <span class="fa-stack fa-lg mb-2">
                            <i class="fa fa-folder-open-o fa-stack-1x text-secondary"></i>
                            <i class="fa fa-exclamation-triangle fa-stack-2x text-danger"></i>
                        </span>
                        <p class="fw-bold mb-0">Error al cargar los formatos. Por favor, intente nuevamente.</p>
                    </td>
                </tr>
            `;
            totalBadge.textContent = 'Error';
        });
    }

    if (filtro) {
        filtro.addEventListener('change', function () {
            cargarArchivos(this.value);
        });
        // Carga inicial al cargar la página
        cargarArchivos('Todos');
    }
});
