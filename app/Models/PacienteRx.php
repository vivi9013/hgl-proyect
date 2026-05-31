<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacienteRx extends Model
{
    use HasFactory;

    protected $table = 'pacientes_rx';
    protected $primaryKey = 'id_paciente';

    // Desactivar timestamps por defecto (el legacy maneja fecha_registro y hora_registro manualmente)
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'ap_paterno',
        'ap_materno',
        'telefono',
        'fecha_nacimiento',
        'sexo',
        'domicilio',
        'rfc',
        'homoclave',
        'nhc_hgl',
        'tiene_nhc',
        'sp',
        'tiene_sp',
        'fecha_registro',
        'hora_registro',
        'activo',
        'usuario',
    ];

    /**
     * Relación con el usuario registrador
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'usuario');
    }
}
