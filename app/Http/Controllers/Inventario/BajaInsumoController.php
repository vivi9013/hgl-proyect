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
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');
        $perPage   = 10;

        // Normalizar y validar fecha_inicio
        $fechaInitDb = null;
        if (!empty($fechaInit)) {
            try {
                if (strpos($fechaInit, '/') !== false) {
                    $fechaInitDb = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaInit)->format('Y-m-d');
                } else {
                    $fechaInitDb = \Carbon\Carbon::parse($fechaInit)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                try {
                    $fechaInitDb = \Carbon\Carbon::parse($fechaInit)->format('Y-m-d');
                } catch (\Exception $ex) {
                    $fechaInit = '';
                }
            }
        }

        // Normalizar y validar fecha_fin
        $fechaFinDb = null;
        if (!empty($fechaFin)) {
            try {
                if (strpos($fechaFin, '/') !== false) {
                    $fechaFinDb = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaFin)->format('Y-m-d');
                } else {
                    $fechaFinDb = \Carbon\Carbon::parse($fechaFin)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                try {
                    $fechaFinDb = \Carbon\Carbon::parse($fechaFin)->format('Y-m-d');
                } catch (\Exception $ex) {
                    $fechaFin = '';
                }
            }
        }

        // Validar coherencia del rango
        if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'La fecha de inicio no puede ser posterior a la fecha de fin.');
        }

        // Asignar los valores normalizados en formato Y-m-d para que se enlacen correctamente en el input date
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

        if ($fechaInitDb) {
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        if ($fechaFinDb) {
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        // AJAX: devolver sugerencias JSON para el autocomplete del buscador de bajas
        if ($request->ajax()) {
            $sugerencias = [];
            $records = $query->limit(10)->get();
            foreach ($records as $b) {
                if ($b->insumo) {
                    $sugerencias[] = [
                        'text' => $b->insumo->descripcion,
                        'type' => 'Insumo',
                        'detail' => $b->insumo->clave
                    ];
                }
                if ($b->areaAlmacen) {
                    $sugerencias[] = [
                        'text' => $b->areaAlmacen->nombre,
                        'type' => 'Área',
                        'detail' => ''
                    ];
                }
                if (!empty($b->motivo)) {
                    $sugerencias[] = [
                        'text' => $b->motivo,
                        'type' => 'Motivo',
                        'detail' => ''
                    ];
                }
            }
            // Eliminar duplicados
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

        // Áreas de almacén activas para el formulario de alta
        $areas = AreaAlmacen::where('activo', 1)->orderBy('nombre')->get();

        return view('inventario.bajas_insumos.index', compact('bajas', 'areas', 'buscar', 'fechaInit', 'fechaFin'));
    }

    /**
     * Busca insumos por clave o descripción para el autocompletado (AJAX).
     */
    public function buscarInsumos(Request $request)
    {
        $termino = $request->get('q', '');
        $idArea  = $request->get('id_area_almacen');
        $all     = $request->boolean('all', false); // panel de atajo (doble clic)

        // En modo panel (all=1) sin área seleccionada: no tiene sentido mostrar claves
        // porque no se puede saber el stock → devolver vacío y el JS mostrará el aviso
        if ($all && !$idArea) {
            return response()->json([]);
        }

        // Búsqueda normal: requerir mínimo 2 caracteres
        if (!$all && strlen($termino) < 2) {
            return response()->json([]);
        }

        $query = Insumo::where('activo', 1);

        // Filtrar por texto solo si se proporcionó un término
        if (strlen($termino) >= 1) {
            $query->where(function ($q) use ($termino) {
                $q->where('descripcion', 'LIKE', "%{$termino}%")
                  ->orWhere('clave', 'LIKE', "%{$termino}%");
            });
        }

        // Filtrar solo insumos con stock >= 1 en el área indicada
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

        // Agregar stock del área si se indicó; si no hay área, omitir la clave 'stock'
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
            // Sin área: no se incluye 'stock' → JS mostrará '—'

            return $data;
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
    /**
     * Alterna el estado de una baja de insumo (Cancela o Reactiva) y actualiza el stock.
     */
    public function toggleStatus($id)
    {
        $baja = BajaInsumo::findOrFail($id);

        if ($baja->cancelado === 'No') {
            // Cancelar la baja (marcar como Si) y restaurar el stock
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
        } else {
            // Reactivar la baja (marcar como No) y descontar el stock
            $insumoArea = InsumoArea::where('id_insumo', $baja->id_insumo)
                ->where('id_area_almacen', $baja->id_area_almacen)
                ->first();

            if (!$insumoArea) {
                return redirect()
                    ->route('bajas_insumos.index')
                    ->with('error', 'El insumo no tiene registro de stock en la misma área.');
            }

            $stockActual = (int) $insumoArea->stock;
            if ($baja->cantidad > $stockActual) {
                return redirect()
                    ->route('bajas_insumos.index')
                    ->with('error', "No se puede reactivar la baja. El stock disponible ({$stockActual} piezas) es insuficiente para dar de baja {$baja->cantidad} piezas.");
            }

            DB::transaction(function () use ($baja, $insumoArea) {
                // Descontar stock
                $nuevoStock = (int) $insumoArea->stock - (int) $baja->cantidad;
                $insumoArea->update(['stock' => (string) $nuevoStock]);

                // Marcar baja como activa
                $baja->update(['cancelado' => 'No']);
            });

            return redirect()
                ->route('bajas_insumos.index')
                ->with('exito', 'La baja de insumo ha sido reactivada y el stock descontado.');
        }
    }

    /**
     * Genera el reporte de bajas en vista de impresión.
     */
    public function imprimir(Request $request)
    {
        $buscar    = $request->get('buscar', '');
        $fechaInit = $request->get('fecha_inicio', '');
        $fechaFin  = $request->get('fecha_fin', '');

        // Normalizar y validar fecha_inicio
        $fechaInitDb = null;
        if (!empty($fechaInit)) {
            try {
                if (strpos($fechaInit, '/') !== false) {
                    $fechaInitDb = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaInit)->format('Y-m-d');
                } else {
                    $fechaInitDb = \Carbon\Carbon::parse($fechaInit)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                try {
                    $fechaInitDb = \Carbon\Carbon::parse($fechaInit)->format('Y-m-d');
                } catch (\Exception $ex) {
                    $fechaInit = '';
                }
            }
        }

        // Normalizar y validar fecha_fin
        $fechaFinDb = null;
        if (!empty($fechaFin)) {
            try {
                if (strpos($fechaFin, '/') !== false) {
                    $fechaFinDb = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaFin)->format('Y-m-d');
                } else {
                    $fechaFinDb = \Carbon\Carbon::parse($fechaFin)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                try {
                    $fechaFinDb = \Carbon\Carbon::parse($fechaFin)->format('Y-m-d');
                } catch (\Exception $ex) {
                    $fechaFin = '';
                }
            }
        }

        // Si hay incoherencia, las intercambiamos para no romper la impresión
        if ($fechaInitDb && $fechaFinDb && $fechaInitDb > $fechaFinDb) {
            $temp = $fechaInitDb;
            $fechaInitDb = $fechaFinDb;
            $fechaFinDb = $temp;
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

        // Limitar a 500 registros para prevenir agotamiento de memoria PHP con tablas masivas
        $bajas = $query->limit(500)->get();

        return view('inventario.bajas_insumos.reporte_impresion', compact('bajas', 'buscar', 'fechaInit', 'fechaFin'));
    }
}
