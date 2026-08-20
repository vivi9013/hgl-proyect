<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoporteArea extends Model
{
    protected $table = 'soporte_area';
    public $timestamps = false;

    protected $fillable = [
        'id_area',
        'id_persona',
        'fecha',
        'hora',
        'activo',
        'usuario',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────────

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }
}
