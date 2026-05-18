<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfiles';
    public $timestamps = false;

    /**
     * Relación de muchos a muchos con Módulos (a través de modulo_perfil)
     */
    public function modulos()
    {
        return $this->belongsToMany(
            Modulo::class, 
            'modulo_perfil', 
            'id_perfil', 
            'id_modulo'
        )->withPivot('usuario', 'fecha', 'hora');
    }
}
