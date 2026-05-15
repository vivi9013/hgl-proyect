<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SyncLegacySession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Si la variable principal no existe, poblamos la sesión estilo Legacy
            if (!session()->has('s_user')) {
                // Eager load everything needed
                $user->load(['persona.sede', 'persona.trabajador.departamento', 'persona.trabajador.puesto', 'perfil']);
                
                $persona = $user->persona;
                $perfil = $user->perfil;
                $trabajador = $persona ? $persona->trabajador : null;
                $config = \App\Models\Configuracion::first();
                $visitas = \App\Models\Actividad::where('filtro', 'Inicio de Sesion')
                                                ->where('id_usuario', $user->id)
                                                ->count();

                // Mapeo de Colores Legacy
                $temaMap = [
                    'black' => ['default', '#2c3e50'],
                    'black-light' => ['default', '#34495e'],
                    'blue' => ['primary', '#2980b9'],
                    'blue-light' => ['primary', '#3498db'],
                    'green' => ['success', '#27ae60'],
                    'green-light' => ['success', '#2ecc71'],
                    'red' => ['danger', '#c0392b'],
                    'red-light' => ['danger', '#e74c3c'],
                    'yellow' => ['warning', '#f39c12'],
                    'yellow-light' => ['warning', '#f1c40f'],
                    'purple' => ['warning', '#8e44ad'],
                    'purple-light' => ['warning', '#9b59b6'],
                    'pink' => ['warning', '#FA58D0'],
                    'pink-light' => ['warning', '#FA58F4'],
                ];

                $colores = $temaMap[$user->tema] ?? ['info', '#2980b9'];

                session([
                    'autentificado'   => 'SI',
                    'ultimoAcceso'    => date("Y-n-j H:i:s"),
                    's_user'          => $user->nombre_usuario,
                    's_clave'         => $user->id,
                    's_id_persona'    => $user->id_persona,
                    's_contra'        => $user->contra,
                    's_sede'          => $persona && $persona->sede ? $persona->sede->nombre : '',
                    's_perfil'        => $user->id_perfil,
                    's_NombrePersona' => $persona ? trim("{$persona->nombre} {$persona->ap_paterno} {$persona->ap_materno}") : 'Usuario',
                    's_MiembroDesde'  => $persona ? $persona->fecha : '',
                    's_Edad'          => $persona ? $persona->fecha_nac : '',
                    's_Skin'          => $user->tema ?? 'blue',
                    's_NombrePerfil'  => $perfil ? $perfil->nombre : '',
                    's_DescPerfil'    => $perfil ? $perfil->descripcion : '',
                    's_NombreCorto'   => $persona ? trim("{$persona->nombre} {$persona->ap_paterno}") : 'Usuario',
                    's_Sesion'        => $config ? $config->sesion : 30,
                    's_Visitas'       => $visitas,
                    's_depto'         => ($trabajador && $trabajador->departamento) ? $trabajador->departamento->nombre : '',
                    's_puesto'        => ($trabajador && $trabajador->puesto) ? $trabajador->puesto->puesto : '',
                    's_genero'        => $persona ? $persona->sexo : '',
                    's_ColorCaja'     => $colores[0],
                    's_colGr'         => $colores[1],
                ]);
            }
        }

        return $next($request);
    }
}

