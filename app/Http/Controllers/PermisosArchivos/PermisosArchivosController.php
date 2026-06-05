<?php

namespace App\Http\Controllers\PermisosArchivos;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\BuscadorArchivos\CategoArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermisosArchivosController extends Controller
{
    /**
     * Muestra la lista de trabajadores activos y sus permisos de categorías de archivos.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $query = Persona::where('personas.activo', 1)
            ->where('personas.estudiante', 0)
            ->join('trabajadores', 'personas.id', '=', 'trabajadores.id_persona')
            ->join('sedes', 'trabajadores.id_sede', '=', 'sedes.id')
            ->select('personas.*', 'sedes.nombre as sede_nombre')
            ->withCount('categorias');

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('personas.nombre', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('personas.ap_paterno', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('personas.ap_materno', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('sedes.nombre', 'like', '%' . $buscarLimpiado . '%');
            });
        }

        $trabajadores = $query->orderByRaw("CONCAT(personas.ap_paterno, ' ', personas.ap_materno, ' ', personas.nombre) ASC")
            ->paginate(10);

        // Si es una petición AJAX (paginación asíncrona), devuelve solo el partial
        if ($request->ajax() || $request->wantsJson()) {
            return view('permisos_archivos.partials.tabla', compact('trabajadores'));
        }

        return view('permisos_archivos.index', compact('trabajadores'));
    }

    /**
     * Muestra la pantalla para asignar categorías a un trabajador específico.
     */
    public function asignar(Request $request, $id)
    {
        $buscar = $request->get('buscar');
        $trabajador = Persona::with('categorias')->findOrFail($id);
        
        $query = CategoArchivo::whereIn('activo', [0, 1])
            ->orderBy('categoria', 'asc');

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where('categoria', 'like', '%' . $buscarLimpiado . '%');
        }

        $categorias = $query->paginate(10);

        // Si es una petición AJAX (paginación de la matriz), devuelve solo el partial de la tabla
        if ($request->ajax() || $request->wantsJson()) {
            return view('permisos_archivos.partials.tabla_asignacion', compact('trabajador', 'categorias'));
        }

        return view('permisos_archivos.agregar', compact('trabajador', 'categorias'));
    }

    /**
     * Guarda la sincronización de las categorías permitidas para un trabajador.
     */
    public function guardar(Request $request, $id)
    {
        $request->validate([
            'categorias'   => 'nullable|array',
            'categorias.*' => 'integer|exists:catego_archivos,id_catego_archivos'
        ]);

        $trabajador = Persona::findOrFail($id);
        
        $categoriasIds = $request->input('categorias', []);
        
        // Generar estructura para guardar metadatos de auditoría en la tabla pivote
        $syncData = [];
        $fecha = now()->toDateString();
        $hora = now()->toTimeString();
        $usuario = Auth::id() ?? 1;

        foreach ($categoriasIds as $catId) {
            $syncData[$catId] = [
                'fecha_registro' => $fecha,
                'hora_registro'  => $hora,
                'usuario'        => $usuario,
            ];
        }

        // Sincronizar de forma segura y limpia en la tabla pivote
        $trabajador->categorias()->sync($syncData);

        return redirect()
            ->route('trabajador_categorias.index')
            ->with('exito', 'El registro se ha actualizado correctamente.');
    }
}
