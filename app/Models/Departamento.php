<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'extension',
        'abreviatura',
        'id_persona',
        'fecha',
        'hora',
        'activo',
        'usuario',
    ];

    /**
     * Persona responsable / jefe del departamento.
     */
    public function responsable()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    /**
     * Trabajadores asignados a este departamento.
     */
    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class, 'id_departamento');
    }

    /**
     * Mobiliario asignado a este departamento.
     */
    public function mobiliarios()
    {
        return $this->hasMany(Mobiliario::class, 'id_departamento');
    }
}
