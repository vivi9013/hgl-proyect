<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Motivo extends Model
{
    /**
     * Tabla legacy.
     */
    protected $table = 'motivos';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_motivo';

    /**
     * Sin timestamps automáticos.
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'descripcion',
        'modificar',
        'activo',
        'fecha_registro',
        'hora_registro',
        'id_usuario',
    ];

    // ── Helpers de dominio ───────────────────────────────────────────────────

    /**
     * Verifica si ya existe un motivo con la misma descripción (case-insensitive).
     * Si se pasa $excluirId se excluye ese registro (útil en edición).
     */
    public static function existeDescripcion(string $descripcion, ?int $excluirId = null): bool
    {
        $query = static::whereRaw('LOWER(descripcion) = ?', [strtolower(trim($descripcion))]);

        if ($excluirId !== null) {
            $query->where('id_motivo', '!=', $excluirId);
        }

        return $query->exists();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Filtra por descripción (búsqueda parcial, insensible a mayúsculas).
     * Si $buscar está vacío no aplica ningún filtro.
     */
    public function scopeFiltradoPor(Builder $query, ?string $buscar): Builder
    {
        if (!empty($buscar)) {
            $query->where('descripcion', 'LIKE', "%{$buscar}%");
        }

        return $query;
    }
}
