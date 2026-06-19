<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EntradaCendis extends Model
{
    /**
     * Tabla legacy en la base de datos.
     */
    protected $table = 'entradas_cendis';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_entrada';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'id_area_surtimiento',
        'id_area_almacen',
        'fecha_entrada',
        'hora_entrada',
        'id_usuario_registro',
        'total_productos',
        'solicitado',
        'total_cantidad',
        'faltante',
        'status',
    ];

    /**
     * Cast de atributos.
     */
    protected $casts = [
        'fecha_entrada' => 'date',
        'solicitado'    => 'integer',
        'faltante'      => 'integer',
        'total_productos' => 'integer',
        'total_cantidad'  => 'integer',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * Área de surtimiento asociada.
     */
    public function areaSurtimiento()
    {
        return $this->belongsTo(AreaSurtimiento::class, 'id_area_surtimiento', 'id_area_surtimiento');
    }

    /**
     * Área de almacén asociada.
     */
    public function areaAlmacen()
    {
        return $this->belongsTo(AreaAlmacen::class, 'id_area_almacen', 'id_area_almacen');
    }

    /**
     * Usuario que registró la entrada.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario_registro', 'id');
    }

    /**
     * Detalles (insumos) de la entrada.
     */
    public function detalles()
    {
        return $this->hasMany(DetalleEntradaCendis::class, 'id_entrada', 'id_entrada');
    }
}
