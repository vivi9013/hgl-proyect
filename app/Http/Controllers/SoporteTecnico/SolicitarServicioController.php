<?php

namespace App\Http\Controllers\SoporteTecnico;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\SoporteTecnico\Servicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitarServicioController extends Controller
{
    // ─── INDEX — Catálogo de áreas de soporte con solicitudes pendientes ─────────
    public function index()
    {
        $personaId = Auth::user()->id_persona;

        // Obtenemos todas las áreas activas con su conteo de servicios pendientes
        // del usuario autenticado
        $areas = Area::where('activo', 1)
            ->withCount([
                'serviciosPendientes as pendientes_count' => function ($q) use ($personaId) {
                    $q->where('id_personaSolicitante', $personaId)
                      ->where('liberado', 0);
                }
            ])
            ->orderBy('area')
            ->get();

        return view('soporte_tecnico.solicitar_servicio.index', compact('areas'));
    }

    // ─── STORE — Registrar nueva solicitud de servicio ───────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|min:10|max:2000',
            'id_area'     => 'required|integer|exists:areas,id',
        ]);

        $user      = Auth::user();
        $personaId = $user->id_persona;
        $ahora     = now();

        // Obtenemos los datos del solicitante a través de sus relaciones
        $datosPersona = Persona::select(
            'personas.id',
            DB::raw("CONCAT(personas.nombre, ' ', personas.ap_paterno, ' ', personas.ap_materno) AS nombre_completo"),
            'personas.sexo',
            'trabajadores.id_departamento',
            'departamentos.nombre AS nombre_departamento',
            'departamentos.extension',
            'trabajadores.id_sede',
            'sedes.nombre   AS nombre_sede',
            'sedes.abreviatura AS abre_sede',
            'sedes.id       AS id_sede_real',
        )
        ->join('trabajadores',  'personas.id', '=', 'trabajadores.id_persona')
        ->join('departamentos', 'trabajadores.id_departamento', '=', 'departamentos.id')
        ->join('sedes',         'trabajadores.id_sede',         '=', 'sedes.id')
        ->where('personas.id', $personaId)
        ->first();

        if (!$datosPersona) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'No se encontraron los datos del trabajador.'], 422);
            }
            return back()->withErrors(['general' => 'No se encontraron los datos del trabajador.']);
        }

        Servicio::create([
            'id_usc'               => $user->id,
            'id_personaSolicitante'=> $personaId,
            'fecha_peticion'       => $ahora->toDateString(),
            'hora_peticion'        => $ahora->toTimeString(),
            'id_departamento'      => $datosPersona->id_departamento,
            'departamento'         => $datosPersona->nombre_departamento,
            'descripcion_servicio' => trim($request->descripcion),
            'id_area'              => $request->id_area,
            'pendiente'            => 1,
            'proceso'              => 0,
            'terminado'            => 0,
            'liberado'             => 0,
            'nombre_solicitante'   => $datosPersona->nombre_completo,
            'sexo_solicitante'     => $datosPersona->sexo,
            'ext_telefonica'       => $datosPersona->extension,
            'sede'                 => $datosPersona->nombre_sede,
            'abre_sede'            => $datosPersona->abre_sede,
            'id_sede'              => $datosPersona->id_sede_real,
            'modificado'           => 0,
            'modificadox'          => 'Nadie',
            'motivo_modificado'    => 'Ninguno',
            'fecha_modificado'     => '0000-00-00',
            'hora_modificado'      => '00:00:00',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Solicitud generada correctamente.']);
        }

        return redirect()->route('solicitar_servicio.index')->with('exitog', 'Solicitud generada correctamente.');
    }

    // ─── SEGUIMIENTO — Listado de servicios activos (sin liberar) ────────────────
    public function seguimiento(Request $request)
    {
        $personaId = Auth::user()->id_persona;
        $buscar    = trim($request->get('buscar', ''));

        $query = Servicio::with('area')
            ->where('id_personaSolicitante', $personaId)
            ->where('liberado', 0)
            ->orderByDesc('id');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion_servicio', 'like', "%{$buscar}%")
                  ->orWhereHas('area', fn ($a) => $a->where('area', 'like', "%{$buscar}%"))
                  ->orWhere('id', 'like', "%{$buscar}%");
            });
        }

        if ($request->ajax() || $request->wantsJson()) {
            $servicios = $query->paginate(15);
            return response()->json([
                'html'  => view('soporte_tecnico.solicitar_servicio.partials.tabla_pendientes', compact('servicios'))->render(),
                'links' => $servicios->links('pagination::bootstrap-4')->render(),
                'total' => $servicios->total(),
                'info'  => 'Mostrando ' . ($servicios->firstItem() ?? 0)
                           . ' a ' . ($servicios->lastItem() ?? 0)
                           . ' de ' . $servicios->total() . ' registros',
            ]);
        }

        $servicios = $query->paginate(15);
        return view('soporte_tecnico.solicitar_servicio.seguimiento', compact('servicios'));
    }

    // ─── HISTORIAL — Servicios liberados o cancelados ────────────────────────────
    public function historial(Request $request)
    {
        $personaId = Auth::user()->id_persona;
        $buscar    = trim($request->get('buscar', ''));

        $query = Servicio::with('area')
            ->where('id_personaSolicitante', $personaId)
            ->whereIn('estatus_final', ['Liberado', 'Cancelado'])
            ->orderByDesc('id');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion_servicio', 'like', "%{$buscar}%")
                  ->orWhereHas('area', fn ($a) => $a->where('area', 'like', "%{$buscar}%"))
                  ->orWhere('id', 'like', "%{$buscar}%")
                  ->orWhere('estatus_final', 'like', "%{$buscar}%");
            });
        }

        if ($request->ajax() || $request->wantsJson()) {
            $servicios = $query->paginate(15);
            return response()->json([
                'html'  => view('soporte_tecnico.solicitar_servicio.partials.tabla_historial', compact('servicios'))->render(),
                'links' => $servicios->links('pagination::bootstrap-4')->render(),
                'total' => $servicios->total(),
                'info'  => 'Mostrando ' . ($servicios->firstItem() ?? 0)
                           . ' a ' . ($servicios->lastItem() ?? 0)
                           . ' de ' . $servicios->total() . ' registros',
            ]);
        }

        $servicios = $query->paginate(15);
        return view('soporte_tecnico.solicitar_servicio.historial', compact('servicios'));
    }

    // ─── LIBERAR — Marca el servicio como liberado por el cliente ────────────────
    public function liberar(Request $request, int $id)
    {
        $personaId = Auth::user()->id_persona;
        $servicio  = Servicio::where('id', $id)
                              ->where('id_personaSolicitante', $personaId)
                              ->firstOrFail();

        // Solo se puede liberar si está terminado
        if (!$servicio->terminado) {
            return response()->json(['error' => 'El servicio aún no ha sido terminado.'], 422);
        }

        $ahora = now();
        $servicio->update([
            'liberado'     => 1,
            'estatus_final'=> 'Liberado',
            'fecha_finaliza'=> $ahora->toDateString(),
            'hora_finaliza' => $ahora->toTimeString(),
            'liberadox'    => 'Cliente',
        ]);

        return response()->json(['success' => true, 'message' => 'El servicio ha sido liberado correctamente.']);
    }

    // ─── DETALLES — Retorna info completa de un servicio para los modales ────────
    public function detalles(int $id)
    {
        $personaId = Auth::user()->id_persona;
        $servicio  = Servicio::with('area')
                              ->where('id_personaSolicitante', $personaId)
                              ->findOrFail($id);

        return response()->json([
            'id'                  => $servicio->id,
            'area'                => $servicio->area ? $servicio->area->area : '—',
            'descripcion'         => $servicio->descripcion_servicio,
            'fecha_peticion'      => $servicio->fecha_peticion
                                     ? \Carbon\Carbon::parse($servicio->fecha_peticion)->format('d-m-Y')
                                     : '—',
            'hora_peticion'       => $servicio->hora_peticion
                                     ? \Carbon\Carbon::parse($servicio->hora_peticion)->format('h:i a')
                                     : '—',
            'nombre_servidor'     => $servicio->nombre_servidor ?? 'El servicio aún no ha sido elegido',
            'ext_servidor'        => $this->obtenerExtServidor($servicio->id_personaServidor),
            'fecha_tomado'        => $servicio->fecha_tomado
                                     ? \Carbon\Carbon::parse($servicio->fecha_tomado)->format('d-m-Y')
                                     : '—',
            'hora_tomado'         => $servicio->hora_tomado
                                     ? \Carbon\Carbon::parse($servicio->hora_tomado)->format('h:i a')
                                     : '—',
            'fecha_termino'       => $servicio->fecha_termino
                                     ? \Carbon\Carbon::parse($servicio->fecha_termino)->format('d-m-Y')
                                     : '—',
            'hora_termino'        => $servicio->hora_termino
                                     ? \Carbon\Carbon::parse($servicio->hora_termino)->format('h:i a')
                                     : '—',
            'clasificacion'       => $servicio->clasificacion_servicio ?? '—',
            'accion_realizada'    => $servicio->accion_realizada ?? '—',
            'tipo_servicio'       => $servicio->tipo_servicio ?? '—',
            'pendiente'           => (bool) $servicio->pendiente,
            'proceso'             => (bool) $servicio->proceso,
            'terminado'           => (bool) $servicio->terminado,
        ]);
    }

    /**
     * Obtiene la extensión del técnico de soporte desde la tabla de trabajadores.
     */
    private function obtenerExtServidor(?int $idPersonaServidor): string
    {
        if (!$idPersonaServidor) return '—';

        $ext = DB::table('personas')
            ->join('trabajadores',  'personas.id', '=', 'trabajadores.id_persona')
            ->join('departamentos', 'trabajadores.id_departamento', '=', 'departamentos.id')
            ->where('personas.id', $idPersonaServidor)
            ->value('departamentos.extension');

        return $ext ?? '—';
    }

    // ─── REPORTES — Vista hub de reportes ────────────────────────────────────────
    public function reportes()
    {
        return view('soporte_tecnico.solicitar_servicio.analitica.reportes.index');
    }

    // ─── IMPRIMIR — Reporte de servicios para impresión ─────────────────────────
    public function imprimirReporte(Request $request)
    {
        $personaId  = Auth::user()->id_persona;
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');
        $estado     = $request->get('estado', 'todos');

        $query = Servicio::with('area')
            ->where('id_personaSolicitante', $personaId)
            ->orderByDesc('id')
            ->limit(500);

        // Filtro de estado
        if ($estado === 'activos') {
            $query->where('liberado', 0);
        } elseif ($estado === 'liberados') {
            $query->where('estatus_final', 'Liberado');
        } elseif ($estado === 'cancelados') {
            $query->where('estatus_final', 'Cancelado');
        }

        // Filtro por rango de fechas
        if ($fechaDesde) {
            $query->whereDate('fecha_peticion', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('fecha_peticion', '<=', $fechaHasta);
        }

        $servicios = $query->get();

        return view('soporte_tecnico.solicitar_servicio.analitica.reportes.impresion', compact('servicios', 'fechaDesde', 'fechaHasta', 'estado'));
    }

    // ─── GRÁFICAS — Datos analíticos para Chart.js ───────────────────────────────
    public function graficas()
    {
        $personaId = Auth::user()->id_persona;

        // Estadísticas generales
        $total     = Servicio::where('id_personaSolicitante', $personaId)->count();
        $activos   = Servicio::where('id_personaSolicitante', $personaId)->where('liberado', 0)->count();
        $liberados = Servicio::where('id_personaSolicitante', $personaId)->where('estatus_final', 'Liberado')->count();
        $cancelados= Servicio::where('id_personaSolicitante', $personaId)->where('estatus_final', 'Cancelado')->count();

        // Por área
        $porArea = Servicio::select('areas.area', DB::raw('COUNT(servicios.id) as total'))
            ->join('areas', 'servicios.id_area', '=', 'areas.id')
            ->where('servicios.id_personaSolicitante', $personaId)
            ->groupBy('areas.area')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Por mes (últimos 12 meses)
        $porMes = Servicio::select(
                DB::raw("DATE_FORMAT(fecha_peticion, '%Y-%m') AS mes"),
                DB::raw('COUNT(id) AS total')
            )
            ->where('id_personaSolicitante', $personaId)
            ->where('fecha_peticion', '>=', now()->subMonths(12)->toDateString())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return view('soporte_tecnico.solicitar_servicio.analitica.graficas', compact(
            'total', 'activos', 'liberados', 'cancelados', 'porArea', 'porMes'
        ));
    }
}
