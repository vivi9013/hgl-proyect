<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('login.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('nombre_usuario', $request->user)->where('activo', 1)->first();
        $password = $request->password;
        $authenticated = false;

        if ($user) {
            // Validación Bcrypt o MD5 (migrando a Bcrypt al entrar)
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
            // Retornamos éxito directo (Código 3 en tu lógica original)
            return response()->json(['resultado' => '3|' . $user->id]);
        }

        // Retornamos fallo (Código 2)
        return response()->json(['resultado' => '2|0']);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    }
}