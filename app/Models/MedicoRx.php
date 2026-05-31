<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicoRx extends Model
{
    use HasFactory;

    protected $table = 'medicos_rx';
    protected $primaryKey = 'id_medicos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'abreviatura',
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
