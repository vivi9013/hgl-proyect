<?php

namespace App\Models\SoporteTecnico;

use Illuminate\Database\Eloquent\Model;
use App\Models\Area;
use App\Models\Persona;
use App\Models\Departamento;
use App\Models\Sede;

class Servicio extends Model
{
    protected $table = 'servicios';

    // La tabla legacy no usa timestamps de Laravel
    public $timestamps = false;

    protected $fillable = [
        'id_usc',
        'id_personaSolicitante',
        'fecha_peticion',
        'hora_peticion',
        'id_departamento',
        'departamento',
        'descripcion_servicio',
        'id_area',
        'pendiente',
        'proceso',
        'terminado',
        'liberado',
        'estatus_final',
        'nombre_solicitante',
        'sexo_solicitante',
        'ext_telefonica',
        'sede',
        'abre_sede',
        'id_sede',
        'id_personaServidor',
        'nombre_servidor',
        'sexo_servidor',
        'fecha_tomado',
        'hora_tomado',
        'fecha_termino',
        'hora_termino',
        'fecha_finaliza',
        'hora_finaliza',
        'liberadox',
        'clasificacion_servicio',
        'accion_realizada',
        'id_tipo_servicio',
        'tipo_servicio',
        'modificado',
        'modificadox',
        'motivo_modificado',
        'fecha_modificado',
        'hora_modificado',
    ];

    // ─── Relaciones Eloquent ─────────────────────────────────────────────────────

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function solicitante()
    {
        return $this->belongsTo(Persona::class, 'id_personaSolicitante');
    }

    public function servidor()
    {
        return $this->belongsTo(Persona::class, 'id_personaServidor');
    }

    // ─── Accessors ───────────────────────────────────────────────────────────────

    /**
     * Devuelve los días transcurridos desde la fecha de petición hasta hoy.
     */
    public function getDiasTranscurridosAttribute(): int
    {
        if (!$this->fecha_peticion) {
            return 0;
        }
        try {
            $desde = \Carbon\Carbon::parse($this->fecha_peticion)->startOfDay();
            return (int) $desde->diffInDays(now()->startOfDay());
        } catch (\Exception $e) {
            return 0;
        }
    }
}
