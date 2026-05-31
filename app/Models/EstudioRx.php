<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstudioRx extends Model
{
    use HasFactory;

    protected $table = 'estudios_rx';
    protected $primaryKey = 'id_estudios';

    public $timestamps = false;

    protected $fillable = [
        'nhc',
        'nombre',
        'ap_paterno',
        'ap_materno',
        'nacimiento',
        'edad',
        'sexo',
        'fecha_estudio',
        'sp',
        'hgl',
        'craneo',
        'tx',
        'abd',
        'col',
        'm_sup',
        'm_inf',
        'contraste',
        'total_estudios',
        'especificado',
        'total_cds',
        'especialidad',
        'medico',
        'otros_datos',
        'fecha_registro',
        'hora_registro',
        'activo',
        'usuario',
    ];

    /**
     * Relación con el Médico RX
     */
    public function medicoRx()
    {
        return $this->belongsTo(MedicoRx::class, 'medico');
    }

    /**
     * Relación con la Especialidad RX
     */
    public function especialidadRx()
    {
        return $this->belongsTo(EspecialidadRx::class, 'especialidad');
    }

    /**
     * Relación con el usuario registrador
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'usuario');
    }
}
