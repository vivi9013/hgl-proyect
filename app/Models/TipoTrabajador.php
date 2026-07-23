<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTrabajador extends Model
{
    protected $table = 'tipo_trabajador';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'fecha',
        'hora',
        'activo',
        'usuario',
    ];

    /**
     * Trabajadores asignados a este tipo de trabajador.
     */
    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class, 'id_tipo_trabajador');
    }
}
