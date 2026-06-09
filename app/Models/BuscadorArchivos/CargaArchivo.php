<?php

namespace App\Models\BuscadorArchivos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Sanitizable;
use Illuminate\Support\Facades\Storage;

class CargaArchivo extends Model
{
    use HasFactory, Sanitizable;

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

    /**
     * Obtiene el nombre del archivo sanitizado con extensión .pdf
     */
    public function getNombreFisicoAttribute()
    {
        return $this->sanearString($this->nombre) . '.pdf';
    }

    /**
     * Obtiene la ruta relativa completa (carpeta + nombre) para el Storage
     */
    public function getRutaFisicaAttribute()
    {
        if (!$this->categoria) return null;
        
        $carpeta = $this->sanearString($this->categoria->categoria);
        return "formats/{$carpeta}/{$this->nombre_fisico}";
    }

    /**
     * Determina si el archivo existe físicamente en el disco.
     */
    public function getExisteFisicoAttribute()
    {
        return $this->ruta_fisica && Storage::disk('local')->exists($this->ruta_fisica);
    }
}
