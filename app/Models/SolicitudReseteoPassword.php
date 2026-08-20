<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudReseteoPassword extends Model
{
    protected $table = 'solicitudes_reseteo_password';
    public $timestamps = false;

    protected $fillable = [
        'nombre_usuario', 'id_usuario', 'nombre_declarado', 'dato_adicional',
        'estado', 'ip', 'fecha', 'hora',
        'revisado_por', 'nota_revision', 'fecha_revision', 'hora_revision',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function revisadoPor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }
}
