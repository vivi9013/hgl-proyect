<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class SubareaAbastecimiento extends Model
{
    /**
     * Tabla legacy.
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
        'nombre',
        'siglas',
        'fecha_registro',
        'hora_registro',
        'activo',
        'id_usuario',
    ];

    /**
     * Relación con la tabla pivot relacion_areas_abastecimiento (solo vínculos activos).
     */
    public function relacionArea()
    {
        return $this->hasOne(RelacionAreaAbastecimiento::class, 'id_subarea_abastecimiento', 'id_subarea_abastecimiento')
            ->where('activo', 1);
    }

    /**
     * Áreas de abastecimiento activas a las que pertenece (a través de la tabla pivot).
     */
    public function areas()
    {
        return $this->belongsToMany(
            AreaAbastecimiento::class,
            'relacion_areas_abastecimiento',
            'id_subarea_abastecimiento',
            'id_area_abastecimiento'
        )
        ->withPivot(['id_relacion_areas_abastecimiento', 'activo', 'fecha_registro', 'hora_registro', 'id_usuario'])
        ->wherePivot('activo', 1);
    }

    /**
     * Área de abastecimiento a la que pertenece (primera activa, para compatibilidad).
     */
    public function areaAbastecimiento()
    {
        return $this->hasOneThrough(
            AreaAbastecimiento::class,
            RelacionAreaAbastecimiento::class,
            'id_subarea_abastecimiento', // FK en relacion_areas_abastecimiento
            'id_area_abastecimiento',    // FK en areasabastecimiento
            'id_subarea_abastecimiento', // LK en subareas_abastecimiento
            'id_area_abastecimiento'     // LK en relacion_areas_abastecimiento
        );
    }
}
