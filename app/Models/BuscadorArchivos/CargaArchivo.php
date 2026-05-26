<?php

namespace App\Models\BuscadorArchivos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaArchivo extends Model
{
    use HasFactory;

    // Nombre de la tabla legacy
    protected $table = 'carga_archivos';

    // Llave primaria no estándar
    protected $primaryKey = 'id_archivo';

    // Desactivar timestamps automáticos
    public $timestamps = false;

    // Columnas que se pueden llenar
    protected $fillable = [
        'nombre',
        'id_catego',
        'descripcion_archivo',
        'version_archivo',
        'fecha_registro',
        'hora_registro',
        'activo',
        'usuario'
    ];

    /**
     * Relación: Un archivo pertenece a una categoría
     */
    public function categoria()
    {
        return $this->belongsTo(CategoArchivo::class, 'id_catego', 'id_catego_archivos');
    }
}
