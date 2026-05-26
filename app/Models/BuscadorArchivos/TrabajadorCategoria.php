<?php

namespace App\Models\BuscadorArchivos;

use Illuminate\Database\Eloquent\Model;

class TrabajadorCategoria extends Model
{
    // Nombre de la tabla legacy
    protected $table = 'trabajador_categorias';

    // Desactivar timestamps automáticos
    public $timestamps = false;

    // Columnas que se pueden llenar
    protected $fillable = [
        'id_trabajador',
        'id_categoria',
        'fecha_registro',
        'hora_registro',
        'usuario'
    ];
}
