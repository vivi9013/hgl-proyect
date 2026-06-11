<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    // Nombre de la tabla legacy
    protected $table = 'proyectos';

    // Llave primaria no estándar
    protected $primaryKey = 'id_proyecto';

    // Desactivar timestamps automáticos
    public $timestamps = false;

    // Columnas rellenables
    protected $fillable = [
        'proyecto',
        'fecha',
        'hora',
        'activo'
    ];

    /**
     * Relación de muchos a muchos con Módulos (a través de modulo_proyecto)
     */
    public function modulos()
    {
        return $this->belongsToMany(
            Modulo::class, 
            'modulo_proyecto', 
            'id_proyecto', 
            'id_modulo'
        )->withPivot('usuario', 'fecha', 'hora');
    }
}
