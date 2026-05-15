<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'activo',
        'tema',
        'primera'
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
}