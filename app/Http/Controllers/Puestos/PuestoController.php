<?php

namespace App\Http\Controllers\Puestos;

use App\Http\Controllers\Controller;
use App\Models\Puesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PuestoController extends Controller
{
    /**
     * Muestra el listado de puestos con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Puesto::orderBy('id', 'desc');

        if (!empty($buscar)) {
            $query->where('puesto', 'LIKE', "%{$buscar}%");
        }

        // AJAX: devolver solo la vista parcial de la tabla
        if ($request->ajax()) {
            $puestos = $query->paginate(10)->withQueryString();
            return view('admin_institucional.puestos.partials.tabla', compact('puestos'));
        }

        $puestos = $query->paginate(10)->withQueryString();

        return view('admin_institucional.puestos.index', compact('puestos', 'buscar'));
    }

    /**
     * Guarda un nuevo puesto en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'puesto' => 'required|string|max:255',
        ], [
            'puesto.required' => 'El nombre del puesto es obligatorio.',
            'puesto.max'      => 'El nombre del puesto no puede superar los 255 caracteres.',
        ]);

        // Verificar duplicado de nombre (insensible a mayúsculas)
        $existePuesto = Puesto::whereRaw(
            'LOWER(puesto) = ?',
            [strtolower(trim($request->puesto))]
        )->exists();

        if ($existePuesto) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['puesto' => 'Este puesto ya se encuentra registrado.']);
        }

        Puesto::create([
            'puesto'  => trim($request->puesto),
            'fecha'   => now()->toDateString(),
            'hora'    => now()->toTimeString(),
            'activo'  => 1,
            'usuario' => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('puestos.index')
            ->with('exitog', 'El puesto se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un puesto.
     */
    public function editar($id)
    {
        $puesto = Puesto::findOrFail($id);

        return view('admin_institucional.puestos.editar', compact('puesto'));
    }

    /**
     * Actualiza los datos de un puesto.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'puesto' => 'required|string|max:255',
        ], [
            'puesto.required' => 'El nombre del puesto es obligatorio.',
            'puesto.max'      => 'El nombre del puesto no puede superar los 255 caracteres.',
        ]);

        $puestoModel = Puesto::findOrFail($id);

        // Verificar duplicado de nombre excluyendo el actual
        $existePuesto = Puesto::whereRaw(
            'LOWER(puesto) = ?',
            [strtolower(trim($request->puesto))]
        )->where('id', '!=', $id)->exists();

        if ($existePuesto) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['puesto' => 'Ya existe otro puesto con ese nombre.']);
        }

        $puestoModel->update([
            'puesto'  => trim($request->puesto),
            'fecha'   => now()->toDateString(),
            'hora'    => now()->toTimeString(),
            'usuario' => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('puestos.index')
            ->with('exito', 'El puesto se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un puesto.
     */
    public function cambiarStatus($id)
    {
        $puesto = Puesto::findOrFail($id);

        $puesto->activo  = $puesto->activo == 1 ? 0 : 1;
        $puesto->fecha   = now()->toDateString();
        $puesto->hora    = now()->toTimeString();
        $puesto->usuario = Auth::id() ?? 1;
        $puesto->save();

        return redirect()
            ->route('puestos.index')
            ->with('exito', 'El estado del puesto se ha actualizado correctamente.');
    }

    /**
     * Verifica por AJAX si el puesto ya está registrado.
     */
    public function verificar(Request $request)
    {
        $puesto    = $request->query('puesto');
        $excluirId = $request->query('excluir_id');

        $resultado = ['puesto_disponible' => true];

        if ($puesto) {
            $query = Puesto::whereRaw('LOWER(puesto) = ?', [strtolower(trim($puesto))]);
            if ($excluirId) {
                $query->where('id', '!=', $excluirId);
            }
            $resultado['puesto_disponible'] = !$query->exists();
        }

        return response()->json($resultado);
    }

    /**
     * Muestra el panel de reportes de puestos.
     */
    public function reportes()
    {
        return view('admin_institucional.puestos.analitica.reportes.index');
    }

    /**
     * Genera el reporte de impresión de puestos.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Puesto::orderBy('puesto', 'asc');

        if (!empty($buscar)) {
            $query->where('puesto', 'LIKE', "%{$buscar}%");
        }

        $puestos = $query->get();

        return view(
            'admin_institucional.puestos.analitica.reportes.impresion',
            compact('puestos', 'buscar')
        );
    }

    /**
     * Muestra las gráficas analíticas del catálogo de puestos.
     */
    public function graficas()
    {
        // Estadísticas generales
        $total     = Puesto::count();
        $activos   = Puesto::where('activo', 1)->count();
        $inactivos = Puesto::where('activo', 0)->count();

        // Donut: distribución activos / inactivos
        $porEstado = [
            'Activos'   => $activos,
            'Inactivos' => $inactivos,
        ];

        // Barras: top 10 puestos con mayor cantidad de trabajadores asignados
        $porTrabajadores = DB::table('trabajadores')
            ->join('puestos', 'trabajadores.id_puesto', '=', 'puestos.id')
            ->select('puestos.puesto', DB::raw('COUNT(*) as total'))
            ->groupBy('puestos.puesto')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'puesto');

        $stats = [
            'total'     => $total,
            'activos'   => $activos,
            'inactivos' => $inactivos,
        ];

        return view('admin_institucional.puestos.analitica.graficas', compact(
            'stats', 'porEstado', 'porTrabajadores'
        ));
    }
}
