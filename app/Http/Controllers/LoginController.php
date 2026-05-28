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

            // LOGICA CONSISTENTE: Solo si el usuario lo pide con el Check
            $conta = 3; // Por defecto: entrar directo
            
            if ($request->cambio) {
                $conta = 4; // Cambio voluntario (Check marcado)
            }

            // Log discreto para que sepamos qué valor tiene la DB sin molestar al usuario
            Log::info("Login exitoso: {$user->nombre_usuario}. Check: " . ($request->cambio ? 'SI' : 'NO') . " | Valor primera en DB: {$user->primera}");


            return response()->json([
                'resultado' => $conta . '|' . $user->id,
                'new_token' => csrf_token()
            ]);
        }


        return response()->json(['resultado' => '2|0']);
    }

    public function showCambiarContra()
    {
        return view('cambiar_contra.index');
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