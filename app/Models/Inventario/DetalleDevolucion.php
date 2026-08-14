<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class DetalleDevolucion extends Model
{
    /**
     * Tabla legacy en la base de datos.
     */
    protected $table = 'detalle_devoluciones';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_detalle_devolucion';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     * Columnas reales: id_devolucion, id_insumo, cantidad (sin motivo).
     */
    protected $fillable = [
        'id_devolucion',
        'id_insumo',
        'cantidad',
        'fecha_caducidad',
    ];

    /**
     * Cast de atributos.
     */
    protected $casts = [
        'cantidad'        => 'integer',
        'fecha_caducidad' => 'date',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * Devolución a la que pertenece este detalle.
     */
    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'id_devolucion', 'id_devolucion');
    }

    /**
     * Insumo referenciado en el detalle.
     */
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }
}
