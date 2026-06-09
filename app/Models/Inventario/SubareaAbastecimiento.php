<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class SubareaAbastecimiento extends Model
{
    /**
     * Tabla legacy. Ajustar el nombre si difiere en la BD.
     * Nombres comunes: subareas_abastecimiento, subareasabastecimiento
     */
    protected $table = 'subareas_abastecimiento';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_subarea_abastecimiento';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'id_area_abastecimiento',
        'nombre',
        'activo',
    ];

    /**
     * Área de abastecimiento a la que pertenece.
     */
    public function areaAbastecimiento()
    {
        return $this->belongsTo(AreaAbastecimiento::class, 'id_area_abastecimiento', 'id_area_abastecimiento');
    }
}
