<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    /**
     * Tabla legacy.
     */
    protected $table = 'detalle_pedidos';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_detalle_pedido';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'id_pedido',
        'id_insumo',
        'cve_insumo',
        'cantidad',
        'existencia',
        'fondo_fijo',
        'surtido',
        'faltante',
    ];

    /**
     * Cast de atributos.
     */
    protected $casts = [
        'cantidad' => 'integer',
        'existencia' => 'integer',
        'fondo_fijo' => 'integer',
        'surtido' => 'integer',
        'faltante' => 'integer',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * Pedido al que pertenece el detalle.
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Insumo asociado al detalle.
     */
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }
}
