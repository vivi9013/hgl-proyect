<?php

namespace App\Http\Controllers\SoporteTecnico;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\SoporteTecnico\TipoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TipoServicioController extends Controller
{
    // ─── INDEX — listado paginado con filtros AJAX ────────────────────────────
    public function index(Request $request)
    {
        $buscar  = trim($request->get('buscar', ''));
        $estatus = $request->input('estatus', []);  // [] = todos
        $areas   = $request->input('areas', []);    // [] = todas las áreas

        $query = TipoServicio::with('area')
            ->orderByDesc('activo')
            ->orderBy('servicio');

        // Filtro por texto (nombre del servicio o nombre del área)
        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('servicio', 'like', "%{$buscar}%")
                  ->orWhereHas('area', fn($a) => $a->where('area', 'like', "%{$buscar}%"));
            });
        }

        // Filtro por estatus
        if (!empty($estatus)) {
            $query->whereIn('activo', array_map('intval', $estatus));
        }

        // Filtro por área
        if (!empty($areas)) {
            $query->whereIn('id_area', array_map('intval', $areas));
        }

        // ── Respuesta AJAX (paginación) ───────────────────────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $servicios = $query->paginate(15);

            return response()->json([
                'html'  => view('soporte_tecnico.tipo_servicio.partials.tabla', [
                    'servicios'  => $servicios,
                    'soloCuerpo' => true,
                ])->render(),
                'links' => $servicios->links('pagination::bootstrap-4')->render(),
                'total' => $servicios->total(),
                'info'  => 'Mostrando ' . ($servicios->firstItem() ?? 0)
                           . ' a ' . ($servicios->lastItem() ?? 0)
                           . ' de ' . $servicios->total() . ' registros',
            ]);
        }

        // ── Carga inicial SSR ─────────────────────────────────────────────────
        $servicios = $query->paginate(15);
        $areasActivas = Area::where('activo', 1)->orderBy('area')->get();

        return view('soporte_tecnico.tipo_servicio.index', compact('servicios', 'areasActivas'));
    }

    // ─── STORE — alta de nuevo tipo de servicio ───────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'servicio' => 'required|string|min:3|max:500',
            'id_area'  => 'required|integer|exists:areas,id',
        ]);

        $servicio = trim($request->servicio);
        $idArea   = $request->id_area;

        // Verificar duplicado dentro del mismo área
        $existe = TipoServicio::where('servicio', $servicio)
            ->where('id_area', $idArea)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un tipo de servicio con ese nombre en el área seleccionada.',
                'errors'  => ['servicio' => ['Nombre duplicado en esta área.']],
            ], 422);
        }

        $ahora = now();

        TipoServicio::create([
            'servicio' => $servicio,
            'id_area'  => $idArea,
            'fecha'    => $ahora->toDateString(),
            'hora'     => $ahora->toTimeString(),
            'activo'   => 1,
            'usuario'  => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de servicio registrado correctamente.',
        ]);
    }

    // ─── UPDATE — edición de un tipo de servicio existente ───────────────────
    public function update(Request $request, int $id)
    {
        $tipoServicio = TipoServicio::findOrFail($id);

        $request->validate([
            'servicio' => 'required|string|min:3|max:500',
            'id_area'  => 'required|integer|exists:areas,id',
        ]);

        $servicio = trim($request->servicio);
        $idArea   = $request->id_area;

        // Verificar duplicado (excluyendo el registro actual)
        $existe = TipoServicio::where('servicio', $servicio)
            ->where('id_area', $idArea)
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un tipo de servicio con ese nombre en el área seleccionada.',
                'errors'  => ['servicio' => ['Nombre duplicado en esta área.']],
            ], 422);
        }

        $ahora = now();

        $tipoServicio->update([
            'servicio' => $servicio,
            'id_area'  => $idArea,
            'fecha'    => $ahora->toDateString(),
            'hora'     => $ahora->toTimeString(),
            'usuario'  => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de servicio actualizado correctamente.',
        ]);
    }

    // ─── CAMBIAR STATUS — toggle activo / inactivo ────────────────────────────
    public function cambiarStatus(int $id)
    {
        $tipoServicio = TipoServicio::findOrFail($id);
        $tipoServicio->activo  = $tipoServicio->activo == 1 ? 0 : 1;
        $tipoServicio->fecha   = now()->toDateString();
        $tipoServicio->hora    = now()->toTimeString();
        $tipoServicio->usuario = Auth::id();
        $tipoServicio->save();

        return response()->json([
            'success' => true,
            'activo'  => $tipoServicio->activo,
            'message' => 'Estado actualizado correctamente.',
        ]);
    }

    // ─── VERIFICAR — duplicado en tiempo real ─────────────────────────────────
    public function verificar(Request $request)
    {
        $servicio = trim($request->get('servicio', ''));
        $idArea   = $request->get('id_area');
        $excluirId = $request->get('excluir_id'); // para edición

        if ($servicio === '' || !$idArea) {
            return response()->json(['existe' => false]);
        }

        $query = TipoServicio::where('servicio', $servicio)
            ->where('id_area', $idArea);

        if ($excluirId) {
            $query->where('id', '!=', (int) $excluirId);
        }

        return response()->json(['existe' => $query->exists()]);
    }

    // ─── IMPRIMIR — reporte imprimible agrupado por área ─────────────────────
    public function imprimir()
    {
        $servicios = TipoServicio::with('area')
            ->orderBy('id_area')
            ->orderByDesc('activo')
            ->orderBy('servicio')
            ->get()
            ->groupBy(fn($s) => $s->area?->area ?? 'Sin Área');

        return view('soporte_tecnico.tipo_servicio.analitica.reportes.impresion', compact('servicios'));
    }
}
