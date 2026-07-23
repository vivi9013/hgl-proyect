<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait RespondeTablaAjax
{
    /**
     * Devuelve JSON para recargas AJAX de tabla, o null si la petición
     * es una carga completa de página (para que el controlador continúe
     * retornando la vista normal).
     */
    protected function respuestaTablaAjax(
        Request $request,
        LengthAwarePaginator $paginador,
        string $vistaPartial,
        array $datosVista,
        string $etiqueta = 'registros'
    ) {
        if (!$request->ajax() && !$request->wantsJson()) {
            return null;
        }

        return response()->json([
            'html'  => view($vistaPartial, $datosVista)->render(),
            'links' => $paginador->links('pagination::bootstrap-4')->render(),
            'total' => $paginador->total(),
            'info'  => 'Mostrando '
                . ($paginador->firstItem() ?? 0)
                . ' a '
                . ($paginador->lastItem() ?? 0)
                . " de {$paginador->total()} $etiqueta",
        ]);
    }
}
