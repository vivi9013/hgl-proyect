<?php

namespace App\Http\Controllers\Trabajadores;

use App\Http\Controllers\Controller;
use App\Models\TipoTrabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\RespondeTablaAjax;

class TipoTrabajadorController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Muestra el listado de tipos de trabajador con búsqueda, filtros y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $status = $request->get('status', '');

        $query = TipoTrabajador::orderBy('id', 'desc');

        if (!empty($buscar)) {
            $query->where('tipo', 'LIKE', "%{$buscar}%");
        }

        if (!empty($status)) {
            $statusArray = is_array($status) ? $status : explode(',', $status);
            $statusInts = array_map(function($val) {
                return $val === 'Activo' ? 1 : 0;
            }, $statusArray);
            $query->whereIn('activo', $statusInts);
        }

        $tipos = $query->paginate(10)->withQueryString();

        // Responder con la preocupación compartida si es AJAX
        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $tipos,
            'admin_institucional.tipo_trabajador.partials.tabla',
            compact('tipos'),
            'tipos de trabajador'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('admin_institucional.tipo_trabajador.index', compact('tipos', 'buscar'));
    }

    /**
     * Guarda un nuevo tipo de trabajador en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|max:255',
        ], [
            'tipo.required' => 'El nombre del tipo de trabajador es obligatorio.',
            'tipo.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        // Verificar duplicados (insensible a mayúsculas/minúsculas)
        $existe = TipoTrabajador::whereRaw(
            'LOWER(tipo) = ?',
            [strtolower(trim($request->tipo))]
        )->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['tipo' => 'Este tipo de trabajador ya se encuentra registrado.']);
        }

        TipoTrabajador::create([
            'tipo'    => trim($request->tipo),
            'fecha'   => now()->toDateString(),
            'hora'    => now()->toTimeString(),
            'activo'  => 1,
            'usuario' => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('tipo_trabajador.index')
            ->with('exitog', 'El tipo de trabajador se ha guardado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un tipo de trabajador.
     */
    public function editar($id)
    {
        $tipo = TipoTrabajador::findOrFail($id);

        return view('admin_institucional.tipo_trabajador.editar', compact('tipo'));
    }

    /**
     * Actualiza los datos de un tipo de trabajador.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|string|max:255',
        ], [
            'tipo.required' => 'El nombre del tipo de trabajador es obligatorio.',
            'tipo.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        $tipo = TipoTrabajador::findOrFail($id);

        // Verificar duplicados excluyendo el registro actual
        $existe = TipoTrabajador::whereRaw(
            'LOWER(tipo) = ?',
            [strtolower(trim($request->tipo))]
        )
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['tipo' => 'Ya existe otro tipo de trabajador registrado con ese nombre.']);
        }

        $tipo->update([
            'tipo'    => trim($request->tipo),
            'fecha'   => now()->toDateString(),
            'hora'    => now()->toTimeString(),
            'usuario' => Auth::id() ?? 1,
        ]);

        return redirect()
            ->route('tipo_trabajador.index')
            ->with('exito', 'El tipo de trabajador se ha actualizado correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un tipo de trabajador.
     */
    public function cambiarStatus(Request $request, $id)
    {
        $tipo = TipoTrabajador::findOrFail($id);

        $tipo->activo  = $tipo->activo == 1 ? 0 : 1;
        $tipo->fecha   = now()->toDateString();
        $tipo->hora    = now()->toTimeString();
        $tipo->usuario = Auth::id() ?? 1;
        $tipo->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo'  => $tipo->activo,
                'mensaje' => 'El estado del tipo de trabajador se ha actualizado correctamente.'
            ]);
        }

        return redirect()
            ->route('tipo_trabajador.index')
            ->with('exito', 'El estado del tipo de trabajador se ha actualizado correctamente.');
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

        $existe = TipoTrabajador::whereRaw(
            'LOWER(tipo) = ?',
            [strtolower(trim($nombre))]
        )->exists();

        return response()->json([
            'disponible' => !$existe,
        ]);
    }

    /**
     * Muestra el panel de reportes de tipos de trabajador.
     */
    public function reportes()
    {
        return view('admin_institucional.tipo_trabajador.analitica.reportes.index');
    }

    /**
     * Muestra las gráficas analíticas del catálogo de tipos de trabajador.
     */
    public function graficas()
    {
        // Estadísticas generales
        $total     = TipoTrabajador::count();
        $activos   = TipoTrabajador::where('activo', 1)->count();
        $inactivos = TipoTrabajador::where('activo', 0)->count();

        // Donut: distribución activos / inactivos
        $porEstado = [
            'Activos'   => $activos,
            'Inactivos' => $inactivos,
        ];

        // Barras: top tipos por cantidad de trabajadores asignados
        $porTrabajador = DB::table('trabajadores')
            ->join('tipo_trabajador', 'trabajadores.id_tipo_trabajador', '=', 'tipo_trabajador.id')
            ->select('tipo_trabajador.tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_trabajador.tipo')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'tipo');

        $stats = [
            'total'     => $total,
            'activos'   => $activos,
            'inactivos' => $inactivos,
        ];

        return view('admin_institucional.tipo_trabajador.analitica.graficas', compact(
            'stats', 'porEstado', 'porTrabajador'
        ));
    }

    /**
     * Genera el reporte de impresión de tipos de trabajador.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $status = $request->get('status', '');

        $query = TipoTrabajador::orderBy('tipo', 'asc');

        if (!empty($buscar)) {
            $query->where('tipo', 'LIKE', "%{$buscar}%");
        }

        if (!empty($status)) {
            $statusArray = is_array($status) ? $status : explode(',', $status);
            $statusInts = array_map(function($val) {
                return $val === 'Activo' ? 1 : 0;
            }, $statusArray);
            $query->whereIn('activo', $statusInts);
        }

        $tipos = $query->get();

        return view(
            'admin_institucional.tipo_trabajador.analitica.reportes.impresion',
            compact('tipos', 'buscar')
        );
    }
}
