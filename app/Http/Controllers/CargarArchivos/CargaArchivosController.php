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

        // Captura de variables para el filtro dinámico
        $categoriaFiltro = $request->get('categoria', 'Todos');
        $buscar = $request->get('buscar');

        $query = CargaArchivo::with('categoria')
            ->orderBy('id_archivo', 'desc');

        // Aplicar filtro por categoría si no es "Todos"
        if ($categoriaFiltro !== 'Todos') {
            $query->whereHas('categoria', function ($q) use ($categoriaFiltro) {
                $q->where('categoria', $categoriaFiltro);
            });
        }

        // Aplicar término de búsqueda si existe
        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('nombre', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('descripcion_archivo', 'like', '%' . $buscarLimpiado . '%');
            });
        }

        $archivos = $query->paginate(10);

        // Si la petición viene por AJAX, retornamos exclusivamente la vista parcial de la tabla
        if ($request->ajax() || $request->wantsJson()) {
            return view('admin_formatos.carga_archivos.partials.tabla', compact('archivos'));
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
        $nombre = $request->query('nombre');
        $version = $request->query('version');
        $tipo = $request->query('tipo');

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

    public function editar($id)
    {
        $archivo = CargaArchivo::findOrFail($id);
        $categorias = CategoArchivo::where('activo', 1)
            ->orderBy('categoria', 'asc')
            ->get(['id_catego_archivos', 'categoria']);

        return view('admin_formatos.carga_archivos.editar', compact('archivo', 'categorias'));
    }

    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'  => [
                'required',
                'string',
                'max:255',
                // Validación única compuesta: nombre + version_archivo dentro de la misma categoría, ignorando el registro actual
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
            'archivo-a-subir' => 'required|file|mimes:pdf|max:51200', // Máx 50MB
        ]);

        $archivo = CargaArchivo::with('categoria')->findOrFail($id);

        if (!$archivo->categoria) {
            return redirect()->back()->withErrors(['error' => 'La categoría del archivo no es válida.']);
        }

        $rutaRelativa = $archivo->ruta_fisica; // Obtiene "formats/carpeta/archivo.pdf"
        $nombreFisico = $archivo->nombre_fisico; // Obtiene "archivo.pdf"

        if (!$rutaRelativa) {
            return redirect()->back()->withErrors(['error' => 'No se pudo determinar la ruta de destino.']);
        }

        // Laravel crea los directorios automáticamente si no existen al usar storeAs
        $request->file('archivo-a-subir')->storeAs(dirname($rutaRelativa), $nombreFisico, 'local');

        return redirect()
            ->route('carga_archivos.index')
            ->with('success', 'El archivo PDF se ha subido correctamente.');
    }

    public function reportes()
    {
        $categorias = CategoArchivo::where('activo', 1)
            ->orderBy('categoria', 'asc')
            ->get();

        return view('admin_formatos.carga_archivos.reportes', compact('categorias'));
    }

    public function imprimirReporte(Request $request)
    {
        $request->validate([
            'tipo' => 'required|integer|exists:catego_archivos,id_catego_archivos'
        ]);

        $categoria = CategoArchivo::findOrFail($request->tipo);

        $archivos = CargaArchivo::where('id_catego', $request->tipo)
            ->where('activo', 1)
            ->orderBy('id_archivo', 'asc')
            ->get();

        return view('admin_formatos.carga_archivos.reporte_impresion', compact('categoria', 'archivos'));
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

        return view('admin_formatos.carga_archivos.graficas', compact('categorias'));
    }
}
