<?php

namespace App\Http\Controllers\PeticionInsumos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondeTablaAjax;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AreaAbastecimientoController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Muestra el listado de áreas de abastecimiento con búsqueda, filtro de estado y paginación AJAX.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $status = $request->get('status', '');

        $query = AreaAbastecimiento::withCount(['subareas' => fn($q) => $q->where('relacion_areas_abastecimiento.activo', 1)])
            ->orderBy('id_area_abastecimiento', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('siglas', 'LIKE', "%{$buscar}%");
            });
        }

        $this->aplicarFiltroEstatus($query, $status);

        $areas = $query->paginate(10);

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $areas,
            'peticion_insumos.areas_abastecimiento.partials.tabla',
            compact('areas'),
            'áreas de abastecimiento'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('peticion_insumos.areas_abastecimiento.index', compact('areas'));
    }

    /**
     * Verifica la disponibilidad del nombre mediante AJAX.
     */
    public function verificar(Request $request)
    {
        $nombre = trim($request->get('nombre', ''));
        $idActual = $request->get('id', null);

        if (empty($nombre)) {
            return response()->json(['valido' => true]);
        }

        $query = AreaAbastecimiento::where('nombre', $nombre);
        if (!empty($idActual)) {
            $query->where('id_area_abastecimiento', '!=', $idActual);
        }

        $existe = $query->exists();

        return response()->json([
            'valido' => !$existe,
            'mensaje' => $existe ? 'El área de abastecimiento ya se encuentra registrada.' : ''
        ]);
    }

    /**
     * Almacena una nueva área de abastecimiento.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150|unique:areasabastecimiento,nombre',
            'siglas' => 'nullable|string|max:50',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no debe superar 150 caracteres.',
            'nombre.unique'   => 'Esta área de abastecimiento ya se encuentra registrada.',
            'siglas.max'      => 'Las siglas no deben superar 50 caracteres.',
        ]);

        AreaAbastecimiento::create([
            'nombre'         => trim($request->nombre),
            'siglas'         => $request->siglas ? trim($request->siglas) : null,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'activo'         => 1,
            'id_usuario'     => auth()->id() ?? 1,
        ]);

        return redirect()
            ->route('areas_abastecimiento.index')
            ->with('success', 'Área de abastecimiento registrada correctamente.');
    }

    /**
     * Muestra la vista de edición de un área.
     */
    public function editar($id)
    {
        $area = AreaAbastecimiento::findOrFail($id);
        return view('peticion_insumos.areas_abastecimiento.editar', compact('area'));
    }

    /**
     * Actualiza el área de abastecimiento en la BD.
     */
    public function actualizar(Request $request, $id)
    {
        $area = AreaAbastecimiento::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:150|unique:areasabastecimiento,nombre,' . $id . ',id_area_abastecimiento',
            'siglas' => 'nullable|string|max:50',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no debe superar 150 caracteres.',
            'nombre.unique'   => 'Esta área de abastecimiento ya se encuentra registrada.',
            'siglas.max'      => 'Las siglas no deben superar 50 caracteres.',
        ]);

        $area->update([
            'nombre' => trim($request->nombre),
            'siglas' => $request->siglas ? trim($request->siglas) : null,
        ]);

        return redirect()
            ->route('areas_abastecimiento.index')
            ->with('success', 'Área de abastecimiento actualizada correctamente.');
    }

    /**
     * Cambia el estatus (activo/inactivo) del área de abastecimiento.
     */
    public function cambiarStatus($id)
    {
        $area = AreaAbastecimiento::findOrFail($id);
        $nuevoEstado = $area->activo == 1 ? 0 : 1;
        $area->update(['activo' => $nuevoEstado]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'activo'  => $nuevoEstado,
                'mensaje' => 'Estatus del área actualizado correctamente.'
            ]);
        }

        return redirect()->back()->with('success', 'Estatus actualizado correctamente.');
    }

    /**
     * Muestra la vista previa de reportes configurables.
     */
    public function reportes()
    {
        return view('peticion_insumos.areas_abastecimiento.analitica.reportes.index');
    }

    /**
     * Genera la vista oficial de impresión extendiendo de reporte_base.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $status = $request->get('status', '');

        $query = AreaAbastecimiento::withCount('subareas')
            ->orderBy('id_area_abastecimiento', 'asc');

        if (!empty($buscar)) {
            $query->where('nombre', 'LIKE', "%{$buscar}%");
        }

        $this->aplicarFiltroEstatus($query, $status);


        $areas = $query->get();

        return view('peticion_insumos.areas_abastecimiento.analitica.reportes.impresion', compact('areas'));
    }

    /**
     * Muestra el panel de analíticas y gráficas del módulo.
     */
    public function graficas()
    {
        $totalActivos = AreaAbastecimiento::where('activo', 1)->count();
        $totalInactivos = AreaAbastecimiento::where('activo', 0)->count();

        $topAreas = AreaAbastecimiento::withCount(['subareas' => fn($q) => $q->where('relacion_areas_abastecimiento.activo', 1)])
            ->orderBy('subareas_count', 'desc')
            ->take(10)
            ->get();

        $dataGrafica = [
            'estatus' => [
                'activos'   => $totalActivos,
                'inactivos' => $totalInactivos,
            ],
            'topSubareas' => [
                'labels' => $topAreas->pluck('nombre')->toArray(),
                'val'    => $topAreas->pluck('subareas_count')->toArray(),
            ]
        ];

        return view('peticion_insumos.areas_abastecimiento.analitica.graficas', compact('dataGrafica'));
    }

    /**
     * Pantalla de Relación de Áreas - Subáreas (tipo mAreaAbastecimiento/relacion_areas.php).
     */
    public function relacionar()
    {
        $areas = AreaAbastecimiento::withCount(['subareas' => fn($q) => $q->where('relacion_areas_abastecimiento.activo', 1)])
            ->with(['subareas'])
            ->orderBy('nombre')
            ->paginate(15);

        $todasSubareas = SubareaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();

        return view('peticion_insumos.areas_abastecimiento.relacionar', compact('areas', 'todasSubareas'));
    }

    /**
     * Vincula una subárea a un área mediante updateOrInsert (reactiva si estaba inactiva).
     */
    public function vincularSubarea(Request $request, $id)
    {
        AreaAbastecimiento::findOrFail($id);

        $request->validate([
            'id_subarea_abastecimiento' => 'required|exists:subareas_abastecimiento,id_subarea_abastecimiento',
        ], [
            'id_subarea_abastecimiento.required' => 'Debe seleccionar una subárea.',
            'id_subarea_abastecimiento.exists'   => 'La subárea seleccionada no es válida.',
        ]);

        DB::table('relacion_areas_abastecimiento')->updateOrInsert(
            [
                'id_area_abastecimiento'    => $id,
                'id_subarea_abastecimiento' => $request->id_subarea_abastecimiento,
            ],
            [
                'activo'         => 1,
                'fecha_registro' => now()->toDateString(),
                'hora_registro'  => now()->toTimeString(),
                'id_usuario'     => Auth::id() ?? 1,
            ]
        );

        $subarea = SubareaAbastecimiento::find($request->id_subarea_abastecimiento);

        return response()->json([
            'success' => true,
            'mensaje' => "Subárea \u2018{$subarea->nombre}\u2019 vinculada correctamente al área.",
            'subarea' => [
                'id'     => $subarea->id_subarea_abastecimiento,
                'nombre' => $subarea->nombre,
                'siglas' => $subarea->siglas,
            ],
        ]);
    }

    /**
     * Desvincula (toggle activo=0) una subárea de un área. NO borra el registro.
     */
    public function desvincularSubarea($id, $idSubarea)
    {
        AreaAbastecimiento::findOrFail($id);

        DB::table('relacion_areas_abastecimiento')
            ->where('id_area_abastecimiento', $id)
            ->where('id_subarea_abastecimiento', $idSubarea)
            ->update(['activo' => 0]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Subárea desvinculada correctamente.',
        ]);
    }
}
