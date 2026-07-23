<?php

namespace App\Models\PeticionInsumos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\Insumo;

class DetalleAlmacenSubarea extends Model
{
    protected $table = 'detalle_almacen_subareas';
    protected $primaryKey = 'id_detalle_almacen_subarea';
    public $timestamps = false;

    protected $fillable = [
        'id_almacen_subarea',
        'id_insumo',
        'cve_insumo',
        'cantidad',
        'fondo_fijo',
    ];

    public function almacenSubarea()
    {
        return $this->belongsTo(AlmacenSubarea::class, 'id_almacen_subarea', 'id_almacen_subarea');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }
}
