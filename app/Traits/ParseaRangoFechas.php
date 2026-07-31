<?php

namespace App\Traits;

use Carbon\Carbon;

trait ParseaRangoFechas
{
    /**
     * Normaliza una fecha individual retornando [fechaFormatDb, fechaOriginal].
     *
     * @param string|null $fecha
     * @return array
     */
    public function normalizarFecha($fecha)
    {
        if (empty($fecha)) {
            return [null, ''];
        }

        try {
            if (strpos($fecha, '/') !== false) {
                $fechaDb = Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
            } else {
                $fechaDb = Carbon::parse($fecha)->format('Y-m-d');
            }
            return [$fechaDb, $fecha];
        } catch (\Exception $e) {
            return [null, $fecha];
        }
    }

    /**
     * Parsea y normaliza un rango de fechas (inicio y fin) enviado desde las peticiones HTTP.
     * Soporta formatos 'd/m/Y' e ISO ('Y-m-d').
     *
     * @param string|null $fechaInit
     * @param string|null $fechaFin
     * @return array [$fechaInitDb, $fechaFinDb, $errorMsg]
     */
    public function parsearRangoFechas($fechaInit, $fechaFin)
    {
        $fechaInitDb = null;
        $fechaFinDb = null;
        $errorMsg = null;

        if (!empty($fechaInit)) {
            try {
                if (strpos($fechaInit, '/') !== false) {
                    $fechaInitDb = Carbon::createFromFormat('d/m/Y', $fechaInit)->format('Y-m-d');
                } else {
                    $fechaInitDb = Carbon::parse($fechaInit)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                try {
                    $fechaInitDb = Carbon::parse($fechaInit)->format('Y-m-d');
                } catch (\Exception $ex) {
                    $fechaInitDb = null;
                }
            }
        }

        if (!empty($fechaFin)) {
            try {
                if (strpos($fechaFin, '/') !== false) {
                    $fechaFinDb = Carbon::createFromFormat('d/m/Y', $fechaFin)->format('Y-m-d');
                } else {
                    $fechaFinDb = Carbon::parse($fechaFin)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                try {
                    $fechaFinDb = Carbon::parse($fechaFin)->format('Y-m-d');
                } catch (\Exception $ex) {
                    $fechaFinDb = null;
                }
            }
        }

        if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {
            $errorMsg = 'La fecha de inicio no puede ser posterior a la fecha de fin.';
        }

        return [$fechaInitDb, $fechaFinDb, $errorMsg];
    }
}
