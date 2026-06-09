<?php

namespace App\Traits;

trait Sanitizable
{
    /**
     * Función de sanitización compatible con la lógica legacy.
     * Elimina acentos y caracteres especiales del español para rutas/archivos físicos.
     */
    public function sanearString($string)
    {
        $string = trim($string);
        $string = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'],
            $string
        );
        return $string;
    }
}
