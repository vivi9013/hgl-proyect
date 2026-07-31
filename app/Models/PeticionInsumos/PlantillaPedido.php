<?php

namespace App\Models\PeticionInsumos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\AreaAlmacen;
use App\Models\User;

class PlantillaPedido extends Model
{
    protected $table = 'plantilla_pedidos';
    protected $primaryKey = 'id_plantilla_pedido';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_area_abastecimiento',
        'id_subarea_abastecimiento',
        'id_area_almacen',
        'fecha_registro',
        'hora_registro',
        'activo',
        'id_usuario',
    ];

    protected $casts = [
        'activo' => 'integer',
        'fecha_registro' => 'date',
    ];

    public function areaAbastecimiento()
    {
        return $this->belongsTo(AreaAbastecimiento::class, 'id_area_abastecimiento', 'id_area_abastecimiento');
    }

    public function subareaAbastecimiento()
    {
        return $this->belongsTo(SubareaAbastecimiento::class, 'id_subarea_abastecimiento', 'id_subarea_abastecimiento');
    }

    public function areaAlmacen()
    {
        return $this->belongsTo(AreaAlmacen::class, 'id_area_almacen', 'id_area_almacen');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePlantillaPedido::class, 'id_plantilla_pedido', 'id_plantilla_pedido');
    }
}
