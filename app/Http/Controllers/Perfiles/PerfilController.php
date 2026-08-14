<?php

namespace App\Http\Controllers\Perfiles;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    /**
     * Aplica el filtro de búsqueda a la query de perfiles.
     */
    private function aplicarFiltro($query, Request $request)
    {
        $buscar = $request->get('buscar');

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('nombre', 'like', '%' . $buscarLimpiado . '%')
                  ->orWhere('descripcion', 'like', '%' . $buscarLimpiado . '%');
            });
        }

        return $query;
    }

    /**
     * Muestra la lista de perfiles.
     */
    public function index(Request $request)
    {
        $query = Perfil::withCount(['modulos' => function ($q) {
            $q->where('activo', 1);
        }])->orderBy('id', 'desc');

        $this->aplicarFiltro($query, $request);

        $perfiles = $query->paginate(10);

        // Si es AJAX, retornamos JSON con la tabla renderizada
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('admin_sistema.perfiles.partials.tabla', compact('perfiles'))->render(),
                'links' => $perfiles->links('pagination::bootstrap-4')->render(),
                'total' => $perfiles->total(),
                'info' => "Mostrando " . ($perfiles->firstItem() ?? 0) . " a " . ($perfiles->lastItem() ?? 0) . " de " . $perfiles->total() . " registros"
            ]);
        }

        return view('admin_sistema.perfiles.index', compact('perfiles'));
    }

    /**
     * Guarda un nuevo perfil en el catálogo.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:perfiles,nombre',
            'descripcion' => 'required|string',
        ], [
            'nombre.unique' => 'Este nombre de perfil ya se encuentra registrado.',
        ]);

        Perfil::create([
            'nombre'      => trim($request->nombre),
            'descripcion' => trim($request->descripcion),
            'fecha'       => now()->toDateString(),
            'hora'        => now()->toTimeString(),
            'usuario'     => Auth::id() ?? 1,
            'activo'      => 1,
        ]);

        return redirect()
            ->route('perfiles.index')
            ->with('exitog', 'El registro se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario para editar un perfil.
     */
    public function editar($id)
    {
        $perfil = Perfil::findOrFail($id);
        return view('admin_sistema.perfiles.editar', compact('perfil'));
    }

    /**
     * Actualiza el perfil en la base de datos.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:perfiles,nombre,' . $id,
            'descripcion' => 'required|string',
        ], [
            'nombre.unique' => 'Este nombre de perfil ya se encuentra registrado.',
        ]);

        $perfil = Perfil::findOrFail($id);

        $perfil->update([
            'nombre'      => trim($request->nombre),
            'descripcion' => trim($request->descripcion),
            'fecha'       => now()->toDateString(),
            'hora'        => now()->toTimeString(),
            'usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('perfiles.index')
            ->with('exito', 'El registro se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo (AJAX).
     */
    public function cambiarStatus($id)
    {
        $perfil = Perfil::findOrFail($id);
        $perfil->activo = ($perfil->activo == 1) ? 0 : 1;
        $perfil->fecha = now()->toDateString();
        $perfil->hora = now()->toTimeString();
        $perfil->usuario = Auth::id() ?? 1;
        $perfil->save();

        return response()->json([
            'success' => true,
            'activo'  => $perfil->activo,
            'message' => 'El estado se ha actualizado correctamente.'
        ]);
    }

    /**
     * Muestra la interfaz para asociar/quitar módulos de un perfil.
     */
    public function agregarModulos($id)
    {
        $perfil = Perfil::findOrFail($id);
        
        // Obtener todos los módulos activos ordenados por categoría y nombre
        $modulos = Modulo::with('categoria')
            ->where('activo', 1)
            ->orderBy('id_CategoriaModulo')
            ->orderBy('nombre')
            ->get();

        // Módulos ya asignados a este perfil
        $asignados = $perfil->modulos->pluck('id');

        return view('admin_sistema.perfiles.modulos', compact('perfil', 'modulos', 'asignados'));
    }

    /**
     * Guarda la asignación de módulos al perfil.
     */
    public function actualizarModulos(Request $request, $id)
    {
        $request->validate([
            'modulos'   => 'nullable|array',
            'modulos.*' => 'required|exists:modulos,id'
        ]);

        $perfil = Perfil::findOrFail($id);
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

        DB::transaction(function () use ($perfil, $syncData) {
            $perfil->modulos()->sync($syncData);
        });

        return redirect()
            ->route('perfiles.modulos', $id)
            ->with('exito', 'Los módulos asociados al perfil se han actualizado correctamente.');
    }

    /**
     * Genera la vista imprimible de perfiles, respetando el filtro de búsqueda activo.
     */
    public function imprimir(Request $request)
    {
        $query = Perfil::orderBy('nombre', 'asc');

        $this->aplicarFiltro($query, $request);

        $perfiles = $query->get();

        return view('admin_sistema.perfiles.analitica.reportes.impresion', compact('perfiles'));
    }

    /**
     * Muestra la vista de analítica con gráficos.
     */
    public function graficas()
    {
        // Obtener la cantidad de módulos activos asignados a cada perfil
        $dataGrafica = Perfil::whereHas('modulos')
            ->withCount(['modulos as contador' => function ($q) {
                $q->where('activo', 1);
            }])
            ->orderBy('nombre', 'asc')
            ->get();

        return view('admin_sistema.perfiles.analitica.graficas', compact('dataGrafica'));
    }

    /**
     * AJAX: Verifica si el nombre del perfil ya está registrado.
     */
    public function verificar(Request $request)
    {
        $nombre = $request->query('nombre');

        if (!$nombre) {
            return response()->json(['disponible' => false, 'error' => 'Parámetro ausente']);
        }

        $existe = Perfil::whereRaw('LOWER(nombre) = ?', [strtolower(trim($nombre))])->exists();

        return response()->json(['disponible' => !$existe]);
    }
}
