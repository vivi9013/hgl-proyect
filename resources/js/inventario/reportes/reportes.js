/**
 * Lógica JavaScript para el módulo de Reportes de Inventario
 * Inventario de Medicamentos y Material de Curación – HGL
 */

document.addEventListener('DOMContentLoaded', function () {
    // ── Elementos - Reporte Diario de Entregas ──
    const almacen1 = $('#almacen1');
    const cmbArea = $('#cmbArea');
    const txtFecha1 = $('#txtFecha1');
    const btnImprimirEntregas = document.getElementById('btnImprimirEntregas');

    // ── Elementos - Concentrado CENDIS ──
    const areaAlmacen = $('#areaalmacen');
    const cmbArea2 = $('#cmbArea2');
    const chkSelectAllAreas = document.getElementById('chkSelectAllAreas');
    const cmbMes2 = $('#cmbMes2');
    const cmbAno2 = $('#cmbAno2');
    const btnImprimirConcentrado = document.getElementById('btnImprimirConcentrado');

    // Inicializar Select2
    if (typeof $.fn.select2 !== 'undefined') {
        almacen1.select2({ width: '100%' });
        cmbArea.select2({ width: '100%' });
        areaAlmacen.select2({ width: '100%' });
        cmbArea2.select2({ 
            width: '100%',
            placeholder: "Seleccione una o más áreas asignadas"
        });
    }

    // ── 1. Cargar Datos Iniciales vía AJAX ──
    cargarAreasAbastecimiento();
    cargarAreasAlmacen();

    // Función para cargar áreas asignadas (Abastecimiento)
    function cargarAreasAbastecimiento() {
        fetch('/reportes-inventario/areas-abastecimiento', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            cmbArea.empty().append('<option value="">Seleccione...</option>');
            cmbArea2.empty();

            data.forEach(item => {
                const option = new Option(item.nombre, item.id_area_abastecimiento);
                cmbArea.append($(option).clone());
                cmbArea2.append(option);
            });

            cmbArea.trigger('change');
            cmbArea2.trigger('change');
        })
        .catch(error => {
            console.error('Error al cargar áreas asignadas:', error);
            mostrarError('No se pudieron cargar las áreas asignadas.');
        });
    }

    // Función para cargar áreas de almacén
    function cargarAreasAlmacen() {
        fetch('/reportes-inventario/areas-almacen', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            almacen1.empty().append('<option value="">Seleccione...</option>');
            areaAlmacen.empty().append('<option value="">Seleccione...</option>');
            data.forEach(item => {
                const option1 = new Option(item.nombre, item.id_area_almacen);
                const option2 = new Option(item.nombre, item.id_area_almacen);
                almacen1.append(option1);
                areaAlmacen.append(option2);
            });
            almacen1.trigger('change');
            areaAlmacen.trigger('change');
        })
        .catch(error => {
            console.error('Error al cargar áreas de almacén:', error);
            mostrarError('No se pudieron cargar las áreas de almacén.');
        });
    }

    // ── 2. Lógica de Checkbox "Seleccionar Todas" (Reporte 2) ──
    if (chkSelectAllAreas) {
        chkSelectAllAreas.addEventListener('change', function () {
            const selectAll = chkSelectAllAreas.checked;
            if (selectAll) {
                const allValues = [];
                cmbArea2.find('option').each(function() {
                    allValues.push($(this).val());
                });
                cmbArea2.val(allValues).trigger('change');
            } else {
                cmbArea2.val(null).trigger('change');
            }
        });
    }

    // Desmarcar "Seleccionar todas" si el usuario remueve manualmente algún área
    cmbArea2.on('change', function() {
        const selectedCount = cmbArea2.val() ? cmbArea2.val().length : 0;
        const totalCount = cmbArea2.find('option').length;
        if (chkSelectAllAreas) {
            chkSelectAllAreas.checked = (selectedCount === totalCount && totalCount > 0);
        }
        validarFormulario2();
    });

    // ── 3. Validación de Formularios (Habilitación de Botones) ──
    function validarFormulario1() {
        const almacen = almacen1.val();
        const area = cmbArea.val();
        const fecha = txtFecha1.val();

        const valido = almacen && area && fecha;
        btnImprimirEntregas.disabled = !valido;
    }

    function validarFormulario2() {
        const almacen = areaAlmacen.val();
        const areas = cmbArea2.val();
        const mes = cmbMes2.val();
        const ano = cmbAno2.val();

        const valido = almacen && areas && areas.length > 0 && mes && ano && ano > 0;
        btnImprimirConcentrado.disabled = !valido;
    }

    // Listeners para validación en Reporte 1
    almacen1.on('change', validarFormulario1);
    cmbArea.on('change', validarFormulario1);
    txtFecha1.on('input change', validarFormulario1);

    // Listeners para validación en Reporte 2
    areaAlmacen.on('change', validarFormulario2);
    cmbMes2.on('change', validarFormulario2);
    cmbAno2.on('input change', validarFormulario2);

    // ── 4. Procesamiento de Impresión con SweetAlert2 ──

    // Botón Imprimir Entregas Diarias
    if (btnImprimirEntregas) {
        btnImprimirEntregas.addEventListener('click', function (e) {
            e.preventDefault();
            if (btnImprimirEntregas.disabled) return;

            const almacenVal = almacen1.val();
            const areaVal = cmbArea.val();
            const fechaVal = txtFecha1.val();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Confirmar Impresión?',
                    text: 'Se abrirá el Reporte Diario de Entregas en una nueva pestaña.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa fa-print"></i> Imprimir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = `/reportes-inventario/imprimir-entregas?AA=${almacenVal}&A=${areaVal}&F=${fechaVal}`;
                        window.open(url, '_blank');
                    }
                });
            } else {
                const url = `/reportes-inventario/imprimir-entregas?AA=${almacenVal}&A=${areaVal}&F=${fechaVal}`;
                window.open(url, '_blank');
            }
        });
    }

    // Botón Imprimir Concentrado CENDIS
    if (btnImprimirConcentrado) {
        btnImprimirConcentrado.addEventListener('click', function (e) {
            e.preventDefault();
            if (btnImprimirConcentrado.disabled) return;

            const almacenVal = areaAlmacen.val();
            const areasVal = (cmbArea2.val() || []).join(',');
            const mesVal = cmbMes2.val();
            const anoVal = cmbAno2.val();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Confirmar Impresión?',
                    text: 'Se abrirá el Concentrado CENDIS en una nueva pestaña.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa fa-print"></i> Imprimir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = `/reportes-inventario/imprimir-concentrado?AA=${almacenVal}&A=${areasVal}&M=${mesVal}&AP=${anoVal}`;
                        window.open(url, '_blank');
                    }
                });
            } else {
                const url = `/reportes-inventario/imprimir-concentrado?AA=${almacenVal}&A=${areasVal}&M=${mesVal}&AP=${anoVal}`;
                window.open(url, '_blank');
            }
        });
    }

    // ── Helper de Alerta de Error ──
    function mostrarError(mensaje) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: mensaje,
                icon: 'error',
                confirmButtonColor: '#000000',
                confirmButtonText: 'Aceptar'
            });
        } else {
            alert(mensaje);
        }
    }
});
