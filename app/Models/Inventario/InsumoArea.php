<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class InsumoArea extends Model
{
    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'insumosarea';

    /**
     * Clave primaria asociada a la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_insumo_area';

    /**
     * Indica si el modelo debe estar marcado con timestamps (created_at/updated_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_insumo',
        'id_area_almacen',
        'stock',
        'fondo_fijo',
    ];

    /**
     * Cast de atributos de la base de datos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stock'      => 'integer',
        'fondo_fijo' => 'integer',
    ];

    /**
     * Devuelve el nivel de stock en función del stock actual y el fondo fijo.
     *
     * Niveles:
     *   - 'muy_bajo'  : < 25%
     *   - 'bajo'      : 25% - 49%
     *   - 'regular'   : 50% - 74%
     *   - 'suficiente': 75% - 100%
     *   - 'excedido'  : > 100%
     */
    public static function calcularNivelStock(int $stock, int $fondoFijo): string
    {
        if ($fondoFijo <= 0) {
            return $stock > 0 ? 'excedido' : 'muy_bajo';
        }

        $porcentaje = ($stock * 100) / $fondoFijo;

        return match (true) {
            $porcentaje < 25   => 'muy_bajo',
            $porcentaje < 50   => 'bajo',
            $porcentaje < 75   => 'regular',
            $porcentaje <= 100 => 'suficiente',
            default            => 'excedido',
        };
    }

    /**
     * Devuelve el objeto con metadatos de nivel de stock (clase CSS, icono FontAwesome, color hex).
     */
    public static function obtenerMetaNivelStock(string $nivel): array
    {
        return match ($nivel) {
            'muy_bajo'   => [
                'etiqueta'   => 'Muy Bajo (< 25%)',
                'badgeClass' => 'bg-danger',
                'icono'      => 'fa-thermometer-empty',
                'color'      => '#e74c3c',
                'stockClass' => 'stock-muy-bajo',
            ],
            'bajo'       => [
                'etiqueta'   => 'Bajo (25-49%)',
                'badgeClass' => 'bg-warning text-dark',
                'icono'      => 'fa-thermometer-quarter',
                'color'      => '#e67e22',
                'stockClass' => 'stock-bajo',
            ],
            'regular'    => [
                'etiqueta'   => 'Regular (50-74%)',
                'badgeClass' => 'bg-info text-dark',
                'icono'      => 'fa-thermometer-half',
                'color'      => '#f1c40f',
                'stockClass' => 'stock-regular',
            ],
            'suficiente' => [
                'etiqueta'   => 'Suficiente (75-100%)',
                'badgeClass' => 'bg-success',
                'icono'      => 'fa-thermometer-three-quarters',
                'color'      => '#27ae60',
                'stockClass' => 'stock-suficiente',
            ],
            'excedido'   => [
                'etiqueta'   => 'Excedido (> 100%)',
                'badgeClass' => 'bg-primary',
                'icono'      => 'fa-thermometer-full',
                'color'      => '#2980b9',
                'stockClass' => 'stock-excedido',
            ],
            default      => [
                'etiqueta'   => 'Desconocido',
                'badgeClass' => 'bg-secondary',
                'icono'      => 'fa-question-circle',
                'color'      => '#7f8c8d',
                'stockClass' => '',
            ],
        };
    }

    /**
     * Accessor: Nivel de stock de esta instancia.
     */
    public function getNivelStockAttribute(): string
    {
        return self::calcularNivelStock($this->stock ?? 0, $this->fondo_fijo ?? 0);
    }

    /**
     * Relación con Insumo.
     */
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo', 'id_insumo');
    }

    /**
     * Relación con AreaAlmacen.
     */
    public function areaAlmacen()
    {
        return $this->belongsTo(AreaAlmacen::class, 'id_area_almacen', 'id_area_almacen');
    }

    /**
     * Scope para filtrar la consulta por uno o varios niveles de stock.
     */
    public function scopeConNivelStock($query, array $niveles)
    {
        if (empty($niveles)) {
            return $query;
        }

        return $query->where(function ($q) use ($niveles) {
            foreach ($niveles as $nivel) {
                match ($nivel) {
                    'muy_bajo'   => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / NULLIF(fondo_fijo, 0)) BETWEEN 0 AND 24'),
                    'bajo'       => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / NULLIF(fondo_fijo, 0)) BETWEEN 25 AND 49'),
                    'regular'    => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / NULLIF(fondo_fijo, 0)) BETWEEN 50 AND 74'),
                    'suficiente' => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / NULLIF(fondo_fijo, 0)) BETWEEN 75 AND 100'),
                    'excedido'   => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / NULLIF(fondo_fijo, 0)) > 100'),
                    default      => null,
                };
            }
        });
    }
}
