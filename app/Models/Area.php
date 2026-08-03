<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';

    // Desactivamos timestamps automáticos ya que el legacy maneja fecha y hora manualmente
    public $timestamps = false;

    protected $fillable = [
        'area',
        'abreviatura',
        'icono',
        'color',
        'fecha',
        'hora',
        'activo',
        'usuario'
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────────

    /**
     * Servicios pendientes de liberación (para módulo Solicitar Servicio).
     */
    public function serviciosPendientes()
    {
        return $this->hasMany(\App\Models\SoporteTecnico\Servicio::class, 'id_area')
                    ->where('liberado', 0);
    }
}
