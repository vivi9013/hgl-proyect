<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SolicitudReseteoPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('login.login');
    }

    public function login(Request $request)
    {
        // Si ya está autenticado (evita el error de conexión por redirección)
        if (Auth::check()) {
            return response()->json(['resultado' => '3|' . Auth::id()]);
        }

        $request->validate([

            'user' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('nombre_usuario', $request->user)->where('activo', 1)->first();
        $password = $request->password;
        $authenticated = false;

        if ($user) {
            // Intentar con Bcrypt o MD5 (migración automática)
            if (password_verify($password, $user->contra)) {
                $authenticated = true;
            } else if (md5($password) === $user->contra) {
                $user->contra = bcrypt($password);
                $user->save();
                $authenticated = true;
            }
        }

        if ($authenticated) {
            Auth::login($user);
            $request->session()->regenerate();

            // Auto-cancelar solicitudes pendientes si el usuario logra iniciar sesión por su cuenta
            SolicitudReseteoPassword::where('id_usuario', $user->id)
                ->where('estado', 'pendiente')
                ->update([
                    'estado'         => 'rechazada',
                    'nota_revision'  => 'Cancelada automáticamente: el usuario inició sesión con su contraseña actual.',
                    'fecha_revision' => now()->toDateString(),
                    'hora_revision'  => now()->toTimeString(),
                ]);

            // ── Registrar actividad de inicio de sesión (equivalente al sistema legacy) ──
            try {
                $user->load('persona', 'perfil');

                // Construir nombre completo
                if ($user->persona) {
                    $nombre    = trim($user->persona->nombre ?? '');
                    $paterno   = trim($user->persona->ap_paterno ?? '');
                    $materno   = trim($user->persona->ap_materno ?? '');
                    $nombreCompleto = collect([$nombre, $paterno, $materno])->filter()->implode(' ');
                } else {
                    $nombreCompleto = $user->nombre_usuario;
                }

                $nombrePerfil = $user->perfil ? trim($user->perfil->nombre) : 'Sin perfil';

                DB::table('actividades')->insert([
                    'descripcion' => "{$nombreCompleto} ha iniciado sesión con perfil {$nombrePerfil}",
                    'filtro'      => 'Inicio de Sesion',
                    'fecha'       => now()->toDateString(),
                    'hora'        => now()->toTimeString(),
                    'id_persona'  => $user->id_persona,
                ]);
            } catch (\Exception $e) {
                Log::warning('No se pudo registrar actividad de inicio de sesión: ' . $e->getMessage());
            }

            // PRIORIDAD: si es el primer ingreso del usuario (primera == 1),
            // se fuerza el cambio de contraseña obligatorio sin importar el check.
            if ($user->primera == 1) {
                $conta = 1; // Primer ingreso: redirigir al módulo de cambio de contraseña
            } elseif ($request->cambio) {
                $conta = 4; // Cambio voluntario (Check marcado)
            } else {
                $conta = 3; // Entrar directo al panel
            }

            // Log de diagnóstico para auditoría interna
            Log::info("Login exitoso: {$user->nombre_usuario}. Check: " . ($request->cambio ? 'SI' : 'NO') . " | primera en BD: {$user->primera} | Respuesta: {$conta}");

            return response()->json([
                'resultado' => $conta . '|' . $user->id,
                'new_token' => csrf_token()
            ]);
        }


        return response()->json(['resultado' => '2|0']);
    }

    public function showCambiarContra()
    {
        return view('sidebar.cambiar_contra.index');
    }

    public function updatePassword(Request $request)
    {
        $request->validate(['pass' => 'required|min:4']);

        // Obtenemos al usuario autenticado (Seguridad de Sistema 2)
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Sesión no válida']);
        }

        try {
            DB::beginTransaction();

            // Actualización atómica
            $user->contra = bcrypt($request->pass);
            $user->primera = 0;
            $user->save();

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar contraseña: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error en la base de datos']);
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function solicitarRecuperacion(Request $request)
    {
        $inicio = microtime(true);
        $mensaje = 'Tu solicitud está en revisión. Si gustas de mayor rapidez, comúnicateen el departamento de sistemas para su validación.';

        try {
            // 1. Validación sin lanzar excepción que rompa el timing
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'user'   => 'required|string',
                'nombre' => 'required|string|max:255',
                'dato'   => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['mensaje' => $mensaje]);
            }

            // 2. Rate Limiting Híbrido: 2/hora por usuario y 10/hora por IP
            $userKey = 'recuperacion-password-user:' . md5(trim($request->user));
            $ipKey   = 'recuperacion-password-ip:' . $request->ip();

            $userExcedido = RateLimiter::tooManyAttempts($userKey, 2);
            $ipExcedida   = RateLimiter::tooManyAttempts($ipKey, 10);

            if (!$userExcedido && !$ipExcedida) {
                RateLimiter::hit($userKey, 3600);
                RateLimiter::hit($ipKey, 3600);

                $usuario = User::where('nombre_usuario', trim($request->user))->first();

                if ($usuario) {
                    $yaPendiente = SolicitudReseteoPassword::where('id_usuario', $usuario->id)
                        ->where('estado', 'pendiente')
                        ->exists();

                    if (!$yaPendiente) {
                        SolicitudReseteoPassword::create([
                            'nombre_usuario'   => trim($request->user),
                            'id_usuario'       => $usuario->id,
                            'nombre_declarado' => trim($request->nombre),
                            'dato_adicional'   => $request->dato ? trim($request->dato) : null,
                            'estado'           => 'pendiente',
                            'ip'               => $request->ip(),
                            'fecha'            => now()->toDateString(),
                            'hora'             => now()->toTimeString(),
                        ]);
                    }

                    DB::table('actividades')->insert([
                        'descripcion' => 'Solicitud de recuperación de contraseña'
                            . ($yaPendiente ? ' (ya existía una pendiente)' : '')
                            . " para el usuario '{$usuario->nombre_usuario}'.",
                        'filtro'     => 'Recuperación de Contraseña',
                        'fecha'      => now()->toDateString(),
                        'hora'       => now()->toTimeString(),
                        'id_persona' => $usuario->id_persona,
                    ]);
                } else {
                    DB::table('actividades')->insert([
                        'descripcion' => "Intento de recuperación de contraseña con usuario no registrado: '" . trim($request->user) . "'.",
                        'filtro'     => 'Recuperación de Contraseña',
                        'fecha'      => now()->toDateString(),
                        'hora'       => now()->toTimeString(),
                        'id_persona' => null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error en solicitud de recuperación de contraseña: ' . $e->getMessage());
        } finally {
            // Se ejecuta SIEMPRE: garantiza respuesta uniforme de 2.5s exactos
            $transcurrido = microtime(true) - $inicio;
            $objetivo = 2.5;
            if ($transcurrido < $objetivo) {
                usleep((int) (($objetivo - $transcurrido) * 1_000_000));
            }
        }

        return response()->json(['mensaje' => $mensaje]);
    }
}