<?php

namespace App\Models\ControlInsumos;

use Illuminate\Database\Eloquent\Model;

class MovimientoInsumo extends Model
{
    protected $table      = 'movimientos_insumos_impresoras';
    protected $primaryKey = 'id_movimiento';
    public    $timestamps = false;

    protected $fillable = [
        'id_insumo_impresora',
        'tipo',        // Entrada / Salida
        'concepto',    // Compra, Donación, Uso, Por daño
        'cantidad',
        'proveedor',
        'fecha_movimiento',
        'activo',      // 1: Activo, 0: Cancelado
        'fecha',
        'hora',
        'usuario',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function insumo()
    {
        return $this->belongsTo(InsumoImpresora::class, 'id_insumo_impresora', 'id_insumo_impresora');
    }

}
