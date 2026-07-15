<?php

namespace App\Http\Controllers\ControlInsumos;

use App\Http\Controllers\Controller;
use App\Models\ControlInsumos\InsumoImpresora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InsumoImpresoraController extends Controller
{
    private const FAMILIAS = ['Tóner', 'Cartucho', 'Cinta'];
    private const COLORES  = ['Negro', 'Cyan', 'Magenta', 'Amarillo', 'Tricolor', 'Otro'];

    // ─── INDEX ───────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $query = InsumoImpresora::orderBy('id_insumo_impresora', 'desc');

        if (!empty($buscar)) {
            $b = trim($buscar);
            $query->where(function ($q) use ($b) {
                $q->where('modelo',             'like', "%{$b}%")
                  ->orWhere('color',              'like', "%{$b}%")
                  ->orWhere('familia',            'like', "%{$b}%")
                  ->orWhere('modelos_compatibles','like', "%{$b}%");
            });
        }

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

    // ─── GUARDAR ─────────────────────────────────────────────────────────────
    public function guardar(Request $request)
    {
        $request->validate([
            'modelo'             => 'required|string|max:100',
            'color'              => 'required|string|max:50',
            'familia'            => 'required|string|max:50',
            'modelos_compatibles'=> 'nullable|string|max:500',
            'tiempo_uso'         => 'nullable|string|max:100',
            'hojas_uso_total'    => 'nullable|integer|min:1',
        ]);

        InsumoImpresora::create([
            'modelo'             => trim($request->modelo),
            'color'              => trim($request->color),
            'familia'            => trim($request->familia),
            'modelos_compatibles'=> trim($request->modelos_compatibles ?? ''),
            'tiempo_uso'         => trim($request->tiempo_uso ?? ''),
            'hojas_uso_total'    => $request->hojas_uso_total,
            'stock'              => 0,
            'activo'             => 1,
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'usuario'            => Auth::id(),
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
            'modelo'             => 'required|string|max:100',
            'color'              => 'required|string|max:50',
            'familia'            => 'required|string|max:50',
            'modelos_compatibles'=> 'nullable|string|max:500',
            'tiempo_uso'         => 'nullable|string|max:100',
            'hojas_uso_total'    => 'nullable|integer|min:1',
        ]);

        $insumo->update([
            'modelo'             => trim($request->modelo),
            'color'              => trim($request->color),
            'familia'            => trim($request->familia),
            'modelos_compatibles'=> trim($request->modelos_compatibles ?? ''),
            'tiempo_uso'         => trim($request->tiempo_uso ?? ''),
            'hojas_uso_total'    => $request->hojas_uso_total,
            'fecha'              => now()->toDateString(),
            'hora'               => now()->toTimeString(),
            'usuario'            => Auth::id(),
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
        $insumo->usuario = Auth::id();
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

    // ─── REPORTE (impresión) ─────────────────────────────────────────────────
    public function imprimir()
    {
        $insumos = InsumoImpresora::orderBy('modelo')->get();
        return view('control_insumos.insumos_impresoras.analitica.reportes.impresion', compact('insumos'));
    }
}
