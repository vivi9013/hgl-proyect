<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    protected $table = 'trabajadores';
    public $timestamps = false;

    protected $fillable = [
        'num_empleado',
        'id_persona',
        'id_sede',
        'id_departamento',
        'id_puesto',
        'id_tipo_trabajador',
        'fecha_ingreso',
        'fecha',
        'hora',
        'usuario',
        'activo',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'id_puesto');
    }

    public function tipoTrabajador()
    {
        return $this->belongsTo(TipoTrabajador::class, 'id_tipo_trabajador');
    }
}
