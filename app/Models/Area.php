<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';

    // Desactivamos timestamps automáticos ya que el legacy maneja fecha y hora manualmente
    public $timestamps = false;

    protected $fillable = [
        'area',
        'abreviatura',
        'icono',
        'color',
        'fecha',
        'hora',
        'activo',
        'usuario'
    ];
}
