<?php

namespace App\Http\Controllers\PeticionInsumos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondeTablaAjax;
use App\Models\Inventario\AreaAbastecimiento;
use Illuminate\Http\Request;
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

        $query = AreaAbastecimiento::orderBy('id_area_abastecimiento', 'desc');
        // withCount('subareas') deshabilitado: la tabla legacy `subareas_abastecimiento`
        // no tiene la columna FK `id_area_abastecimiento`. Añadir con migración para activarlo.

        if (!empty($buscar)) {
            $query->where('nombre', 'LIKE', "%{$buscar}%");
        }

        if (!empty($status)) {
            $statusArray = is_array($status) ? $status : explode(',', $status);
            $statusInts = array_map(function ($val) {
                return $val === 'Activo' ? 1 : 0;
            }, $statusArray);
            $query->whereIn('activo', $statusInts);
        }

        $areas = $query->paginate(10);

        if ($request->ajax()) {
            return $this->respondeTablaAjax('peticion_insumos.areas_abastecimiento.partials.tabla', compact('areas'));
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
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no debe superar 150 caracteres.',
            'nombre.unique'   => 'Esta área de abastecimiento ya se encuentra registrada.',
        ]);

        AreaAbastecimiento::create([
            'nombre' => trim($request->nombre),
            'activo' => 1,
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
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no debe superar 150 caracteres.',
            'nombre.unique'   => 'Esta área de abastecimiento ya se encuentra registrada.',
        ]);

        $area->update([
            'nombre' => trim($request->nombre),
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

        if (!empty($status)) {
            $statusArray = is_array($status) ? $status : explode(',', $status);
            $statusInts = array_map(function ($val) {
                return $val === 'Activo' ? 1 : 0;
            }, $statusArray);
            $query->whereIn('activo', $statusInts);
        }

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

        $topAreas = AreaAbastecimiento::withCount('subareas')
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
}
