<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class AreaAlmacen extends Model
{
    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'areas_almacen';

    /**
     * Clave primaria personalizada para alineación con base de datos legacy.
     */
    protected $primaryKey = 'id_area_almacen';

    /**
     * Desactivar timestamps por defecto (el legacy maneja fecha_registro y hora_registro manualmente).
     */
    public $timestamps = false;

    /**
     * Campos permitidos para asignación masiva.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'activo',
        'fecha_registro',
        'hora_registro',
        'id_usuario',
    ];

    /**
     * Conversión de tipos automáticos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo'         => 'integer',
        'fecha_registro' => 'date',
    ];
}
