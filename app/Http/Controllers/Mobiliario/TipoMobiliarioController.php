<?php

namespace App\Http\Controllers\Mobiliario;

use App\Http\Controllers\Controller;
use App\Models\TipoMobiliario;
use App\Models\Mobiliario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TipoMobiliarioController extends Controller
{
    /**
     * Muestra el listado de tipos de mobiliario con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = TipoMobiliario::orderBy('id', 'desc');

        if (!empty($buscar)) {
            $query->where('tipo', 'LIKE', "%{$buscar}%");
        }

        // AJAX: devolver solo la vista parcial de la tabla
        if ($request->ajax()) {
            $tipos = $query->paginate(10)->withQueryString();
            return view('admin_mobiliario.tipo_mobiliario.partials.tabla', compact('tipos'));
        }

        $tipos = $query->paginate(10)->withQueryString();

        return view('admin_mobiliario.tipo_mobiliario.index', compact('tipos', 'buscar'));
    }

    /**
     * Guarda un nuevo tipo de mobiliario en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|max:255',
        ], [
            'tipo.required' => 'El nombre del tipo de mobiliario es obligatorio.',
            'tipo.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        // Verificar duplicados (insensible a mayúsculas/minúsculas)
        $existe = TipoMobiliario::whereRaw(
            'LOWER(tipo) = ?',
            [strtolower(trim($request->tipo))]
        )->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['tipo' => 'Este tipo de mobiliario ya se encuentra registrado.']);
        }

        TipoMobiliario::create([
            'tipo'    => trim($request->tipo),
            'fecha'   => now()->toDateString(),
            'hora'    => now()->toTimeString(),
            'activo'  => 1,
            'usuario' => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('tipo_mobiliario.index')
            ->with('exitog', 'El tipo de mobiliario se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un tipo de mobiliario.
     */
    public function editar($id)
    {
        $tipo = TipoMobiliario::findOrFail($id);

        return view('admin_mobiliario.tipo_mobiliario.editar', compact('tipo'));
    }

    /**
     * Actualiza los datos de un tipo de mobiliario.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|string|max:255',
        ], [
            'tipo.required' => 'El nombre del tipo de mobiliario es obligatorio.',
            'tipo.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        $tipo = TipoMobiliario::findOrFail($id);

        // Verificar duplicados excluyendo el registro actual
        $existe = TipoMobiliario::whereRaw(
            'LOWER(tipo) = ?',
            [strtolower(trim($request->tipo))]
        )
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['tipo' => 'Ya existe otro tipo de mobiliario registrado con ese nombre.']);
        }

        $tipo->update([
            'tipo'    => trim($request->tipo),
            'fecha'   => now()->toDateString(),
            'hora'    => now()->toTimeString(),
            'usuario' => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('tipo_mobiliario.index')
            ->with('exito', 'El tipo de mobiliario se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un tipo de mobiliario.
     */
    public function cambiarStatus($id)
    {
        $tipo = TipoMobiliario::findOrFail($id);

        $tipo->activo  = $tipo->activo == 1 ? 0 : 1;
        $tipo->fecha   = now()->toDateString();
        $tipo->hora    = now()->toTimeString();
        $tipo->usuario = Auth::id() ?? 1;
        $tipo->save();

        return redirect()
            ->route('tipo_mobiliario.index')
            ->with('exito', 'El estado del tipo de mobiliario se ha actualizado correctamente.');
    }

    /**
     * Verifica por AJAX si el nombre ya está registrado.
     */
    public function verificar(Request $request)
    {
        $nombre = $request->query('tipo');

        if (!$nombre) {
            return response()->json([
                'disponible' => false,
                'error'      => 'Parámetro ausente',
            ]);
        }

        $existe = TipoMobiliario::whereRaw(
            'LOWER(tipo) = ?',
            [strtolower(trim($nombre))]
        )->exists();

        return response()->json([
            'disponible' => !$existe,
        ]);
    }

    /**
     * Muestra el panel de reportes de tipos de mobiliario.
     */
    public function reportes()
    {
        return view('admin_mobiliario.tipo_mobiliario.analitica.reportes.index');
    }

    /**
     * Muestra las gráficas analíticas del catálogo de tipos de mobiliario.
     */
    public function graficas()
    {
        // Estadísticas generales
        $total    = TipoMobiliario::count();
        $activos  = TipoMobiliario::where('activo', 1)->count();
        $inactivos = TipoMobiliario::where('activo', 0)->count();

        // Donut: distribución activos / inactivos
        $porEstado = [
            'Activos'   => $activos,
            'Inactivos' => $inactivos,
        ];

        // Barras: top tipos por cantidad de mobiliario registrado
        $porMobiliario = DB::table('mobiliario')
            ->join('tipo_mobiliario', 'mobiliario.id_tipo_mobiliario', '=', 'tipo_mobiliario.id')
            ->select('tipo_mobiliario.tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_mobiliario.tipo')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'tipo');

        $stats = [
            'total'    => $total,
            'activos'  => $activos,
            'inactivos'=> $inactivos,
        ];

        return view('admin_mobiliario.tipo_mobiliario.analitica.graficas', compact(
            'stats', 'porEstado', 'porMobiliario'
        ));
    }

    /**
     * Genera el reporte de impresión de tipos de mobiliario.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = TipoMobiliario::orderBy('tipo', 'asc');

        if (!empty($buscar)) {
            $query->where('tipo', 'LIKE', "%{$buscar}%");
        }

        $tipos = $query->get();

        return view(
            'admin_mobiliario.tipo_mobiliario.analitica.reportes.impresion',
            compact('tipos', 'buscar')
        );
    }
}
