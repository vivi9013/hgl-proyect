<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    // Nombre de la tabla legacy
    protected $table = 'modulos';

    // Llave primaria estándar
    protected $primaryKey = 'id';

    // Desactivar timestamps automáticos
    public $timestamps = false;

    // Columnas que se pueden llenar
    protected $fillable = [
        'nombre',
        'carpeta',
        'id_CategoriaModulo',
        'descripcion',
        'creador',
        'orden',
        'color',
        'icono',
        'fecha',
        'hora',
        'activo',
        'usuario'
    ];

    /**
     * Relación: Un módulo pertenece a una categoría
     */
    public function categoria()
    {
        return $this->belongsTo(CategoriaModulo::class, 'id_CategoriaModulo', 'id_CategoriaModulo');
    }

    /**
     * Alias de categoría (para compatibilidad con vistas de reportes).
     */
    public function categoriaModulo()
    {
        return $this->belongsTo(CategoriaModulo::class, 'id_CategoriaModulo', 'id_CategoriaModulo');
    }

    /**
     * Relación de muchos a muchos con Perfiles (a través de la tabla pivote modulo_perfil)
     */
    public function perfiles()
    {
        return $this->belongsToMany(
            Perfil::class, 
            'modulo_perfil', 
            'id_modulo', 
            'id_perfil'
        )->withPivot('usuario', 'fecha', 'hora');
    }

    /**
     * Relación de muchos a muchos con Proyectos (a través de la tabla pivote modulo_proyecto)
     */
    public function proyectos()
    {
        return $this->belongsToMany(
            Proyecto::class, 
            'modulo_proyecto', 
            'id_modulo', 
            'id_proyecto'
        )->withPivot('usuario', 'fecha', 'hora');
    }
}
