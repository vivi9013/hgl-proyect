<div class="table-responsive border rounded mb-3" style="max-height: 380px; overflow-y: auto;">
    <table class="table table-hover table-striped align-middle mb-0" id="tablaInsumosDiferencia" style="font-size: 0.85rem;">
        <thead class="bg-dark text-white sticky-top">
            <tr>
                <th class="ps-3 text-center" style="width: 40px;">
                    <input type="checkbox" id="checkSelectAllDiferencia" class="form-check-input" checked title="Seleccionar Todos">
                </th>
                <th style="width: 120px;">CLAVE</th>
                <th>DESCRIPCIÓN DEL INSUMO</th>
                <th class="text-center" style="width: 100px;">STOCK</th>
                <th class="text-center" style="width: 110px;">FONDO FIJO</th>
                <th class="text-center" style="width: 120px;">DÉFICIT / DIF.</th>
                <th class="text-center" style="width: 150px;">CANTIDAD A PEDIR</th>
            </tr>
        </thead>
        <tbody id="tbodyInsumosDiferencia">
            <tr id="trEmptyDiferencia">
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="bi bi-funnel fs-3 d-block mb-1 text-secondary"></i>
                    Seleccione un Área / Subárea o Almacén para calcular los faltantes automáticamente.
                </td>
            </tr>
        </tbody>
    </table>
</div>
