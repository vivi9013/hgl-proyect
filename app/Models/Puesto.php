<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    protected $table = 'puestos';
    public $timestamps = false;

    protected $fillable = [
        'puesto',
        'fecha',
        'hora',
        'activo',
        'usuario',
    ];

    /**
     * Trabajadores asignados a este puesto.
     */
    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class, 'id_puesto');
    }
}
