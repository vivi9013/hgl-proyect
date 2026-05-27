<?php

namespace App\Http\Controllers\CargarArchivos;

use App\Http\Controllers\Controller;
use App\Models\BuscadorArchivos\CategoArchivo;
use App\Models\BuscadorArchivos\CargaArchivo;
use Illuminate\Http\Request;

class CargaArchivosController extends Controller
{
    public function index()
    {
        $categorias = CategoArchivo::where('activo', 1)
            ->orderBy('categoria', 'asc')
            ->get(['id_catego_archivos', 'categoria']);

        $archivos = CargaArchivo::with('categoria')
            ->orderBy('id_archivo', 'desc')
            ->get();

        foreach ($archivos as $archivo) {
            if ($archivo->categoria) {
                $carpetaSanitizada = $this->sanearString($archivo->categoria->categoria);
                $nombreSanitizado = $this->sanearString($archivo->nombre) . '.pdf';
                
                $ruta1 = "legacy-system-1/mCargaArchivos/hojasArchivos/{$carpetaSanitizada}/{$nombreSanitizado}";
                $ruta2 = "legacy-system-1/mCargaArchivos/hojasArchivo/{$carpetaSanitizada}/{$nombreSanitizado}";
                
                $archivo->existe_fisico = file_exists(base_path($ruta1)) || file_exists(base_path($ruta2));
            } else {
                $archivo->existe_fisico = false;
            }
        }

        return view('carga_archivos.index', compact('categorias', 'archivos'));
    }

    public function toggleStatus($id)
    {
        $archivo = CargaArchivo::findOrFail($id);
        $archivo->activo = $archivo->activo == 1 ? 0 : 1;
        $archivo->save();

        return redirect()->back()->with('success', 'El estado del archivo se ha actualizado.');
    }

    private function sanearString($string)
    {
        $string = trim($string);
        $string = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'],
            $string
        );
        return $string;
    }

    public function guardar(Request $request)
    {
        // 1. Validar los datos recibidos
        $request->validate([
            'nombre'  => 'required|string|max:255',
            'tipo'    => 'required|integer|exists:catego_archivos,id_catego_archivos',
            'version' => 'required|integer|min:1',
            'desc'    => 'required|string',
        ]);

        // 2. Crear el registro usando el modelo Eloquent
        CargaArchivo::create([
            'nombre'              => $request->nombre,
            'id_catego'           => $request->tipo,
            'version_archivo'     => $request->version,
            'descripcion_archivo' => $request->desc,
            'fecha_registro'      => now()->toDateString(), // Equivale a date("Y-m-d")
            'hora_registro'       => now()->toTimeString(),  // Equivale a date("h:i:s")
            'activo'              => 1,
            'usuario'             => auth()->user()->usuario ?? 'sistema', // Usuario autenticado
        ]);

        // 3. Redireccionar de vuelta con un mensaje de éxito
        return redirect()
            ->route('carga_archivos.index')
            ->with('success', 'El registro se ha guardado correctamente.');
    }

    public function revisarexistencia(Request $request)
    {   // verificacion de disponibilidad
        $nombre = $request->query('nombre');
        $id_catego = $request->query('id_catego');

        if (!$nombre || !$id_catego) {
            return response()->json(['disponible' => false, 'error' => 'Faltan parámetros']);
        }

        $existe = CargaArchivo::where('nombre', $nombre)
            ->where('id_catego', $id_catego)
            ->exists();

        return response()->json(['disponible' => !$existe]);
    }

    public function editar($id)
    {
        $archivo = CargaArchivo::findOrFail($id);
        $categorias = CategoArchivo::where('activo', 1)
            ->orderBy('categoria', 'asc')
            ->get(['id_catego_archivos', 'categoria']);

        return view('carga_archivos.editar', compact('archivo', 'categorias'));
    }

    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'  => 'required|string|max:255',
            'tipo'    => 'required|integer|exists:catego_archivos,id_catego_archivos',
            'version' => 'required|integer|min:1',
            'desc'    => 'required|string',
        ]);

        $archivo = CargaArchivo::findOrFail($id);
        $archivo->update([
            'nombre'              => $request->nombre,
            'id_catego'           => $request->tipo,
            'version_archivo'     => $request->version,
            'descripcion_archivo' => $request->desc,
            'fecha_registro'      => now()->toDateString(),
            'hora_registro'       => now()->toTimeString(),
            'activo'              => 1,
            'usuario'             => auth()->user()->usuario ?? 'sistema',
        ]);

        return redirect()
            ->route('carga_archivos.index')
            ->with('success', 'El registro se ha actualizado correctamente.');
    }

    public function cargar($id)
    {
        $archivo = CargaArchivo::with('categoria')->findOrFail($id);
        return view('carga_archivos.cargar', compact('archivo'));
    }

    public function subirArchivo(Request $request, $id)
    {
        $request->validate([
            'archivo-a-subir' => 'required|file|mimes:pdf|max:51200', // Máx 50MB
        ]);

        $archivo = CargaArchivo::with('categoria')->findOrFail($id);

        if (!$archivo->categoria) {
            return redirect()->back()->withErrors(['error' => 'La categoría del archivo no es válida.']);
        }

        $carpetaSanitizada = $this->sanearString($archivo->categoria->categoria);
        $nombreSanitizado = $this->sanearString($archivo->nombre) . '.pdf';

        $rutaDirectorio = base_path("legacy-system-1/mCargaArchivos/hojasArchivos/{$carpetaSanitizada}");

        if (!file_exists($rutaDirectorio)) {
            mkdir($rutaDirectorio, 0777, true);
        }

        $file = $request->file('archivo-a-subir');
        $file->move($rutaDirectorio, $nombreSanitizado);

        return redirect()
            ->route('carga_archivos.index')
            ->with('success', 'El archivo PDF se ha subido correctamente.');
    }
}
