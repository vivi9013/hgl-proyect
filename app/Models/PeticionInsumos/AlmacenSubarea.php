<?php

namespace App\Models\PeticionInsumos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;

class AlmacenSubarea extends Model
{
    protected $table = 'almacen_subareas';
    protected $primaryKey = 'id_almacen_subarea';
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

    public function detalles()
    {
        return $this->hasMany(DetalleAlmacenSubarea::class, 'id_almacen_subarea', 'id_almacen_subarea');
    }
}
