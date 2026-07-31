<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoporteArea extends Model
{
    protected $table = 'soporte_area';
    public $timestamps = false;

    protected $fillable = [
        'id_area',
        'id_persona',
        'fecha',
        'hora',
        'activo',
        'usuario',
    ];
}
