/**
 * stock-niveles.js — Clasificación compartida de niveles de stock
 *
 * Devuelve metadatos para el nivel según el porcentaje stock / fondoFijo:
 *   - muy_bajo  : < 25%
 *   - bajo      : 25% - 49%
 *   - regular   : 50% - 74%
 *   - suficiente: 75% - 100%
 *   - excedido  : > 100%
 */

export function clasificarStock(stock, fondoFijo) {
    const ff = parseInt(fondoFijo) || 0;
    const st = parseInt(stock) || 0;
    const porcentaje = ff > 0 ? (st * 100) / ff : (st > 0 ? 101 : 0);

    let nivel = 'muy_bajo';
    let iconoClass = 'fa fa-thermometer-empty fa-2x thermometer-icon';
    let color = '#e74c3c';
    let stockClass = 'stock-muy-bajo';
    let badgeClass = 'bg-danger';

    if (porcentaje < 25) {
        nivel = 'muy_bajo';
        iconoClass = 'fa fa-thermometer-empty fa-2x thermometer-icon';
        color = '#e74c3c';
        stockClass = 'stock-muy-bajo';
        badgeClass = 'bg-danger';
    } else if (porcentaje < 50) {
        nivel = 'bajo';
        iconoClass = 'fa fa-thermometer-quarter fa-2x thermometer-icon';
        color = '#e67e22';
        stockClass = 'stock-bajo';
        badgeClass = 'bg-warning text-dark';
    } else if (porcentaje < 75) {
        nivel = 'regular';
        iconoClass = 'fa fa-thermometer-half fa-2x thermometer-icon';
        color = '#f1c40f';
        stockClass = 'stock-regular';
        badgeClass = 'bg-info text-dark';
    } else if (porcentaje <= 100) {
        nivel = 'suficiente';
        iconoClass = 'fa fa-thermometer-three-quarters fa-2x thermometer-icon';
        color = '#27ae60';
        stockClass = 'stock-suficiente';
        badgeClass = 'bg-success';
    } else {
        nivel = 'excedido';
        iconoClass = 'fa fa-thermometer-full fa-2x thermometer-icon';
        color = '#2980b9';
        stockClass = 'stock-excedido';
        badgeClass = 'bg-primary';
    }

    return {
        nivel,
        porcentaje: Math.round(porcentaje * 10) / 10,
        iconoClass,
        color,
        stockClass,
        badgeClass
    };
}
