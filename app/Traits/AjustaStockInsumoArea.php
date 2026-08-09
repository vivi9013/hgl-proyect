<?php

namespace App\Traits;

use App\Models\Inventario\InsumoArea;

trait AjustaStockInsumoArea
{
    /**
     * Incrementa o decrementa el stock de un registro InsumoArea garantizando un piso de 0.
     *
     * @param InsumoArea $insumoArea
     * @param int $cantidad
     * @param string $operacion 'sumar' | 'restar'
     * @return void
     */
    protected function ajustarStockInsumoArea(InsumoArea $insumoArea, int $cantidad, string $operacion = 'sumar'): void
    {
        $stockActual = (int) $insumoArea->stock;
        $nuevoStock  = ($operacion === 'sumar') ? ($stockActual + $cantidad) : ($stockActual - $cantidad);

        $insumoArea->update([
            'stock' => max(0, $nuevoStock)
        ]);
    }
}
