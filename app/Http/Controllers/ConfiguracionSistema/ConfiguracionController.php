<?php

namespace App\Http\Controllers\ConfiguracionSistema;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    /**
     * Obtiene el único registro de configuración del sistema.
     */
    private function getConfig(): Configuracion
    {
        return Configuracion::firstOrFail();
    }

    /**
     * Muestra la página de configuración general.
     */
    public function index()
    {
        $config          = $this->getConfig();
        $tieneEncabezado = file_exists(public_path('images/encabezado.jpg'));

        return view('admin_sistema.configuracion_sistema.index', compact('config', 'tieneEncabezado'));
    }

    /**
     * Actualiza los datos de la institución.
     */
    public function actualizarInstitucion(Request $request)
    {
        $request->validate([
            'institucion'   => 'required|string|max:255',
            'director'      => 'required|string|max:255',
            'administrador' => 'required|string|max:255',
        ]);

        $this->getConfig()->update([
            'institucion'   => trim($request->institucion),
            'director'      => trim($request->director),
            'administrador' => trim($request->administrador),
            'fecha'         => now()->toDateString(),
            'hora'          => now()->toTimeString(),
            'usuario'       => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('configuracion_sistema.index')
            ->with('exito_institucion', 'Información de la institución actualizada.');
    }

    /**
     * Actualiza la configuración de seguridad del sistema.
     */
    public function actualizarSeguridad(Request $request)
    {
        $request->validate([
            'sesion' => 'required|integer|min:1|max:1440',
            'contra' => 'required|string|min:4|max:100',
        ]);

        $this->getConfig()->update([
            'sesion'  => $request->sesion,
            'contra'  => $request->contra,
            'fecha'   => now()->toDateString(),
            'hora'    => now()->toTimeString(),
            'usuario' => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('configuracion_sistema.index')
            ->with('exito_seguridad', 'Configuración de seguridad actualizada.');
    }

    /**
     * Sube y reemplaza la imagen de encabezado de reportes.
     */
    public function subirEncabezado(Request $request)
    {
        $request->validate([
            'encabezado' => 'required|image|mimes:jpeg,jpg|max:51200',
        ]);

        $request->file('encabezado')->move(public_path('images'), 'encabezado.jpg');

        // Actualizar la fecha y hora de la configuración para forzar la actualización de caché en el navegador
        $this->getConfig()->update([
            'fecha'   => now()->toDateString(),
            'hora'    => now()->toTimeString(),
            'usuario' => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('configuracion_sistema.index')
            ->with('exito_encabezado', 'Encabezado de reportes actualizado.');
    }
}