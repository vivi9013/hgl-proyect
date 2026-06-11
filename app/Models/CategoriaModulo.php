<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Modulo;

class CategoriaModulo extends Model
{
    use HasFactory;

    // Nombre de la tabla legacy
    protected $table = 'categoria_modulo';

    // Llave primaria no estándar
    protected $primaryKey = 'id_CategoriaModulo';

    // Desactivar timestamps automáticos
    public $timestamps = false;

    // Columnas que se pueden llenar
    protected $fillable = [
        'categoria',
        'proyecto',
        'colapsado',
        'fecha_registro',
        'hora_registro',
        'id_usuario',
        'activo',
        'orden'
    ];

    /**
     * Relación con los módulos pertenecientes a esta categoría
     */
    public function modulos()
    {
        return $this->hasMany(Modulo::class, 'id_CategoriaModulo', 'id_CategoriaModulo');
    }
}
