<?php

namespace App\Models\PeticionInsumos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\Insumo;

class DetallePlantillaPedido extends Model
{
    protected $table = 'detalle_plantilla_pedidos';
    protected $primaryKey = 'id_detalle_plantilla';
    public $timestamps = false;

    protected $fillable = [
        'id_plantilla_pedido',
        'id_insumo',
        'cve_insumo',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    public function plantilla()
    {
        return $this->belongsTo(PlantillaPedido::class, 'id_plantilla_pedido', 'id_plantilla_pedido');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }
}
