<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProyectoController extends Controller
{
    /**
     * Muestra la lista de proyectos.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $query = Proyecto::withCount(['modulos' => function ($q) {
            $q->where('activo', 1);
        }])->orderBy('id_proyecto', 'desc');

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where('proyecto', 'like', '%' . $buscarLimpiado . '%');
        }

        $proyectos = $query->paginate(10);

        // Si es AJAX, retornamos JSON con la tabla renderizada
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin_sistema.proyectos.partials.tabla', compact('proyectos'))->render(),
                'links' => $proyectos->links('pagination::bootstrap-4')->render(),
                'total' => $proyectos->total(),
                'info' => "Mostrando " . ($proyectos->firstItem() ?? 0) . " a " . ($proyectos->lastItem() ?? 0) . " de " . $proyectos->total() . " registros"
            ]);
        }

        return view('admin_sistema.proyectos.index', compact('proyectos'));
    }

    /**
     * Guarda un nuevo proyecto en el catálogo.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'proyecto' => 'required|string|max:255|unique:proyectos,proyecto',
        ], [
            'proyecto.unique' => 'Este nombre de proyecto ya se encuentra registrado.',
        ]);

        Proyecto::create([
            'proyecto' => trim($request->proyecto),
            'fecha'    => now()->toDateString(),
            'hora'     => now()->toTimeString(),
            'activo'   => 1,
        ]);

        return redirect()
            ->route('proyectos.index')
            ->with('exitog', 'El registro se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario para editar un proyecto.
     */
    public function editar($id)
    {
        $proyecto = Proyecto::findOrFail($id);

        // Obtener todos los módulos activos ordenados por categoría y nombre
        $modulos = Modulo::with('categoria')
            ->where('activo', 1)
            ->orderBy('id_CategoriaModulo')
            ->orderBy('nombre')
            ->get();

        // Módulos ya asignados a este proyecto
        $asignadosModulos = $proyecto->modulos->pluck('id');

        return view('admin_sistema.proyectos.editar', compact('proyecto', 'modulos', 'asignadosModulos'));
    }

    /**
     * Actualiza el proyecto en la base de datos.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'proyecto' => 'required|string|max:255|unique:proyectos,proyecto,' . $id . ',id_proyecto',
        ], [
            'proyecto.unique' => 'Este nombre de proyecto ya se encuentra registrado.',
        ]);

        $proyecto = Proyecto::findOrFail($id);

        $proyecto->update([
            'proyecto' => trim($request->proyecto),
            'fecha'    => now()->toDateString(),
            'hora'     => now()->toTimeString(),
        ]);

        return redirect()
            ->route('proyectos.index')
            ->with('exito', 'El registro se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo (AJAX).
     */
    public function cambiarStatus($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        $proyecto->activo = ($proyecto->activo == 1) ? 0 : 1;
        $proyecto->fecha = now()->toDateString();
        $proyecto->hora = now()->toTimeString();
        $proyecto->save();

        return response()->json([
            'success' => true,
            'activo'  => $proyecto->activo,
            'message' => 'El estado se ha actualizado correctamente.'
        ]);
    }

    /**
     * Guarda la asignación de módulos al proyecto.
     */
    public function actualizarModulos(Request $request, $id)
    {
        $request->validate([
            'modulos'   => 'nullable|array',
            'modulos.*' => 'required|exists:modulos,id'
        ]);

        $proyecto = Proyecto::findOrFail($id);
        $moduloIds = $request->input('modulos', []);

        $syncData = [];
        $fecha = now()->toDateString();
        $hora = now()->toTimeString();
        $usuario = Auth::id() ?? 1;

        foreach ($moduloIds as $idModulo) {
            $syncData[(int) $idModulo] = [
                'fecha'   => $fecha,
                'hora'    => $hora,
                'usuario' => $usuario,
            ];
        }

        DB::transaction(function () use ($proyecto, $syncData) {
            $proyecto->modulos()->sync($syncData);
        });

        return redirect()
            ->route('proyectos.edit', ['id' => $id, 'seccion' => 'modulos'])
            ->with('exito', 'Los módulos asociados al proyecto se han actualizado correctamente.');
    }

    /**
     * Muestra la vista de reportes.
     */
    public function reportes()
    {
        return view('admin_sistema.proyectos.analitica.reportes.index');
    }

    /**
     * Genera la vista imprimible de proyectos.
     */
    public function imprimir()
    {
        $proyectos = Proyecto::orderBy('proyecto', 'asc')->get();
        return view('admin_sistema.proyectos.analitica.reportes.impresion', compact('proyectos'));
    }

    /**
     * Muestra la vista de analítica con gráficos.
     */
    public function graficas()
    {
        $dataGrafica = Proyecto::whereHas('modulos')
            ->withCount(['modulos as contador' => function ($q) {
                $q->where('activo', 1);
            }])
            ->orderBy('proyecto', 'asc')
            ->get();

        return view('admin_sistema.proyectos.analitica.graficas', compact('dataGrafica'));
    }

    /**
     * AJAX: Verifica si el nombre del proyecto ya está registrado.
     */
    public function verificar(Request $request)
    {
        $nombre = $request->query('nombre');

        if (!$nombre) {
            return response()->json(['disponible' => false, 'error' => 'Parámetro ausente']);
        }

        $existe = Proyecto::whereRaw('LOWER(proyecto) = ?', [strtolower(trim($nombre))])->exists();

        return response()->json(['disponible' => !$existe]);
    }
}
