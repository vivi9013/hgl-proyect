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

    /**
     * Aplica el filtro de estatus (Activo / Inactivo) a un query Builder.
     *
     * Acepta un valor string ('Activo', 'Inactivo') o array de ellos,
     * o un string separado por comas ('Activo,Inactivo').
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array  $status
     * @param  string  $columna  Nombre de la columna booleana (default: 'activo')
     * @return void
     */
    protected function aplicarFiltroEstatus($query, $status, string $columna = 'activo'): void
    {
        if (empty($status)) {
            return;
        }

        $statusArray = is_array($status) ? $status : explode(',', $status);
        $statusInts  = array_map(fn($v) => $v === 'Activo' ? 1 : 0, $statusArray);
        $query->whereIn($columna, $statusInts);
    }
}
