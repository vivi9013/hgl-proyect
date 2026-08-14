<?php

namespace App\Traits;

use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use Illuminate\Http\Request;

trait BuscaInsumosAjax
{
    /**
     * Búsqueda AJAX de insumos por clave o descripción con soporte opcional para filtrado por área de almacén.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarInsumos(Request $request)
    {
        $termino = $request->get('q', '');
        $idArea  = $request->get('id_area_almacen');
        $all     = $request->boolean('all', false);

        if ($all && $idArea !== null && empty($idArea)) {
            return response()->json([]);
        }

        if (!$all && strlen($termino) < 2) {
            return response()->json([]);
        }

        $query = Insumo::with(['areaAbastecimiento'])->where('activo', 1);

        if (strlen($termino) >= 1) {
            $query->where(function ($q) use ($termino) {
                $q->where('descripcion', 'LIKE', "%{$termino}%")
                  ->orWhere('clave', 'LIKE', "%{$termino}%");
            });
        }

        if ($idArea) {
            $query->whereHas('insumosArea', function ($q) use ($idArea) {
                $q->where('id_area_almacen', $idArea)
                  ->whereRaw('CAST(stock AS UNSIGNED) >= 1');
            });
        }

        $insumos = $query->select('id_insumo', 'clave', 'descripcion', 'tipo', 'id_area_abastecimiento')
            ->orderBy('clave')
            ->when(!$all, fn($q) => $q->limit(20))
            ->get();

        if ($idArea) {
            $resultado = $insumos->map(function ($insumo) use ($idArea) {
                $insumoArea = InsumoArea::where('id_insumo', $insumo->id_insumo)
                    ->where('id_area_almacen', $idArea)
                    ->first();

                return [
                    'id_insumo'     => $insumo->id_insumo,
                    'clave'         => $insumo->clave,
                    'descripcion'   => $insumo->descripcion,
                    'tipo'          => $insumo->tipo,
                    'area_asignada' => $insumo->areaAbastecimiento->nombre ?? 'Sin Área Asignada',
                    'stock'         => $insumoArea ? (int)$insumoArea->stock : 0,
                ];
            });

            return response()->json($resultado);
        }

        $resultado = $insumos->map(function ($insumo) {
            $stockTotal = InsumoArea::where('id_insumo', $insumo->id_insumo)
                ->get()
                ->sum(fn($ia) => (int)$ia->stock);

            return [
                'id_insumo'     => $insumo->id_insumo,
                'clave'         => $insumo->clave,
                'descripcion'   => $insumo->descripcion,
                'tipo'          => $insumo->tipo,
                'area_asignada' => $insumo->areaAbastecimiento->nombre ?? 'Sin Área Asignada',
                'stock'         => $stockTotal,
            ];
        });

        return response()->json($resultado);
    }
}
