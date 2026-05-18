<?php

namespace App\Http\Controllers;

use App\Models\CategoriaModulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $idPerfil = $user->id_perfil;

        // Obtener las categorías de módulos con sus respectivos módulos activos para el perfil del usuario
        $categorias = CategoriaModulo::where('activo', 1)
            ->whereHas('modulos', function ($query) use ($idPerfil) {
                $query->where('activo', 1)
                      ->whereHas('perfiles', function ($q) use ($idPerfil) {
                          $q->where('id_perfil', $idPerfil);
                      });
            })
            ->with(['modulos' => function ($query) use ($idPerfil) {
                $query->where('activo', 1)
                      ->whereHas('perfiles', function ($q) use ($idPerfil) {
                          $q->where('id_perfil', $idPerfil);
                      })
                      ->orderBy('orden')
                      ->orderBy('nombre');
            }])
            ->orderBy('orden')
            ->orderBy('categoria')
            ->get();

        // Enviar a la vista index
        return view('index', [
            'categorias' => $categorias,
            'skin' => session('s_Skin', 'blue'),
            'user' => $user
        ]);
    }
}
