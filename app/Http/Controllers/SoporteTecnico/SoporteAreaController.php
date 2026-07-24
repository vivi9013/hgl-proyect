<?php

namespace App\Http\Controllers\SoporteTecnico;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Persona;
use App\Models\SoporteArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SoporteAreaController extends Controller
{
    // ─── INDEX — listado de trabajadores con conteo de áreas asignadas ────────
    public function index(Request $request)
    {
        $buscar  = trim($request->get('buscar', ''));
        $estatus = $request->input('estatus', []);   // [] = todos, [1] = activos, [0] = inactivos
        $areas   = $request->input('areas', []);     // [] = todos, ['con'] = con áreas, ['sin'] = sin áreas

        $query = Persona::select(
                'personas.id',
                DB::raw("CONCAT(personas.ap_paterno, ' ', personas.ap_materno, ' ', personas.nombre) AS nombre_completo"),
                'personas.activo',
                DB::raw('(SELECT COUNT(id) FROM soporte_area WHERE soporte_area.id_persona = personas.id) AS cantidad_areas')
            )
            ->join('trabajadores', 'personas.id', '=', 'trabajadores.id_persona')
            ->where('trabajadores.activo', 1)
            ->orderByDesc('personas.activo')
            ->orderBy('personas.ap_paterno')
            ->orderBy('personas.ap_materno');

        // Filtro de búsqueda por nombre
        if ($buscar !== '') {
            $query->where(
                DB::raw("CONCAT(personas.ap_paterno, ' ', personas.ap_materno, ' ', personas.nombre)"),
                'like',
                "%{$buscar}%"
            );
        }

        // Filtro por estatus (activo/inactivo)
        if (!empty($estatus)) {
            $query->whereIn('personas.activo', array_map('intval', $estatus));
        }

        // Filtro por áreas asignadas
        if (!empty($areas) && count($areas) < 2) {
            if (in_array('con', $areas)) {
                $query->having('cantidad_areas', '>', 0);
            } elseif (in_array('sin', $areas)) {
                $query->having('cantidad_areas', '=', 0);
            }
        }

        // ── Respuesta AJAX (paginación) ──────────────────────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            $trabajadores = $query->paginate(15);

            return response()->json([
                'html'  => view('soporte_tecnico.ligar_usuario.partials.tabla', [
                    'trabajadores' => $trabajadores,
                    'soloCuerpo'   => true,
                ])->render(),
                'links' => $trabajadores->links('pagination::bootstrap-4')->render(),
                'total' => $trabajadores->total(),
                'info'  => 'Mostrando ' . ($trabajadores->firstItem() ?? 0)
                           . ' a ' . ($trabajadores->lastItem() ?? 0)
                           . ' de ' . $trabajadores->total() . ' registros',
            ]);
        }

        // ── Carga inicial (SSR) ─────────────────────────────────────────────
        $trabajadores = $query->paginate(15);
        return view('soporte_tecnico.ligar_usuario.index', compact('trabajadores'));
    }

    // ─── ASIGNAR ÁREAS (GET) — vista de asignación para un trabajador ─────────
    public function asignarAreas(int $id)
    {
        $persona = Persona::findOrFail($id);

        $areas = Area::where('activo', 1)
            ->orderBy('area')
            ->get()
            ->map(function ($area) use ($id) {
                $area->asignada = SoporteArea::where('id_area', $area->id)
                    ->where('id_persona', $id)
                    ->exists();
                return $area;
            });

        return view('soporte_tecnico.ligar_usuario.asignar_areas', compact('persona', 'areas'));
    }

    // ─── SINCRONIZAR ÁREAS (POST) — toggle masivo por checkbox ──────────────
    public function sincronizarAreas(Request $request, int $id)
    {
        $persona = Persona::findOrFail($id);

        // IDs de áreas marcadas en el formulario (puede ser vacío)
        $areasSeleccionadas = collect($request->input('areas', []));

        // Todas las áreas activas disponibles
        $todasAreas = Area::where('activo', 1)->pluck('id');

        DB::transaction(function () use ($persona, $areasSeleccionadas, $todasAreas) {
            $ahora    = now();
            $fecha    = $ahora->toDateString();
            $hora     = $ahora->toTimeString();
            $usuario  = Auth::id();

            foreach ($todasAreas as $idArea) {
                $existe = SoporteArea::where('id_area', $idArea)
                    ->where('id_persona', $persona->id)
                    ->exists();

                $debeEstar = $areasSeleccionadas->contains((string) $idArea);

                if ($debeEstar && !$existe) {
                    // Insertar nueva asignación
                    SoporteArea::create([
                        'id_area'    => $idArea,
                        'id_persona' => $persona->id,
                        'fecha'      => $fecha,
                        'hora'       => $hora,
                        'activo'     => 1,
                        'usuario'    => $usuario,
                    ]);
                } elseif (!$debeEstar && $existe) {
                    // Eliminar asignación existente
                    SoporteArea::where('id_area', $idArea)
                        ->where('id_persona', $persona->id)
                        ->delete();
                }
            }
        });

        return redirect()
            ->route('soporte_area.asignar', $persona->id)
            ->with('exitog', 'Las áreas de soporte se han actualizado correctamente.');
    }

    // ─── CAMBIAR STATUS ──────────────────────────────────────────────────────
    public function cambiarStatus(int $id)
    {
        $persona = Persona::findOrFail($id);
        $persona->activo = $persona->activo == 1 ? 0 : 1;
        $persona->fecha  = now()->toDateString();
        $persona->hora   = now()->toTimeString();
        $persona->usuario = Auth::id();
        $persona->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo'  => $persona->activo,
                'message' => 'El estado del trabajador ha sido actualizado.'
            ]);
        }

        return redirect()->route('soporte_area.index')->with('exitog', 'Estado actualizado.');
    }

    // ─── REPORTE IMPRESIÓN ────────────────────────────────────────────────────
    public function imprimir()
    {
        $trabajadores = Persona::select(
                'personas.id',
                DB::raw("CONCAT(personas.ap_paterno, ' ', personas.ap_materno, ' ', personas.nombre) AS nombre_completo"),
                'personas.activo',
                DB::raw('(SELECT COUNT(id) FROM soporte_area WHERE soporte_area.id_persona = personas.id) AS cantidad_areas')
            )
            ->join('trabajadores', 'personas.id', '=', 'trabajadores.id_persona')
            ->where('trabajadores.activo', 1)
            ->orderByDesc('personas.activo')
            ->orderBy('personas.ap_paterno')
            ->orderBy('personas.ap_materno')
            ->limit(500)
            ->get();

        return view('soporte_tecnico.ligar_usuario.analitica.reportes.impresion', compact('trabajadores'));
    }
}
