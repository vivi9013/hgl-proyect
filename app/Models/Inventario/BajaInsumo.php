<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class BajaInsumo extends Model
{
    /**
     * Nombre de la tabla legacy en la base de datos.
     *
     * @var string
     */
    protected $table = 'bajasinsumos';

    /**
     * Clave primaria personalizada.
     *
     * @var string
     */
    protected $primaryKey = 'id_baja_insumo';

    /**
     * Deshabilitar timestamps automáticos (manejo manual con fecha_baja / hora_baja).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_insumo',
        'id_area_almacen',
        'motivo',
        'cantidad',
        'fecha_baja',
        'hora_baja',
        'id_usuario',
        'cancelado',
    ];

    /**
     * Cast de atributos de la base de datos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad'   => 'integer',
        'fecha_baja' => 'date',
    ];

    /**
     * Relación con el modelo Insumo.
     */
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }

    /**
     * Relación con el modelo AreaAlmacen.
     */
    public function areaAlmacen()
    {
        return $this->belongsTo(AreaAlmacen::class, 'id_area_almacen', 'id_area_almacen');
    }
}
