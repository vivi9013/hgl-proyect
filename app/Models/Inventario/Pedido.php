<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Pedido extends Model
{
    /**
     * Tabla legacy.
     */
    protected $table = 'pedidos';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_pedido';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'id_area_abastecimiento',
        'id_subarea_abastecimiento',
        'id_area_almacen',
        'fecha_registro',
        'hora_registro',
        'status',
        'activo',
        'id_usuario',
        'id_persona_entrega',
        'fecha_entrega',
        'hora_entrega',
        'porcentaje_entrega',
    ];

    /**
     * Cast de atributos.
     */
    protected $casts = [
        'fecha_registro' => 'date',
        'fecha_entrega' => 'date',
        'activo' => 'integer',
        'porcentaje_entrega' => 'float',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * Área de abastecimiento asociada.
     */
    public function areaAbastecimiento()
    {
        return $this->belongsTo(AreaAbastecimiento::class, 'id_area_abastecimiento', 'id_area_abastecimiento');
    }

    /**
     * Subárea de abastecimiento asociada.
     */
    public function subareaAbastecimiento()
    {
        return $this->belongsTo(SubareaAbastecimiento::class, 'id_subarea_abastecimiento', 'id_subarea_abastecimiento');
    }

    /**
     * Área de almacén asociada.
     */
    public function areaAlmacen()
    {
        return $this->belongsTo(AreaAlmacen::class, 'id_area_almacen', 'id_area_almacen');
    }

    /**
     * Usuario que registró el pedido.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Detalles del pedido.
     */
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }
}
