<?php

namespace App\Http\Controllers\CargarArchivos;

use App\Http\Controllers\Controller;
use App\Models\BuscadorArchivos\CategoArchivo;
use App\Models\BuscadorArchivos\CargaArchivo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\Sanitizable;
use Illuminate\Support\Facades\Storage;

class CargaArchivosController extends Controller
{
    use Sanitizable;

    public function index(Request $request)
    {
        $categorias = CategoArchivo::where('activo', 1)
            ->orderBy('categoria', 'asc')
            ->get(['id_catego_archivos', 'categoria']);

        $archivos = $this->buildQuery($request)->paginate(10);

        // Si la petición viene por AJAX, retornamos JSON con el HTML compilado
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'  => view('admin_formatos.carga_archivos.partials.tabla', compact('archivos'))->render(),
                'total' => $archivos->total(),
                'info'  => 'Mostrando ' . ($archivos->firstItem() ?? 0) . ' a ' . ($archivos->lastItem() ?? 0)
                    . ' de ' . $archivos->total() . ' registros',
                'links' => (string) $archivos->links('pagination::bootstrap-4'),
            ]);
        }

        return view('admin_formatos.carga_archivos.index', compact('categorias', 'archivos'));
    }

    public function toggleStatus($id)
    {
        $archivo = CargaArchivo::findOrFail($id);
        $archivo->activo = $archivo->activo == 1 ? 0 : 1;
        $archivo->save();

        return redirect()->route('carga_archivos.index')->with('success', 'El estado del archivo se ha actualizado.');
    }

    public function guardar(Request $request)
    {
        // 1. Validar los datos recibidos (Evita duplicados de nombre + versión a nivel global)
        $request->validate([
            'nombre'  => [
                'required',
                'string',
                'max:255',
                // Validación única compuesta: nombre + version_archivo dentro de la misma categoría
                Rule::unique('carga_archivos', 'nombre')->where(function ($query) use ($request) {
                    return $query->where('version_archivo', $request->version)
                                 ->where('id_catego', $request->tipo);
                }),
            ],
            'tipo'    => 'required|integer|exists:catego_archivos,id_catego_archivos',
            'version' => 'required|integer|min:1',
            'desc'    => 'required|string',
        ], [
            // Mensaje personalizado para cuando falle la restricción
            'nombre.unique' => 'Ya existe un archivo con este mismo nombre y versión en esta categoría.',
        ]);

        // 2. Crear el registro usando el modelo Eloquent
        CargaArchivo::create([
            'nombre'              => $request->nombre,
            'id_catego'           => $request->tipo,
            'version_archivo'     => $request->version,
            'descripcion_archivo' => $request->desc,
            'fecha_registro'      => now()->toDateString(),
            'hora_registro'       => now()->toTimeString(),
            'activo'              => 1,
            'usuario'             => auth()->user()->usuario ?? 'sistema',
        ]);

        // 3. Redireccionar de vuelta con un mensaje de éxito
        return redirect()
            ->route('carga_archivos.index')
            ->with('success', 'El registro se ha guardado correctamente.');
    }

    public function revisarexistencia(Request $request)
    {
        // Verificación de disponibilidad por Nombre, Versión y Categoría
        $nombre  = $request->query('nombre');
        $version = $request->query('version');
        $tipo    = $request->query('tipo');

        if (!$nombre || !$version || !$tipo) {
            return response()->json(['disponible' => false, 'error' => 'Faltan parámetros']);
        }

        // Busca si ya existe esa combinación exacta en la categoría seleccionada
        $existe = CargaArchivo::where('nombre', $nombre)
            ->where('version_archivo', $version)
            ->where('id_catego', $tipo)
            ->exists();

        return response()->json(['disponible' => !$existe]);
    }

    public function editar(Request $request, $id)
    {
        $archivo = CargaArchivo::with('categoria')->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'archivo' => $archivo,
            ]);
        }

        return redirect()->route('carga_archivos.index');
    }

    public function actualizar(Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nombre'  => [
                'required',
                'string',
                'max:255',
                Rule::unique('carga_archivos', 'nombre')->where(function ($query) use ($request) {
                    return $query->where('version_archivo', $request->version)
                                 ->where('id_catego', $request->tipo);
                })->ignore($id, 'id_archivo'),
            ],
            'tipo'    => 'required|integer|exists:catego_archivos,id_catego_archivos',
            'version' => 'required|integer|min:1',
            'desc'    => 'required|string',
        ], [
            'nombre.unique' => 'Ya existe un archivo con este mismo nombre y versión en esta categoría.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('hasEditFormErrors', $id);
        }

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
        return view('admin_formatos.carga_archivos.cargar', compact('archivo'));
    }

    public function subirArchivo(Request $request, $id)
    {
        $request->validate([
            'archivo-a-subir' => 'required|file|mimes:pdf,doc,docx|max:51200', // Máx 50MB
        ], [
            'archivo-a-subir.required' => 'Debes seleccionar un archivo para subir.',
            'archivo-a-subir.mimes'    => 'El archivo debe ser un formato válido: PDF (.pdf) o Word (.doc, .docx).',
            'archivo-a-subir.max'      => 'El archivo no debe exceder los 50MB.',
        ]);

        $archivo = CargaArchivo::with('categoria')->findOrFail($id);

        if (!$archivo->categoria) {
            return redirect()->back()->withErrors(['error' => 'La categoría del archivo no es válida.']);
        }

        $carpeta   = $archivo->sanearString($archivo->categoria->categoria);
        $baseName  = $archivo->sanearString($archivo->nombre);
        $extension = strtolower($request->file('archivo-a-subir')->getClientOriginalExtension());

        // Limpiar archivos físicos previos para evitar guardar duplicados con distintas extensiones
        $posiblesExts = ['pdf', 'docx', 'doc'];
        foreach ($posiblesExts as $ext) {
            $rutaVieja = "formats/{$carpeta}/{$baseName}.{$ext}";
            if (Storage::disk('local')->exists($rutaVieja)) {
                Storage::disk('local')->delete($rutaVieja);
            }
        }

        $nuevoNombreFisico = "{$baseName}.{$extension}";
        $request->file('archivo-a-subir')->storeAs("formats/{$carpeta}", $nuevoNombreFisico, 'local');

        return redirect()
            ->route('carga_archivos.index')
            ->with('success', 'El archivo (' . strtoupper($extension) . ') se ha subido correctamente.');
    }

    /**
     * Impresión filtrada: reutiliza buildQuery() para reflejar exactamente
     * los filtros activos del índice (categoria[] + buscar).
     */
    public function imprimirReporte(Request $request)
    {
        $categoriaFiltro = $request->input('categoria', []);

        // Colección de modelos CategoArchivo para los IDs seleccionados (vacía si no se filtró)
        $categoriasSeleccionadas = !empty($categoriaFiltro)
            ? CategoArchivo::whereIn('id_catego_archivos', (array) $categoriaFiltro)->get()
            : collect();

        $archivos = $this->buildQuery($request)->limit(500)->get();

        return view('admin_formatos.carga_archivos.analitica.reportes.impresion',
            compact('archivos', 'categoriasSeleccionadas'));
    }

    public function graficas()
    {
        $categorias = CategoArchivo::where('activo', 1)
            ->whereHas('archivos', function ($query) {
                $query->where('activo', 1);
            })
            ->withCount(['archivos' => function ($query) {
                $query->where('activo', 1);
            }])
            ->orderBy('categoria', 'asc')
            ->get();

        return view('admin_formatos.carga_archivos.analitica.graficas', compact('categorias'));
    }

    // ─── PRIVADOS ─────────────────────────────────────────────────────────────

    /**
     * Construye la consulta base con los filtros categoria[] y buscar.
     * Reutilizada por index() (paginada) e imprimirReporte() (limitada).
     *
     * @param  Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQuery(Request $request)
    {
        $categoriaFiltro = $request->input('categoria', []);
        $buscar          = $request->get('buscar');

        $query = CargaArchivo::with('categoria')
            ->orderBy('id_archivo', 'desc');

        // Filtro por categorías seleccionadas (dropdown de checkboxes)
        if (!empty($categoriaFiltro)) {
            $query->whereIn('id_catego', (array) $categoriaFiltro);
        }

        // Filtro por término de búsqueda (encapsulado en closure)
        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('nombre', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('descripcion_archivo', 'like', '%' . $buscarLimpiado . '%');
            });
        }

        return $query;
    }
}