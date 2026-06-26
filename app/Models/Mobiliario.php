<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobiliario extends Model
{
    protected $table = 'mobiliario';

    // Desactivamos timestamps automáticos ya que el legacy maneja fecha y hora manualmente
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'marca',
        'modelo',
        'serie',
        'inventario',
        'otros',
        'id_tipo_mobiliario',
        'id_area',
        'id_persona',
        'id_departamento',
        'fecha',
        'hora',
        'activo',
        'usuario',
        'id_factura'
    ];

    /**
     * Relación con la especificación técnica de la computadora.
     * Vinculado a través de la columna 'inventario'.
     */
    public function computadora()
    {
        return $this->hasOne(Computadora::class, 'inventario', 'inventario');
    }

    /**
     * Relación con el tipo de mobiliario.
     */
    public function tipoMobiliario()
    {
        return $this->belongsTo(TipoMobiliario::class, 'id_tipo_mobiliario');
    }

    /**
     * Relación con el Área de asignación general.
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    /**
     * Relación con el Departamento de asignación.
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    /**
     * Relación con la Persona responsable asignada.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }
}
