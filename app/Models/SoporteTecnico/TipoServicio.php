<?php

namespace App\Models\SoporteTecnico;

use Illuminate\Database\Eloquent\Model;
use App\Models\Area;

class TipoServicio extends Model
{
    protected $table = 'tipo_servicio';

    // La tabla legacy no usa timestamps automáticos de Laravel
    public $timestamps = false;

    protected $fillable = [
        'servicio',
        'id_area',
        'fecha',
        'hora',
        'activo',
        'usuario',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }
}
