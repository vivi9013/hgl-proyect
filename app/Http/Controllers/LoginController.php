<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}