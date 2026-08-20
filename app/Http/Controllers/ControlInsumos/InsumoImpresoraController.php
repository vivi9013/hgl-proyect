<?php

namespace App\Http\Controllers\ControlInsumos;

use App\Exports\InsumosImpresorasExport;
use App\Http\Controllers\Controller;
use App\Models\ControlInsumos\InsumoImpresora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class InsumoImpresoraController extends Controller
{
    private const FAMILIAS = ['Tóner', 'Cartucho', 'Cinta'];
    private const COLORES  = ['Negro', 'Cyan', 'Magenta', 'Amarillo', 'Tricolor', 'Otro'];

    // ─── INDEX ───────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = $this->aplicarFiltros($request);
        $insumos = $query->paginate(10);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'  => view('control_insumos.insumos_impresoras.partials.tabla', compact('insumos'))->render(),
                'links' => $insumos->links('pagination::bootstrap-4')->render(),
                'total' => $insumos->total(),
                'info'  => 'Mostrando ' . ($insumos->firstItem() ?? 0)
                           . ' a ' . ($insumos->lastItem() ?? 0)
                           . ' de ' . $insumos->total() . ' registros',
            ]);
        }

        return view('control_insumos.insumos_impresoras.index', [
            'insumos'  => $insumos,
            'familias' => self::FAMILIAS,
            'colores'  => self::COLORES,
        ]);
    }

    // ─── FILTROS COMPARTIDOS (index + imprimir) ───────────────────────────────
    private function aplicarFiltros(Request $request)
    {
        $buscar      = trim($request->get('buscar', ''));
        $familia     = $request->input('familia', []);
        $status      = $request->input('status', []);
        $fechaInicio = $request->get('fecha_inicio', '');
        $fechaFin    = $request->get('fecha_fin', '');

        $query = InsumoImpresora::orderBy('id_insumo_impresora', 'desc');

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('modelo',             'like', "%{$buscar}%")
                  ->orWhere('color',              'like', "%{$buscar}%")
                  ->orWhere('familia',            'like', "%{$buscar}%")
                  ->orWhere('modelos_compatibles','like', "%{$buscar}%");
            });
        }

        if (!empty($familia)) {
            $query->whereIn('familia', (array) $familia);
        }

        if (count((array) $status) > 0) {
            $query->whereIn('activo', array_map('intval', (array) $status));
        }

        if (!empty($fechaInicio)) {
            $query->where('fecha', '>=', $fechaInicio);
        }

        if (!empty($fechaFin)) {
            $query->where('fecha', '<=', $fechaFin);
        }

        return $query;
    }


    // ─── GUARDAR ─────────────────────────────────────────────────────────────
    public function guardar(Request $request)
    {
        $request->validate([
            'modelo'             => [
                'required',
                'string',
                'max:100',
                Rule::unique('insumos_impresoras', 'modelo')->where(function ($query) use ($request) {
                    return $query->where('color', trim($request->color))
                                 ->where('familia', trim($request->familia));
                }),
            ],
            'color'              => 'required|string|max:50',
            'familia'            => 'required|string|max:50',
            'modelos_compatibles'=> 'nullable|string|max:500',
            'tiempo_uso'         => 'nullable|string|max:100',
            'hojas_uso_total'    => 'nullable|integer|min:1',
            'stock_inicial'      => 'nullable|integer|min:0',
        ], [
            'modelo.unique' => 'Este insumo (modelo, color y tipo) ya se encuentra registrado en el sistema.',
        ]);

        InsumoImpresora::create([
            'modelo'             => trim($request->modelo),
            'color'              => trim($request->color),
            'familia'            => trim($request->familia),
            'modelos_compatibles'=> $request->filled('modelos_compatibles') ? trim($request->modelos_compatibles) : null,
            'tiempo_uso'         => $request->filled('tiempo_uso')          ? trim($request->tiempo_uso)          : null,
            'hojas_uso_total'    => $request->hojas_uso_total,
            'stock'              => (int) ($request->stock_inicial ?? 0),
            'activo'             => 1,
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'id_usuario'         => Auth::id(),
        ]);

        return redirect()
            ->route('insumos_impresoras.index')
            ->with('exitog', 'El insumo ha sido registrado correctamente.');
    }

    // ─── EDITAR (formulario) ─────────────────────────────────────────────────
    public function editar(int $id)
    {
        $insumo = InsumoImpresora::findOrFail($id);

        return view('control_insumos.insumos_impresoras.editar', [
            'insumo'   => $insumo,
            'familias' => self::FAMILIAS,
            'colores'  => self::COLORES,
        ]);
    }

    // ─── ACTUALIZAR ──────────────────────────────────────────────────────────
    public function actualizar(Request $request, int $id)
    {
        $insumo = InsumoImpresora::findOrFail($id);

        $request->validate([
            'modelo'             => [
                'required',
                'string',
                'max:100',
                Rule::unique('insumos_impresoras', 'modelo')
                    ->where(function ($query) use ($request) {
                        return $query->where('color', trim($request->color))
                                     ->where('familia', trim($request->familia));
                    })
                    ->ignore($id, 'id_insumo_impresora'),
            ],
            'color'              => 'required|string|max:50',
            'familia'            => 'required|string|max:50',
            'modelos_compatibles'=> 'nullable|string|max:500',
            'tiempo_uso'         => 'nullable|string|max:100',
            'hojas_uso_total'    => 'nullable|integer|min:1',
            'stock'              => 'nullable|integer|min:0',
        ], [
            'modelo.unique' => 'Ya existe otro insumo registrado con este mismo modelo, color y tipo.',
        ]);

        $insumo->update([
            'modelo'             => trim($request->modelo),
            'color'              => trim($request->color),
            'familia'            => trim($request->familia),
            'modelos_compatibles'=> $request->filled('modelos_compatibles') ? trim($request->modelos_compatibles) : null,
            'tiempo_uso'         => $request->filled('tiempo_uso')          ? trim($request->tiempo_uso)          : null,
            'hojas_uso_total'    => $request->hojas_uso_total,
            'stock'              => (int) ($request->stock ?? $insumo->stock),
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'id_usuario'         => Auth::id(),
        ]);

        return redirect()
            ->route('insumos_impresoras.index')
            ->with('exito', 'El insumo ha sido actualizado correctamente.');
    }

    // ─── CAMBIAR STATUS (AJAX) ────────────────────────────────────────────────
    public function cambiarStatus(int $id)
    {
        $insumo         = InsumoImpresora::findOrFail($id);
        $insumo->activo = ($insumo->activo == 1) ? 0 : 1;
        $insumo->fecha  = now()->toDateString();
        $insumo->hora   = now()->toTimeString();
        $insumo->id_usuario = Auth::id();
        $insumo->save();

        return response()->json([
            'success' => true,
            'activo'  => $insumo->activo,
            'message' => 'El estatus del insumo ha sido actualizado.',
        ]);
    }

    // ─── BUSCAR INSUMO (AJAX para el módulo de Movimientos) ──────────────────
    public function buscar(Request $request)
    {
        $q = trim($request->get('q', ''));

        $insumos = InsumoImpresora::where('activo', 1)
            ->where(function ($query) use ($q) {
                $query->where('modelo','like', "%{$q}%")
                      ->orWhere('color', 'like', "%{$q}%");
            })
            ->select('id_insumo_impresora', 'modelo', 'color',
                     'familia', 'modelos_compatibles', 'tiempo_uso',
                     'hojas_uso_total', 'stock')
            ->orderBy('modelo')
            ->limit(15)
            ->get();

        return response()->json($insumos);
    }

    // ─── EXPORTAR EXCEL ───────────────────────────────────────────────────────
    public function exportarExcel(Request $request)
    {
        // Reutiliza el mismo helper de filtros que comparten index() e imprimir().
        $insumos  = $this->aplicarFiltros($request)->orderBy('modelo')->get();
        $filename = 'Reporte_Catalogo_Insumos_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new InsumosImpresorasExport($insumos), $filename);
    }

    // ─── REPORTE (impresión) ──────────────────────────────────────────
    public function imprimir(Request $request)
    {
        $insumos = $this->aplicarFiltros($request)->orderBy('modelo')->get();
        return view('control_insumos.insumos_impresoras.analitica.reportes.impresion', compact('insumos'));
    }
}
