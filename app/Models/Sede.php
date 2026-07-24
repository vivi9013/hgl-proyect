<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $table = 'sedes';
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'abreviatura',
        'fecha',
        'hora',
        'activo',
        'usuario',
    ];

    /**
     * Personas asignadas a esta sede.
     */
    public function personas()
    {
        return $this->hasMany(Persona::class, 'id_sede');
    }
}
