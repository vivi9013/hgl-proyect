<?php

namespace App\Http\Controllers\Mobiliario;

use App\Http\Controllers\Controller;
use App\Models\Mobiliario;
use App\Models\TipoMobiliario;
use App\Models\Area;
use App\Models\Persona;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobiliarioController extends Controller
{
    /**
     * Muestra el listado de mobiliario con búsqueda, filtros y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $areaFiltro = $request->get('area_id', 'Todos');
        $tipoFiltro = $request->get('tipo_id', 'Todos');

        $query = Mobiliario::with(['tipoMobiliario', 'area', 'persona', 'departamento'])
            ->orderBy('id', 'desc');

        // Filtrar por Área si es diferente a "Todos"
        if ($areaFiltro !== 'Todos') {
            $query->where('id_area', $areaFiltro);
        }

        // Filtrar por Tipo de Mobiliario si es diferente a "Todos"
        if ($tipoFiltro !== 'Todos') {
            $query->where('id_tipo_mobiliario', $tipoFiltro);
        }

        // Aplicar término de búsqueda si existe
        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function ($q) use ($buscarLimpiado) {
                $q->where('inventario', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('marca', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('modelo', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('serie', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('otros', 'LIKE', "%{$buscarLimpiado}%");
            });
        }

        $mobiliarios = $query->paginate(10);

        // Si la petición viene por AJAX, retornamos exclusivamente la vista parcial de la tabla
        if ($request->ajax()) {
            return view('admin_mobiliario.mobiliario.partials.tabla', compact('mobiliarios'));
        }

        // Obtener catálogos para el alta/edición
        $areas = Area::where('activo', 1)->orderBy('area', 'asc')->get();
        $personas = Persona::where('activo', 1)->orderBy('nombre', 'asc')->get();
        $departamentos = Departamento::where('activo', 1)->orderBy('nombre', 'asc')->get();
        $tiposMobiliario = TipoMobiliario::where('activo', 1)->orderBy('tipo', 'asc')->get();

        return view('admin_mobiliario.mobiliario.index', compact(
            'mobiliarios',
            'buscar',
            'areas',
            'personas',
            'departamentos',
            'tiposMobiliario'
        ));
    }

    /**
     * Guarda un nuevo mobiliario en la base de datos.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'inventario' => 'required|string|max:50|unique:mobiliario,inventario',
            'id_tipo_mobiliario' => 'required|integer',
            'descripcion' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'serie' => 'nullable|string|max:255',
            'id_area' => 'required|integer',
            'id_persona' => 'required|integer',
            'id_departamento' => 'required|integer',
            'otros' => 'nullable|string',
        ], [
            'inventario.unique' => 'Este número de inventario ya está registrado.',
        ]);

        try {
            Mobiliario::create([
                'descripcion' => trim($request->descripcion),
                'marca' => trim($request->marca),
                'modelo' => trim($request->modelo),
                'serie' => $request->filled('serie') ? trim($request->serie) : null,
                'inventario' => trim($request->inventario),
                'otros' => $request->filled('otros') ? trim($request->otros) : null,
                'id_tipo_mobiliario' => $request->id_tipo_mobiliario,
                'id_area' => $request->id_area,
                'id_persona' => $request->id_persona,
                'id_departamento' => $request->id_departamento,
                'fecha' => now()->toDateString(),
                'hora' => now()->toTimeString(),
                'activo' => 1,
                'usuario' => Auth::id() ?? 1,
                'id_factura' => 0
            ]);

            return redirect()
                ->route('mobiliario.index')
                ->with('exitog', 'El mobiliario se ha guardado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al guardar el mobiliario: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function editar($id)
    {
        $mobiliario = Mobiliario::findOrFail($id);
        
        $areas = Area::where('activo', 1)->orderBy('area', 'asc')->get();
        $personas = Persona::where('activo', 1)->orderBy('nombre', 'asc')->get();
        $departamentos = Departamento::where('activo', 1)->orderBy('nombre', 'asc')->get();
        $tiposMobiliario = TipoMobiliario::where('activo', 1)->orderBy('tipo', 'asc')->get();

        return view('admin_mobiliario.mobiliario.editar', compact(
            'mobiliario',
            'areas',
            'personas',
            'departamentos',
            'tiposMobiliario'
        ));
    }

    /**
     * Actualiza los datos del mobiliario en la base de datos.
     */
    public function actualizar(Request $request, $id)
    {
        $mobiliario = Mobiliario::findOrFail($id);

        $request->validate([
            'inventario' => "required|string|max:50|unique:mobiliario,inventario,{$id}",
            'id_tipo_mobiliario' => 'required|integer',
            'descripcion' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'serie' => 'nullable|string|max:255',
            'id_area' => 'required|integer',
            'id_persona' => 'required|integer',
            'id_departamento' => 'required|integer',
            'otros' => 'nullable|string',
        ], [
            'inventario.unique' => 'Este número de inventario ya está registrado.',
        ]);

        try {
            $mobiliario->update([
                'descripcion' => trim($request->descripcion),
                'marca' => trim($request->marca),
                'modelo' => trim($request->modelo),
                'serie' => $request->filled('serie') ? trim($request->serie) : null,
                'inventario' => trim($request->inventario),
                'otros' => $request->filled('otros') ? trim($request->otros) : null,
                'id_tipo_mobiliario' => $request->id_tipo_mobiliario,
                'id_area' => $request->id_area,
                'id_persona' => $request->id_persona,
                'id_departamento' => $request->id_departamento,
                'fecha' => now()->toDateString(),
                'hora' => now()->toTimeString(),
                'usuario' => Auth::id() ?? 1,
            ]);

            return redirect()
                ->route('mobiliario.index')
                ->with('exito', 'El mobiliario se ha actualizado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el mobiliario: ' . $e->getMessage()]);
        }
    }

    /**
     * Alterna el estado activo/inactivo del mobiliario.
     */
    public function cambiarStatus($id)
    {
        $mobiliario = Mobiliario::findOrFail($id);
        $nuevoEstado = $mobiliario->activo == 1 ? 0 : 1;

        try {
            $mobiliario->update([
                'activo' => $nuevoEstado,
                'fecha' => now()->toDateString(),
                'hora' => now()->toTimeString(),
                'usuario' => Auth::id() ?? 1,
            ]);

            return redirect()
                ->route('mobiliario.index')
                ->with('exito', 'El estado del mobiliario se ha actualizado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->route('mobiliario.index')
                ->withErrors(['error' => 'Error al cambiar el estado: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra las gráficas analíticas del módulo de mobiliario general.
     */
    public function graficas()
    {
        // Estadísticas generales
        $total    = Mobiliario::count();
        $activos  = Mobiliario::where('activo', 1)->count();
        $inactivos = Mobiliario::where('activo', 0)->count();

        $stats = compact('total', 'activos', 'inactivos');

        // Donut: activos vs inactivos
        $porEstado = [
            'Activos'   => $activos,
            'Inactivos' => $inactivos,
        ];

        // Barras: top 8 por tipo de mobiliario
        $porTipo = DB::table('mobiliario')
            ->join('tipo_mobiliario', 'mobiliario.id_tipo_mobiliario', '=', 'tipo_mobiliario.id')
            ->select('tipo_mobiliario.tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_mobiliario.tipo')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'tipo');

        // Barras: top 8 por marca
        $porMarca = DB::table('mobiliario')
            ->select('marca', DB::raw('COUNT(*) as total'))
            ->groupBy('marca')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'marca');

        // Barras: top 8 por área (PK es 'id' en la tabla areas)
        $porArea = DB::table('mobiliario')
            ->join('areas', 'mobiliario.id_area', '=', 'areas.id')
            ->select('areas.area', DB::raw('COUNT(*) as total'))
            ->groupBy('areas.area')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'area');

        return view('admin_mobiliario.mobiliario.analitica.graficas', compact(
            'stats', 'porEstado', 'porTipo', 'porMarca', 'porArea'
        ));
    }

    /**
     * Muestra el panel de reportes de mobiliario.
     */
    public function reportes()
    {
        return view('admin_mobiliario.mobiliario.analitica.reportes.index');
    }

    /**
     * Genera el reporte de impresión de mobiliario.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $areaFiltro = $request->get('area_id', 'Todos');
        $tipoFiltro = $request->get('tipo_id', 'Todos');

        $query = Mobiliario::with(['tipoMobiliario', 'area', 'persona', 'departamento'])
            ->orderBy('id', 'asc');

        if ($areaFiltro !== 'Todos') {
            $query->where('id_area', $areaFiltro);
        }

        if ($tipoFiltro !== 'Todos') {
            $query->where('id_tipo_mobiliario', $tipoFiltro);
        }

        if (!empty($buscar)) {
            $buscarLimpiado = trim($buscar);
            $query->where(function($q) use ($buscarLimpiado) {
                $q->where('inventario', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('marca', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('modelo', 'LIKE', "%{$buscarLimpiado}%")
                  ->orWhere('serie', 'LIKE', "%{$buscarLimpiado}%");
            });
        }

        $mobiliarios = $query->get();

        return view('admin_mobiliario.mobiliario.analitica.reportes.impresion', compact('mobiliarios', 'buscar'));
    }
}
