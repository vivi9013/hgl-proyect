<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Computadora extends Model
{
    protected $table = 'computadoras';
    protected $primaryKey = 'id_computadora';

    // Desactivamos timestamps automáticos ya que el legacy maneja fecha y hora manualmente
    public $timestamps = false;

    protected $fillable = [
        'inventario',
        'so',
        'ram',
        'disco_duro',
        'ip',
        'tipo',
        'nombre_equipo',
        'activo',
        'fecha',
        'hora',
        'usuario'
    ];

    /**
     * Relación inversa con Mobiliario.
     * Vinculado a través de la columna 'inventario'.
     */
    public function mobiliario()
    {
        return $this->belongsTo(Mobiliario::class, 'inventario', 'inventario');
    }
}
