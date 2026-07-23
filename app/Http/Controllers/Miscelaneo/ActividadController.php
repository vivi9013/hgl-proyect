<?php

namespace App\Http\Controllers\Miscelaneo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Miscelaneo\Actividad;
use Carbon\Carbon;

class ActividadController extends Controller
{
    /**
     * Listado principal y respuesta AJAX para la tabla
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $query = Actividad::with(['persona']);

            // Búsqueda libre (q)
            if ($q = $request->query('q')) {
                $query->where(function ($query) use ($q) {
                    $query->where('descripcion', 'like', "%{$q}%")
                          ->orWhere('filtro', 'like', "%{$q}%")
                          ->orWhereHas('persona', function ($query) use ($q) {
                              $query->where('nombre', 'like', "%{$q}%")
                                    ->orWhere('ap_paterno', 'like', "%{$q}%")
                                    ->orWhere('ap_materno', 'like', "%{$q}%");
                          });
                });
            }

            // Filtro por tipo de actividad específico
            if ($filtro = $request->query('filtro')) {
                $query->where('filtro', $filtro);
            }

            // Rango de fechas
            if ($fi = $request->query('fi')) {
                $query->where('fecha', '>=', $fi);
            }
            if ($ff = $request->query('ff')) {
                $query->where('fecha', '<=', $ff);
            }

            $actividades = $query->orderBy('id_actividad', 'desc')->paginate(15);
            return response()->json($actividades);
        }

        // Obtener filtros únicos para el combo/dropdown
        $filtros = DB::table('actividades')
            ->select('filtro')
            ->distinct()
            ->whereNotNull('filtro')
            ->where('filtro', '<>', '')
            ->pluck('filtro');

        $hoy = Carbon::today()->toDateString();

        return view('miscelaneo.actividades.index', compact('filtros', 'hoy'));
    }

    /**
     * Vista de gráficas
     */
    public function graficas()
    {
        $hoy = Carbon::today()->toDateString();
        return view('miscelaneo.actividades.graficas', compact('hoy'));
    }

    /**
     * Datos para generar las gráficas
     */
    public function datosGraficas(Request $request)
    {
        $fi = $request->query('fi', Carbon::today()->toDateString());
        $ff = $request->query('ff', Carbon::today()->toDateString());

        // Consulta de inicios de sesión por persona
        $datos = DB::table('actividades as A')
            ->join('personas as P', 'A.id_persona', '=', 'P.id')
            ->where('A.filtro', 'Inicio de Sesion')
            ->whereBetween('A.fecha', [$fi, $ff])
            ->select(
                DB::raw("COUNT(A.id_actividad) as value"),
                DB::raw("CONCAT(P.nombre, ' ', P.ap_paterno, ' ', COALESCE(P.ap_materno, '')) as label")
            )
            ->groupBy('A.id_persona', 'P.nombre', 'P.ap_paterno', 'P.ap_materno')
            ->orderBy('value', 'desc')
            ->get();

        return response()->json($datos);
    }
}
