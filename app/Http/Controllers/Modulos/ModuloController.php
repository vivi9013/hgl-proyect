<?php

namespace App\Http\Controllers\Modulos;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Models\CategoriaModulo;
use App\Models\Proyecto;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModuloController extends Controller
{
    // Traducciones de color de AdminLTE para mostrar en la interfaz
    protected $colorTranslations = [
        'red' => 'Rojo',
        'yellow' => 'Amarillo',
        'aqua' => 'Aqua',
        'blue' => 'Azul',
        'light-blue' => 'Azul Claro',
        'green' => 'Verde',
        'navy' => 'Militar',
        'teal' => 'Verde Azulado',
        'olive' => 'Verde Olivo',
        'lime' => 'Lima',
        'orange' => 'Naranja',
        'fuchsia' => 'Fucsia',
        'purple' => 'Morado',
        'maroon' => 'Granada',
        'black' => 'Negro',
        'red-active' => 'Rojo Activo',
        'yellow-active' => 'Amarillo Activo',
        'aqua-active' => 'Aqua Activo',
        'blue-active' => 'Azul Activo',
        'light-blue-active' => 'Azul Claro Activo',
        'green-active' => 'Verde Activo',
        'navy-active' => 'Militar Activo',
        'teal-active' => 'Verde Azulado Activo',
        'olive-active' => 'Verde Olivo Activo',
        'lime-active' => 'Lima Activo',
        'orange-active' => 'Naranja Activo',
        'fuchsia-active' => 'Fucsia Activo',
        'purple-active' => 'Morado Activo',
        'maroon-active' => 'Granada Activo',
        'black-active' => 'Negro Activo',
    ];

    /**
     * Muestra la lista de módulos y el formulario de alta.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $query = Modulo::with('categoria')
            ->orderBy('id', 'desc');

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('nombre', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('carpeta', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('creador', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhereHas('categoria', function ($subQ) use ($buscarLimpiado) {
                      $subQ->where('categoria', 'like', '%' . $buscarLimpiado . '%');
                  });
            });
        }

        $modulos = $query->paginate(10);

        // Si es petición AJAX, retornamos la tabla renderizada
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin_sistema.modulos.partials.tabla', compact('modulos'))->render(),
                'links' => $modulos->links('pagination::bootstrap-4')->render(),
                'total' => $modulos->total(),
                'info' => "Mostrando " . ($modulos->firstItem() ?? 0) . " a " . ($modulos->lastItem() ?? 0) . " de " . $modulos->total() . " registros"
            ]);
        }

        // Cargar categorías activas para el formulario
        $categorias = CategoriaModulo::where('activo', 1)
            ->orderBy('categoria', 'asc')
            ->get();

        $colores = $this->colorTranslations;

        return view('admin_sistema.modulos.index', compact('modulos', 'categorias', 'colores'));
    }

    /**
     * Guarda un nuevo módulo.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre'             => 'required|string|max:255',
            'carpeta'            => 'required|string|max:255',
            'id_CategoriaModulo' => 'required|exists:categoria_modulo,id_CategoriaModulo',
            'color'              => 'required|string|max:50',
            'icono'              => 'required|string|max:100',
            'creador'            => 'required|string|max:255',
            'descripcion'        => 'required|string',
        ]);

        // Evitar duplicados por nombre en la misma categoría
        $existe = Modulo::where('nombre', trim($request->nombre))
            ->where('id_CategoriaModulo', $request->id_CategoriaModulo)
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Este nombre de módulo ya se encuentra registrado en esta categoría.']);
        }

        Modulo::create([
            'nombre'             => trim($request->nombre),
            'carpeta'            => trim($request->carpeta),
            'id_CategoriaModulo' => $request->id_CategoriaModulo,
            'color'              => trim($request->color),
            'icono'              => trim($request->icono),
            'creador'            => trim($request->creador),
            'descripcion'        => trim($request->descripcion),
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'activo'             => 1,
            'usuario'            => Auth::id() ?? 1,
            'orden'              => 1,
        ]);

        return redirect()
            ->route('modulos.index')
            ->with('exitog', 'El registro se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario para editar un módulo.
     */
    public function editar($id)
    {
        $modulo = Modulo::findOrFail($id);
        
        $categorias = CategoriaModulo::where('activo', 1)
            ->orderBy('categoria', 'asc')
            ->get();

        $colores = $this->colorTranslations;

        return view('admin_sistema.modulos.editar', compact('modulo', 'categorias', 'colores'));
    }

    /**
     * Actualiza el módulo en la base de datos.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'             => 'required|string|max:255',
            'carpeta'            => 'required|string|max:255',
            'id_CategoriaModulo' => 'required|exists:categoria_modulo,id_CategoriaModulo',
            'color'              => 'required|string|max:50',
            'icono'              => 'required|string|max:100',
            'creador'            => 'required|string|max:255',
            'descripcion'        => 'required|string',
        ]);

        $modulo = Modulo::findOrFail($id);

        // Validar duplicado excluyendo el ID actual
        $existe = Modulo::where('nombre', trim($request->nombre))
            ->where('id_CategoriaModulo', $request->id_CategoriaModulo)
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nombre' => 'Este nombre de módulo ya se encuentra registrado en esta categoría.']);
        }

        $modulo->update([
            'nombre'             => trim($request->nombre),
            'carpeta'            => trim($request->carpeta),
            'id_CategoriaModulo' => $request->id_CategoriaModulo,
            'color'              => trim($request->color),
            'icono'              => trim($request->icono),
            'creador'            => trim($request->creador),
            'descripcion'        => trim($request->descripcion),
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'usuario'            => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('modulos.index')
            ->with('exito', 'El registro se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo (AJAX).
     */
    public function cambiarStatus($id)
    {
        $modulo = Modulo::findOrFail($id);
        $modulo->activo = ($modulo->activo == 1) ? 0 : 1;
        $modulo->fecha = now()->toDateString();
        $modulo->hora = now()->toTimeString();
        $modulo->usuario = Auth::id() ?? 1;
        $modulo->save();

        return response()->json([
            'success' => true,
            'activo'  => $modulo->activo,
            'message' => 'El estado del módulo se ha actualizado correctamente.'
        ]);
    }

    /**
     * Interfaz para asignar proyectos.
     */
    public function proyectos($id)
    {
        $modulo = Modulo::with('proyectos')->findOrFail($id);

        $proyectos = Proyecto::where('activo', 1)
            ->orderBy('id_proyecto', 'desc')
            ->get();

        // IDs de proyectos ya asignados (colección para usar contains() en la vista)
        $asignados = $modulo->proyectos->pluck('id_proyecto');

        return view('admin_sistema.modulos.proyectos', compact('modulo', 'proyectos', 'asignados'));
    }

    /**
     * Guarda la asignación de proyectos (sync atómico).
     */
    public function actualizarProyectos(Request $request, $id)
    {
        $modulo = Modulo::findOrFail($id);

        $proyectoIds = array_filter((array) $request->input('proyectos', []), 'is_numeric');

        $syncData = [];
        $fecha   = now()->toDateString();
        $hora    = now()->toTimeString();
        $usuario = Auth::id() ?? 1;

        foreach ($proyectoIds as $idProyecto) {
            $syncData[(int) $idProyecto] = [
                'fecha'   => $fecha,
                'hora'    => $hora,
                'usuario' => $usuario,
            ];
        }

        DB::transaction(function () use ($modulo, $syncData) {
            $modulo->proyectos()->sync($syncData);
        });

        return redirect()
            ->route('modulos.proyectos', $id)
            ->with('exito', 'Los proyectos asociados al módulo se han actualizado correctamente.');
    }

    /**
     * Interfaz para asignar perfiles.
     */
    public function perfiles($id)
    {
        $modulo = Modulo::with('perfiles')->findOrFail($id);

        $perfiles = Perfil::where('activo', 1)
            ->orderBy('id', 'desc')
            ->get();

        // IDs de perfiles ya asignados (colección para usar contains() en la vista)
        $asignados = $modulo->perfiles->pluck('id');

        return view('admin_sistema.modulos.perfiles', compact('modulo', 'perfiles', 'asignados'));
    }

    /**
     * Guarda la asignación de perfiles (sync atómico).
     */
    public function actualizarPerfiles(Request $request, $id)
    {
        $modulo = Modulo::findOrFail($id);

        $perfilIds = array_filter((array) $request->input('perfiles', []), 'is_numeric');

        $syncData = [];
        $fecha   = now()->toDateString();
        $hora    = now()->toTimeString();
        $usuario = Auth::id() ?? 1;

        foreach ($perfilIds as $idPerfil) {
            $syncData[(int) $idPerfil] = [
                'fecha'   => $fecha,
                'hora'    => $hora,
                'usuario' => $usuario,
            ];
        }

        DB::transaction(function () use ($modulo, $syncData) {
            $modulo->perfiles()->sync($syncData);
        });

        return redirect()
            ->route('modulos.perfiles', $id)
            ->with('exito', 'Los perfiles asociados al módulo se han actualizado correctamente.');
    }

    /**
     * Muestra la interfaz de reportes con estadísticas rápidas.
     */
    public function reportes()
    {
        $stats = [
            'total'      => Modulo::count(),
            'activos'    => Modulo::where('activo', 1)->count(),
            'inactivos'  => Modulo::where('activo', 0)->count(),
            'categorias' => CategoriaModulo::whereHas('modulos')->count(),
        ];

        return view('admin_sistema.modulos.reportes', compact('stats'));
    }

    /**
     * Genera el reporte imprimible según tipo: 'completa', 'categoria', 'estado'.
     */
    public function imprimir($tipo = 'completa')
    {
        $query = Modulo::with('categoriaModulo')->orderBy('nombre', 'asc');

        if ($tipo === 'estado') {
            $query->orderBy('activo', 'desc');
        }

        $modulos = $query->get();

        return view('admin_sistema.modulos.reportes.reporte_impresion', compact('modulos', 'tipo'));
    }

    /**
     * Muestra la interfaz de gráficas con datos analíticos.
     */
    public function graficas()
    {
        // Stats rápidos para KPIs
        $stats = [
            'total'      => Modulo::count(),
            'activos'    => Modulo::where('activo', 1)->count(),
            'inactivos'  => Modulo::where('activo', 0)->count(),
            'categorias' => CategoriaModulo::whereHas('modulos')->count(),
        ];

        // Módulos por categoría (variable $dataCategoria esperada por la vista)
        $dataCategoria = CategoriaModulo::withCount('modulos as contador')
            ->having('contador', '>', 0)
            ->orderBy('categoria', 'asc')
            ->get();

        // Módulos por proyecto (variable $dataProyectos esperada por la vista)
        $dataProyectos = DB::table('modulo_proyecto')
            ->join('proyectos', 'modulo_proyecto.id_proyecto', '=', 'proyectos.id_proyecto')
            ->selectRaw('proyectos.proyecto, COUNT(modulo_proyecto.id_modulo) as contador')
            ->where('proyectos.activo', 1)
            ->groupBy('proyectos.id_proyecto', 'proyectos.proyecto')
            ->orderBy('proyectos.proyecto', 'asc')
            ->get();

        // Módulos por perfil (variable $dataPerfiles esperada por la vista)
        $dataPerfiles = DB::table('modulo_perfil')
            ->join('perfiles', 'modulo_perfil.id_perfil', '=', 'perfiles.id')
            ->selectRaw('perfiles.nombre as perfil, COUNT(modulo_perfil.id_modulo) as contador')
            ->where('perfiles.activo', 1)
            ->groupBy('perfiles.id', 'perfiles.nombre')
            ->orderBy('perfiles.nombre', 'asc')
            ->get();

        return view('admin_sistema.modulos.analitica.graficas', compact(
            'stats',
            'dataCategoria',
            'dataProyectos',
            'dataPerfiles'
        ));
    }

    /**
     * Retorna HTML renderizado con los módulos de una categoría (AJAX).
     */
    public function categoriaPreview(Request $request)
    {
        $idCategoria = $request->input('idCategoria');
        $idPerfil = Auth::user()->id_perfil ?? 1;

        // Obtener categorías que tengan módulos activos para este perfil y categoría
        $categorias = CategoriaModulo::where('id_CategoriaModulo', $idCategoria)
            ->where('activo', 1)
            ->whereHas('modulos', function ($query) use ($idPerfil) {
                $query->where('activo', 1)
                      ->whereHas('perfiles', function ($q) use ($idPerfil) {
                          $q->where('id_perfil', $idPerfil);
                      });
            })
            ->with(['modulos' => function ($query) use ($idPerfil) {
                $query->where('activo', 1)
                      ->whereHas('perfiles', function ($q) use ($idPerfil) {
                          $q->where('id_perfil', $idPerfil);
                      })
                      ->orderBy('orden')
                      ->orderBy('nombre');
            }])
            ->get();

        return view('admin_sistema.modulos.partials.acordeon_modulos', compact('categorias'));
    }
}
