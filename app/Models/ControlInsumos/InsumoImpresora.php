<?php

namespace App\Models\ControlInsumos;

use Illuminate\Database\Eloquent\Model;

class InsumoImpresora extends Model
{
    protected $table      = 'insumos_impresoras';
    protected $primaryKey = 'id_insumo_impresora';
    public    $timestamps = false;

    protected $fillable = [
        'modelo',
        'color',
        'familia',
        'modelos_compatibles',
        'tiempo_uso',
        'hojas_uso_total',
        'stock',
        'activo',
        'fecha',
        'hora',
        'usuario',
    ];
}
