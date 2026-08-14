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
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $query = CategoArchivo::orderBy('id_catego_archivos', 'desc');

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where('categoria', 'like', '%' . $buscarLimpiado . '%');
        }

        $categorias = $query->paginate(10);

        // Si la petición viene por AJAX (para cambiar de página o buscar), retornamos JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'  => view('admin_formatos.categoria_archivos.partials.tabla', compact('categorias'))->render(),
                'total' => $categorias->total(),
                'info'  => 'Mostrando ' . ($categorias->firstItem() ?? 0) . ' a ' . ($categorias->lastItem() ?? 0)
                    . ' de ' . $categorias->total() . ' registros',
                'links' => (string) $categorias->links('pagination::bootstrap-4'),
            ]);
        }
        return view('admin_formatos.categoria_archivos.index', compact('categorias'));
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

        $user = Auth::user();
        $userId = $user ? $user->id : 1;

        $categoria = CategoArchivo::create([
            'categoria'      => $request->categoria,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'activo'         => 1,
            'usuario'        => $userId,
        ]);

        // Sincronización automática: Asignar permiso de acceso al creador del módulo
        if ($user && $user->id_persona) {
            TrabajadorCategoria::create([
                'id_trabajador'  => $user->id_persona,
                'id_categoria'   => $categoria->id_catego_archivos,
                'fecha_registro' => now()->toDateString(),
                'hora_registro'  => now()->toTimeString(),
                'usuario'        => $userId,
            ]);
        }

        return redirect()
            ->route('categoria_archivos.index')
            ->with('exitog', 'El registro se ha guardado correctamente.');
    }

    /**
     * Muestra o retorna los datos para editar una categoría.
     */
    public function editar(Request $request, $id)
    {
        $categoria = CategoArchivo::findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'categoria' => $categoria
            ]);
        }

        return redirect()->route('categoria_archivos.index');
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
                ->with('hasEditFormErrors', $id)
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
     * Genera el reporte de impresión filtrado (premium print-friendly HTML).
     * Regla de negocio conservada: solo categorías activo=1 con al menos un archivo activo.
     * Agrega el filtro buscar del índice sobre el nombre de la categoría.
     */
    public function imprimir(Request $request)
    {
        $buscar = trim($request->get('buscar', ''));

        $query = CategoArchivo::where('activo', 1)
            ->whereHas('archivos', function ($q) {
                $q->where('activo', 1);
            })
            ->withCount(['archivos' => function ($q) {
                $q->where('activo', 1);
            }])
            ->orderBy('categoria', 'asc');

        if ($buscar !== '') {
            $query->where('categoria', 'like', '%' . $buscar . '%');
        }

        $categorias = $query->get();

        return view('admin_formatos.categoria_archivos.analitica.reportes.impresion',
            compact('categorias', 'buscar'));
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