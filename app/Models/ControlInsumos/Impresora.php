<?php

namespace App\Models\ControlInsumos;

use Illuminate\Database\Eloquent\Model;

class Impresora extends Model
{
    protected $table      = 'impresoras';
    protected $primaryKey = 'id_impresora';
    public    $timestamps = false; // Auditoría manual: fecha, hora, usuario

    protected $fillable = [
        'inventario',
        'tipo',
        'serie',
        'modelo',
        'marca',
        'descripcion',
        'tecnologia',
        'consumible',
        'red',
        'ip',
        'comodato',
        'fecha',
        'hora',
        'usuario',
        'activo',
    ];
}
