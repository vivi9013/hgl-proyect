<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class DetalleEntradaCendis extends Model
{
    /**
     * Tabla legacy en la base de datos.
     */
    protected $table = 'detalle_entradas_cendis';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_detalle_entrada';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'id_entrada',
        'id_insumo',
        'solicitado',
        'cantidad',
        'faltante',
    ];

    /**
     * Cast de atributos.
     */
    protected $casts = [
        'solicitado' => 'integer',
        'cantidad'   => 'integer',
        'faltante'   => 'integer',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * Entrada de Cendis a la que pertenece este detalle.
     */
    public function entradaCendis()
    {
        return $this->belongsTo(EntradaCendis::class, 'id_entrada', 'id_entrada');
    }

    /**
     * Insumo referenciado en el detalle.
     */
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }
}
