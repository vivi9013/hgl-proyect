<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Motivo extends Model
{
    /**
     * Tabla legacy.
     */
    protected $table = 'motivos';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_motivo';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'descripcion',
        'modificar',
        'activo',
        'fecha_registro',
        'hora_registro',
        'id_usuario',
    ];
}
