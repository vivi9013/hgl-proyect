<?php

namespace App\Http\Controllers\BuscadorArchivos;

use App\Http\Controllers\Controller;
use App\Models\BuscadorArchivos\CargaArchivo;
use App\Models\BuscadorArchivos\CategoArchivo;
use App\Models\BuscadorArchivos\TrabajadorCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\Sanitizable;
use Illuminate\Support\Facades\Storage;

class BuscadorArchivosController extends Controller
{
    use Sanitizable;

    /**
     * Vista principal del Buscador.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $idPersona = $user->id_persona;

        // Obtener categorías permitidas para el trabajador
        $categoriasPermitidasIds = TrabajadorCategoria::where('id_trabajador', $idPersona)
            ->pluck('id_categoria');

        $categorias = CategoArchivo::where('activo', 1)
            ->whereIn('id_catego_archivos', $categoriasPermitidasIds)
            ->orderBy('categoria')
            ->get();

        // Carga inicial paginada (SSR)
        $archivos = CargaArchivo::where('activo', 1)
            ->whereIn('id_catego', $categoriasPermitidasIds)
            ->with('categoria')
            ->paginate(10);

        return view('admin_formatos.buscador_archivos.index', compact('categorias', 'archivos'));
    }

    /**
     * Carga y filtra dinámicamente la tabla vía AJAX.
     */
    public function filtrar(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autorizado'], 401);
        }
        $idPersona = $user->id_persona;

        // Validar permisos del trabajador
        $categoriasPermitidasIds = TrabajadorCategoria::where('id_trabajador', $idPersona)
            ->pluck('id_categoria');

        $archivos = $this->buildQuery($request, $categoriasPermitidasIds)->paginate(10);

        return response()->json([
            'html'  => view('admin_formatos.buscador_archivos.partials.tabla', compact('archivos'))->render(),
            'total' => $archivos->total(),
            'info'  => 'Mostrando ' . ($archivos->firstItem() ?? 0) . ' a ' . ($archivos->lastItem() ?? 0)
                . ' de ' . $archivos->total() . ' registros',
            'links' => (string) $archivos->appends($request->except('page'))->links('pagination::bootstrap-4'),
        ]);
    }

    /**
     * Descarga de archivos segura y validada.
     */
    public function descargar($id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'No autorizado');
        }
        $idPersona = $user->id_persona;

        $archivo = CargaArchivo::where('activo', 1)
            ->where('id_archivo', $id)
            ->firstOrFail();

        // Validar que el trabajador tiene acceso a la categoría del archivo
        $tieneAcceso = TrabajadorCategoria::where('id_trabajador', $idPersona)
            ->where('id_categoria', $archivo->id_catego)
            ->exists();

        if (!$tieneAcceso) {
            abort(403, 'No tienes permiso para descargar este archivo.');
        }

        $rutaRelativa   = $archivo->ruta_fisica;
        $nombreDescarga = $archivo->nombre_fisico;
        $pathReal       = storage_path("app/{$rutaRelativa}");

        if (!Storage::disk('local')->exists($rutaRelativa)) {
            Log::warning("Archivo no encontrado: {$pathReal}");
            abort(404, 'El archivo físico no fue encontrado en el servidor.');
        }

        return response()->download($pathReal, $nombreDescarga);
    }

    /**
     * Impresión de lista filtrada (HTML print-friendly).
     * Reutiliza buildQuery() para reflejar exactamente los filtros activos del índice.
     */
    public function imprimirReporte(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'No autorizado');
        }
        $idPersona = $user->id_persona;

        // La validación de permisos por categoría se mantiene intacta
        $categoriasPermitidasIds = TrabajadorCategoria::where('id_trabajador', $idPersona)
            ->pluck('id_categoria');

        $archivos = $this->buildQuery($request, $categoriasPermitidasIds)
            ->limit(500)
            ->get();

        return view('admin_formatos.buscador_archivos.analitica.reportes.impresion', compact('archivos'));
    }

    // ─── PRIVADOS ─────────────────────────────────────────────────────────────

    /**
     * Construye la consulta base aplicando los filtros de categoria[] y buscar,
     * restringida a las categorías permitidas para el trabajador.
     *
     * @param  Request                         $request
     * @param  \Illuminate\Support\Collection  $categoriasPermitidasIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQuery(Request $request, $categoriasPermitidasIds)
    {
        $categoriaFiltro = $request->input('categoria', []);
        $buscar          = $request->get('buscar');

        $query = CargaArchivo::where('activo', 1)
            ->whereIn('id_catego', $categoriasPermitidasIds)
            ->with('categoria');

        // Filtro por categorías seleccionadas (dropdown de checkboxes)
        if (!empty($categoriaFiltro)) {
            $query->whereIn('id_catego', (array) $categoriaFiltro);
        }

        // Filtro por buscador (encapsulado en closure para evitar fugas de información)
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