<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait GestionaCatalogoSimple
{
    /**
     * Alterna el estado activo (1/0) de un modelo de catálogo.
     *
     * @param mixed $modelo Instancia del modelo Eloquent a modificar.
     * @param string $rutaIndex Nombre de la ruta de redirección.
     * @param string $mensajeExito Mensaje de éxito para la sesión.
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function alternarEstadoCatalogo($modelo, string $rutaIndex, string $mensajeExito = 'El estado del registro se ha actualizado correctamente.')
    {
        $modelo->activo         = $modelo->activo == 1 ? 0 : 1;
        $modelo->fecha_registro = now()->toDateString();
        $modelo->hora_registro  = now()->toTimeString();
        $modelo->id_usuario     = Auth::id() ?? 1;
        $modelo->save();

        return redirect()->route($rutaIndex)->with('exitog', $mensajeExito);
    }
}
