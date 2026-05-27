<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sepomex extends Model
{
    protected $table = 'sepomex';

    public $timestamps = false;

    protected $fillable = ['d_estado', 'd_codigo'];

    /**
     * Retorna los estados únicos disponibles, ordenados alfabéticamente.
     */
    public static function estadosUnicos()
    {
        return self::select('d_estado')
            ->where('d_codigo', '<>', 0)
            ->groupBy('d_estado')
            ->orderBy('d_estado')
            ->pluck('d_estado');
    }
}
