<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class AreaAbastecimiento extends Model
{
    /**
     * Tabla legacy.
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
        'siglas',
        'fecha_registro',
        'hora_registro',
        'activo',
        'id_usuario',
    ];

    /**
     * Subáreas que pertenecen a esta área (a través de la relación pivot).
     */
    public function subareas()
    {
        return $this->belongsToMany(
            SubareaAbastecimiento::class,
            'relacion_areas_abastecimiento',
            'id_area_abastecimiento',
            'id_subarea_abastecimiento'
        )
        ->withPivot(['id_relacion_areas_abastecimiento', 'activo', 'fecha_registro', 'hora_registro', 'id_usuario'])
        ->wherePivot('activo', 1);
    }

    /**
     * Todas las subáreas vinculadas (incluyendo inactivas) - para el toggle sin duplicados.
     */
    public function todasSubareas()
    {
        return $this->belongsToMany(
            SubareaAbastecimiento::class,
            'relacion_areas_abastecimiento',
            'id_area_abastecimiento',
            'id_subarea_abastecimiento'
        )
        ->withPivot(['id_relacion_areas_abastecimiento', 'activo', 'fecha_registro', 'hora_registro', 'id_usuario']);
    }

    /**
     * Plantillas de pedido que pertenecen a esta área.
     */
    public function plantillas()
    {
        return $this->hasMany(\App\Models\PeticionInsumos\PlantillaPedido::class, 'id_area_abastecimiento', 'id_area_abastecimiento');
    }
}
