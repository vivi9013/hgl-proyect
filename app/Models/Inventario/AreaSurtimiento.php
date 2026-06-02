<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AreaSurtimiento extends Model
{
    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'areas_surtimiento';

    /**
     * Clave primaria personalizada para alineación con base de datos legacy.
     */
    protected $primaryKey = 'id_area_surtimiento';

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
        'tipo',
        'fecha_registro',
        'hora_registro',
        'activo',
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

    /**
     * Relación con el usuario registrador.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
