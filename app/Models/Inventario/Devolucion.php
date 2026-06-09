<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Devolucion extends Model
{
    /**
     * Tabla legacy en la base de datos.
     */
    protected $table = 'devoluciones';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_devolucion';

    /**
     * Sin timestamps automáticos (se manejan manualmente).
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     * Mapeados exactamente a las columnas de la tabla legacy.
     */
    protected $fillable = [
        'id_area_abastecimiento',
        'id_subarea_abastecimiento',
        'id_area_almacen',
        'fecha_devolucion',
        'hora_devolucion',
        'id_usuario_registro',
        'total_productos',
        'total_cantidad',
        'status',
        'id_motivo',
    ];

    /**
     * Cast de atributos.
     */
    protected $casts = [
        'fecha_devolucion' => 'date',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────
    
    /**
     * Motivo asociado a la devolución.
     */
    public function motivo()
    {
        return $this->belongsTo(Motivo::class, 'id_motivo', 'id_motivo');
    }

    /**
     * Detalles (insumos) de la devolución.
     */
    public function detalles()
    {
        return $this->hasMany(DetalleDevolucion::class, 'id_devolucion', 'id_devolucion');
    }

    /**
     * Usuario que registró la devolución.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario_registro', 'id');
    }

    /**
     * Área de almacén asociada.
     */
    public function areaAlmacen()
    {
        return $this->belongsTo(AreaAlmacen::class, 'id_area_almacen', 'id_area_almacen');
    }

    /**
     * Área de abastecimiento.
     */
    public function areaAbastecimiento()
    {
        return $this->belongsTo(AreaAbastecimiento::class, 'id_area_abastecimiento', 'id_area_abastecimiento');
    }

    /**
     * Subárea de abastecimiento.
     */
    public function subareaAbastecimiento()
    {
        return $this->belongsTo(SubareaAbastecimiento::class, 'id_subarea_abastecimiento', 'id_subarea_abastecimiento');
    }
}
