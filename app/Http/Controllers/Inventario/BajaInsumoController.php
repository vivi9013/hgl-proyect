<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Inventario\BajaInsumo;
use App\Models\Inventario\Insumo;
use App\Models\Inventario\InsumoArea;
use App\Models\Inventario\AreaAlmacen;
use App\Models\Inventario\AreaSurtimiento;
use App\Models\Inventario\AreaAbastecimiento;
use App\Models\Inventario\Motivo;
use App\Traits\ParseaRangoFechas;
use App\Traits\AjustaStockInsumoArea;
use App\Traits\BuscaInsumosAjax;
use App\Traits\ConsultaStockInsumoArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BajaInsumoController extends Controller
{
    use ParseaRangoFechas, AjustaStockInsumoArea, BuscaInsumosAjax, ConsultaStockInsumoArea;

    /**
     * Muestra el listado principal de bajas con paginación, filtros de búsqueda y rango de fechas.
     */
    public function index(Request $request)
    {
        $buscar     = $request->get('buscar', '');
        $fechaInit  = $request->get('fecha_inicio', '');
        $fechaFin   = $request->get('fecha_fin', '');
        $filtroArea = $request->get('id_area_abastecimiento', $request->get('id_area_almacen', ''));
        $perPage    = 10;

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

        $query = BajaInsumo::with(['insumo.areaAbastecimiento', 'insumo.areaSurtimiento', 'areaAlmacen'])
            ->orderBy('id_baja_insumo', 'desc');

        if (!empty($filtroArea)) {
            $query->where(function ($q) use ($filtroArea) {
                $q->whereHas('insumo', function ($q2) use ($filtroArea) {
                    $q2->where('id_area_abastecimiento', $filtroArea)
                       ->orWhere('id_area_surtimiento', $filtroArea);
                })
                ->orWhere('id_area_almacen', $filtroArea);
            });
        }

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('motivo', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('insumo', function ($q2) use ($buscar) {
                      $q2->where('descripcion', 'LIKE', "%{$buscar}%")
                         ->orWhere('clave', 'LIKE', "%{$buscar}%")
                         ->orWhereHas('areaAbastecimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
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

        $areasSurtimiento = AreaSurtimiento::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $areasAbastecimiento = AreaAbastecimiento::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        $motivos = Motivo::where('activo', 1)
            ->orderBy('descripcion')
            ->get();

        return view(
            'inventario.bajas_insumos.index',
            compact('bajas', 'areas', 'areasSurtimiento', 'areasAbastecimiento', 'motivos', 'buscar', 'fechaInit', 'fechaFin', 'filtroArea')
        );
    }





    /**
     * Registra una nueva baja de insumo y descuenta la cantidad correspondiente del inventario.
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'id_insumo'              => 'required|integer|exists:insumos,id_insumo',
            'id_area_almacen'        => 'required|integer|exists:areas_almacen,id_area_almacen',
            'id_area_abastecimiento' => 'nullable|integer|exists:areasabastecimiento,id_area_abastecimiento',
            'motivo'                 => 'required|string|max:500',
            'doctor_nombre'          => 'nullable|string|max:200',
            'doctor_especialidad'    => 'nullable|string|max:200',
            'cantidad'               => 'required|integer|min:1',
        ], [
            'id_insumo.required'              => 'Debe seleccionar un insumo.',
            'id_insumo.exists'                => 'El insumo seleccionado no existe.',
            'id_area_almacen.required'        => 'Debe seleccionar un área de almacén.',
            'id_area_almacen.exists'          => 'El área seleccionada no existe.',
            'id_area_abastecimiento.exists'   => 'El área de asignación seleccionada no existe.',
            'motivo.required'                 => 'El motivo de la baja es obligatorio.',
            'motivo.max'                      => 'El motivo no puede superar los 500 caracteres.',
            'doctor_nombre.max'               => 'El nombre del doctor no puede superar 200 caracteres.',
            'doctor_especialidad.max'         => 'La especialidad del doctor no puede superar 200 caracteres.',
            'cantidad.required'               => 'La cantidad es obligatoria.',
            'cantidad.min'                    => 'La cantidad debe ser al menos 1.',
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
            // Si el usuario cambió el área de asignación, actualizarla en el insumo
            if ($request->filled('id_area_abastecimiento')) {
                Insumo::where('id_insumo', $request->id_insumo)->update([
                    'id_area_abastecimiento' => $request->id_area_abastecimiento,
                ]);
            }

            BajaInsumo::create([
                'id_insumo'           => $request->id_insumo,
                'id_area_almacen'     => $request->id_area_almacen,
                'motivo'              => trim($request->motivo),
                'doctor_nombre'       => $request->filled('doctor_nombre') ? trim($request->doctor_nombre) : null,
                'doctor_especialidad' => $request->filled('doctor_especialidad') ? trim($request->doctor_especialidad) : null,
                'cantidad'            => $request->cantidad,
                'fecha_baja'          => now()->toDateString(),
                'hora_baja'           => now()->toTimeString(),
                'id_usuario'          => Auth::id() ?? 1,
                'cancelado'           => 'No',
            ]);

            $this->ajustarStockInsumoArea($insumoArea, (int) $request->cantidad, 'restar');
        });

        return redirect()
            ->route('bajas_insumos.index')
            ->with('exitog', 'La baja de insumo se ha registrado correctamente.');
    }

    /**
     * Obtiene el historial de bajas filtrado por el área asignada al insumo.
     */
    public function historialPorAreaAsignada(Request $request)
    {
        $idAreaAbastecimiento = $request->get('id_area_abastecimiento', $request->get('id_area_surtimiento', ''));
        $fechaInit            = $request->get('fecha_inicio', '');
        $fechaFin             = $request->get('fecha_fin', '');

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        if ($errorMsg && !$request->ajax()) {
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        $query = BajaInsumo::with(['insumo.areaAbastecimiento', 'insumo.areaSurtimiento', 'areaAlmacen'])
            ->orderBy('id_baja_insumo', 'desc');

        if (!empty($idAreaAbastecimiento)) {
            $query->whereHas('insumo', function ($q) use ($idAreaAbastecimiento) {
                $q->where('id_area_abastecimiento', $idAreaAbastecimiento)
                  ->orWhere('id_area_surtimiento', $idAreaAbastecimiento);
            });
        }

        if ($fechaInitDb) {
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        if ($fechaFinDb) {
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        $bajas = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            $html = view('inventario.bajas_insumos.partials.tabla_historial_area', compact('bajas'))->render();
            return response()->json(['html' => $html, 'total' => $bajas->total()]);
        }

        $areasAbastecimiento = AreaAbastecimiento::where('activo', 1)->orderBy('nombre')->get();

        return view('inventario.bajas_insumos.historial_area', compact('bajas', 'areasAbastecimiento', 'idAreaAbastecimiento', 'fechaInit', 'fechaFin'));
    }

    /**
     * Exporta el reporte de bajas de insumos por área asignada / fechas a Excel.
     */
    public function exportarExcel(Request $request)
    {
        $request->validate([
            'id_area_abastecimiento' => 'nullable|integer|exists:areasabastecimiento,id_area_abastecimiento',
            'id_area_surtimiento'    => 'nullable|integer',
            'fecha_inicio'           => 'nullable|date',
            'fecha_fin'              => 'nullable|date',
        ]);

        $idAreaAbastecimiento = $request->get('id_area_abastecimiento', $request->get('id_area_surtimiento', ''));
        $fechaInit            = $request->get('fecha_inicio', '');
        $fechaFin             = $request->get('fecha_fin', '');

        [$fechaInitDb, $fechaFinDb, $errorMsg] = $this->parsearRangoFechas($fechaInit, $fechaFin);

        $query = BajaInsumo::with(['insumo.areaAbastecimiento', 'insumo.areaSurtimiento', 'areaAlmacen'])
            ->orderBy('fecha_baja', 'desc')
            ->orderBy('hora_baja', 'desc');

        if (!empty($idAreaAbastecimiento)) {
            $query->whereHas('insumo', function ($q) use ($idAreaAbastecimiento) {
                $q->where('id_area_abastecimiento', $idAreaAbastecimiento)
                  ->orWhere('id_area_surtimiento', $idAreaAbastecimiento);
            });
        }

        if ($fechaInitDb) {
            $query->whereDate('fecha_baja', '>=', $fechaInitDb);
        }

        if ($fechaFinDb) {
            $query->whereDate('fecha_baja', '<=', $fechaFinDb);
        }

        $bajas = $query->get();

        $areaSeleccionada = !empty($idAreaAbastecimiento) ? AreaAbastecimiento::find($idAreaAbastecimiento) : null;

        // Agrupar las bajas por la área asignada al insumo (areaAbastecimiento)
        $bajasPorArea = $bajas->groupBy(function ($baja) {
            return $baja->insumo->areaAbastecimiento->nombre 
                ?? $baja->insumo->areaSurtimiento->nombre 
                ?? 'Sin Área Asignada';
        });

        $filename = 'Reporte_Bajas_Por_Area_Asignada_' . date('Y-m-d_H-i-s') . '.xls';

        return response()->streamDownload(function () use ($bajasPorArea, $areaSeleccionada, $fechaInit, $fechaFin) {
            echo view('inventario.bajas_insumos.exportar_excel', compact('bajasPorArea', 'areaSeleccionada', 'fechaInit', 'fechaFin'))->render();
        }, $filename, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
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
        $buscar     = $request->get('buscar', '');
        $fechaInit  = $request->get('fecha_inicio', '');
        $fechaFin   = $request->get('fecha_fin', '');
        $filtroArea = $request->get('id_area_abastecimiento', $request->get('id_area_almacen', ''));

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

        $query = BajaInsumo::with(['insumo.areaAbastecimiento', 'insumo.areaSurtimiento', 'areaAlmacen'])
            ->orderBy('fecha_baja', 'desc')
            ->orderBy('hora_baja', 'desc');

        if (!empty($filtroArea)) {
            $query->where(function ($q) use ($filtroArea) {
                $q->whereHas('insumo', function ($q2) use ($filtroArea) {
                    $q2->where('id_area_abastecimiento', $filtroArea)
                       ->orWhere('id_area_surtimiento', $filtroArea);
                })
                ->orWhere('id_area_almacen', $filtroArea);
            });
        }

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('motivo', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('insumo', function ($q2) use ($buscar) {
                      $q2->where('descripcion', 'LIKE', "%{$buscar}%")
                         ->orWhere('clave', 'LIKE', "%{$buscar}%")
                         ->orWhereHas('areaAbastecimiento', fn($q3) => $q3->where('nombre', 'LIKE', "%{$buscar}%"));
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

        $bajas = $query->limit(500)->get();

        $areaFiltrada = !empty($filtroArea) ? AreaAbastecimiento::find($filtroArea) : null;

        return view(
            'inventario.bajas_insumos.reporte_impresion',
            compact('bajas', 'buscar', 'fechaInit', 'fechaFin', 'areaFiltrada')
        );
    }


}