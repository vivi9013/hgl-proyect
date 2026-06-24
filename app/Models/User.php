<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 1. Definimos el nombre real de tu tabla
    protected $table = 'usuarios';

    // Definimos la llave primaria explícitamente
    protected $primaryKey = 'id';

    // Desactivamos timestamps porque la tabla legacy no los tiene

    public $timestamps = false;


    // 2. Definimos las columnas que se pueden llenar (Ajústalas según tus campos reales)
    protected $fillable = [
        'nombre_usuario', 
        'contra',         // Tu columna de password se llama 'contra'
        'id_persona',
        'id_perfil',
        'activo',
        'tema',
        'primera',
        'fecha',
        'hora',
        'usuario',
        'cambiar_contrasena'
    ];

    // 3. Ocultamos la contraseña para que no se filtre en consultas
    protected $hidden = [
        'contra',
        'remember_token',
    ];

    // 4. IMPORTANTE: Desactivamos el hash automático de Laravel
    // Laravel por defecto intenta encriptar 'password'. 
    // Como tú usas MD5 manualmente, quitamos el cast de 'password' => 'hashed'.
    protected function casts(): array
    {
        return [
            // Si no usas email_verified_at en tu tabla, puedes borrar esta línea
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Le decimos a Laravel que la columna de la contraseña no es 'password' sino 'contra'
     */
    public function getAuthPassword()
    {
        return $this->contra;
    }

    /**
     * Relación con los datos personales del usuario
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'id_perfil');
    }

    /**
     * Obtener las iniciales del usuario a partir de sus datos reales o de usuario
     */
    public function getInitialsAttribute()
    {
        if ($this->persona) {
            $nombre = trim($this->persona->nombre);
            $apellido = trim($this->persona->ap_paterno);
            $in1 = !empty($nombre) ? mb_substr($nombre, 0, 1) : '';
            $in2 = !empty($apellido) ? mb_substr($apellido, 0, 1) : '';
            $initials = $in1 . $in2;
            return !empty($initials) ? mb_strtoupper($initials) : 'U';
        }
        return mb_strtoupper(mb_substr($this->nombre_usuario, 0, 2));
    }

    /**
     * Obtener la URL de la fotografía del usuario.
     * Retorna la foto personalizada si existe en storage, de lo contrario un avatar por defecto.
     */
    public function getFotoUrlAttribute()
    {
        $idPersona = $this->id_persona;
        if (!$idPersona) {
            return asset('images/avatar.png');
        }

        $path = "{$idPersona}.jpg";
        if (Storage::disk('fotos')->exists($path)) {
            // Añadimos un query param timestamp para evitar almacenamiento en caché en el navegador al actualizar la foto
            return asset("fotos/" . $path) . '?t=' . time();
        }

        return asset('images/avatar.png');
    }
}