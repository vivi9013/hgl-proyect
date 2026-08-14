// Importa la función que inicializa el panel de claves compartido.
// Se utiliza import porque esta función está definida en otro archivo JavaScript
// y fue exportada para poder reutilizarla en este módulo.
import { initPanelClaves } from '../shared/panel-claves.js';

/*
 * Lógica JavaScript para el módulo de Bajas de Insumos.
 *
 * Este archivo controla las búsquedas, validaciones, alertas,
 * filtros y comportamiento del formulario de bajas.
 */

// Espera a que todo el HTML haya sido cargado antes de ejecutar el código.
// DOMContentLoaded se utiliza para asegurarse de que los elementos del formulario
// ya existan cuando JavaScript intente obtenerlos.
document.addEventListener('DOMContentLoaded', function () {

    // Obtiene el selector del área de almacén.
    // getElementById() busca un elemento HTML utilizando su atributo id.
    const selectArea = document.getElementById('id_area_almacen');

    // Obtiene el campo donde el usuario escribe el insumo que desea buscar.
    const inputBuscarInsumo = document.getElementById('buscarInsumo');

    // Obtiene el campo oculto que almacena el ID real del insumo seleccionado.
    // El usuario ve la descripción, pero este campo conserva el identificador
    // que se enviará al servidor.
    const inputIdInsumo = document.getElementById('id_insumo');

    // Obtiene el contenedor donde se mostrarán las sugerencias de búsqueda.
    const sugerenciasDiv = document.getElementById('sugerenciasInsumo');

    // Obtiene el elemento que contiene la información del stock.
    const infoStock = document.getElementById('infoStock');

    // Obtiene el elemento donde se mostrará la cantidad de stock disponible.
    const stockDisponible = document.getElementById('stockDisponible');

    // Obtiene el campo donde el usuario captura la cantidad de insumos.
    const inputCantidad = document.getElementById('cantidad');

    // Obtiene el botón utilizado para guardar la baja.
    const btnGuardar = document.getElementById('btnGuardar');

    // Obtiene el contenedor que mostrará el área asignada al insumo.
    const infoAreaAsignada = document.getElementById('infoAreaAsignada');

    // Obtiene el elemento donde se colocará el nombre del área asignada.
    const nombreAreaAsignada = document.getElementById('nombreAreaAsignada');


    // ── 1. Alertas SweetAlert2 con sesión de Laravel ──────────────────────

    // Obtiene los elementos que Laravel utiliza para informar el resultado
    // de operaciones realizadas en el servidor.
    const alertaExitog = document.getElementById('alertaExitog');
    const alertaExito = document.getElementById('alertaExito');
    const alertaError = document.getElementById('alertaError');

    // Comprueba que exista la alerta de registro exitoso y que SweetAlert2
    // esté disponible antes de intentar utilizar Swal.
    //
    // typeof se utiliza porque permite comprobar si Swal existe sin provocar
    // un error de JavaScript cuando la librería no fue cargada.
    if (alertaExitog && typeof Swal !== 'undefined') {

        // Muestra una ventana de confirmación indicando que la baja fue registrada.
        // Swal.fire() pertenece a SweetAlert2 y recibe un objeto con la configuración
        // de la ventana que se mostrará.
        Swal.fire({
            title: '¡Baja Registrada!',
            text: 'La baja de insumo se ha registrado correctamente.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // Comprueba si Laravel indicó que una baja fue cancelada correctamente.
    if (alertaExito && typeof Swal !== 'undefined') {

        // Muestra una alerta indicando que la operación fue realizada.
        Swal.fire({
            title: '¡Operación Satisfactoria!',
            text: 'La baja de insumo ha sido cancelada y el stock restaurado.',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }

    // Comprueba si existe un mensaje de error enviado desde Laravel.
    if (alertaError && typeof Swal !== 'undefined') {

        // Obtiene el mensaje guardado en el atributo data-message.
        //
        // getAttribute() permite leer atributos HTML.
        // || establece un mensaje predeterminado si data-message no tiene valor.
        const msg = alertaError.getAttribute('data-message') || 'Ocurrió un error inesperado.';

        // Muestra el mensaje de error mediante SweetAlert2.
        Swal.fire({
            title: 'Aviso',
            text: msg,
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    }


    // ── 2. Buscador de insumos con autocompletado ─────────────────────────

    // Guarda el identificador del temporizador utilizado para retrasar la búsqueda.
    //
    // Se utiliza let porque el valor de esta variable cambiará cada vez
    // que se programe una nueva búsqueda.
    let timeoutBusqueda = null;

    // Guarda temporalmente el máximo de stock disponible del insumo seleccionado.
    // Se utiliza let porque este valor cambia cuando se selecciona otro insumo.
    let stockMaximo = 0;


    // Limpia el insumo seleccionado y la información relacionada.
    //
    // Se utiliza una función flecha porque la función es pequeña y solamente
    // realiza una operación específica.
    const resetearInsumo = () => {

        // Limpia el campo oculto que contiene el ID del insumo.
        inputIdInsumo.value = '';

        // Oculta la información del stock.
        infoStock.style.display = 'none';

        // Oculta el área asignada si el elemento existe.
        //
        // if evita intentar modificar style si el elemento no fue encontrado.
        if (infoAreaAsignada) infoAreaAsignada.style.display = 'none';

        // Restablece el texto del área asignada.
        if (nombreAreaAsignada) nombreAreaAsignada.textContent = '—';

        // Reinicia el stock máximo.
        stockMaximo = 0;

        // Comprueba que exista el campo de cantidad antes de modificarlo.
        if (inputCantidad) {

            // Elimina el atributo max para quitar el límite anterior.
            inputCantidad.removeAttribute('max');
        }
    };


    // Realiza la búsqueda de insumos en el servidor mediante fetch().
    //
    // fetch() se utiliza para hacer una petición HTTP sin recargar toda la página.
    // En este caso permite obtener las sugerencias de manera dinámica.
    const buscarInsumos = () => {

        // Obtiene el texto escrito por el usuario.
        //
        // ?. permite acceder a value solamente si el elemento existe.
        // || '' utiliza una cadena vacía cuando no existe un valor.
        // trim() elimina espacios al principio y al final del texto.
        const termino = (inputBuscarInsumo?.value || '').trim();

        // Obtiene el ID del área seleccionada.
        // Si no existe el selector o no tiene valor, utiliza una cadena vacía.
        const idArea = selectArea?.value || '';

        // Cancela la búsqueda anterior si todavía estaba esperando ejecutarse.
        //
        // Esto evita realizar varias búsquedas cuando el usuario escribe
        // rápidamente varias letras.
        clearTimeout(timeoutBusqueda);

        // No realiza la búsqueda hasta que existan al menos dos caracteres.
        if (termino.length < 2) {

            // Oculta las sugerencias.
            sugerenciasDiv.style.display = 'none';

            // Elimina cualquier sugerencia que todavía estuviera mostrada.
            sugerenciasDiv.innerHTML = '';

            return;
        }

        // Retrasa la búsqueda 300 milisegundos.
        //
        // setTimeout() permite esperar antes de ejecutar una función.
        // Aquí se utiliza para evitar realizar una petición al servidor
        // por cada tecla que presiona el usuario.
        timeoutBusqueda = setTimeout(() => {

            // Construye la URL que se utilizará para buscar los insumos.
            //
            // encodeURIComponent() convierte caracteres especiales del texto
            // para que puedan enviarse correctamente dentro de una URL.
            let url = `/bajas-insumos/buscar-insumos?q=${encodeURIComponent(termino)}`;

            // Si existe un área seleccionada, agrega su ID a la consulta.
            if (idArea) url += `&id_area_almacen=${encodeURIComponent(idArea)}`;

            // Realiza la petición al servidor.
            //
            // headers indica qué tipo de petición se está realizando y
            // qué formato de respuesta espera recibir JavaScript.
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })

                // then() procesa la respuesta cuando el servidor responde.
                .then(res => {

                    // Comprueba si la respuesta HTTP fue correcta.
                    if (!res.ok) throw new Error('Error de servidor');

                    // Convierte la respuesta del servidor de JSON a un objeto JavaScript.
                    return res.json();
                })

                // Recibe los datos convertidos desde JSON.
                .then(data => {

                    // Comprueba que el usuario todavía tenga escrito el mismo término
                    // que se utilizó cuando comenzó esta búsqueda.
                    //
                    // Esto evita mostrar resultados viejos si el usuario
                    // ya escribió algo diferente.
                    if ((inputBuscarInsumo?.value || '').trim() !== termino) {
                        return;
                    }

                    // Elimina las sugerencias anteriores.
                    sugerenciasDiv.innerHTML = '';

                    // Si el servidor no devolvió resultados, oculta las sugerencias.
                    if (!data || data.length === 0) {
                        sugerenciasDiv.style.display = 'none';
                        return;
                    }

                    // Recorre cada insumo recibido desde el servidor.
                    //
                    // forEach() se utiliza porque se necesita realizar la misma
                    // operación sobre cada elemento de la respuesta.
                    data.forEach(insumo => {

                        // Crea un botón para representar cada sugerencia.
                        const item = document.createElement('button');

                        // Define el tipo del botón.
                        item.type = 'button';

                        // Asigna las clases de Bootstrap para darle apariencia de elemento seleccionable.
                        item.className = 'list-group-item list-group-item-action';

                        // Establece una clase de color predeterminada para el tipo de insumo.
                        let badgeColorClass = 'bg-secondary';

                        // Cambia el color dependiendo del tipo de insumo.
                        if (insumo.tipo === 'Medicamento') {
                            badgeColorClass = 'bg-primary';
                        } else if (insumo.tipo === 'Material de curación') {
                            badgeColorClass = 'bg-success';
                        }

                        // Construye el contenido HTML que se mostrará en la sugerencia.
                        //
                        // Las comillas invertidas permiten utilizar template literals,
                        // lo que permite insertar variables directamente mediante ${}.
                        item.innerHTML = `
                            ${insumo.tipo ? `<span class="badge ${badgeColorClass} me-1" style="font-size: 0.7rem;">${insumo.tipo}</span>` : ''}
                            <span class="clave-badge">${insumo.clave}</span>
                            ${insumo.descripcion}
                            ${idArea ? `<span class="stock-info">Stock: ${insumo.stock}</span>` : ''}
                        `;

                        // Ejecuta esta función cuando el usuario selecciona la sugerencia.
                        item.addEventListener('click', () => {

                            // Muestra la clave y descripción del insumo seleccionado.
                            inputBuscarInsumo.value = `[${insumo.clave}] ${insumo.descripcion}`;

                            // Guarda el ID del insumo seleccionado.
                            inputIdInsumo.value = insumo.id_insumo;

                            // Oculta la lista de sugerencias.
                            sugerenciasDiv.style.display = 'none';

                            // Limpia las sugerencias del HTML.
                            sugerenciasDiv.innerHTML = '';

                            // Muestra el área asignada al insumo.
                            if (infoAreaAsignada && nombreAreaAsignada) {
                                nombreAreaAsignada.textContent = insumo.area_asignada || 'Sin Área Asignada';
                                infoAreaAsignada.style.display = 'inline-block';
                            }

                            // Si existe un área seleccionada, muestra el stock disponible.
                            if (idArea) {

                                // Convierte el stock recibido a número entero.
                                //
                                // parseInt() se utiliza porque el valor recibido
                                // puede llegar como texto.
                                // || 0 establece cero si la conversión no produce un valor válido.
                                stockMaximo = parseInt(insumo.stock) || 0;

                                // Muestra el stock disponible.
                                stockDisponible.textContent = stockMaximo;

                                // Muestra el contenedor del stock.
                                infoStock.style.display = 'inline-block';

                                // Establece el stock como cantidad máxima permitida.
                                if (inputCantidad) {
                                    inputCantidad.setAttribute('max', stockMaximo);
                                }
                            }
                        });

                        // Agrega el botón creado al contenedor de sugerencias.
                        sugerenciasDiv.appendChild(item);
                    });

                    // Muestra la lista de sugerencias.
                    sugerenciasDiv.style.display = 'block';
                })

                // catch() captura errores ocurridos durante la petición o procesamiento.
                .catch(err => {

                    // Muestra el error en la consola para facilitar la revisión.
                    console.error('Error al buscar insumos:', err);

                    // Oculta las sugerencias si ocurrió un error.
                    sugerenciasDiv.style.display = 'none';
                });

            // Espera 300 milisegundos antes de ejecutar la función.
        }, 300);
    };


    // Comprueba que exista el campo de búsqueda antes de registrar eventos.
    if (inputBuscarInsumo) {

        // Detecta cada cambio realizado en el campo de búsqueda.
        inputBuscarInsumo.addEventListener('input', () => {

            // Limpia el insumo seleccionado anteriormente.
            resetearInsumo();

            // Realiza nuevamente la búsqueda.
            buscarInsumos();
        });

        // Cierra las sugerencias cuando el usuario hace clic fuera del buscador.
        //
        // document permite escuchar eventos realizados en cualquier parte
        // de la página.
        document.addEventListener('click', (e) => {

            // contains() comprueba si un elemento contiene al elemento indicado.
            // Si el clic no ocurrió dentro del buscador ni dentro de las sugerencias,
            // se oculta la lista.
            if (!inputBuscarInsumo.contains(e.target) && !sugerenciasDiv.contains(e.target)) {
                sugerenciasDiv.style.display = 'none';
            }
        });
    }


    // ── 3. Al cambiar el área se reinicia el insumo ──────────────────────

    // Comprueba que exista el selector del área.
    if (selectArea) {

        // Detecta cuando el usuario selecciona otra área.
        selectArea.addEventListener('change', () => {

            // Limpia el insumo seleccionado porque pertenece al área anterior.
            resetearInsumo();

            // Si ya existe un texto de búsqueda con al menos dos caracteres,
            // vuelve a consultar los insumos para la nueva área.
            if (inputBuscarInsumo && inputBuscarInsumo.value.trim().length >= 2) {
                buscarInsumos();
            }
        });
    }


    // ── 4. Validar cantidad contra stock antes de enviar ─────────────────

    // Obtiene el formulario de baja.
    const formBaja = document.getElementById('formBaja');

    // Solo registra el evento si el formulario existe.
    if (formBaja) {

        // Escucha el evento submit antes de que el formulario sea enviado.
        formBaja.addEventListener('submit', function (e) {

            // Obtiene el ID del insumo seleccionado.
            const idInsumo = inputIdInsumo?.value;

            // Convierte la cantidad capturada a número entero.
            const cantidad = parseInt(inputCantidad?.value || 0);

            // Comprueba que el usuario haya seleccionado un insumo.
            if (!idInsumo) {

                // preventDefault() evita que el formulario se envíe.
                e.preventDefault();

                // Utiliza SweetAlert2 si está disponible.
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Insumo requerido',
                        text: 'Debe seleccionar un insumo de la lista de sugerencias.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                } else {

                    // Si SweetAlert2 no está disponible, utiliza la alerta
                    // básica del navegador.
                    alert('Debe seleccionar un insumo de la lista de sugerencias.');
                }

                // Detiene la ejecución del evento.
                return;
            }

            // Comprueba si la cantidad solicitada supera el stock disponible.
            if (stockMaximo > 0 && cantidad > stockMaximo) {

                // Evita enviar el formulario cuando no hay suficiente stock.
                e.preventDefault();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Stock insuficiente',
                        text: `La cantidad ingresada (${cantidad}) excede el stock disponible (${stockMaximo} piezas).`,
                        icon: 'error',
                        confirmButtonText: 'Corregir'
                    });
                } else {
                    alert(`La cantidad excede el stock disponible (${stockMaximo}).`);
                }
            }
        });
    }


    // ── 5. Confirmación para cambiar el estado de una baja ───────────────

    // Obtiene todos los enlaces que permiten cancelar o reactivar una baja.
    //
    // querySelectorAll() se utiliza porque puede existir más de un enlace
    // con esta clase dentro de la tabla.
    const toggleStatusLinks = document.querySelectorAll('.btn-toggle-baja-status');

    // Recorre todos los enlaces encontrados.
    toggleStatusLinks.forEach(link => {

        // Ejecuta la función cuando el usuario hace clic.
        link.addEventListener('click', function (e) {

            // Evita que el enlace navegue inmediatamente.
            // Primero se mostrará la ventana de confirmación.
            e.preventDefault();

            // Obtiene la URL almacenada en data-url.
            const url = this.getAttribute('data-url');

            // Obtiene el nombre del insumo almacenado en data-insumo.
            const insumo = this.getAttribute('data-insumo');

            // Obtiene la cantidad almacenada en data-cantidad.
            const cantidad = this.getAttribute('data-cantidad');

            // Obtiene la acción que se realizará.
            // Puede ser "cancelar" o "activar".
            const accion = this.getAttribute('data-accion');

            // Declara las variables que posteriormente recibirán
            // la configuración de la ventana de confirmación.
            let title, html, icon, confirmColor, confirmText;

            // Configura el mensaje cuando la acción es cancelar.
            if (accion === 'cancelar') {
                title = '¿Cancelar esta baja?';
                html = `Se cancelará la baja de <strong>${cantidad}</strong> unidad(es) de <strong>"${insumo}"</strong>.<br>El stock será restaurado automáticamente.`;
                icon = 'warning';
                confirmColor = '#d33';
                confirmText = 'Sí, cancelar baja';

                // Si la acción no es cancelar, se prepara la confirmación para reactivar.
            } else {
                title = '¿Reactivar esta baja?';
                html = `Se reactivará la baja de <strong>${cantidad}</strong> unidad(es) de <strong>"${insumo}"</strong>.<br>El stock será descontado del almacén.`;
                icon = 'question';
                confirmColor = '#3085d6';
                confirmText = 'Sí, reactivar baja';
            }

            // Comprueba si SweetAlert2 está disponible.
            if (typeof Swal !== 'undefined') {

                // Muestra la ventana de confirmación.
                Swal.fire({
                    title: title,
                    html: html,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {

                    // Solo continúa si el usuario confirmó la operación.
                    if (result.isConfirmed) {

                        // Redirige a la URL que realizará la operación.
                        window.location.href = url;
                    }
                });

                // Si SweetAlert2 no está disponible, utiliza confirm().
            } else {

                // Determina el texto que se mostrará en la confirmación.
                const desc = accion === 'cancelar' ? 'cancelar' : 'reactivar';

                // confirm() devuelve true cuando el usuario acepta.
                if (confirm(`¿Está seguro de que desea ${desc} la baja de "${insumo}"?`)) {
                    window.location.href = url;
                }
            }
        });
    });


    // ── 6. Buscador y filtrado en tiempo real ─────────────────────────────

    // Obtiene el campo donde el usuario escribe el texto de búsqueda.
    const inputBuscar = document.getElementById('inputBuscar');

    // Obtiene el formulario utilizado para realizar la búsqueda en el servidor.
    const formBuscar = document.getElementById('formBuscar');

    // Solo activa el buscador si existen ambos elementos.
    if (inputBuscar && formBuscar) {

        // Detecta cada cambio realizado en el campo de búsqueda.
        inputBuscar.addEventListener('input', function () {

            // Convierte el texto a minúsculas y elimina espacios innecesarios.
            // Esto permite realizar una comparación sin distinguir mayúsculas
            // de minúsculas.
            const query = inputBuscar.value.toLowerCase().trim();

            // Define las columnas de la tabla que serán revisadas.
            //
            // index representa la posición de la celda dentro de la fila.
            // label contiene el nombre que se mostrará cuando haya coincidencia.
            const columnasMap = [
                { index: 1, label: 'Insumo' },
                { index: 2, label: 'Clave' },
                { index: 3, label: 'Área' },
                { index: 4, label: 'Motivo' }
            ];

            // Obtiene todas las filas del cuerpo de la tabla.
            const rows = document.querySelectorAll('#tablaAreas tbody tr');

            // Guarda cuántas filas coinciden con la búsqueda.
            let matchCount = 0;

            // Recorre cada fila encontrada.
            rows.forEach(row => {

                // Ignora las filas que solamente muestran mensajes.
                if (row.cells.length === 1 && row.cells[0].classList.contains('text-center')) {
                    return;
                }

                // Ignora la fila creada específicamente para indicar
                // que no existen resultados locales.
                if (row.id === 'noLocalResultsRow') {
                    return;
                }

                // Obtiene la celda correspondiente al insumo.
                const celdaInsumo = row.cells[1];

                // Si existe la celda, busca un indicador de coincidencia anterior.
                if (celdaInsumo) {
                    const badgeViejo = celdaInsumo.querySelector('[data-match-badge]');

                    // Elimina el indicador anterior para evitar duplicarlo.
                    if (badgeViejo) {
                        badgeViejo.remove();
                    }
                }

                // Si el buscador está vacío, muestra nuevamente todas las filas.
                if (query === '') {
                    row.classList.remove('d-none');
                    return;
                }

                // Guarda la primera columna donde se encontró una coincidencia.
                let primeraCoincidenciaCol = null;

                // Revisa las columnas configuradas anteriormente.
                for (const col of columnasMap) {

                    // Obtiene la celda correspondiente a la columna actual.
                    const cell = row.cells[col.index];

                    // Comprueba que exista la celda y que su contenido incluya
                    // el texto buscado.
                    if (cell && cell.textContent.toLowerCase().includes(query)) {
                        primeraCoincidenciaCol = col;
                        break;
                    }
                }

                // Si encontró una coincidencia en alguna columna:
                if (primeraCoincidenciaCol) {

                    // Muestra la fila.
                    row.classList.remove('d-none');

                    // Incrementa el contador de coincidencias.
                    matchCount++;

                    // Si la coincidencia no fue encontrada en la columna Insumo,
                    // agrega una etiqueta indicando dónde se encontró.
                    if (celdaInsumo && primeraCoincidenciaCol.index !== 1) {

                        // Crea un elemento span para mostrar el indicador.
                        const badgeMatch = document.createElement('span');

                        // Asigna las clases visuales al indicador.
                        badgeMatch.className = 'badge bg-info text-dark ms-1 shadow-sm';

                        // Guarda una marca para poder localizar este indicador después.
                        badgeMatch.setAttribute('data-match-badge', 'true');

                        // Define el tamaño de la fuente.
                        badgeMatch.style.fontSize = '0.65rem';

                        // Muestra el nombre de la columna donde coincidió.
                        badgeMatch.textContent = `en ${primeraCoincidenciaCol.label}`;

                        // Agrega el indicador dentro de la celda del insumo.
                        celdaInsumo.appendChild(badgeMatch);
                    }

                    // Si no existe coincidencia, oculta la fila.
                } else {
                    row.classList.add('d-none');
                }
            });

            // Obtiene la fila que informa cuando no existen resultados locales.
            let noRecordsRow = document.getElementById('noLocalResultsRow');

            // Si existe una búsqueda y ninguna fila coincide, muestra el mensaje.
            if (query !== '' && matchCount === 0) {

                // Si todavía no existe la fila, la crea.
                if (!noRecordsRow) {
                    noRecordsRow = document.createElement('tr');
                    noRecordsRow.id = 'noLocalResultsRow';

                    // Define el contenido que mostrará el mensaje.
                    noRecordsRow.innerHTML = `
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-search fa-2x mb-2 d-block"></i>
                            No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en el servidor.
                        </td>
                    `;

                    // Agrega la fila al cuerpo de la tabla.
                    document.querySelector('#tablaAreas tbody').appendChild(noRecordsRow);

                    // Si la fila ya existe, solamente la vuelve a mostrar.
                } else {
                    noRecordsRow.style.display = '';

                    // Actualiza el mensaje con el texto de búsqueda actual.
                    noRecordsRow.querySelector('td').innerHTML = `
                        <i class="fa fa-search fa-2x mb-2 d-block"></i>
                        No se encontraron resultados locales para "${inputBuscar.value}". Presione Enter para buscar en el servidor.
                    `;
                }

                // Si existen resultados, oculta la fila de mensaje.
            } else if (noRecordsRow) {
                noRecordsRow.style.display = 'none';
            }
        });
    }


    // ── 7. Fechas: enviar el formulario automáticamente ─────────────────

    // Obtiene el campo correspondiente a la fecha inicial.
    const fechaInicio = document.getElementById('fecha_inicio');

    // Obtiene el campo correspondiente a la fecha final.
    const fechaFin = document.getElementById('fecha_fin');

    // Valida el rango de fechas y envía el formulario.
    const autoSubmitFecha = (e) => {

        // Obtiene la fecha inicial.
        const valInicio = fechaInicio?.value;

        // Obtiene la fecha final.
        const valFin = fechaFin?.value;

        // Comprueba que ambas fechas existan y que la inicial
        // no sea posterior a la fecha final.
        if (valInicio && valFin && valInicio > valFin) {

            // Utiliza SweetAlert2 cuando está disponible.
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Rango de fechas inválido',
                    text: 'La fecha de inicio no puede ser posterior a la fecha de fin.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                });
            } else {

                // Utiliza la alerta nativa si SweetAlert2 no está disponible.
                alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
            }

            // Restablece el campo que provocó el rango inválido.
            //
            // e.target representa el elemento que generó el evento change.
            if (e && e.target) {
                e.target.value = '';
            }

            // Detiene la función para evitar enviar el formulario con fechas inválidas.
            return;
        }

        // Envía el formulario si existe.
        //
        // submit() realiza el envío directamente sin generar nuevamente
        // el evento submit del formulario.
        if (formBuscar) formBuscar.submit();
    };

    // Ejecuta la validación cuando cambia la fecha inicial.
    if (fechaInicio) fechaInicio.addEventListener('change', (e) => autoSubmitFecha(e));

    // Ejecuta la validación cuando cambia la fecha final.
    if (fechaFin) fechaFin.addEventListener('change', (e) => autoSubmitFecha(e));

    // Obtiene el selector utilizado para filtrar por área.
    const filtroArea = document.getElementById('filtro_area');

    // Comprueba que existan el filtro y el formulario.
    if (filtroArea && formBuscar) {

        // Envía automáticamente el formulario cuando cambia el área.
        filtroArea.addEventListener('change', () => formBuscar.submit());
    }


    // ── 8. Panel de claves ────────────────────────────────────────────────

    // Inicializa el panel reutilizable de claves.
    //
    // Se utiliza una función importada para no repetir en este módulo
    // toda la lógica necesaria para manejar el panel.
    //
    // El objeto enviado como argumento permite configurar el comportamiento
    // del panel según los elementos y rutas de este módulo.
    initPanelClaves({
        panelId: 'panelClaves',
        inputBuscarId: 'buscarInsumo',
        inputHiddenId: 'id_insumo',
        sugerenciasId: 'sugerenciasInsumo',
        areaInputId: 'id_area_almacen',
        endpoint: '/bajas-insumos/buscar-insumos',
        columnaExtra: 'stock',

        // onSelect se ejecuta cuando el usuario selecciona un insumo
        // desde el panel de claves.
        onSelect: (insumo) => {

            // Obtiene el área seleccionada.
            const idArea = selectArea?.value || '';

            // Muestra el área asignada al insumo seleccionado.
            if (infoAreaAsignada && nombreAreaAsignada) {
                nombreAreaAsignada.textContent = insumo.area_asignada || 'Sin Área Asignada';
                infoAreaAsignada.style.display = 'inline-block';
            }

            // Si el servidor proporcionó stock y existe un área seleccionada,
            // actualiza la información de stock.
            if (insumo.stock !== undefined && idArea) {

                // Convierte el stock recibido a número entero.
                stockMaximo = parseInt(insumo.stock) || 0;

                // Muestra la cantidad disponible.
                if (stockDisponible) stockDisponible.textContent = stockMaximo;

                // Muestra la información de stock.
                if (infoStock) infoStock.style.display = 'inline-block';

                // Establece el stock como límite máximo del campo cantidad.
                if (inputCantidad) inputCantidad.setAttribute('max', stockMaximo);
            }
        }
    });


    // Comprueba si el modal fue abierto nuevamente debido a un error de validación.
    //
    // querySelector() busca el primer elemento que coincida con el selector.
    // En este caso busca un campo que tenga la clase is-invalid dentro del modal.
    let esReabiertoPorErrorBaja = document.querySelector('#modalAltaBaja .is-invalid') !== null;

    // Obtiene el modal donde se captura la baja.
    const modalAltaBaja = document.getElementById('modalAltaBaja');

    // Comprueba que el modal exista antes de agregarle el evento.
    if (modalAltaBaja) {

        // hidden.bs.modal es un evento de Bootstrap que se ejecuta
        // después de que el modal termina de cerrarse.
        modalAltaBaja.addEventListener('hidden.bs.modal', () => {

            // Obtiene el panel de claves.
            const panelClaves = document.getElementById('panelClaves');

            // Oculta el panel cuando se cierra el modal.
            if (panelClaves) panelClaves.style.display = 'none';

            // Si el modal fue abierto nuevamente por un error de validación,
            // conserva los datos para que el usuario pueda corregirlos.
            if (esReabiertoPorErrorBaja) {
                esReabiertoPorErrorBaja = false;
                return;
            }

            // Limpia el área seleccionada.
            if (selectArea) selectArea.value = '';

            // Limpia el texto de búsqueda del insumo.
            if (inputBuscarInsumo) inputBuscarInsumo.value = '';

            // Limpia el ID del insumo seleccionado.
            if (inputIdInsumo) inputIdInsumo.value = '';

            // Limpia la cantidad y elimina el límite máximo anterior.
            if (inputCantidad) {
                inputCantidad.value = '';
                inputCantidad.removeAttribute('max');
            }

            // Limpia los campos adicionales si existen.
            const motivoEl = document.getElementById('motivo');
            if (motivoEl) {
                motivoEl.value = '';
                motivoEl.dispatchEvent(new Event('change'));
            }

            const motivoOtroEl = document.getElementById('motivo_otro');
            if (motivoOtroEl) motivoOtroEl.value = '';

            const inicialesPacienteEl = document.getElementById('iniciales_paciente');
            if (inicialesPacienteEl) inicialesPacienteEl.value = '';

            const noExpedienteEl = document.getElementById('no_expediente');
            if (noExpedienteEl) noExpedienteEl.value = '';

            const doctorNombreEl = document.getElementById('doctor_nombre');
            if (doctorNombreEl) doctorNombreEl.value = '';

            const doctorEspecialidadEl = document.getElementById('doctor_especialidad');
            if (doctorEspecialidadEl) doctorEspecialidadEl.value = '';

            const personaEntregaEl = document.getElementById('persona_entrega');
            if (personaEntregaEl) personaEntregaEl.value = '';

            // Oculta la información del stock.
            if (infoStock) infoStock.style.display = 'none';

            // Oculta las sugerencias del buscador.
            if (sugerenciasDiv) sugerenciasDiv.style.display = 'none';

            // Reinicia el stock máximo.
            stockMaximo = 0;

            // Obtiene nuevamente el formulario de baja.
            const formBajaEl = document.getElementById('formBaja');

            // Si existe el formulario, busca los campos que tienen errores de validación.
            if (formBajaEl) {

                // querySelectorAll() obtiene todos los elementos que coinciden
                // con el selector indicado.
                formBajaEl.querySelectorAll('.is-invalid').forEach(el => {

                    // Elimina la clase que marca el campo como inválido.
                    el.classList.remove('is-invalid');
                });
            }
        });
    }

    // ── 6. Manejo dinámico del motivo 'Otro' ─────────────────────────────
    const motivoSelect = document.getElementById('motivo');
    const containerMotivoOtro = document.getElementById('container_motivo_otro');
    const motivoOtroInput = document.getElementById('motivo_otro');

    if (motivoSelect && containerMotivoOtro) {
        function toggleMotivoOtro() {
            if (motivoSelect.value === 'Otro') {
                containerMotivoOtro.style.display = 'block';
                if (motivoOtroInput) {
                    motivoOtroInput.setAttribute('required', 'required');
                }
            } else {
                containerMotivoOtro.style.display = 'none';
                if (motivoOtroInput) {
                    motivoOtroInput.removeAttribute('required');
                }
            }
        }

        motivoSelect.addEventListener('change', toggleMotivoOtro);
        toggleMotivoOtro();
    }
});