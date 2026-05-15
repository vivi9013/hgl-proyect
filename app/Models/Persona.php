<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';

    public $timestamps = false;


    protected $fillable = [
        'nombre',
        'ap_paterno',
        'ap_materno',
        'fecha',
        'fecha_nac',
        'sexo',
        'id_sede',
        'activo'
    ];

    public function usuario()
    {
        return $this->hasOne(User::class, 'id_persona');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede');
    }

    public function trabajador()
    {
        return $this->hasOne(Trabajador::class, 'id_persona');
    }
}

