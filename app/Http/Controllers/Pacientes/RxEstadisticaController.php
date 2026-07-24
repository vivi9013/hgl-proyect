<?php

namespace App\Http\Controllers\Pacientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EstudioRx;
use Carbon\Carbon;

class RxEstadisticaController extends Controller
{
    /**
     * Vista principal del panel de estadísticas y reportes
     */
    public function index()
    {
        $hoy = Carbon::today()->toDateString();
        return view('pacientes.estadisticas.index', compact('hoy'));
    }

    /**
     * Obtener estadísticas consolidadas en formato JSON
     */
    public function datos(Request $request)
    {
        $fi = $request->query('fi');
        $ff = $request->query('ff');

        if (!$fi) {
            $fi = Carbon::today()->toDateString();
        }
        if (!$ff) {
            $ff = Carbon::today()->toDateString();
        }

        // 1. Total general de estudios (sumatoria del campo total_estudios en el periodo)
        $totalEstudios = DB::table('estudios_rx')
            ->where('activo', 1)
            ->whereDate('fecha_estudio', '>=', $fi)
            ->whereDate('fecha_estudio', '<=', $ff)
            ->sum('total_estudios');

        // 2. Sumatoria por regiones anatómicas
        $regiones = DB::table('estudios_rx')
            ->where('activo', 1)
            ->whereDate('fecha_estudio', '>=', $fi)
            ->whereDate('fecha_estudio', '<=', $ff)
            ->select(
                DB::raw('COALESCE(SUM(craneo), 0) as craneo'),
                DB::raw('COALESCE(SUM(tx), 0) as torax'),
                DB::raw('COALESCE(SUM(abd), 0) as abdomen'),
                DB::raw('COALESCE(SUM(col), 0) as columna'),
                DB::raw('COALESCE(SUM(m_sup), 0) as m_sup'),
                DB::raw('COALESCE(SUM(m_inf), 0) as m_inf'),
                DB::raw('COALESCE(SUM(contraste), 0) as contraste')
            )
            ->first();

        // 3. Conteo de estudios por Técnico (agrupado por usuario)
        $tecnicos = DB::table('estudios_rx')
            ->join('usuarios', 'usuarios.id', '=', 'estudios_rx.usuario')
            ->join('personas', 'personas.id', '=', 'usuarios.id_persona')
            ->where('estudios_rx.activo', 1)
            ->whereDate('estudios_rx.fecha_estudio', '>=', $fi)
            ->whereDate('estudios_rx.fecha_estudio', '<=', $ff)
            ->select(
                DB::raw("CONCAT(personas.nombre, ' ', personas.ap_paterno) as nombre"),
                DB::raw("COUNT(estudios_rx.id_estudios) as cantidad")
            )
            ->groupBy('estudios_rx.usuario', 'personas.nombre', 'personas.ap_paterno')
            ->get();

        // 4. Conteo por género del paciente
        $generos = DB::table('estudios_rx')
            ->where('activo', 1)
            ->whereDate('fecha_estudio', '>=', $fi)
            ->whereDate('fecha_estudio', '<=', $ff)
            ->select(
                'sexo',
                DB::raw("COUNT(id_estudios) as cantidad")
            )
            ->groupBy('sexo')
            ->get();

        // Mapear sexos a etiquetas en español
        $generoDatos = [];
        $totalGeneros = $generos->sum('cantidad');
        foreach ($generos as $g) {
            $label = $g->sexo === 'M' ? 'Masculino' : ($g->sexo === 'F' ? 'Femenino' : 'Otro');
            $porcentaje = $totalGeneros > 0 ? round(($g->cantidad * 100) / $totalGeneros, 2) : 0;
            $generoDatos[] = [
                'name' => "$label ({$g->cantidad})",
                'y' => $porcentaje,
                'cantidad' => $g->cantidad
            ];
        }

        // Mapear técnicos a porcentajes para gráfico circular
        $tecnicosDatos = [];
        $totalTecnicos = $tecnicos->sum('cantidad');
        foreach ($tecnicos as $t) {
            $porcentaje = $totalTecnicos > 0 ? round(($t->cantidad * 100) / $totalTecnicos, 2) : 0;
            $tecnicosDatos[] = [
                'name' => "{$t->nombre} ({$t->cantidad})",
                'y' => $porcentaje,
                'cantidad' => $t->cantidad
            ];
        }

        return response()->json([
            'total_estudios' => (int) $totalEstudios,
            'regiones' => $regiones,
            'tecnicos' => $tecnicosDatos,
            'generos' => $generoDatos
        ]);
    }

    /**
     * Imprimir el reporte diario de estudios en formato apaisado
     */
    public function imprimir(Request $request)
    {
        $fi = $request->query('fi');
        $ff = $request->query('ff');

        if (!$fi) {
            $fi = Carbon::today()->toDateString();
        }
        if (!$ff) {
            $ff = Carbon::today()->toDateString();
        }

        $estudios = EstudioRx::with(['creador.persona'])
            ->where('activo', 1)
            ->whereDate('fecha_estudio', '>=', $fi)
            ->whereDate('fecha_estudio', '<=', $ff)
            ->orderBy('fecha_estudio', 'asc')
            ->orderBy('id_estudios', 'asc')
            ->get();

        return view('pacientes.estadisticas.reportes.impresion', compact('estudios', 'fi', 'ff'));
    }
}
