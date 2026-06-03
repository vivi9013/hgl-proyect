<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'insumos';

    /**
     * Clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_insumo';

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
        'clave',
        'descripcion',
        'tipo',
        'fecha_registro',
        'hora_registro',
        'activo',
        'id_usuario',
    ];

    /**
     * Cast de atributos de la base de datos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo'         => 'integer',
        'fecha_registro' => 'date',
    ];

    /**
     * Relación con insumosarea (stock por área).
     */
    public function insumosArea()
    {
        return $this->hasMany(InsumoArea::class, 'id_insumo', 'id_insumo');
    }

    /**
     * Relación con bajasinsumos.
     */
    public function bajas()
    {
        return $this->hasMany(BajaInsumo::class, 'id_insumo', 'id_insumo');
    }
}
