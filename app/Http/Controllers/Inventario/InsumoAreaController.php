<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\AreaAlmacen;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\BuscaInsumosAjax;

class InsumoAreaController extends Controller
{
    use BuscaInsumosAjax;
    /**
     * Muestra el listado de insumos por área con el formulario de asignación.
     */
    public function index(Request $request)
    {
        $buscar          = $request->get('buscar', '');
        $filtroArea       = $request->get('id_area_abastecimiento', $request->get('id_area_almacen', ''));
        $filtroCategoria  = $request->get('id_categoria', '');

        $query = InsumoArea::with(['insumo.areaAbastecimiento', 'insumo.categoria', 'areaAlmacen'])
            ->whereHas('insumo', fn($q) => $q->where('activo', 1))
            ->orderBy('id_insumo_area', 'desc');

        if (!empty($filtroArea)) {
            $query->where(function ($q) use ($filtroArea) {
                $q->whereHas('insumo', fn($q2) => $q2->where('id_area_abastecimiento', $filtroArea))
                  ->orWhere('id_area_almacen', $filtroArea);
            });
        }

        if (!empty($filtroCategoria)) {
            $query->whereHas('insumo', function ($q) use ($filtroCategoria) {
                $q->where('id_categoria', $filtroCategoria);
            });
        }

        if (!empty($buscar)) {
            $query->whereHas('insumo', function ($q) use ($buscar) {
                $q->where('clave', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $insumosArea         = $query->paginate(15)->withQueryString();
        $areasAlmacen        = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        $areasAbastecimiento = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();
        $categorias          = Categoria::orderBy('nombre_categoria')->get();

        return view('inventario.insumos_area.index', compact(
            'insumosArea', 'areasAlmacen', 'areasAbastecimiento', 'categorias', 'buscar', 'filtroArea', 'filtroCategoria'
        ));
    }

    /**
     * Guarda una nueva asignación de insumo a área.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_insumo'              => 'required|integer',
            'id_area_almacen'        => 'required|integer',
            'id_area_abastecimiento' => 'nullable|integer',
            'fondo_fijo'             => 'required|integer|min:1',
            'stock'                  => 'required|integer|min:0',
        ], [
            'id_insumo.required'       => 'Debe seleccionar un insumo.',
            'id_area_almacen.required' => 'Debe seleccionar un área de almacén.',
            'fondo_fijo.required'      => 'El fondo fijo es obligatorio.',
            'fondo_fijo.min'           => 'El fondo fijo debe ser mayor a cero.',
            'stock.required'           => 'El stock es obligatorio.',
            'stock.min'                => 'El stock no puede ser negativo.',
        ]);

        // Verificar duplicado: el mismo insumo puede asignarse a la misma área de almacén si cambia el Área Asignada (abastecimiento)
        $queryExiste = InsumoArea::where('id_insumo', $request->id_insumo)
            ->where('id_area_almacen', $request->id_area_almacen);

        if ($request->filled('id_area_abastecimiento')) {
            $queryExiste->whereHas('insumo', function ($q) use ($request) {
                $q->where('id_area_abastecimiento', $request->id_area_abastecimiento);
            });
        }

        if ($queryExiste->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['id_insumo' => 'Este insumo ya se encuentra asignado al área seleccionada.']);
        }

        if ($request->filled('id_area_abastecimiento')) {
            Insumo::where('id_insumo', $request->id_insumo)->update([
                'id_area_abastecimiento' => $request->id_area_abastecimiento
            ]);
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
            'id_area_almacen'        => 'required|integer',
            'id_area_abastecimiento' => 'nullable|integer',
            'stock'                  => 'required|integer|min:0',
            'fondo_fijo'             => 'required|integer|min:1',
        ], [
            'id_area_almacen.required' => 'Debe seleccionar un área de almacén.',
            'stock.required'           => 'El stock es obligatorio.',
            'stock.min'                => 'El stock no puede ser negativo.',
            'fondo_fijo.required'      => 'El fondo fijo es obligatorio.',
            'fondo_fijo.min'           => 'El fondo fijo debe ser mayor a cero.',
        ]);

        $insumoArea = InsumoArea::findOrFail($id);

        // Verificar que el nuevo área no genere un duplicado
        $queryExiste = InsumoArea::where('id_insumo', $insumoArea->id_insumo)
            ->where('id_area_almacen', $request->id_area_almacen)
            ->where('id_insumo_area', '!=', $id);

        if ($request->filled('id_area_abastecimiento')) {
            $queryExiste->whereHas('insumo', function ($q) use ($request) {
                $q->where('id_area_abastecimiento', $request->id_area_abastecimiento);
            });
        }

        if ($queryExiste->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['id_area_almacen' => 'Este insumo ya está asignado al área seleccionada.']);
        }

        if ($request->filled('id_area_abastecimiento')) {
            Insumo::where('id_insumo', $insumoArea->id_insumo)->update([
                'id_area_abastecimiento' => $request->id_area_abastecimiento
            ]);
        }

        $insumoArea->update([
            'id_area_almacen' => $request->id_area_almacen,
            'stock'           => $request->stock,
            'fondo_fijo'      => $request->fondo_fijo,
        ]);

        return redirect()
            ->route('insumos_area.index')
            ->with('exitog', 'La asignación se ha actualizado correctamente.');
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
        return $this->buscarInsumos($request);
    }



    /**
     * Vista del panel de reportes con filtros por nivel de stock.
     */
    public function reportes(Request $request)
    {
        $areasAlmacen = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();
        $areasAbastecimiento = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();

        return view('inventario.insumos_area.reportes', compact('areasAlmacen', 'areasAbastecimiento'));
    }

    /**
     * AJAX: devuelve el listado filtrado de insumos por área y nivel de stock.
     */
    public function obtenerReporteDatos(Request $request)
    {
        $areaId  = $request->get('area_id', $request->get('id_area_abastecimiento', $request->get('id_area_almacen', 0)));
        $niveles = $request->get('niveles', []); // array: ['muy_bajo','bajo','regular','suficiente','excedido']

        if (!$areaId) {
            return response()->json(['ok' => false, 'mensaje' => 'Área no especificada.']);
        }

        $query = InsumoArea::with(['insumo.areaAbastecimiento', 'areaAlmacen'])
            ->whereHas('insumo', fn($q) => $q->where('activo', 1));

        // Buscar insumos que coincidan por área de almacén o por área de abastecimiento/asignación
        $query->where(function ($q) use ($areaId) {
            $q->where('id_area_almacen', $areaId)
              ->orWhereHas('insumo', function ($q2) use ($areaId) {
                  $q2->where('id_area_abastecimiento', $areaId);
              });
        });

        // Filtrado por niveles de porcentaje (stock*100/fondo_fijo)
        $query->conNivelStock($niveles);

        $insumos = $query->get()->map(function ($ia) {
            $stock      = $ia->stock;
            $ff         = $ia->fondo_fijo;
            $porcentaje = $ff > 0 ? round(($stock * 100) / $ff, 1) : 0;
            $nombreArea = $ia->insumo->areaAbastecimiento->nombre ?? $ia->areaAlmacen->nombre ?? '—';

            return [
                'id_insumo_area' => $ia->id_insumo_area,
                'clave'          => $ia->insumo->clave ?? '—',
                'descripcion'    => $ia->insumo->descripcion ?? '—',
                'tipo'           => $ia->insumo->tipo ?? '—',
                'area'           => $nombreArea,
                'stock'          => $stock,
                'fondo_fijo'     => $ff,
                'porcentaje'     => $porcentaje,
                'nivel'          => $ia->nivel_stock,
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
        $areaId  = $request->get('area_id', $request->get('id_area_almacen', 0));
        $niveles = $request->get('niveles', []);

        $query = InsumoArea::with(['insumo.areaAbastecimiento', 'areaAlmacen'])
            ->whereHas('insumo', fn($q) => $q->where('activo', 1));

        if ($areaId) {
            $query->where(function ($q) use ($areaId) {
                $q->where('id_area_almacen', $areaId)
                  ->orWhereHas('insumo', function ($q2) use ($areaId) {
                      $q2->where('id_area_abastecimiento', $areaId);
                  });
            });
        }

        $query->conNivelStock($niveles);

        $insumos = $query->orderBy('id_insumo_area')->get();

        // Resolver nombre del área (puede ser de almacén o de abastecimiento)
        $area = null;
        if ($areaId) {
            $area = AreaAlmacen::find($areaId) ?? AreaAbastecimiento::find($areaId);
        }

        return view('inventario.insumos_area.reporte_impresion', compact('insumos', 'area', 'niveles'));
    }
}
