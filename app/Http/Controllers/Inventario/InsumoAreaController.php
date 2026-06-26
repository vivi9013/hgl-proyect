<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\AreaAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsumoAreaController extends Controller
{
    /**
     * Muestra el listado de insumos por área con el formulario de asignación.
     */
    public function index(Request $request)
    {
        $buscar       = $request->get('buscar', '');
        $filtroArea   = $request->get('id_area_almacen', '');

        $query = InsumoArea::with(['insumo', 'areaAlmacen'])
            ->whereHas('insumo', fn($q) => $q->where('activo', 1))
            ->orderBy('id_insumo_area', 'desc');

        if (!empty($filtroArea)) {
            $query->where('id_area_almacen', $filtroArea);
        }

        if (!empty($buscar)) {
            $query->whereHas('insumo', function ($q) use ($buscar) {
                $q->where('clave', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $insumosArea  = $query->paginate(15)->withQueryString();
        $areasAlmacen = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();

        return view('inventario.insumos_area.index', compact(
            'insumosArea', 'areasAlmacen', 'buscar', 'filtroArea'
        ));
    }

    /**
     * Guarda una nueva asignación de insumo a área.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_insumo'       => 'required|integer',
            'id_area_almacen' => 'required|integer',
            'fondo_fijo'      => 'required|integer|min:1',
            'stock'           => 'required|integer|min:0',
        ], [
            'id_insumo.required'       => 'Debe seleccionar un insumo.',
            'id_area_almacen.required' => 'Debe seleccionar un área de almacén.',
            'fondo_fijo.required'      => 'El fondo fijo es obligatorio.',
            'fondo_fijo.min'           => 'El fondo fijo debe ser mayor a cero.',
            'stock.required'           => 'El stock es obligatorio.',
            'stock.min'                => 'El stock no puede ser negativo.',
        ]);

        // Verificar duplicado: el mismo insumo no puede estar asignado dos veces al mismo área
        $existe = InsumoArea::where('id_insumo', $request->id_insumo)
            ->where('id_area_almacen', $request->id_area_almacen)
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['id_insumo' => 'Este insumo ya se encuentra asignado al área seleccionada.']);
        }

        InsumoArea::create([
            'id_insumo'       => $request->id_insumo,
            'id_area_almacen' => $request->id_area_almacen,
            'fondo_fijo'      => $request->fondo_fijo,
            'stock'           => $request->stock,
        ]);

        return redirect()
            ->route('insumos_area.index')
            ->with('exitog', 'El insumo se ha asignado al área correctamente.');
    }

    /**
     * Muestra el formulario de edición para cambiar el área asignada.
     */
    public function editar($id)
    {
        $insumoArea   = InsumoArea::with(['insumo', 'areaAlmacen'])->findOrFail($id);
        $areasAlmacen = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();

        return view('inventario.insumos_area.editar', compact('insumoArea', 'areasAlmacen'));
    }

    /**
     * Actualiza el área asignada de una relación insumo-área.
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'id_area_almacen' => 'required|integer',
            'stock'           => 'required|integer|min:0',
            'fondo_fijo'      => 'required|integer|min:1',
        ], [
            'id_area_almacen.required' => 'Debe seleccionar un área de almacén.',
            'stock.required'           => 'El stock es obligatorio.',
            'stock.min'                => 'El stock no puede ser negativo.',
            'fondo_fijo.required'      => 'El fondo fijo es obligatorio.',
            'fondo_fijo.min'           => 'El fondo fijo debe ser mayor a cero.',
        ]);

        $insumoArea = InsumoArea::findOrFail($id);

        // Verificar que el nuevo área no genere un duplicado (solo si cambia de área)
        if ($insumoArea->id_area_almacen != $request->id_area_almacen) {
            $existe = InsumoArea::where('id_insumo', $insumoArea->id_insumo)
                ->where('id_area_almacen', $request->id_area_almacen)
                ->where('id_insumo_area', '!=', $id)
                ->exists();

            if ($existe) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['id_area_almacen' => 'Este insumo ya está asignado al área seleccionada.']);
            }
        }

        $insumoArea->update([
            'id_area_almacen' => $request->id_area_almacen,
            'stock'           => $request->stock,
            'fondo_fijo'      => $request->fondo_fijo,
        ]);

        return redirect()
            ->route('insumos_area.index')
            ->with('exito', 'La asignación se ha actualizado correctamente.');
    }

    /**
     * Actualiza el stock de un insumo-área vía AJAX (PATCH).
     */
    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $insumoArea = InsumoArea::findOrFail($id);
        $insumoArea->stock = $request->stock;
        $insumoArea->save();

        $porcentaje = $insumoArea->fondo_fijo > 0
            ? round(($request->stock * 100) / $insumoArea->fondo_fijo, 1)
            : 0;

        return response()->json([
            'ok'         => true,
            'mensaje'    => 'Stock actualizado.',
            'stock'      => $insumoArea->stock,
            'fondo_fijo' => $insumoArea->fondo_fijo,
            'porcentaje' => $porcentaje,
        ]);
    }

    /**
     * Actualiza el fondo fijo de un insumo-área vía AJAX (PATCH).
     */
    public function updateFondoFijo(Request $request, $id)
    {
        $request->validate([
            'fondo_fijo' => 'required|integer|min:1',
        ]);

        $insumoArea = InsumoArea::findOrFail($id);
        $insumoArea->fondo_fijo = $request->fondo_fijo;
        $insumoArea->save();

        $porcentaje = $insumoArea->fondo_fijo > 0
            ? round(((int) $insumoArea->stock * 100) / $insumoArea->fondo_fijo, 1)
            : 0;

        return response()->json([
            'ok'         => true,
            'mensaje'    => 'Fondo fijo actualizado.',
            'stock'      => $insumoArea->stock,
            'fondo_fijo' => $insumoArea->fondo_fijo,
            'porcentaje' => $porcentaje,
        ]);
    }

    /**
     * AJAX: devuelve la lista de insumos activos del catálogo para el modal de búsqueda.
     */
    public function buscarInsumosCatalog(Request $request)
    {
        $buscar = $request->get('q', '');
        $all = $request->boolean('all', false);

        if (!$all && strlen($buscar) < 2) {
            return response()->json([]);
        }

        $insumos = Insumo::where('activo', 1)
            ->when($buscar, fn($q) => $q->where('clave', 'LIKE', "%{$buscar}%")
                ->orWhere('descripcion', 'LIKE', "%{$buscar}%"))
            ->orderBy('clave')
            ->when(!$all, fn($q) => $q->limit(20))
            ->get(['id_insumo', 'clave', 'descripcion', 'tipo']);

        return response()->json($insumos);
    }

    /**
     * AJAX: dado una clave, devuelve los datos del insumo (id, descripción, tipo).
     */
    public function consultarInsumoClave(Request $request)
    {
        $clave = trim($request->get('clave', ''));

        if (!$clave) {
            return response()->json(['encontrado' => false]);
        }

        $insumo = Insumo::where('activo', 1)
            ->whereRaw('LOWER(clave) = ?', [strtolower($clave)])
            ->first(['id_insumo', 'clave', 'descripcion', 'tipo']);

        if (!$insumo) {
            return response()->json(['encontrado' => false]);
        }

        return response()->json([
            'encontrado'  => true,
            'id_insumo'   => $insumo->id_insumo,
            'clave'       => $insumo->clave,
            'descripcion' => $insumo->descripcion,
            'tipo'        => $insumo->tipo,
        ]);
    }

    /**
     * Vista del panel de reportes con filtros por nivel de stock.
     */
    public function reportes(Request $request)
    {
        $areasAlmacen = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        return view('inventario.insumos_area.reportes', compact('areasAlmacen'));
    }

    /**
     * AJAX: devuelve el listado filtrado de insumos por área y nivel de stock.
     */
    public function obtenerReporteDatos(Request $request)
    {
        $idArea   = $request->get('id_area_almacen', 0);
        $niveles  = $request->get('niveles', []); // array: ['muy_bajo','bajo','regular','suficiente','excedido']

        if (!$idArea) {
            return response()->json(['ok' => false, 'mensaje' => 'Área no especificada.']);
        }

        $query = InsumoArea::with(['insumo', 'areaAlmacen'])
            ->whereHas('insumo', fn($q) => $q->where('activo', 1))
            ->where('id_area_almacen', $idArea);

        // Filtrado por niveles de porcentaje (stock*100/fondo_fijo)
        if (!empty($niveles)) {
            $query->where(function ($q) use ($niveles) {
                foreach ($niveles as $nivel) {
                    match ($nivel) {
                        'muy_bajo'   => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) BETWEEN 0 AND 24'),
                        'bajo'       => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) BETWEEN 25 AND 49'),
                        'regular'    => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) BETWEEN 50 AND 74'),
                        'suficiente' => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) BETWEEN 75 AND 100'),
                        'excedido'   => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) > 100'),
                        default      => null,
                    };
                }
            });
        }

        $insumos = $query->get()->map(function ($ia) {
            $stock      = (int) $ia->stock;
            $ff         = (int) $ia->fondo_fijo;
            $porcentaje = $ff > 0 ? round(($stock * 100) / $ff, 1) : 0;

            return [
                'id_insumo_area' => $ia->id_insumo_area,
                'clave'          => $ia->insumo->clave ?? '—',
                'descripcion'    => $ia->insumo->descripcion ?? '—',
                'tipo'           => $ia->insumo->tipo ?? '—',
                'area'           => $ia->areaAlmacen->nombre ?? '—',
                'stock'          => $stock,
                'fondo_fijo'     => $ff,
                'porcentaje'     => $porcentaje,
            ];
        });

        $totalStock = $insumos->sum('stock');

        return response()->json([
            'ok'          => true,
            'insumos'     => $insumos,
            'total_stock' => $totalStock,
        ]);
    }

    /**
     * Vista de impresión del reporte de insumos por área filtrado por niveles.
     */
    public function imprimir(Request $request)
    {
        $idArea  = $request->get('id_area_almacen', 0);
        $niveles = $request->get('niveles', []);

        $query = InsumoArea::with(['insumo', 'areaAlmacen'])
            ->whereHas('insumo', fn($q) => $q->where('activo', 1));

        if ($idArea) {
            $query->where('id_area_almacen', $idArea);
        }

        if (!empty($niveles)) {
            $query->where(function ($q) use ($niveles) {
                foreach ($niveles as $nivel) {
                    match ($nivel) {
                        'muy_bajo'   => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) BETWEEN 0 AND 24'),
                        'bajo'       => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) BETWEEN 25 AND 49'),
                        'regular'    => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) BETWEEN 50 AND 74'),
                        'suficiente' => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) BETWEEN 75 AND 100'),
                        'excedido'   => $q->orWhereRaw('(CAST(stock AS DECIMAL(10,2)) * 100 / fondo_fijo) > 100'),
                        default      => null,
                    };
                }
            });
        }

        $insumos = $query->orderBy('id_insumo_area')->get();

        $area = $idArea ? AreaAlmacen::find($idArea) : null;

        return view('inventario.insumos_area.reporte_impresion', compact('insumos', 'area', 'niveles'));
    }
}
