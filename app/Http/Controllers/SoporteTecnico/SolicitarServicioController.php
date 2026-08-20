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
        ], [
            'descripcion.required' => 'Debes describir el problema o servicio requerido.',
            'descripcion.min'      => 'La descripción debe tener al menos 10 caracteres para mayor claridad.',
            'id_area.required'     => 'Debes seleccionar el área correspondiente.',
            'id_area.exists'       => 'El área seleccionada no es válida.',
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
            'sedes.id       AS id_sede_real'
        )
        ->join('trabajadores',  'personas.id', '=', 'trabajadores.id_persona')
        ->join('departamentos', 'trabajadores.id_departamento', '=', 'departamentos.id')
        ->join('sedes',         'trabajadores.id_sede',         '=', 'sedes.id')
        ->where('personas.id', $personaId)
        ->where('trabajadores.activo', 1)
        ->first();

        if (!$datosPersona) {
            // Fallback si no tiene registro activo en trabajadores: buscar persona directamente
            $personaSimple = Persona::find($personaId);
            if (!$personaSimple) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'No se encontraron los datos del usuario en el sistema.'], 422);
                }
                return back()->withErrors(['general' => 'No se encontraron los datos del usuario en el sistema.']);
            }

            $nombreCompleto = trim($personaSimple->nombre . ' ' . $personaSimple->ap_paterno . ' ' . $personaSimple->ap_materno);
            $sexo = $personaSimple->sexo ?? 'M';

            // Datos por defecto o primer departamento/sede disponible
            $primerDep = Departamento::first();
            $primerSede = Sede::first();

            $idDepartamento = $primerDep ? $primerDep->id : 1;
            $nombreDepartamento = $primerDep ? $primerDep->nombre : 'General';
            $extTelefonica = $primerDep ? ($primerDep->extension ?? 'S/Ext') : 'S/Ext';
            $nombreSede = $primerSede ? $primerSede->nombre : 'Principal';
            $abreSede = $primerSede ? $primerSede->abreviatura : 'PRIN';
            $idSede = $primerSede ? $primerSede->id : 1;
        } else {
            $nombreCompleto = $datosPersona->nombre_completo;
            $sexo = $datosPersona->sexo;
            $idDepartamento = $datosPersona->id_departamento;
            $nombreDepartamento = $datosPersona->nombre_departamento;
            $extTelefonica = $datosPersona->extension ?? 'S/Ext';
            $nombreSede = $datosPersona->nombre_sede;
            $abreSede = $datosPersona->abre_sede;
            $idSede = $datosPersona->id_sede_real;
        }

        $servicio = Servicio::create([
            'id_usc'               => $user->id,
            'id_personaSolicitante'=> $personaId,
            'fecha_peticion'       => $ahora->toDateString(),
            'hora_peticion'        => $ahora->toTimeString(),
            'id_departamento'      => $idDepartamento,
            'departamento'         => $nombreDepartamento,
            'descripcion_servicio' => trim($request->descripcion),
            'id_area'              => $request->id_area,
            'pendiente'            => 1,
            'proceso'              => 0,
            'terminado'            => 0,
            'liberado'             => 0,
            'nombre_solicitante'   => $nombreCompleto,
            'sexo_solicitante'     => $sexo,
            'ext_telefonica'       => $extTelefonica,
            'sede'                 => $nombreSede,
            'abre_sede'            => $abreSede,
            'id_sede'              => $idSede,
            'modificado'           => 0,
            'modificadox'          => null,
            'motivo_modificado'    => null,
            'fecha_modificado'     => null,
            'hora_modificado'      => null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitud #' . $servicio->id . ' generada correctamente.',
                'folio'   => $servicio->id
            ]);
        }

        return redirect()->route('solicitar_servicio.seguimiento')->with('exitog', 'Solicitud #' . $servicio->id . ' generada correctamente.');
    }

    // ─── SEGUIMIENTO — Listado de servicios activos (sin liberar) ────────────────
    public function seguimiento(Request $request)
    {
        $personaId = Auth::user()->id_persona;
        $buscar    = trim($request->get('buscar', ''));

        $query = Servicio::with(['area', 'servidor'])
            ->where('id_personaSolicitante', $personaId)
            ->where('liberado', 0)
            ->where(function ($q) {
                $q->whereNull('estatus_final')
                  ->orWhere('estatus_final', '!=', 'Cancelado');
            })
            ->orderByDesc('id');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion_servicio', 'like', "%{$buscar}%")
                  ->orWhereHas('area', fn ($a) => $a->where('area', 'like', "%{$buscar}%"))
                  ->orWhere('id', 'like', "%{$buscar}%")
                  ->orWhere('nombre_servidor', 'like', "%{$buscar}%");
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

        $query = Servicio::with(['area', 'servidor'])
            ->where('id_personaSolicitante', $personaId)
            ->where(function ($q) {
                $q->where('liberado', 1)
                  ->orWhere('estatus_final', 'Cancelado');
            })
            ->orderByDesc('id');

        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion_servicio', 'like', "%{$buscar}%")
                  ->orWhereHas('area', fn ($a) => $a->where('area', 'like', "%{$buscar}%"))
                  ->orWhere('id', 'like', "%{$buscar}%")
                  ->orWhere('estatus_final', 'like', "%{$buscar}%")
                  ->orWhere('nombre_servidor', 'like', "%{$buscar}%");
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

    // ─── CANCELAR — Cancelar solicitud pendiente por parte del solicitante ───────
    public function cancelar(Request $request, int $id)
    {
        $personaId = Auth::user()->id_persona;
        $servicio  = Servicio::where('id', $id)
                             ->where('id_personaSolicitante', $personaId)
                             ->firstOrFail();

        // Solo se puede cancelar si sigue en estado pendiente y nadie lo ha tomado
        if ($servicio->proceso == 1 || $servicio->terminado == 1 || $servicio->liberado == 1) {
            return response()->json([
                'error' => 'No se puede cancelar la solicitud porque ya ha sido tomada o atendida por el personal técnico.'
            ], 422);
        }

        $motivo = trim($request->input('motivo', 'Cancelado por el solicitante'));
        $ahora = now();

        $servicio->update([
            'pendiente'         => 0,
            'estatus_final'     => 'Cancelado',
            'liberado'          => 1,
            'liberadox'         => 'Cliente (Cancelado)',
            'fecha_finaliza'    => $ahora->toDateString(),
            'hora_finaliza'     => $ahora->toTimeString(),
            'motivo_modificado' => $motivo,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'La solicitud #' . $id . ' ha sido cancelada correctamente.'
        ]);
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
            return response()->json(['error' => 'El servicio aún no ha sido concluido por el área de soporte.'], 422);
        }

        $ahora = now();
        $servicio->update([
            'liberado'      => 1,
            'estatus_final' => 'Liberado',
            'fecha_finaliza'=> $ahora->toDateString(),
            'hora_finaliza' => $ahora->toTimeString(),
            'liberadox'     => 'Cliente',
        ]);

        return response()->json(['success' => true, 'message' => 'El servicio #' . $id . ' ha sido liberado de conformidad.']);
    }

    // ─── DETALLES — Retorna info completa de un servicio para los modales ────────
    public function detalles(int $id)
    {
        $personaId = Auth::user()->id_persona;
        $servicio  = Servicio::with(['area', 'mobiliario'])
                              ->where('id_personaSolicitante', $personaId)
                              ->findOrFail($id);

        return response()->json([
            'id'                  => $servicio->id,
            'area'                => $servicio->area ? $servicio->area->area : '—',
            'area_color'          => $servicio->area ? ($servicio->area->color ?? 'bg-dark') : 'bg-dark',
            'descripcion'         => $servicio->descripcion_servicio,
            'fecha_peticion'      => $servicio->fecha_peticion
                                     ? \Carbon\Carbon::parse($servicio->fecha_peticion)->format('d-m-Y')
                                     : '—',
            'hora_peticion'       => $servicio->hora_peticion
                                     ? \Carbon\Carbon::parse($servicio->hora_peticion)->format('h:i A')
                                     : '—',
            'nombre_servidor'     => $servicio->nombre_servidor ?? 'Pendiente de asignación',
            'ext_servidor'        => $this->obtenerExtServidor($servicio->id_personaServidor),
            'fecha_tomado'        => $servicio->fecha_tomado
                                     ? \Carbon\Carbon::parse($servicio->fecha_tomado)->format('d-m-Y')
                                     : '—',
            'hora_tomado'         => $servicio->hora_tomado
                                     ? \Carbon\Carbon::parse($servicio->hora_tomado)->format('h:i A')
                                     : '—',
            'fecha_termino'       => $servicio->fecha_termino
                                     ? \Carbon\Carbon::parse($servicio->fecha_termino)->format('d-m-Y')
                                     : '—',
            'hora_termino'        => $servicio->hora_termino
                                     ? \Carbon\Carbon::parse($servicio->hora_termino)->format('h:i A')
                                     : '—',
            'fecha_finaliza'      => $servicio->fecha_finaliza
                                     ? \Carbon\Carbon::parse($servicio->fecha_finaliza)->format('d-m-Y')
                                     : '—',
            'hora_finaliza'       => $servicio->hora_finaliza
                                     ? \Carbon\Carbon::parse($servicio->hora_finaliza)->format('h:i A')
                                     : '—',
            'clasificacion'       => $servicio->clasificacion_servicio ?? '—',
            'accion_realizada'    => $servicio->accion_realizada ?? '—',
            'tipo_servicio'       => $servicio->tipo_servicio ?? '—',
            'inventario'          => $servicio->inventario ?? 'Sin equipo específico',
            'descripcion_mobiliario' => $servicio->descripcion_mobiliario ?? '—',
            'pendiente'           => (bool) $servicio->pendiente,
            'proceso'             => (bool) $servicio->proceso,
            'terminado'           => (bool) $servicio->terminado,
            'liberado'            => (bool) $servicio->liberado,
            'estatus_final'       => $servicio->estatus_final ?? ($servicio->liberado ? 'Liberado' : ($servicio->terminado ? 'Terminado' : ($servicio->proceso ? 'En Proceso' : 'Pendiente'))),
            'liberadox'           => $servicio->liberadox ?? '—',
            'dias_transcurridos'  => $servicio->dias_transcurridos,
        ]);
    }

    /**
     * Obtiene la extensión del técnico de soporte desde la tabla de departamentos del trabajador.
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
            $query->where('liberado', 0)->where(function ($q) {
                $q->whereNull('estatus_final')->orWhere('estatus_final', '!=', 'Cancelado');
            });
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
        $activos   = Servicio::where('id_personaSolicitante', $personaId)->where('liberado', 0)->where(fn($q)=>$q->whereNull('estatus_final')->orWhere('estatus_final','!=','Cancelado'))->count();
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
