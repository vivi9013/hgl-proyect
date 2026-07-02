<?php

namespace App\Http\Controllers\Tema;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemaController extends Controller
{
    /**
     * Muestra la vista de selección de temas
     */
    public function index()
    {
        $user = Auth::user();
        
        $themes = [
            ['id' => 'black', 'nombre' => 'Negro', 'color' => '#2c3e50'],
            ['id' => 'black-light', 'nombre' => 'Negro Ligero', 'color' => '#34495e'],
            ['id' => 'blue', 'nombre' => 'Azul', 'color' => '#2980b9'],
            ['id' => 'blue-light', 'nombre' => 'Azul Ligero', 'color' => '#3498db'],
            ['id' => 'pink', 'nombre' => 'Rosa', 'color' => '#FA58D0'],
            ['id' => 'pink-light', 'nombre' => 'Rosa Ligero', 'color' => '#FA58F4'],
            ['id' => 'green', 'nombre' => 'Verde', 'color' => '#27ae60'],
            ['id' => 'green-light', 'nombre' => 'Verde Ligero', 'color' => '#2ecc71'],
            ['id' => 'purple', 'nombre' => 'Morado', 'color' => '#8e44ad'],
            ['id' => 'purple-light', 'nombre' => 'Morado Ligero', 'color' => '#9b59b6'],
            ['id' => 'red', 'nombre' => 'Rojo', 'color' => '#c0392b'],
            ['id' => 'red-light', 'nombre' => 'Rojo Ligero', 'color' => '#e74c3c'],
            ['id' => 'yellow', 'nombre' => 'Amarillo', 'color' => '#f39c12'],
            ['id' => 'yellow-light', 'nombre' => 'Amarillo Ligero', 'color' => '#f1c40f'],
        ];

        return view('sidebar.tema.index', compact('themes', 'user'));
    }

    /**
     * Actualiza el tema del usuario en sesión y base de datos
     */
    public function update(Request $request)
    {
        $request->validate([
            'color' => 'required|string',
        ]);

        $color = $request->input('color');
        
        // Mapeo exacto de temas legacy (para sincronizar la sesión)
        $temaMap = [
            'black'        => ['default', '#2c3e50'],
            'black-light'  => ['default', '#34495e'],
            'blue'         => ['primary', '#2980b9'],
            'blue-light'   => ['primary', '#3498db'],
            'green'        => ['success', '#27ae60'],
            'green-light'  => ['success', '#2ecc71'],
            'red'          => ['danger',  '#c0392b'],
            'red-light'    => ['danger',  '#e74c3c'],
            'yellow'       => ['warning', '#f39c12'],
            'yellow-light' => ['warning', '#f1c40f'],
            'purple'       => ['warning', '#8e44ad'], // mantiene consistencia con SyncLegacySession
            'purple-light' => ['warning', '#9b59b6'],
            'pink'         => ['warning', '#FA58D0'],
            'pink-light'   => ['warning', '#FA58F4'],
        ];

        // Validar que el color exista en el mapeo
        if (!array_key_exists($color, $temaMap)) {
            return redirect()->back()->with('error', 'El tema seleccionado no es válido.');
        }

        $colores = $temaMap[$color];

        // Actualizar base de datos
        $user = Auth::user();
        $user->tema = $color;
        $user->save();

        // Actualizar variables de sesión activas
        session([
            's_Skin'      => $color,
            's_ColorCaja' => $colores[0],
            's_colGr'     => $colores[1],
        ]);

        return redirect()->route('tema.index')->with('success', 'El tema del sistema ha sido modificado con éxito.');
    }
}
