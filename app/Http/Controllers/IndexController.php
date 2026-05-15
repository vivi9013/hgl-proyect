<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        // Aquí preparamos los datos para la vista
        $data = [
            'skin' => 'skin-blue',
            'sIdPerfil' => 1,
            'conexion' => null, // Esto fallará si el código de la vista usa mysql_query
        ];

        return view('index', $data);
    }
}
