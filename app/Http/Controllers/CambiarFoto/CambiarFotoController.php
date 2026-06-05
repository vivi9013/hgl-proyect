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
            'archivo-a-subir' => 'required|image|max:5120',
        ], [
            'archivo-a-subir.required' => 'Debes seleccionar una imagen para subir.',
            'archivo-a-subir.image'    => 'El archivo debe ser una imagen válida (JPG, PNG, WEBP, GIF, etc.).',
            'archivo-a-subir.max'      => 'La imagen sobrepasa los 5MB permitidos.',
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
            $archivo    = $request->file('archivo-a-subir');
            $tmpPath    = $archivo->getRealPath();
            $mime       = $archivo->getMimeType();

            // ── Fallback si GD no está habilitada en Apache ──
            if (!\extension_loaded('gd') || !\function_exists('imagecreatefromjpeg')) {
                $archivo->storeAs('', $idPersona . '.jpg', 'fotos');
                return redirect()->route('cambiar_foto.index')
                                 ->with('success', '¡Operación Satisfactoria! La imagen ha sido guardada correctamente.');
            }

            // ── Crear imagen GD desde cualquier formato ──────────
            $src = match (true) {
                str_contains($mime, 'png')  => \imagecreatefrompng($tmpPath),
                str_contains($mime, 'gif')  => \imagecreatefromgif($tmpPath),
                str_contains($mime, 'webp') => \imagecreatefromwebp($tmpPath),
                str_contains($mime, 'bmp')  => \imagecreatefrombmp($tmpPath),
                default                     => \imagecreatefromjpeg($tmpPath),
            };

            if ($src === false) {
                return redirect()->back()->with('error', 'No se pudo procesar la imagen. Intenta con otro archivo.');
            }

            // ── Preservar transparencia (PNG/GIF) → fondo blanco ─
            $w   = \imagesx($src);
            $h   = \imagesy($src);
            $dst = \imagecreatetruecolor($w, $h);
            $white = \imagecolorallocate($dst, 255, 255, 255);
            \imagefill($dst, 0, 0, $white);
            \imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
            \imagedestroy($src);

            // ── Guardar como JPG en la carpeta de 'fotos'
            $destPath = Storage::disk('fotos')->path($idPersona . '.jpg');
            \imagejpeg($dst, $destPath, 90);   // calidad 90%
            \imagedestroy($dst);

            return redirect()->route('cambiar_foto.index')
                             ->with('success', '¡Operación Satisfactoria! La imagen ha sido guardada como JPG.');

        } catch (\Exception $e) {
            Log::error("Error al subir fotografía de usuario (ID Persona: {$idPersona}): " . $e->getMessage());
            return redirect()->route('cambiar_foto.index')
                             ->with('error', 'Ha ocurrido un error al momento de subir la imagen.');
        }
    }
}
