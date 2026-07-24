<?php

namespace App\Models\Miscelaneo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Persona;

class Actividad extends Model
{
    use HasFactory;

    protected $table = 'actividades';
    protected $primaryKey = 'id_actividad';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'filtro',
        'fecha',
        'hora',
        'id_persona',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }
}
