<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class AreaAbastecimiento extends Model
{
    /**
     * Tabla legacy. Ajustar el nombre si difiere en la BD.
     * Nombres comunes: areas_abastecimiento, areasabastecimiento, areas_surtimiento_ext
     */
    protected $table = 'areasabastecimiento';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_area_abastecimiento';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'nombre',
        'activo',
    ];

    /**
     * Subáreas que pertenecen a esta área.
     */
    public function subareas()
    {
        return $this->hasMany(SubareaAbastecimiento::class, 'id_area_abastecimiento', 'id_area_abastecimiento');
    }
}
