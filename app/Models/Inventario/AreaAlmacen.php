<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class AreaAlmacen extends Model
{
    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'areas_almacen';

    /**
     * Clave primaria personalizada para alineación con base de datos legacy.
     */
    protected $primaryKey = 'id_area_almacen';

    /**
     * Desactivar timestamps por defecto (el legacy maneja fecha_registro y hora_registro manualmente).
     */
    public $timestamps = false;

    /**
     * Campos permitidos para asignación masiva.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'activo',
        'fecha_registro',
        'hora_registro',
        'id_usuario',
    ];

    /**
     * Conversión de tipos automáticos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo'         => 'integer',
        'fecha_registro' => 'date',
    ];

    // ── Helpers de dominio ───────────────────────────────────────────────────

    public static function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $query = static::whereRaw('LOWER(nombre) = ?', [strtolower(trim($nombre))]);

        if ($excluirId !== null) {
            $query->where('id_area_almacen', '!=', $excluirId);
        }

        return $query->exists();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeFiltradoPor($query, ?string $buscar)
    {
        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('id_area_almacen', 'LIKE', "%{$buscar}%");
            });
        }

        return $query;
    }
}
