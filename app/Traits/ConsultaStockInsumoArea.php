<?php

namespace App\Traits;

use App\Models\Inventario\InsumoArea;
use Illuminate\Http\Request;

trait ConsultaStockInsumoArea
{
    /**
     * Consulta el stock disponible de un insumo específico en un área de almacén vía AJAX.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function consultarStock(Request $request)
    {
        $idInsumo = $request->get('id_insumo');
        $idArea   = $request->get('id_area_almacen');

        if (!$idInsumo || !$idArea) {
            return response()->json([
                'stock' => 0,
                'error' => 'Parámetros incompletos'
            ]);
        }

        $insumoArea = InsumoArea::where('id_insumo', $idInsumo)
            ->where('id_area_almacen', $idArea)
            ->first();

        $stock = $insumoArea ? (int)$insumoArea->stock : 0;

        return response()->json([
            'stock' => $stock
        ]);
    }
}
