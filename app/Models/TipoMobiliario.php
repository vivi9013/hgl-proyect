<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMobiliario extends Model
{
    protected $table = 'tipo_mobiliario';

    // Desactivamos timestamps automáticos ya que el legacy maneja fecha y hora manualmente
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'fecha',
        'hora',
        'activo',
        'usuario'
    ];
}
