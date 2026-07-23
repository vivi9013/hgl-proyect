<?php

namespace App\Http\Controllers\Sedes;

use App\Http\Controllers\Controller;
use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SedeController extends Controller
{
    /**
     * Muestra el listado de sedes con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Sede::orderBy('id', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('abreviatura', 'LIKE', "%{$buscar}%");
            });
        }

        // AJAX: devolver solo la vista parcial de la tabla
        if ($request->ajax()) {
            $sedes = $query->paginate(10)->withQueryString();
            return view('admin_institucional.sedes.partials.tabla', compact('sedes'));
        }

        $sedes = $query->paginate(10)->withQueryString();

        return view('admin_institucional.sedes.index', compact('sedes', 'buscar'));
    }

    /**
     * Guarda una nueva sede en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'abreviatura' => 'required|string|max:50',
        ], [
            'nombre.required'      => 'El nombre de la sede es obligatorio.',
            'nombre.max'           => 'El nombre de la sede no puede superar los 255 caracteres.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.max'      => 'La abreviatura no puede superar los 50 caracteres.',
        ]);

        // Verificar duplicado de nombre (insensible a mayúsculas)
        $existeNombre = Sede::whereRaw(
            'LOWER(nombre) = ?',
            [strtolower(trim($request->nombre))]
        )->exists();

        if ($existeNombre) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['nombre' => 'Este nombre de sede ya se encuentra registrado.']);
        }

        // Verificar duplicado de abreviatura
        $existeAbrev = Sede::whereRaw(
            'LOWER(abreviatura) = ?',
            [strtolower(trim($request->abreviatura))]
        )->exists();

        if ($existeAbrev) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['abreviatura' => 'Esta abreviatura ya se encuentra registrada.']);
        }

        Sede::create([
            'nombre'      => trim($request->nombre),
            'abreviatura' => strtoupper(trim($request->abreviatura)),
            'fecha'       => now()->toDateString(),
            'hora'        => now()->toTimeString(),
            'activo'      => 1,
            'usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('sedes.index')
            ->with('exitog', 'La sede se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición de una sede.
     */
    public function editar($id)
    {
        $sede = Sede::findOrFail($id);

        return view('admin_institucional.sedes.editar', compact('sede'));
    }

    /**
     * Actualiza los datos de una sede.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'abreviatura' => 'required|string|max:50',
        ], [
            'nombre.required'      => 'El nombre de la sede es obligatorio.',
            'nombre.max'           => 'El nombre de la sede no puede superar los 255 caracteres.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.max'      => 'La abreviatura no puede superar los 50 caracteres.',
        ]);

        $sedeModel = Sede::findOrFail($id);

        // Verificar duplicado de nombre excluyendo el actual
        $existeNombre = Sede::whereRaw(
            'LOWER(nombre) = ?',
            [strtolower(trim($request->nombre))]
        )->where('id', '!=', $id)->exists();

        if ($existeNombre) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['nombre' => 'Ya existe otra sede con ese nombre.']);
        }

        // Verificar duplicado de abreviatura excluyendo la actual
        $existeAbrev = Sede::whereRaw(
            'LOWER(abreviatura) = ?',
            [strtolower(trim($request->abreviatura))]
        )->where('id', '!=', $id)->exists();

        if ($existeAbrev) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['abreviatura' => 'Ya existe otra sede con esta abreviatura.']);
        }

        $sedeModel->update([
            'nombre'      => trim($request->nombre),
            'abreviatura' => strtoupper(trim($request->abreviatura)),
            'fecha'       => now()->toDateString(),
            'hora'        => now()->toTimeString(),
            'usuario'     => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('sedes.index')
            ->with('exito', 'La sede se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de una sede.
     */
    public function cambiarStatus($id)
    {
        $sede = Sede::findOrFail($id);

        $sede->activo  = $sede->activo == 1 ? 0 : 1;
        $sede->fecha   = now()->toDateString();
        $sede->hora    = now()->toTimeString();
        $sede->usuario = Auth::id() ?? 1;
        $sede->save();

        return redirect()
            ->route('sedes.index')
            ->with('exito', 'El estado de la sede se ha actualizado correctamente.');
    }

    /**
     * Verifica por AJAX si la sede o su abreviatura ya están registradas.
     */
    public function verificar(Request $request)
    {
        $nombre      = $request->query('nombre');
        $abreviatura = $request->query('abreviatura');
        $excluirId   = $request->query('excluir_id');

        $resultado = [
            'nombre_disponible'      => true,
            'abreviatura_disponible' => true
        ];

        if ($nombre) {
            $query = Sede::whereRaw('LOWER(nombre) = ?', [strtolower(trim($nombre))]);
            if ($excluirId) {
                $query->where('id', '!=', $excluirId);
            }
            $resultado['nombre_disponible'] = !$query->exists();
        }

        if ($abreviatura) {
            $query = Sede::whereRaw('LOWER(abreviatura) = ?', [strtolower(trim($abreviatura))]);
            if ($excluirId) {
                $query->where('id', '!=', $excluirId);
            }
            $resultado['abreviatura_disponible'] = !$query->exists();
        }

        return response()->json($resultado);
    }

    /**
     * Muestra el panel de reportes de sedes.
     */
    public function reportes()
    {
        return view('admin_institucional.sedes.analitica.reportes.index');
    }

    /**
     * Genera el reporte de impresión de sedes.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Sede::orderBy('nombre', 'asc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('abreviatura', 'LIKE', "%{$buscar}%");
            });
        }

        $sedes = $query->get();

        return view(
            'admin_institucional.sedes.analitica.reportes.impresion',
            compact('sedes', 'buscar')
        );
    }

    /**
     * Muestra las gráficas analíticas del catálogo de sedes.
     */
    public function graficas()
    {
        // Estadísticas generales
        $total     = Sede::count();
        $activos   = Sede::where('activo', 1)->count();
        $inactivos = Sede::where('activo', 0)->count();

        // Donut: distribución activos / inactivos
        $porEstado = [
            'Activos'   => $activos,
            'Inactivos' => $inactivos,
        ];

        // Barras: top 10 sedes con mayor cantidad de personas asignadas
        $porTrabajadores = DB::table('personas')
            ->join('sedes', 'personas.id_sede', '=', 'sedes.id')
            ->select('sedes.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('sedes.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'nombre');

        $stats = [
            'total'     => $total,
            'activos'   => $activos,
            'inactivos' => $inactivos,
        ];

        return view('admin_institucional.sedes.analitica.graficas', compact(
            'stats', 'porEstado', 'porTrabajadores'
        ));
    }
}
