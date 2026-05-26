<?php

namespace App\Models\BuscadorArchivos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoArchivo extends Model
{
    use HasFactory;

    // Nombre de la tabla legacy
    protected $table = 'catego_archivos';

    // Llave primaria no estándar
    protected $primaryKey = 'id_catego_archivos';

    // Desactivar timestamps automáticos
    public $timestamps = false;

    // Columnas que se pueden llenar
    protected $fillable = [
        'categoria',
        'fecha_registro',
        'hora_registro',
        'activo',
        'usuario'
    ];

    /**
     * Relación con los archivos pertenecientes a esta categoría
     */
    public function archivos()
    {
        return $this->hasMany(CargaArchivo::class, 'id_catego', 'id_catego_archivos');
    }
}
