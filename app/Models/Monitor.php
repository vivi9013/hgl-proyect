<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    protected $table = 'monitores';
    protected $primaryKey = 'id_monitor';

    // Desactivamos timestamps automáticos ya que el legacy maneja fecha y hora manualmente
    public $timestamps = false;

    protected $fillable = [
        'inventario',
        'serie',
        'modelo',
        'marca',
        'descripcion',
        'tipo',
        'fecha',
        'hora',
        'activo',
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
