<?php

namespace App\Http\Controllers\Monitores;

use App\Http\Controllers\Controller;
use App\Models\Monitor;
use App\Models\Mobiliario;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    private const MARCAS = ['HP', 'ACER', 'Samsung', 'NEC', 'ASUS', 'Generico'];
    private const TIPOS  = ['Plano', 'CRT'];

    /**
     * Obtiene el listado de inventario de tipo Monitor (id_tipo_mobiliario = 4)
     * que no ha sido asignado a la tabla monitores.
     */
    private function inventarioDisponible(?int $excluirId = null)
    {
        return DB::table('mobiliario')
            ->leftJoin('monitores', 'mobiliario.inventario', '=', 'monitores.inventario')
            ->where('mobiliario.id_tipo_mobiliario', 4) // 4 = Monitor
            ->where('mobiliario.activo', 1)
            ->where(function ($q) use ($excluirId) {
                $q->whereNull('monitores.id_monitor');
                if ($excluirId) {
                    $q->orWhere('monitores.id_monitor', $excluirId);
                }
            })
            ->select('mobiliario.inventario')
            ->orderBy('mobiliario.inventario')
            ->pluck('mobiliario.inventario');
    }

    /**
     * Muestra el listado de monitores con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $areaFiltro = $request->get('area_id', 'Todos');

        $query = Monitor::with(['mobiliario.area', 'mobiliario.persona', 'mobiliario.departamento'])
            ->orderBy('id_monitor', 'desc');

        // Filtrar por Área si es diferente a "Todos"
        if ($areaFiltro !== 'Todos') {
            $query->whereHas('mobiliario', function ($q) use ($areaFiltro) {
                $q->where('id_area', $areaFiltro);
            });
        }

        // Aplicar término de búsqueda
        if (!empty($buscar)) {
            $b = trim($buscar);
            $query->where(function ($q) use ($b) {
                $q->where('inventario', 'LIKE', "%{$b}%")
                  ->orWhere('marca', 'LIKE', "%{$b}%")
                  ->orWhere('modelo', 'LIKE', "%{$b}%")
                  ->orWhere('serie', 'LIKE', "%{$b}%")
                  ->orWhere('tipo', 'LIKE', "%{$b}%")
                  ->orWhere('descripcion', 'LIKE', "%{$b}%");
            });
        }

        $monitores = $query->paginate(10);

        // Si es petición AJAX, retornamos la vista parcial de la tabla
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'  => view('admin_mobiliario.monitores.partials.tabla', compact('monitores'))->render(),
                'links' => $monitores->links('pagination::bootstrap-4')->render(),
                'total' => $monitores->total(),
                'info'  => 'Mostrando ' . ($monitores->firstItem() ?? 0)
                           . ' a ' . ($monitores->lastItem() ?? 0)
                           . ' de ' . $monitores->total() . ' registros',
            ]);
        }

        $areas = Area::where('activo', 1)->orderBy('area', 'asc')->get();
        $inventario = $this->inventarioDisponible();

        return view('admin_mobiliario.monitores.index', compact(
            'monitores',
            'areas',
            'inventario'
        ) + ['marcas' => self::MARCAS, 'tipos' => self::TIPOS]);
    }

    /**
     * Guarda un nuevo monitor.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'inventario'  => 'required|string|max:50|unique:monitores,inventario',
            'marca'       => 'required|string|in:' . implode(',', self::MARCAS),
            'tipo'        => 'required|string|in:' . implode(',', self::TIPOS),
            'serie'       => 'nullable|string|max:100',
            'modelo'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'inventario.unique' => 'Este número de inventario ya está registrado en un monitor.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Registrar monitor
                Monitor::create([
                    'inventario'  => trim($request->inventario),
                    'marca'       => trim($request->marca),
                    'tipo'        => trim($request->tipo),
                    'serie'       => trim($request->serie ?? ''),
                    'modelo'      => trim($request->modelo),
                    'descripcion' => trim($request->descripcion ?? ''),
                    'fecha'       => now()->toDateString(),
                    'hora'        => now()->toTimeString(),
                    'usuario'     => Auth::id() ?? 1,
                    'activo'      => 1,
                ]);

                // 2. Sincronizar campos principales en mobiliario correspondiente
                Mobiliario::where('inventario', trim($request->inventario))->update([
                    'marca'       => trim($request->marca),
                    'modelo'      => trim($request->modelo),
                    'serie'       => trim($request->serie ?? ''),
                    'descripcion' => trim($request->descripcion ?? ''),
                    'fecha'       => now()->toDateString(),
                    'hora'        => now()->toTimeString(),
                    'usuario'     => Auth::id() ?? 1,
                ]);
            });

            return redirect()
                ->route('monitores.index')
                ->with('exitog', 'El monitor se ha registrado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al registrar el monitor: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra la vista de edición.
     */
    public function editar($id)
    {
        $monitor = Monitor::findOrFail($id);
        $inventario = $this->inventarioDisponible($id);

        return view('admin_mobiliario.monitores.editar', compact('monitor', 'inventario')
            + ['marcas' => self::MARCAS, 'tipos' => self::TIPOS]);
    }

    /**
     * Actualiza el monitor.
     */
    public function actualizar(Request $request, $id)
    {
        $monitor = Monitor::findOrFail($id);

        $request->validate([
            'marca'       => 'required|string|in:' . implode(',', self::MARCAS),
            'tipo'        => 'required|string|in:' . implode(',', self::TIPOS),
            'serie'       => 'nullable|string|max:100',
            'modelo'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request, $monitor) {
                // 1. Actualizar monitor
                $monitor->update([
                    'marca'       => trim($request->marca),
                    'tipo'        => trim($request->tipo),
                    'serie'       => trim($request->serie ?? ''),
                    'modelo'      => trim($request->modelo),
                    'descripcion' => trim($request->descripcion ?? ''),
                    'fecha'       => now()->toDateString(),
                    'hora'        => now()->toTimeString(),
                    'usuario'     => Auth::id() ?? 1,
                ]);

                // 2. Sincronizar campos principales en mobiliario correspondiente
                Mobiliario::where('inventario', $monitor->inventario)->update([
                    'marca'       => trim($request->marca),
                    'modelo'      => trim($request->modelo),
                    'serie'       => trim($request->serie ?? ''),
                    'descripcion' => trim($request->descripcion ?? ''),
                    'fecha'       => now()->toDateString(),
                    'hora'        => now()->toTimeString(),
                    'usuario'     => Auth::id() ?? 1,
                ]);
            });

            return redirect()
                ->route('monitores.index')
                ->with('exito', 'El monitor se ha actualizado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el monitor: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estado activo/inactivo (AJAX).
     */
    public function cambiarStatus($id)
    {
        $monitor = Monitor::findOrFail($id);
        $monitor->activo = ($monitor->activo == 1) ? 0 : 1;
        $monitor->fecha  = now()->toDateString();
        $monitor->hora   = now()->toTimeString();
        $monitor->usuario = Auth::id() ?? 1;

        try {
            DB::transaction(function () use ($monitor) {
                $monitor->save();

                // Actualizar mobiliario correspondiente
                Mobiliario::where('inventario', $monitor->inventario)->update([
                    'activo' => $monitor->activo,
                    'fecha' => now()->toDateString(),
                    'hora' => now()->toTimeString(),
                    'usuario' => Auth::id() ?? 1,
                ]);
            });

            return response()->json([
                'success' => true,
                'activo'  => $monitor->activo,
                'message' => 'El estatus del monitor ha sido actualizado correctamente.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estatus: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene la información del mobiliario de forma asíncrona para prellenar el formulario (AJAX).
     */
    public function getMobiliarioInfo($inventario)
    {
        $mob = Mobiliario::where('inventario', $inventario)->first();

        if (!$mob) {
            return response()->json(['success' => false, 'message' => 'No se encontró el mobiliario.'], 404);
        }

        // Normalizamos la marca para ver si corresponde a las marcas del catálogo
        $marcaNormalizada = 'Generico';
        foreach (self::MARCAS as $m) {
            if (strcasecmp($mob->marca, $m) === 0) {
                $marcaNormalizada = $m;
                break;
            }
        }

        return response()->json([
            'success'     => true,
            'marca'       => $marcaNormalizada,
            'modelo'      => $mob->modelo,
            'serie'       => $mob->serie,
            'descripcion' => $mob->descripcion
        ]);
    }

    /**
     * Muestra la vista de inicio de reportes.
     */
    public function reportes()
    {
        $stats = [
            'total'     => Monitor::count(),
            'activos'   => Monitor::where('activo', 1)->count(),
            'inactivos' => Monitor::where('activo', 0)->count(),
        ];

        return view('admin_mobiliario.monitores.analitica.reportes.index', compact('stats'));
    }

    /**
     * Genera el reporte imprimible.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');

        $query = Monitor::with(['mobiliario.area', 'mobiliario.persona', 'mobiliario.departamento'])
            ->orderBy('id_monitor', 'asc');

        if (!empty($buscar)) {
            $b = trim($buscar);
            $query->where(function ($q) use ($b) {
                $q->where('inventario', 'LIKE', "%{$b}%")
                  ->orWhere('marca', 'LIKE', "%{$b}%")
                  ->orWhere('modelo', 'LIKE', "%{$b}%")
                  ->orWhere('serie', 'LIKE', "%{$b}%")
                  ->orWhere('descripcion', 'LIKE', "%{$b}%");
            });
        }

        $monitores = $query->get();

        return view('admin_mobiliario.monitores.analitica.reportes.impresion', compact('monitores', 'buscar'));
    }

    /**
     * Muestra las gráficas analíticas.
     */
    public function graficas()
    {
        $stats = [
            'total'     => Monitor::count(),
            'activos'   => Monitor::where('activo', 1)->count(),
            'inactivos' => Monitor::where('activo', 0)->count(),
        ];

        // Agrupado por marca
        $porMarca = Monitor::selectRaw('marca, COUNT(*) as total')
            ->groupBy('marca')
            ->orderBy('total', 'desc')
            ->pluck('total', 'marca');

        // Agrupado por tipo
        $porTipo = Monitor::selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->orderBy('total', 'desc')
            ->pluck('total', 'tipo');

        return view('admin_mobiliario.monitores.analitica.graficas', compact(
            'stats',
            'porMarca',
            'porTipo'
        ));
    }
}
