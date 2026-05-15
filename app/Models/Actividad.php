<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividades';
    public $timestamps = false;

    protected $fillable = [
        'icono',
        'descripcion',
        'filtro',
        'id_usuario',
        'id_persona',
        'fecha',
        'hora'
    ];
}
