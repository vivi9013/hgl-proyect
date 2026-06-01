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
        'activo',
        'ecivil',
        'rfc',
        'curp',
        'e_mail',
        'telefono',
        'colonia',
        'calle',
        'numero',
        'municipio',
        'estado',
        'hora',
        'usuario'
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

    public function categorias()
    {
        return $this->belongsToMany(
            \App\Models\BuscadorArchivos\CategoArchivo::class,
            'trabajador_categorias',
            'id_trabajador',
            'id_categoria'
        )->withPivot('fecha_registro', 'hora_registro', 'usuario');
    }
}


