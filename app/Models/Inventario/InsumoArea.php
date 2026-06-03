<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class InsumoArea extends Model
{
    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'insumosarea';

    /**
     * Clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_insumo_area';

    /**
     * Indica si el modelo debe estar marcado con timestamps (created_at/updated_at).
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
        'stock',
        'fondo_fijo',
    ];

    /**
     * Cast de atributos de la base de datos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fondo_fijo' => 'integer',
    ];

    /**
     * Relación con Insumo.
     */
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }

    /**
     * Relación con AreaAlmacen.
     */
    public function areaAlmacen()
    {
        return $this->belongsTo(AreaAlmacen::class, 'id_area_almacen', 'id_area_almacen');
    }
}
