<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Pedido extends Model
{
    /**
     * Constantes de estado (mapeadas de la base de datos legacy).
     */
    const STATUS_PENDIENTE = 'terminado';   // "terminado" en DB legacy = enviado, pendiente de surtir
    const STATUS_ACEPTADO  = 'Aceptado';    // surtido y liberado
    const STATUS_CANCELADO = 'cancelado';   // cancelado
    const STATUS_BORRADOR  = 'borrador';    // guardado como borrador, aún no enviado

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
        'fecha_registro'     => 'date',
        'fecha_entrega'      => 'date',
        'activo'             => 'integer',
        'porcentaje_entrega' => 'float',
    ];

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Formato estandarizado de folio para pedidos (ej. PED-00042).
     */
    public function getFolioAttribute(): string
    {
        return 'PED-' . str_pad($this->id_pedido, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Configuración visual del badge de estado para las vistas Blade.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            self::STATUS_PENDIENTE => [
                'class' => 'bg-warning text-dark',
                'label' => 'Pendiente por surtir',
                'icon'  => 'fa-clock-o',
            ],
            self::STATUS_ACEPTADO => [
                'class' => 'bg-success',
                'label' => 'Aceptado',
                'icon'  => 'fa-check-circle',
            ],
            self::STATUS_CANCELADO => [
                'class' => 'bg-danger',
                'label' => 'Cancelado',
                'icon'  => 'fa-ban',
            ],
            self::STATUS_BORRADOR => [
                'class' => 'bg-secondary',
                'label' => 'Borrador',
                'icon'  => 'bi-pencil-square',
            ],
            default => [
                'class' => 'bg-secondary',
                'label' => $this->status ?? 'Desconocido',
                'icon'  => 'fa-question-circle',
            ],
        };
    }

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
