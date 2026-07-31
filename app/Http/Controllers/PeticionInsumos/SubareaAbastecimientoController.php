<?php

namespace App\Http\Controllers\PeticionInsumos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondeTablaAjax;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\SubareaAbastecimiento;
use App\Models\Inventario\RelacionAreaAbastecimiento;
use Illuminate\Http\Request;

class SubareaAbastecimientoController extends Controller
{
    use RespondeTablaAjax;

    /**
     * Muestra el listado de subáreas de abastecimiento con búsqueda, filtros y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $idArea = $request->get('id_area_abastecimiento', '');
        $status = $request->get('status', '');

        $query = SubareaAbastecimiento::with('areaAbastecimiento')
            ->orderBy('id_subarea_abastecimiento', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('siglas', 'LIKE', "%{$buscar}%");
            });
        }

        if (!empty($idArea)) {
            $query->whereHas('relacionArea', function ($rq) use ($idArea) {
                $rq->where('id_area_abastecimiento', $idArea);
            });
        }

        $this->aplicarFiltroEstatus($query, $status);

        $subareas = $query->paginate(10);
        $areas = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();

        $ajaxResponse = $this->respuestaTablaAjax(
            $request,
            $subareas,
            'peticion_insumos.subareas_abastecimiento.partials.tabla',
            compact('subareas'),
            'subáreas de abastecimiento'
        );

        if ($ajaxResponse) {
            return $ajaxResponse;
        }

        return view('peticion_insumos.subareas_abastecimiento.index', compact('subareas', 'areas'));
    }

    /**
     * Verifica la disponibilidad del nombre de la subárea en el área seleccionada.
     */
    public function verificar(Request $request)
    {
        $nombre = trim($request->get('nombre', ''));
        $idArea = $request->get('id_area_abastecimiento', null);
        $idActual = $request->get('id', null);

        if (empty($nombre) || empty($idArea)) {
            return response()->json(['valido' => true]);
        }

        $query = SubareaAbastecimiento::whereHas('relacionArea', function ($rq) use ($idArea) {
                $rq->where('id_area_abastecimiento', $idArea);
            })
            ->where('nombre', $nombre);

        if (!empty($idActual)) {
            $query->where('id_subarea_abastecimiento', '!=', $idActual);
        }

        $existe = $query->exists();

        return response()->json([
            'valido' => !$existe,
            'mensaje' => $existe ? 'La subárea ya se encuentra registrada en esta área de abastecimiento.' : ''
        ]);
    }

    /**
     * Guarda una nueva subárea de abastecimiento.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_area_abastecimiento' => 'required|integer|exists:areasabastecimiento,id_area_abastecimiento',
            'nombre'                 => 'required|string|max:150',
            'siglas'                 => 'nullable|string|max:20',
        ], [
            'id_area_abastecimiento.required' => 'Debe seleccionar un área de abastecimiento.',
            'nombre.required'                 => 'El nombre de la subárea es obligatorio.',
            'nombre.max'                      => 'El nombre no debe superar 150 caracteres.',
            'siglas.max'                      => 'Las siglas no deben superar 20 caracteres.',
        ]);

        // Verificar duplicado en la misma área
        $existe = SubareaAbastecimiento::whereHas('relacionArea', function ($rq) use ($request) {
                $rq->where('id_area_abastecimiento', $request->id_area_abastecimiento);
            })
            ->where('nombre', trim($request->nombre))
            ->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['nombre' => 'Esta subárea ya se encuentra registrada en el área seleccionada.']);
        }

        $subarea = SubareaAbastecimiento::create([
            'nombre'         => trim($request->nombre),
            'siglas'         => $request->siglas ? trim($request->siglas) : null,
            'fecha_registro' => now()->toDateString(),
            'hora_registro'  => now()->toTimeString(),
            'activo'         => 1,
            'id_usuario'     => auth()->id() ?? 1,
        ]);

        RelacionAreaAbastecimiento::create([
            'id_area_abastecimiento'    => $request->id_area_abastecimiento,
            'id_subarea_abastecimiento' => $subarea->id_subarea_abastecimiento,
            'fecha_registro'            => now()->toDateString(),
            'hora_registro'             => now()->toTimeString(),
            'activo'                    => 1,
            'id_usuario'                => auth()->id() ?? 1,
        ]);

        return redirect()
            ->route('subareas_abastecimiento.index')
            ->with('success', 'Subárea de abastecimiento registrada correctamente.');
    }

    /**
     * Muestra la vista de edición.
     */
    public function editar($id)
    {
        $subarea = SubareaAbastecimiento::with('areaAbastecimiento')->findOrFail($id);
        $areas = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();

        return view('peticion_insumos.subareas_abastecimiento.editar', compact('subarea', 'areas'));
    }

    /**
     * Actualiza una subárea existente.
     */
    public function actualizar(Request $request, $id)
    {
        $subarea = SubareaAbastecimiento::findOrFail($id);

        $request->validate([
            'id_area_abastecimiento' => 'required|integer|exists:areasabastecimiento,id_area_abastecimiento',
            'nombre'                 => 'required|string|max:150',
            'siglas'                 => 'nullable|string|max:20',
        ], [
            'id_area_abastecimiento.required' => 'Debe seleccionar un área de abastecimiento.',
            'nombre.required'                 => 'El nombre de la subárea es obligatorio.',
            'nombre.max'                      => 'El nombre no debe superar 150 caracteres.',
            'siglas.max'                      => 'Las siglas no deben superar 20 caracteres.',
        ]);

        $existe = SubareaAbastecimiento::whereHas('relacionArea', function ($rq) use ($request) {
                $rq->where('id_area_abastecimiento', $request->id_area_abastecimiento);
            })
            ->where('nombre', trim($request->nombre))
            ->where('id_subarea_abastecimiento', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['nombre' => 'Esta subárea ya se encuentra registrada en el área seleccionada.']);
        }

        $subarea->update([
            'nombre' => trim($request->nombre),
            'siglas' => $request->siglas ? trim($request->siglas) : null,
        ]);

        RelacionAreaAbastecimiento::updateOrCreate(
            ['id_subarea_abastecimiento' => $subarea->id_subarea_abastecimiento],
            [
                'id_area_abastecimiento' => $request->id_area_abastecimiento,
                'fecha_registro'         => now()->toDateString(),
                'hora_registro'          => now()->toTimeString(),
                'activo'                 => 1,
                'id_usuario'             => auth()->id() ?? 1,
            ]
        );

        return redirect()
            ->route('subareas_abastecimiento.index')
            ->with('success', 'Subárea de abastecimiento actualizada correctamente.');
    }

    /**
     * Alterna el estado activo/inactivo.
     */
    public function cambiarStatus($id)
    {
        $subarea = SubareaAbastecimiento::findOrFail($id);
        $nuevoEstado = $subarea->activo == 1 ? 0 : 1;
        $subarea->update(['activo' => $nuevoEstado]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'activo'  => $nuevoEstado,
                'mensaje' => 'Estatus de la subárea actualizado correctamente.'
            ]);
        }

        return redirect()->back()->with('success', 'Estatus actualizado correctamente.');
    }

    /**
     * Muestra la vista de reportes configurables.
     */
    public function reportes()
    {
        $areas = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        return view('peticion_insumos.subareas_abastecimiento.analitica.reportes.index', compact('areas'));
    }

    /**
     * Genera la vista oficial de impresión extendiendo reporte_base.
     */
    public function imprimir(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $idArea = $request->get('id_area_abastecimiento', '');
        $status = $request->get('status', '');

        $query = SubareaAbastecimiento::with('areaAbastecimiento')
            ->orderBy('id_subarea_abastecimiento', 'asc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('siglas', 'LIKE', "%{$buscar}%");
            });
        }

        if (!empty($idArea)) {
            $query->whereHas('relacionArea', function ($rq) use ($idArea) {
                $rq->where('id_area_abastecimiento', $idArea);
            });
        }

        $this->aplicarFiltroEstatus($query, $status);

        $subareas = $query->get();

        return view('peticion_insumos.subareas_abastecimiento.analitica.reportes.impresion', compact('subareas'));
    }

    /**
     * Muestra las gráficas estadísticas del módulo.
     */
    public function graficas()
    {
        $totalActivos = SubareaAbastecimiento::where('activo', 1)->count();
        $totalInactivos = SubareaAbastecimiento::where('activo', 0)->count();

        $subareasPorArea = AreaAbastecimiento::withCount('subareas')
            ->orderBy('subareas_count', 'desc')
            ->take(10)
            ->get();

        $dataGrafica = [
            'estatus' => [
                'activos'   => $totalActivos,
                'inactivos' => $totalInactivos,
            ],
            'porArea' => [
                'labels' => $subareasPorArea->pluck('nombre')->toArray(),
                'val'    => $subareasPorArea->pluck('subareas_count')->toArray(),
            ]
        ];

        return view('peticion_insumos.subareas_abastecimiento.analitica.graficas', compact('dataGrafica'));
    }
}
