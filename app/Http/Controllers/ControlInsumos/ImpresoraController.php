<?php

namespace App\Http\Controllers\ControlInsumos;

use App\Http\Controllers\Controller;
use App\Models\ControlInsumos\Impresora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImpresoraController extends Controller
{
    // ─── Opciones de catálogo (constantes de negocio) ────────────────────────
    private const TIPOS       = ['Lasser', 'Inyeccion de tinta', 'Matriz de Puntos', 'Termica'];
    private const CONSUMIBLES = ['Tonner', 'Cartucho', 'Cinta'];
    private const RED         = ['Si', 'No'];
    private const COMODATO    = ['Si', 'No'];

    // ─── Inventario disponible (mobiliario tipo impresora sin asignar) ────────
    private function inventarioDisponible(?int $excluirId = null)
    {
        return DB::table('mobiliario')
            ->join('tipo_mobiliario', 'mobiliario.id_tipo_mobiliario', '=', 'tipo_mobiliario.id')
            ->leftJoin('impresoras', 'mobiliario.inventario', '=', 'impresoras.inventario')
            ->where('tipo_mobiliario.tipo', 'like', '%impre%')
            ->where('mobiliario.activo', 1)
            ->where(function ($q) use ($excluirId) {
                // Permite que el registro actual siga mostrando su inventario al editar
                $q->whereNull('impresoras.id_impresora');
                if ($excluirId) {
                    $q->orWhere('impresoras.id_impresora', $excluirId);
                }
            })
            ->select('mobiliario.inventario')
            ->orderBy('mobiliario.inventario')
            ->pluck('mobiliario.inventario');
    }

    // ─── INDEX ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $query = Impresora::orderBy('id_impresora', 'desc');

        if (!empty($buscar)) {
            $b = trim($buscar);
            $query->where(function ($q) use ($b) {
                $q->where('serie',       'like', "%{$b}%")
                  ->orWhere('marca',       'like', "%{$b}%")
                  ->orWhere('modelo',      'like', "%{$b}%")
                  ->orWhere('ip',          'like', "%{$b}%")
                  ->orWhere('inventario',  'like', "%{$b}%")
                  ->orWhere('descripcion', 'like', "%{$b}%");
            });
        }

        $impresoras = $query->paginate(10);

        // Respuesta AJAX (paginación asíncrona / búsqueda reactiva)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html'  => view('admin_mobiliario.impresoras.partials.tabla', compact('impresoras'))->render(),
                'links' => $impresoras->links('pagination::bootstrap-4')->render(),
                'total' => $impresoras->total(),
                'info'  => 'Mostrando ' . ($impresoras->firstItem() ?? 0)
                           . ' a ' . ($impresoras->lastItem() ?? 0)
                           . ' de ' . $impresoras->total() . ' registros',
            ]);
        }

        $inventario = $this->inventarioDisponible();

        return view('admin_mobiliario.impresoras.index', compact(
            'impresoras',
            'inventario',
        ) + ['tipos' => self::TIPOS, 'consumibles' => self::CONSUMIBLES, 'redOpts' => self::RED, 'comodatoOpts' => self::COMODATO]);
    }

    // ─── GUARDAR ─────────────────────────────────────────────────────────────
    public function guardar(Request $request)
    {
        $request->validate([
            'inventario'  => 'required|string|max:50|unique:impresoras,inventario',
            'tipo'        => 'required|string|max:50',
            'serie'       => 'required|string|max:100',
            'modelo'      => 'nullable|string|max:100',
            'marca'       => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'tecnologia'  => 'nullable|string|max:100',
            'consumible'  => 'required|string|max:50',
            'red'         => 'required|in:Si,No',
            'ip'          => 'nullable|string|max:50',
            'comodato'    => 'required|in:Si,No',
        ], [
            'inventario.unique' => 'Este número de inventario ya está registrado en una impresora.',
        ]);

        Impresora::create([
            'inventario'  => trim($request->inventario),
            'tipo'        => trim($request->tipo),
            'serie'       => trim($request->serie),
            'modelo'      => trim($request->modelo ?? ''),
            'marca'       => trim($request->marca),
            'descripcion' => trim($request->descripcion ?? ''),
            'tecnologia'  => trim($request->tecnologia ?? ''),
            'consumible'  => trim($request->consumible),
            'red'         => trim($request->red),
            'ip'          => trim($request->ip ?? ''),
            'comodato'    => trim($request->comodato),
            'fecha'       => now()->toDateString(),
            'hora'        => now()->toTimeString(),
            'usuario'     => Auth::id(),
            'activo'      => 1,
        ]);

        return redirect()
            ->route('impresoras.index')
            ->with('exitog', 'La impresora se ha registrado correctamente.');
    }

    // ─── EDITAR (formulario) ─────────────────────────────────────────────────
    public function editar(int $id)
    {
        $impresora  = Impresora::findOrFail($id);
        $inventario = $this->inventarioDisponible($id);

        return view('admin_mobiliario.impresoras.editar', compact('impresora', 'inventario')
            + ['tipos' => self::TIPOS, 'consumibles' => self::CONSUMIBLES, 'redOpts' => self::RED, 'comodatoOpts' => self::COMODATO]);
    }

    // ─── ACTUALIZAR ──────────────────────────────────────────────────────────
    public function actualizar(Request $request, int $id)
    {
        $impresora = Impresora::findOrFail($id);

        $request->validate([
            'tipo'        => 'required|string|max:50',
            'serie'       => 'required|string|max:100',
            'modelo'      => 'nullable|string|max:100',
            'marca'       => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'tecnologia'  => 'nullable|string|max:100',
            'consumible'  => 'required|string|max:50',
            'red'         => 'required|in:Si,No',
            'ip'          => 'nullable|string|max:50',
            'comodato'    => 'required|in:Si,No',
        ]);

        $impresora->update([
            'tipo'        => trim($request->tipo),
            'serie'       => trim($request->serie),
            'modelo'      => trim($request->modelo ?? ''),
            'marca'       => trim($request->marca),
            'descripcion' => trim($request->descripcion ?? ''),
            'tecnologia'  => trim($request->tecnologia ?? ''),
            'consumible'  => trim($request->consumible),
            'red'         => trim($request->red),
            'ip'          => trim($request->ip ?? ''),
            'comodato'    => trim($request->comodato),
            'fecha'       => now()->toDateString(),
            'hora'        => now()->toTimeString(),
            'usuario'     => Auth::id(),
        ]);

        return redirect()
            ->route('impresoras.index')
            ->with('exito', 'La impresora se ha actualizado correctamente.');
    }

    // ─── CAMBIAR ESTATUS (AJAX) ───────────────────────────────────────────────
    public function cambiarStatus(int $id)
    {
        $impresora         = Impresora::findOrFail($id);
        $impresora->activo = ($impresora->activo == 1) ? 0 : 1;
        $impresora->fecha  = now()->toDateString();
        $impresora->hora   = now()->toTimeString();
        $impresora->usuario = Auth::id();
        $impresora->save();

        return response()->json([
            'success' => true,
            'activo'  => $impresora->activo,
            'message' => 'El estatus de la impresora ha sido actualizado.',
        ]);
    }

    // ─── VERIFICAR IP (AJAX - validación en vivo) ─────────────────────────────
    public function verificarIp(Request $request)
    {
        $ip        = trim($request->get('ip', ''));
        $excluirId = $request->get('excluir');

        if (empty($ip)) {
            return response()->json(['disponible' => true]);
        }

        $query = Impresora::where('ip', $ip);
        if ($excluirId) {
            $query->where('id_impresora', '!=', $excluirId);
        }

        return response()->json(['disponible' => $query->doesntExist()]);
    }

    // ─── REPORTES (panel de estadísticas) ────────────────────────────────────
    public function reportes()
    {
        $stats = [
            'total'     => Impresora::count(),
            'activas'   => Impresora::where('activo', 1)->count(),
            'inactivas' => Impresora::where('activo', 0)->count(),
            'en_red'    => Impresora::where('red', 'Si')->where('activo', 1)->count(),
        ];

        return view('admin_mobiliario.impresoras.analitica.reportes.index', compact('stats'));
    }

    // ─── IMPRIMIR (reporte imprimible) ───────────────────────────────────────
    public function imprimir()
    {
        $impresoras = Impresora::orderBy('id_impresora', 'desc')->get();

        return view('admin_mobiliario.impresoras.analitica.reportes.impresion', compact('impresoras'));
    }

    // ─── GRÁFICAS ─────────────────────────────────────────────────────────────
    public function graficas()
    {
        $stats = [
            'total'     => Impresora::count(),
            'activas'   => Impresora::where('activo', 1)->count(),
            'inactivas' => Impresora::where('activo', 0)->count(),
            'en_red'    => Impresora::where('red', 'Si')->where('activo', 1)->count(),
        ];

        // Agrupado por tecnología
        $porTecnologia = Impresora::selectRaw('tecnologia, COUNT(*) as total')
            ->whereNotNull('tecnologia')
            ->where('tecnologia', '!=', '')
            ->groupBy('tecnologia')
            ->orderBy('total', 'desc')
            ->pluck('total', 'tecnologia');

        // Agrupado por tipo
        $porTipo = Impresora::selectRaw('tipo, COUNT(*) as total')
            ->whereNotNull('tipo')
            ->groupBy('tipo')
            ->orderBy('total', 'desc')
            ->pluck('total', 'tipo');

        return view('admin_mobiliario.impresoras.analitica.graficas', compact(
            'stats',
            'porTecnologia',
            'porTipo',
        ));
    }
}
