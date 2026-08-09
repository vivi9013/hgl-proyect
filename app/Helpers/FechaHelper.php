<?php

namespace App\Helpers;

class FechaHelper
{
    /**
     * Devuelve el listado de meses en español indexados del 1 al 12.
     *
     * @return array<int, string>
     */
    public static function mesesEnEspanol(): array
    {
        return [
            1  => 'Enero',
            2  => 'Febrero',
            3  => 'Marzo',
            4  => 'Abril',
            5  => 'Mayo',
            6  => 'Junio',
            7  => 'Julio',
            8  => 'Agosto',
            9  => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    }

    /**
     * Devuelve el nombre de un mes específico (1 al 12).
     *
     * @param int $mes
     * @return string
     */
    public static function obtenerNombreMes(int $mes): string
    {
        $meses = static::mesesEnEspanol();
        return $meses[$mes] ?? '';
    }
}
