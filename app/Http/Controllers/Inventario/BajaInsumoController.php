<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\BajaInsumo;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\AreaAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BajaInsumoController extends Controller
{
    /**
     * Muestra el listado paginado de bajas de insumos.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar', '');
        $perPage = 10;

        $query = BajaInsumo::with(['insumo', 'areaAlmacen'])
            ->orderBy('id_baja_insumo', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                // Buscar por motivo
                $q->where('motivo', 'LIKE', "%{$buscar}%")
                  // Buscar por descripción del insumo (join)
                  ->orWhereHas('insumo', function ($q2) use ($buscar) {
                      $q2->where('descripcion', 'LIKE', "%{$buscar}%")
                         ->orWhere('clave', 'LIKE', "%{$buscar}%");
                  })
                  // Buscar por nombre del área de almacén
                  ->orWhereHas('areaAlmacen', function ($q3) use ($buscar) {
                      $q3->where('nombre', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        $bajas = $query->paginate($perPage)->withQueryString();

        // Áreas de almacén activas para el formulario de alta
        $areas = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();

        return view('inventario.bajas_insumos.index', compact('bajas', 'areas', 'buscar'));
    }

    /**
     * Busca insumos por clave o descripción para el autocompletado (AJAX).
     */
    public function buscarInsumos(Request $request)
    {
        $termino = $request->get('q', '');
        $idArea  = $request->get('id_area_almacen');

        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $query = Insumo::where('activo', 1)
            ->where(function ($q) use ($termino) {
                $q->where('descripcion', 'LIKE', "%{$termino}%")
                  ->orWhere('clave', 'LIKE', "%{$termino}%");
            });

        // Si se especifica un área, filtrar solo insumos con stock en esa área
        if ($idArea) {
            $query->whereHas('insumosArea', function ($q) use ($idArea) {
                $q->where('id_area_almacen', $idArea)
                  ->whereRaw('CAST(stock AS UNSIGNED) > 0');
            });
        }

        $insumos = $query->select('id_insumo', 'clave', 'descripcion', 'tipo')
            ->orderBy('descripcion')
            ->limit(20)
            ->get();

        // Agregar stock del área si se indicó
        $resultado = $insumos->map(function ($insumo) use ($idArea) {
            $stock = 0;
            if ($idArea) {
                $insumoArea = InsumoArea::where('id_insumo', $insumo->id_insumo)
                    ->where('id_area_almacen', $idArea)
                    ->first();
                $stock = $insumoArea ? (int) $insumoArea->stock : 0;
            }
            return [
                'id_insumo'   => $insumo->id_insumo,
                'clave'       => $insumo->clave,
                'descripcion' => $insumo->descripcion,
                'tipo'        => $insumo->tipo,
                'stock'       => $stock,
            ];
        });

        return response()->json($resultado);
    }

    /**
     * Consulta el stock de un insumo en un área específica (AJAX).
     */
    public function consultarStock(Request $request)
    {
        $idInsumo = $request->get('id_insumo');
        $idArea   = $request->get('id_area_almacen');

        if (!$idInsumo || !$idArea) {
            return response()->json(['stock' => 0, 'error' => 'Parámetros incompletos']);
        }

        $insumoArea = InsumoArea::where('id_insumo', $idInsumo)
            ->where('id_area_almacen', $idArea)
            ->first();

        $stock = $insumoArea ? (int) $insumoArea->stock : 0;

        return response()->json(['stock' => $stock]);
    }

    /**
     * Guarda una nueva baja de insumo.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_insumo'       => 'required|integer|exists:insumos,id_insumo',
            'id_area_almacen' => 'required|integer|exists:areas_almacen,id_area_almacen',
            'motivo'          => 'required|string|max:500',
            'cantidad'        => 'required|integer|min:1',
        ], [
            'id_insumo.required'       => 'Debe seleccionar un insumo.',
            'id_insumo.exists'         => 'El insumo seleccionado no existe.',
            'id_area_almacen.required' => 'Debe seleccionar un área de almacén.',
            'id_area_almacen.exists'   => 'El área seleccionada no existe.',
            'motivo.required'          => 'El motivo de la baja es obligatorio.',
            'motivo.max'               => 'El motivo no puede superar los 500 caracteres.',
            'cantidad.required'        => 'La cantidad es obligatoria.',
            'cantidad.min'             => 'La cantidad debe ser al menos 1.',
        ]);

        // Verificar stock disponible
        $insumoArea = InsumoArea::where('id_insumo', $request->id_insumo)
            ->where('id_area_almacen', $request->id_area_almacen)
            ->first();

        if (!$insumoArea) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cantidad' => 'El insumo no tiene existencia en el área seleccionada.']);
        }

        $stockActual = (int) $insumoArea->stock;

        if ($request->cantidad > $stockActual) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cantidad' => "La cantidad excede el stock disponible ({$stockActual} piezas)."]);
        }

        DB::transaction(function () use ($request, $insumoArea) {
            // Registrar la baja
            BajaInsumo::create([
                'id_insumo'       => $request->id_insumo,
                'id_area_almacen' => $request->id_area_almacen,
                'motivo'          => trim($request->motivo),
                'cantidad'        => $request->cantidad,
                'fecha_baja'      => now()->toDateString(),
                'hora_baja'       => now()->toTimeString(),
                'id_usuario'      => Auth::id() ?? 1,
                'cancelado'       => 'No',
            ]);

            // Descontar del stock en insumosarea
            $nuevoStock = (int) $insumoArea->stock - (int) $request->cantidad;
            $insumoArea->update(['stock' => (string) $nuevoStock]);
        });

        return redirect()
            ->route('bajas_insumos.index')
            ->with('exitog', 'La baja de insumo se ha registrado correctamente.');
    }

    /**
     * Cancela una baja de insumo y restaura el stock.
     */
    public function cancelar($id)
    {
        $baja = BajaInsumo::findOrFail($id);

        if ($baja->cancelado === 'Si') {
            return redirect()
                ->route('bajas_insumos.index')
                ->with('error', 'Esta baja ya fue cancelada anteriormente.');
        }

        DB::transaction(function () use ($baja) {
            // Restaurar stock en insumosarea
            $insumoArea = InsumoArea::where('id_insumo', $baja->id_insumo)
                ->where('id_area_almacen', $baja->id_area_almacen)
                ->first();

            if ($insumoArea) {
                $stockRestaurado = (int) $insumoArea->stock + (int) $baja->cantidad;
                $insumoArea->update(['stock' => (string) $stockRestaurado]);
            }

            // Marcar baja como cancelada
            $baja->update(['cancelado' => 'Si']);
        });

        return redirect()
            ->route('bajas_insumos.index')
            ->with('exito', 'La baja de insumo ha sido cancelada y el stock restaurado.');
    }

    /**
     * Genera el reporte de bajas en vista de impresión.
     */
    public function imprimir(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');

        $query = BajaInsumo::with(['insumo', 'areaAlmacen'])
            ->orderBy('fecha_baja', 'desc')
            ->orderBy('hora_baja', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('motivo', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('insumo', function ($q2) use ($buscar) {
                      $q2->where('descripcion', 'LIKE', "%{$buscar}%")
                         ->orWhere('clave', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        if (!empty($fechaInit)) {
            $query->whereDate('fecha_baja', '>=', $fechaInit);
        }

        if (!empty($fechaFin)) {
            $query->whereDate('fecha_baja', '<=', $fechaFin);
        }

        $bajas = $query->get();

        return view('inventario.bajas_insumos.reporte_impresion', compact('bajas', 'buscar', 'fechaInit', 'fechaFin'));
    }
}
