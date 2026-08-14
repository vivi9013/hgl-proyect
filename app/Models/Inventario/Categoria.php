<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    /**
     * Tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'categorias';

    /**
     * Clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_categoria';

    /**
     * Desactivar timestamps automáticos.
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
        'nombre_categoria',
        'descripcion',
    ];

    /**
     * Relación con los insumos pertenecientes a esta categoría.
     */
    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'id_categoria', 'id_categoria');
    }
}
