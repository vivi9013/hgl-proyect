<?php

namespace App\Http\Controllers\CategoriaArchivos;

use App\Http\Controllers\Controller;
use App\Models\BuscadorArchivos\CategoArchivo;
use App\Models\BuscadorArchivos\TrabajadorCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaArchivosController extends Controller
{
    /**
     * Muestra la lista de categorías (catálogo).
     */
    public function index()
    {
        // Ordena de manera descendente y pagina de 10 en 10
        $categorias = CategoArchivo::orderBy('id_catego_archivos', 'desc')->paginate(10);
        // Si la petición viene por AJAX (para cambiar de página), retornamos la vista parcial
        if (request()->ajax() || request()->wantsJson()) {
            return view('categoria_archivos.partials.tabla', compact('categorias'));
        }
        return view('categoria_archivos.index', compact('categorias'));
    }

    /**
     * Guarda una nueva categoría en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'categoria' => 'required|string|max:255',
        ]);

        // Evitar duplicados exactos (insensible a mayúsculas/minúsculas)
        $existe = CategoArchivo::whereRaw('LOWER(categoria) = ?', [strtolower($request->categoria)])->exists();
        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['categoria' => 'Esta categoría ya se encuentra registrada.']);
        }

        $categoria = CategoArchivo::create([
            'categoria'      => $request->categoria,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'activo'         => 1,
            'usuario'        => Auth::id() ?? 1,
        ]);

        // Sincronización automática: Asignar permiso de acceso al creador del módulo
        $user = Auth::user();
        if ($user && $user->id_persona) {
            TrabajadorCategoria::create([
                'id_trabajador'  => $user->id_persona,
                'id_categoria'   => $categoria->id_catego_archivos,
                'fecha_registro' => now()->toDateString(),
                'hora_registro'  => now()->toTimeString(),
                'usuario'        => $user->id,
            ]);
        }

        return redirect()
            ->route('categoria_archivos.index')
            ->with('exitog', 'El registro se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario para editar una categoría.
     */
    public function editar($id)
    {
        $categoria = CategoArchivo::findOrFail($id);

        return view('categoria_archivos.editar', compact('categoria'));
    }

    /**
     * Actualiza la categoría en la base de datos.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'categoria' => 'required|string|max:255',
        ]);

        $categoria = CategoArchivo::findOrFail($id);

        // Evitar duplicados exactos excluyendo la categoría actual
        $existe = CategoArchivo::whereRaw('LOWER(categoria) = ?', [strtolower($request->categoria)])
            ->where('id_catego_archivos', '!=', $id)
            ->exists();
        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['categoria' => 'Esta categoría ya se encuentra registrada con otra clave.']);
        }

        $categoria->update([
            'categoria'      => $request->categoria,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'usuario'        => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('categoria_archivos.index')
            ->with('exito', 'El registro se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de la categoría.
     */
    public function cambiarStatus($id)
    {
        $categoria = CategoArchivo::findOrFail($id);
        $categoria->activo = $categoria->activo == 1 ? 0 : 1;
        $categoria->fecha_registro = now()->toDateString();
        $categoria->hora_registro = now()->toTimeString();
        $categoria->usuario = Auth::id() ?? 1;
        $categoria->save();

        return redirect()
            ->route('categoria_archivos.index')
            ->with('exito', 'El estado del registro se ha actualizado.');
    }

    /**
     * Muestra la vista de opciones de reportes del módulo.
     */
    public function reportes()
    {
        return view('categoria_archivos.reportes.reportes');
    }

    /**
     * Genera el reporte de impresión de categorías (premium print-friendly HTML).
     */
    public function imprimir()
    {
        // Replicamos la lógica legacy INNER JOIN:
        // Solo categorías activas que tienen al menos un archivo activo asignado.
        $categorias = CategoArchivo::where('activo', 1)
            ->whereHas('archivos', function ($query) {
                $query->where('activo', 1);
            })
            ->withCount(['archivos' => function ($query) {
                $query->where('activo', 1);
            }])
            ->orderBy('categoria', 'asc')
            ->get();

        return view('categoria_archivos.reportes.reporte_impresion', compact('categorias'));
    }

    /**
     * AJAX: Verifica si el nombre de la categoría ya está registrado.
     */
    public function verificar(Request $request)
    {
        $nombre = $request->query('categoria');

        if (!$nombre) {
            return response()->json(['disponible' => false, 'error' => 'Parámetro ausente']);
        }

        $existe = CategoArchivo::whereRaw('LOWER(categoria) = ?', [strtolower(trim($nombre))])->exists();

        return response()->json(['disponible' => !$existe]);
    }
}
