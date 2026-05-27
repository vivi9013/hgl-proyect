<?php

namespace App\Http\Controllers\CambiarFoto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CambiarFotoController extends Controller
{
    // Mostrar la vista
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('layouts.CambiarFoto.cambiar_foto', compact('user'));
    }

    // Procesar la subida
    public function store(Request $request)
    {
        $request->validate([
            'archivo-a-subir' => 'required|image|mimes:jpg,jpeg|max:5120',
        ], [
            'archivo-a-subir.required' => 'Debes seleccionar una imagen para subir.',
            'archivo-a-subir.image' => 'El archivo debe ser una imagen válida.',
            'archivo-a-subir.mimes' => 'La imagen cargada no pertenece a la extensión jpg.',
            'archivo-a-subir.max' => 'La imagen sobrepasa los 5MB permitidos.',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sesión no válida.');
        }

        $idPersona = $user->id_persona;
        if (!$idPersona) {
            return redirect()->back()->with('error', 'El usuario no tiene una persona asociada en la base de datos.');
        }

        try {
            // Guardamos la foto en storage/app/public/fotos/id_persona.jpg
            $request->file('archivo-a-subir')->storeAs('fotos', $idPersona . '.jpg', 'public');
            
            return redirect()->route('cambiar_foto.index')
                             ->with('success', '¡Operación Satisfactoria! La imagen ha sido modificada.');
        } catch (\Exception $e) {
            Log::error("Error al subir fotografía de usuario (ID Persona: {$idPersona}): " . $e->getMessage());
            return redirect()->route('cambiar_foto.index')
                             ->with('error', 'Ha ocurrido un error al momento de subir la imagen.');
        }
    }
}
