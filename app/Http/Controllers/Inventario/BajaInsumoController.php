<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\BajaInsumo;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\AreaAlmacen;
use App\Traits\ParseaRangoFechas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BajaInsumoController extends Controller
{
    use ParseaRangoFechas;

    /**
     * Muestra el listado principal de bajas con paginación, filtros de búsqueda y rango de fechas.
     */
    public function index(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');
        $perPage   = 10;

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        if ($errorMsg) {
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMsg);
        }

        if ($fechaInitDb) {
            $fechaInit = $fechaInitDb;
        }
        if ($fechaFinDb) {
            $fechaFin = $fechaFinDb;
        }

        $query = BajaInsumo::with(['insumo', 'areaAlmacen'])
            ->orderBy('id_baja_insumo', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('motivo', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('insumo', function ($q2) use ($buscar) {
                      $q2->where('descripcion', 'LIKE', "%{$buscar}%")
                         ->orWhere('clave', 'LIKE', "%{$buscar}%");
                  })
                  ->orWhereHas('areaAlmacen', function ($q3) use ($buscar) {
                      $q3->where('nombre', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        if ($fechaInitDb) {
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        if ($fechaFinDb) {
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        if ($request->ajax()) {
            $sugerencias = [];
            $records = $query->limit(10)->get();

            foreach ($records as $b) {
                if ($b->insumo) {
                    $sugerencias[] = [
                        'text'   => $b->insumo->descripcion,
                        'type'   => 'Insumo',
                        'detail' => $b->insumo->clave
                    ];
                }
                if ($b->areaAlmacen) {
                    $sugerencias[] = [
                        'text'   => $b->areaAlmacen->nombre,
                        'type'   => 'Área',
                        'detail' => ''
                    ];
                }
                if (!empty($b->motivo)) {
                    $sugerencias[] = [
                        'text'   => $b->motivo,
                        'type'   => 'Motivo',
                        'detail' => ''
                    ];
                }
            }

            $uniqueSugerencias = [];
            $seen = [];

            foreach ($sugerencias as $sug) {
                $key = strtolower($sug['text']);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $uniqueSugerencias[] = $sug;
                }
            }

            return response()->json(array_values($uniqueSugerencias));
        }

        $bajas = $query->paginate($perPage)->withQueryString();

        $areas = AreaAlmacen::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view(
            'inventario.bajas_insumos.index',
            compact('bajas', 'areas', 'buscar', 'fechaInit', 'fechaFin')
        );
    }

    /**
     * Busca insumos por clave o descripción para el autocompletado (AJAX).
     */
    public function buscarInsumos(Request $request)
    {
        $termino = $request->get('q', '');
        $idArea  = $request->get('id_area_almacen');
        $all     = $request->boolean('all', false);

        if ($all && !$idArea) {
            return response()->json([]);
        }

        if (!$all && strlen($termino) < 2) {
            return response()->json([]);
        }

        $query = Insumo::where('activo', 1);

        if (strlen($termino) >= 1) {
            $query->where(function ($q) use ($termino) {
                $q->where('descripcion', 'LIKE', "%{$termino}%")
                  ->orWhere('clave', 'LIKE', "%{$termino}%");
            });
        }

        if ($idArea) {
            $query->whereHas('insumosArea', function ($q) use ($idArea) {
                $q->where('id_area_almacen', $idArea)
                  ->whereRaw('CAST(stock AS UNSIGNED) >= 1');
            });
        }

        $insumos = $query->select('id_insumo', 'clave', 'descripcion', 'tipo')
            ->orderBy('clave')
            ->when(!$all, fn($q) => $q->limit(20))
            ->get();

        $resultado = $insumos->map(function ($insumo) use ($idArea) {
            $data = [
                'id_insumo'   => $insumo->id_insumo,
                'clave'       => $insumo->clave,
                'descripcion' => $insumo->descripcion,
                'tipo'        => $insumo->tipo,
            ];

            if ($idArea) {
                $insumoArea = InsumoArea::where('id_insumo', $insumo->id_insumo)
                    ->where('id_area_almacen', $idArea)
                    ->first();

                $data['stock'] = $insumoArea ? (int) $insumoArea->stock : 0;
            }

            return $data;
        });

        return response()->json($resultado);
    }

    /**
     * Consulta el stock disponible de un insumo específico en un área de almacén.
     */
    public function consultarStock(Request $request)
    {
        $idInsumo = $request->get('id_insumo');
        $idArea   = $request->get('id_area_almacen');

        if (!$idInsumo || !$idArea) {
            return response()->json([
                'stock' => 0,
                'error' => 'Parámetros incompletos'
            ]);
        }

        $insumoArea = InsumoArea::where('id_insumo', $idInsumo)
            ->where('id_area_almacen', $idArea)
            ->first();

        $stock = $insumoArea ? (int) $insumoArea->stock : 0;

        return response()->json([
            'stock' => $stock
        ]);
    }

    /**
     * Registra una nueva baja de insumo y descuenta la cantidad correspondiente del inventario.
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

        $insumoArea = InsumoArea::where('id_insumo', $request->id_insumo)
            ->where('id_area_almacen', $request->id_area_almacen)
            ->first();

        if (!$insumoArea) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'cantidad' => 'El insumo no tiene existencia en el área seleccionada.'
                ]);
        }

        $stockActual = (int) $insumoArea->stock;

        if ($request->cantidad > $stockActual) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'cantidad' => "La cantidad excede el stock disponible ({$stockActual} piezas)."
                ]);
        }

        DB::transaction(function () use ($request, $insumoArea) {
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

            $this->ajustarStockInsumoArea($insumoArea, (int) $request->cantidad, 'restar');
        });

        return redirect()
            ->route('bajas_insumos.index')
            ->with('exitog', 'La baja de insumo se ha registrado correctamente.');
    }

    /**
     * Cancela o reactiva una baja de insumo existente, restaurando o descontando el stock correspondiente.
     */
    public function toggleStatus($id)
    {
        $baja = BajaInsumo::findOrFail($id);

        if ($baja->cancelado === 'No') {
            DB::transaction(function () use ($baja) {
                $insumoArea = InsumoArea::where('id_insumo', $baja->id_insumo)
                    ->where('id_area_almacen', $baja->id_area_almacen)
                    ->first();

                if ($insumoArea) {
                    $this->ajustarStockInsumoArea($insumoArea, (int) $baja->cantidad, 'sumar');
                }

                $baja->update([
                    'cancelado' => 'Si'
                ]);
            });

            return redirect()
                ->route('bajas_insumos.index')
                ->with(
                    'exito',
                    'La baja de insumo ha sido cancelada y el stock restaurado.'
                );
        } else {
            $insumoArea = InsumoArea::where('id_insumo', $baja->id_insumo)
                ->where('id_area_almacen', $baja->id_area_almacen)
                ->first();

            if (!$insumoArea) {
                return redirect()
                    ->route('bajas_insumos.index')
                    ->with(
                        'error',
                        'El insumo no tiene registro de stock en la misma área.'
                    );
            }

            $stockActual = (int) $insumoArea->stock;

            if ($baja->cantidad > $stockActual) {
                return redirect()
                    ->route('bajas_insumos.index')
                    ->with(
                        'error',
                        "No se puede reactivar la baja. El stock disponible ({$stockActual} piezas) es insuficiente para dar de baja {$baja->cantidad} piezas."
                    );
            }

            DB::transaction(function () use ($baja, $insumoArea) {
                $this->ajustarStockInsumoArea($insumoArea, (int) $baja->cantidad, 'restar');

                $baja->update([
                    'cancelado' => 'No'
                ]);
            });

            return redirect()
                ->route('bajas_insumos.index')
                ->with(
                    'exito',
                    'La baja de insumo ha sido reactivada y el stock descontado.'
                );
        }
    }

    /**
     * Genera la vista imprimible del reporte de bajas respetando los filtros de búsqueda y fechas.
     */
    public function imprimir(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        if ($errorMsg) {
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMsg);
        }

        if ($fechaInitDb) {
            $fechaInit = $fechaInitDb;
        }
        if ($fechaFinDb) {
            $fechaFin = $fechaFinDb;
        }

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

        if ($fechaInitDb) {
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        if ($fechaFinDb) {
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        $bajas = $query->limit(500)->get();

        return view(
            'inventario.bajas_insumos.reporte_impresion',
            compact('bajas', 'buscar', 'fechaInit', 'fechaFin')
        );
    }

    /**
     * Incremente o decremente la existencia de stock en un InsumoArea.
     *
     * @param InsumoArea $insumoArea
     * @param int $cantidad
     * @param string $operacion 'restar' | 'sumar'
     * @return void
     */
    private function ajustarStockInsumoArea(InsumoArea $insumoArea, int $cantidad, string $operacion = 'restar')
    {
        $stockActual = (int) $insumoArea->stock;
        $nuevoStock  = ($operacion === 'sumar') ? ($stockActual + $cantidad) : ($stockActual - $cantidad);

        $insumoArea->update([
            'stock' => (string) max(0, $nuevoStock)
        ]);
    }
}