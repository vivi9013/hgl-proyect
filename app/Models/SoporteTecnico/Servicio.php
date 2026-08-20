<?php

namespace App\Models\SoporteTecnico;

use Illuminate\Database\Eloquent\Model;
use App\Models\Area;
use App\Models\Persona;
use App\Models\Departamento;
use App\Models\Sede;
use App\Models\Mobiliario;

class Servicio extends Model
{
    protected $table = 'servicios';

    // La tabla legacy no usa timestamps de Laravel
    public $timestamps = false;

    protected $fillable = [
        'id_usc',
        'id_personaSolicitante',
        'nombre_solicitante',
        'sexo_solicitante',
        'fecha_peticion',
        'hora_peticion',
        'id_departamento',
        'departamento',
        'ext_telefonica',
        'id_mobiliario',
        'inventario',
        'descripcion_mobiliario',
        'descripcion_servicio',
        'accion_realizada',
        'id_uss',
        'id_personaServidor',
        'nombre_servidor',
        'sexo_servidor',
        'fecha_tomado',
        'hora_tomado',
        'fecha_termino',
        'hora_termino',
        'fecha_finaliza',
        'hora_finaliza',
        'pendiente',
        'proceso',
        'terminado',
        'liberado',
        'estatus_final',
        'finaliza',
        'clasificacion_servicio',
        'id_area',
        'id_tipo_servicio',
        'tipo_servicio',
        'sede',
        'abre_sede',
        'id_sede',
        'liberadox',
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

    public function mobiliario()
    {
        return $this->belongsTo(Mobiliario::class, 'id_mobiliario');
    }

    public function tipoServicioRel()
    {
        return $this->belongsTo(TipoServicio::class, 'id_tipo_servicio');
    }

    public function departamentoRel()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function sedeRel()
    {
        return $this->belongsTo(Sede::class, 'id_sede');
    }

    // ─── Scopes de Consulta ──────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('pendiente', 1)
                     ->where('proceso', 0)
                     ->where('liberado', 0);
    }

    public function scopeEnProceso($query)
    {
        return $query->where('proceso', 1)
                     ->where('terminado', 0)
                     ->where('liberado', 0);
    }

    public function scopeTerminados($query)
    {
        return $query->where('terminado', 1)
                     ->where('liberado', 0);
    }

    public function scopeLiberados($query)
    {
        return $query->where('liberado', 1);
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

    /**
     * Etiqueta de estado legible para la interfaz.
     */
    public function getEstadoTextoAttribute(): string
    {
        if ($this->estatus_final === 'Cancelado') {
            return 'Cancelado';
        }
        if ($this->liberado == 1) {
            return 'Liberado';
        }
        if ($this->terminado == 1) {
            return 'Terminado (Por Liberar)';
        }
        if ($this->proceso == 1) {
            return 'En Proceso';
        }
        return 'Pendiente';
    }

    /**
     * Clase de badge CSS según estado.
     */
    public function getEstadoBadgeClassAttribute(): string
    {
        if ($this->estatus_final === 'Cancelado') {
            return 'bg-danger text-white';
        }
        if ($this->liberado == 1) {
            return 'bg-success text-white';
        }
        if ($this->terminado == 1) {
            return 'bg-primary text-white';
        }
        if ($this->proceso == 1) {
            return 'bg-warning text-dark';
        }
        return 'bg-secondary text-white';
    }
}
