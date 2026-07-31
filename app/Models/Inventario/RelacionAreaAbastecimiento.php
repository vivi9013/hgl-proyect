<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class RelacionAreaAbastecimiento extends Model
{
    protected $table = 'relacion_areas_abastecimiento';
    protected $primaryKey = 'id_relacion_areas_abastecimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_area_abastecimiento',
        'id_subarea_abastecimiento',
        'fecha_registro',
        'hora_registro',
        'activo',
        'id_usuario',
    ];

    public function areaAbastecimiento()
    {
        return $this->belongsTo(AreaAbastecimiento::class, 'id_area_abastecimiento', 'id_area_abastecimiento');
    }

    public function subareaAbastecimiento()
    {
        return $this->belongsTo(SubareaAbastecimiento::class, 'id_subarea_abastecimiento', 'id_subarea_abastecimiento');
    }
}
